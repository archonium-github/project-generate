<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
// use App\Models\Voucher;

class GenerateVoucher implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $count;
    protected $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Create a new job instance.
     */
    public function __construct($count)
    {
        $this->count = $count;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $vouchers = [];
        for ($i = 0; $i < $this->count; $i++) {
            $code = $this->generateCode();
            // Ensure uniqueness
            if (!DB::table('vouchers')->where('code', $code)->exists()) {
                $vouchers[] = ['code' => $code];

                // Batch insert every 1000 vouchers
                if (count($vouchers) >= 1000) {
                    DB::table('vouchers')->insert($vouchers);
                    $vouchers = []; // Reset for next batch
                }
            }
        }

        // Insert any remaining vouchers
        if (count($vouchers) > 0) {
            DB::table('vouchers')->insert($vouchers);
        }
    }

    private function generateCode()
    {
        $characters = $this->characters;
        $code = '';
        for ($i = 0; $i < 10; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $code;
    }
}
