<?php

namespace App\Filament\Resources\BoothResource\Pages;

use App\Filament\Resources\BoothResource;
use App\Models\Booth;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBooth extends CreateRecord
{
    protected static string $resource = BoothResource::class;

    /**
     * Override: simpan banyak record berdasarkan textarea "codes".
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Ambil & parse codes (comma / newline / spasi)
        $codes = collect(preg_split('/[\s,]+/u', trim($data['codes'] ?? '')))
            ->filter()
            ->map(fn ($c) => trim($c))
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            throw ValidationException::withMessages([
                'codes' => 'Kode booth tidak boleh kosong.',
            ]);
        }

        // Cek duplikasi di DB (unik per event)
        $exists = Booth::query()
            ->where('event_id', $data['event_id'])
            ->whereIn('code', $codes->all())
            ->pluck('code');

        if ($exists->isNotEmpty()) {
            throw ValidationException::withMessages([
                'codes' => 'Kode sudah ada: ' . $exists->implode(', '),
            ]);
        }

        // Siapkan rows untuk insert
        $now = now();
        $rows = $codes->map(function (string $code) use ($data, $now) {
            return [
                'event_id'   => $data['event_id'],
                'zone_id'    => $data['zone_id'],
                'code'       => $code,
                'base_price' => (int) $data['base_price'],
                'status'     => $data['status'],
                'expires_at' => ($data['status'] === 'ON_HOLD') ? ($data['expires_at'] ?? null) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        });

        DB::table('booths')->insert($rows->all());

        // Notifikasi manis 😎
        Notification::make()
            ->title('Berhasil Membuat Booth')
            ->body($codes->count().' booth berhasil dibuat.')
            ->success()
            ->send();

        // Kembalikan salah satu model (Filament butuh return Model)
        return Booth::query()
            ->where('event_id', $data['event_id'])
            ->where('code', $codes->first())
            ->firstOrFail();
    }

    /**
     * Setelah create banyak, arahin balik ke index biar admin lihat hasilnya.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
