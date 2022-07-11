<?php

namespace App\Http\Controllers;

use App\Models\Characteristic;
use App\Models\Price;
use App\Models\Variant;
use Illuminate\Support\Facades\Http;
use JsonException;

class VariantController extends Controller
{
    public static string $entityName = 'variant';
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
        $limit = 500;
        $offset = 0;

        $variants_array = [];
        $characteristics_array = [];
        $prices_array = [];
        $images_array = [];

        do {
            $response = Http::timeout(180)
                ->withBasicAuth($this->username, $this->password)
                ->get('https://online.moysklad.ru/api/remap/1.2/entity/' . self::$entityName . '/',
                    [
                        "limit" => $limit,
                        "offset" => $offset,
                    ]);

            $variants = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

            foreach ($variants->rows as $variant) {
                $variants_array[$variant->id]["uuid"] = $variant->id;
                if (property_exists($variant, "characteristics")) {
                    $characteristics_array[$variant->id] = $variant->characteristics;
                }
                $prices_array[$variant->id] = $variant->salePrices;
                $variants_array[$variant->id]["name"] = $variant->name;
                $variants_array[$variant->id]["code"] = $variant->code;
                $product_uuid_array = explode("/", $variant->product->meta->href);
                $product_uuid = end($product_uuid_array);
                $variants_array[$variant->id]["relative_product"] = $product_uuid;
                if ($with_images) {
                    $images_array[$variant->id] = $variant->images->meta->href;
                }
            }

            $rows_count = count($variants->rows);
            $offset += $limit;

        } while ($rows_count === $limit);

        if ($with_images) {
            foreach ($images_array as $img => $value) {
                $this->imageController->upload($img, self::$entityName);
            }
        }

        Characteristic::truncate();

        foreach ($characteristics_array as $character => $value) {
            foreach ($value as $ch) {
                Characteristic::updateOrCreate(
                    ['uuid' => $ch->id, 'entity_uuid' => (string)$character],
                    [
                        'name' => $ch->name,
                        'value' => $ch->value,
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

        foreach ($variants_array as $variant) {
            Variant::updateOrCreate(
                ['uuid' => (string)$variant["uuid"]],
                [
                    'code' => $variants_array[$variant["uuid"]]["code"],
                    'name' => $variants_array[$variant["uuid"]]["name"],
                    'relative_product' => $variants_array[$variant["uuid"]]["relative_product"],
                ]
            );
        }

        return true;
    }
}
