<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $rows = [
            // === Toggle Midtrans ===
            [
                'group'     => 'payments',
                'key'        => 'midtrans_enabled',
                'value'      => '1', // "1" = ON, "0" = OFF
                'type'       => 'bool',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // === Toggle Bank Transfer ===
            [
                'group'     => 'payments',
                'key'        => 'bank_transfer_enabled',
                'value'      => '1',
                'type'       => 'bool',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // === Pesan banner untuk fallback ke transfer manual ===
            [
                'group'     => 'payments',
                'key'        => 'fallback_banner',
                'value'      => '⚠️ Pembayaran via Midtrans sementara nonaktif. Silakan gunakan transfer manual & upload bukti pembayaran.',
                'type'       => 'text',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // === Instruksi transfer (multi-baris) ===
            [
                'group'      => 'payments',
                'key'        => 'bank_transfer_instructions',
                'value'      => <<<TEXT
                                    Silakan transfer ke salah satu rekening berikut:

                                    • BCA 1234567890 a.n. PT Golek Tenant
                                    • BNI 9876543210 a.n. PT Golek Tenant

                                    Nominal harus sesuai invoice.
                                    Setelah transfer, unggah bukti pada halaman "Upload Bukti Pembayaran".
                                    Verifikasi manual membutuhkan waktu maks. 1x24 jam kerja.
                                    TEXT,
                'type'       => 'text',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        
        // Upsert by unique "key"
        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                [
                    'group'      => $row['group'],
                    'value'      => $row['value'],
                    'type'       => $row['type'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                ]
            );
        }
    }
}
