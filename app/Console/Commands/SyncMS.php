<?php

namespace App\Console\Commands;

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\XMLController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use JsonException;

class SyncMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ms:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws JsonException
     */
    public function handle(): void
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('/var/www/html/storage/logs/sync.log'),
        ]);

        Log::info("SyncMS start \n");

        $image_controller = new ImageController();

        $category_controller = new CategoryController();
        if ($category_controller->fetch()) {
            Log::info("category_controller ok \n");
        }

        $product_controller = new ProductController($image_controller);
        if ($product_controller->fetch(true)) {
            Log::info("product_controller ok \n");
        }

        $stock_controller = new StockController();
        if ($stock_controller->fetch()) {
            Log::info("stock_controller ok \n");
        }

        $variant_controller = new VariantController($image_controller);
        if ($variant_controller->fetch(true)) {
            Log::info("variant_controller ok \n");
        }

        Log::info("SyncMS end \n ******* \n");

        Log::info("XML generate start \n");

        $xml_controller = new XMLController();
        if ($xml_controller->index()) {
            Log::info("xml_controller ok \n");
        }

        Log::info("XML generate end \n -------- \n");
    }
}
