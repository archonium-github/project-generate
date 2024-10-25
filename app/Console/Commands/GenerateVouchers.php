<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voucher;

class GenerateVouchers extends Command
{
    protected $signature = 'vouchers:generate {count=100000} {batchSize=500}';
    protected $description = 'Generate unique voucher codes';

    public function handle()
    {
        try {
            $count = (int) $this->argument('count');
            $batchSize = (int) $this->argument('batchSize');
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $generatedCount = 0;

            while ($generatedCount < $count) {
                $vouchers = [];

                for ($i = 0; $i < $batchSize && $generatedCount < $count; $i++) {
                    $code = $this->generateCode($characters);
                    // Attempt to create the voucher
                    if (!in_array($code, $vouchers)) {
                        $vouchers[] = $code;
                    }
                }

                // Insert in bulk and handle any potential duplicates
                $this->insertVouchers($vouchers);
                $generatedCount += count($vouchers);
            }

            $this->info("Generated {$generatedCount} unique voucher codes.");
        } catch (\Exception $e) {
            \Log::error('Voucher insertion failed: ' . $e->getMessage());
        } catch (\Throwable $t) {
            \Log::error('Voucher insertion failed throwable: ' . $t->getMessage());
        }

    }

    private function generateCode($characters)
    {
        $code = '';
        for ($i = 0; $i < 10; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $code;
    }

    private function insertVouchers(array $vouchers)
    {
        try {
            // Use insertOrIgnore to avoid unique constraint violations
            Voucher::insertOrIgnore(array_map(fn($code) => ['code' => $code, 'created_at' => now(), 'updated_at' => now()], $vouchers));
        } catch (\Exception $e) {
            Log::error('Failed to insert vouchers: ' . $e->getMessage());
        }
    }
}
