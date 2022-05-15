<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use JsonException;

class ProductController extends Controller
{
    public static string $entityName = 'product';
    public string $username;
    public string $password;

    protected ImageController $imageController;

    public function __construct(ImageController $imageController)
    {
        $this->imageController = $imageController;
        $this->username = config('ms.ms_login');
        $this->password = config('ms.ms_password');
    }

    /**
     * @throws JsonException
     */
    public function fetch($with_images = false): bool
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->get('https://online.moysklad.ru/api/remap/1.2/entity/' . self::$entityName . '/', []);

        $products = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

        $products_array = [];
        $attributes_array = [];
        $prices_array = [];
        $images_array = [];

        foreach ($products->rows as $product) {
            $products_array[$product->id]["uuid"] = $product->id;
            if (property_exists($product, "attributes")) {
                $attributes_array[$product->id] = $product->attributes;
            }
            $prices_array[$product->id] = $product->salePrices;
            if ($with_images) {
                $images_array[$product->id] = $product->images->meta->href;
            }
            $products_array[$product->id]["name"] = $product->name;
            $products_array[$product->id]["code"] = $product->code;

            if (property_exists($product, "description")) {
                $products_array[$product->id]["description"] = $product->description;
            } else {
                $products_array[$product->id]["description"] = "";
            }
        }

        if ($with_images) {
            foreach ($images_array as $img => $value) {
                $this->imageController->upload($img, self::$entityName);
            }
        }

        foreach ($attributes_array as $attr => $value) {
            foreach ($value as $ar) {
                if ($ar->type === "customentity") {
                    $ar_value = $ar->value->name;
                } else {
                    $ar_value = $ar->value;
                }
                Attribute::updateOrCreate(
                    ['uuid' => $ar->id, 'product_uuid' => (string)$attr],
                    [
                        'name' => $ar->name,
                        'value' => $ar_value,
                    ]
                );
            }
        }

        foreach ($prices_array as $price => $value) {
            foreach ($value as $pr) {
                Price::updateOrCreate(
                    ['product_uuid' => (string)$price, 'name' => $pr->priceType->name],
                    ['value' => (int)$pr->value / 100]
                );
            }
        }

        foreach ($products_array as $product) {
            Product::updateOrCreate(
                ['uuid' => (string)$product["uuid"]],
                [
                    'code' => $products_array[$product["uuid"]]["code"],
                    'name' => $products_array[$product["uuid"]]["name"],
                    'description' => $products_array[$product["uuid"]]["description"],
                ]
            );
        }
        return true;
    }
}
