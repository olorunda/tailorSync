<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateInvoiceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update invoice with ID 3 to pending status with specific timestamp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Update invoice with ID 3 to have status 'pending' and specific updated_at timestamp
        $updated = DB::table('invoices')
            ->where('id', 3)
            ->update([
                'status' => 'pending',
                'updated_at' => '2025-06-18 01:48:35'
            ]);

        if ($updated) {
            $this->info('Invoice #3 has been updated to pending status with timestamp 2025-06-18 01:48:35');
        } else {
            $this->error('Invoice #3 could not be updated or does not exist');
        }
    }
}
