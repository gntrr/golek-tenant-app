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
                                    Pembayaran melalui Virtual Account (VA):

                                    • Pilih bank VA yang diinginkan (BCA, BNI, BRI, Mandiri, Permata, CIMB, dll.).
                                    • Sistem akan membuat nomor VA unik untuk pesanan Anda.
                                    • Bayar tepat sesuai nominal pada invoice.
                                    • Lakukan pembayaran via ATM, m-banking, internet banking, atau setor tunai sesuai bank terpilih.
                                    • Batas waktu pembayaran 24 jam sejak VA dibuat.
                                    • Setelah berhasil, silahkan upload bukti pembayaran di kolom "Bukti Transfer".
                                    • Jika VA kedaluwarsa atau pembayaran gagal, buat ulang nomor VA dari halaman invoice.
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
