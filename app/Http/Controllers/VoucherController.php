<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\GenerateVoucher;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    public function generate(Request $request)
    {
        try {
            $totalVouchers = 3000000;
            $batchSize = 20000;

            // Dispatch jobs in batches
            for ($i = 0; $i < $totalVouchers; $i += $batchSize) {
                GenerateVoucher::dispatch(min($batchSize, $totalVouchers - $i));
            }

            $this->waitForJobsToFinish();

            return $this->generateCsv();

        } catch (\Exception $e) {
            \Log::error('Voucher generation failed: ' . $e->getMessage());
        }
    }

    public function waitForJobsToFinish()
    {
        // Retrieve all job ids
        $jobIds = DB::table('jobs')->where('payload', 'LIKE', '%GenerateVoucher%')->pluck('id')->toArray();
        // var_dump($jobIds);
        do {
            $jobsPending = 0;

            foreach ($jobIds as $jobId) {
                $job = DB::table('jobs')->find($jobId);
                if ($job) {
                    $jobsPending++;
                }
            }

            sleep(1);
        } while ($jobsPending > 0);
    }

    public function generateCsv()
    {
        try {
            $filePath = storage_path('/app/public/vouchers.csv');


            $handle = fopen($filePath, 'w');
            fputcsv($handle, ['Codes']);

            // Add data rows
            foreach (DB::table('vouchers')->cursor() as $voucher) {
                fputcsv($handle, [
                    $voucher->code
                ]);
            }

            // Close the file pointer
            fclose($handle);

            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
}

