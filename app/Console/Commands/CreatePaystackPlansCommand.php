<?php

namespace App\Console\Commands;

use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CreatePaystackPlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-paystack-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Paystack subscription plans based on available plans and update PAYSTACK_PLAN_CODES constant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to create Paystack subscription plans...');

        // Get all available subscription plans
        $plans = SubscriptionService::getPlans();

        // Filter out the free plan
        $paidPlans = array_filter($plans, function ($key) {
            return $key !== 'free';
        }, ARRAY_FILTER_USE_KEY);

        if (empty($paidPlans)) {
            $this->error('No paid plans found in SubscriptionService::PLANS');
            return 1;
        }

        // Get Paystack API credentials from config
        $secretKey = Config::get('services.payment.subscription.paystack.secret_key');
        $publicKey = Config::get('services.payment.subscription.paystack.public_key');

        if (!$secretKey || !$publicKey) {
            $this->error('Paystack API keys are not configured. Please set them in your .env file.');
            return 1;
        }

        // Create a dummy user for PaymentService
        $dummyUser = new \App\Models\User();
        $dummyUser->email = 'dummy@example.com';
        $dummyUser->businessDetail = new \App\Models\BusinessDetail();

        // Initialize PaymentService for subscription payments
        try {
            $paymentService = PaymentService::forSubscription($dummyUser);
        } catch (\Exception $e) {
            $this->error('Failed to initialize PaymentService: ' . $e->getMessage());
            return 1;
        }

        $planCodes = [];
        $success = true;

        // Create each plan in Paystack
        foreach ($paidPlans as $planKey => $planDetails) {
            $this->info("Creating plan: {$planDetails['name']} ({$planKey})");

            try {
                // Convert amount to kobo (smallest currency unit for NGN)
                $amountInKobo = $planDetails['price'] * 100;

                // Create the plan in Paystack
                $result = $paymentService->createPaystackPlan(
                    $planDetails['name'] . ' Plan',
                    $amountInKobo,
                    'monthly', // Assuming all plans are monthly
                    "TailorFit {$planDetails['name']} subscription plan"
                );

                if ($result['success']) {
                    $this->info("Successfully created plan: {$result['name']} with code: {$result['plan_code']}");
                    $planCodes[$planKey] = $result['plan_code'];
                } else {
                    $this->error("Failed to create plan: {$planDetails['name']}");
                    $success = false;
                }
            } catch (\Exception $e) {
                $this->error("Error creating plan {$planDetails['name']}: " . $e->getMessage());
                Log::error("Error creating Paystack plan: " . $e->getMessage());
                $success = false;
            }
        }

        if (!$success) {
            $this->error('Some plans failed to create. Check the logs for details.');
            return 1;
        }

        // Update the PAYSTACK_PLAN_CODES constant in PaymentService.php
        $this->info('Updating PAYSTACK_PLAN_CODES constant in PaymentService.php...');

        try {
            $this->updatePaystackPlanCodes($planCodes);
            $this->info('Successfully updated PAYSTACK_PLAN_CODES constant.');
        } catch (\Exception $e) {
            $this->error('Failed to update PAYSTACK_PLAN_CODES constant: ' . $e->getMessage());
            Log::error('Failed to update PAYSTACK_PLAN_CODES constant: ' . $e->getMessage());
            return 1;
        }

        $this->info('All Paystack subscription plans created successfully!');
        return 0;
    }

    /**
     * Update the PAYSTACK_PLAN_CODES constant in PaymentService.php
     *
     * @param array $planCodes
     * @return void
     */
    protected function updatePaystackPlanCodes(array $planCodes)
    {
        $filePath = app_path('Services/PaymentService.php');

        if (!File::exists($filePath)) {
            throw new \Exception("PaymentService.php file not found at: {$filePath}");
        }

        $fileContent = File::get($filePath);

        // Build the new constant definition
        $newConstantDefinition = "    // Paystack subscription plan codes\n";
        $newConstantDefinition .= "    const PAYSTACK_PLAN_CODES = [\n";

        foreach ($planCodes as $planKey => $planCode) {
            $newConstantDefinition .= "        '{$planKey}' => '{$planCode}',\n";
        }

        $newConstantDefinition .= "    ];";

        // Define the pattern to match the existing constant definition
        $pattern = '/\s+\/\/ Paystack subscription plan codes\s+const PAYSTACK_PLAN_CODES = \[\s+(?:\'[^\']+\' => (?:null|\'[^\']+\'),\s+)*\s*\];/s';

        // Replace the constant definition in the file content
        $updatedContent = preg_replace($pattern, $newConstantDefinition, $fileContent);

        if ($updatedContent === $fileContent) {
            throw new \Exception("Failed to update PAYSTACK_PLAN_CODES constant. Pattern not found.");
        }

        // Write the updated content back to the file
        File::put($filePath, $updatedContent);
    }
}
