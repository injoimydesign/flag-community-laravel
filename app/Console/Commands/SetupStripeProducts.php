<?php
// app/Console/Commands/SetupStripeProducts.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FlagProduct;
use App\Services\StripeService;

class SetupStripeProducts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'stripe:setup-products 
                           {--force : Force update existing products}
                           {--product= : Setup specific product by ID}';

    /**
     * The console command description.
     */
    protected $description = 'Create or update Stripe products and prices for all flag products';

    protected StripeService $stripeService;

    /**
     * Create a new command instance.
     */
    public function __construct(StripeService $stripeService)
    {
        parent::__construct();
        $this->stripeService = $stripeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Stripe products and prices...');

        $query = FlagProduct::with(['flagType', 'flagSize'])->active();

        // Filter by specific product if provided
        if ($this->option('product')) {
            $query->where('id', $this->option('product'));
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            $this->error('No products found to setup.');
            return 1;
        }

        $this->info("Found {$products->count()} products to process.");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $success = 0;
        $errors = 0;

        foreach ($products as $product) {
            try {
                // Skip if product already has Stripe IDs and not forcing update
                if (!$this->option('force') && 
                    $product->stripe_price_id_onetime && 
                    $product->stripe_price_id_annual) {
                    $bar->advance();
                    continue;
                }

                $this->stripeService->createOrUpdateProduct($product);
                
                $this->newLine();
                $this->info("✓ Created/updated: {$product->display_name}");
                
                $success++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("✗ Failed to setup {$product->display_name}: " . $e->getMessage());
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($success > 0) {
            $this->info("Successfully processed {$success} products.");
        }

        if ($errors > 0) {
            $this->error("Failed to process {$errors} products.");
            return 1;
        }

        $this->info('All products have been successfully setup in Stripe!');
        return 0;
    }
}