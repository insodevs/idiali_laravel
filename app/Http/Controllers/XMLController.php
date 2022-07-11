<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Characteristic;
use App\Models\Image;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductToCategory;
use App\Models\Stock;
use App\Models\Variant;
use DateTime;

class XMLController extends Controller
{
    public function index()
    {
        $date = new DateTime();
        $date->modify('-8 hours');
        $formatted_date = $date->format('Y-m-d H:i:s');

        $all_price_types = array(
            "Цена опт" => "opt_price",
            "Цена продажи" => "default_price",
            "Цена дроп" => "drop_price");

        $categories = Category::all()->toArray();
        $products = Product::where('name', 'not like', "%Распродажа%")->get()->toArray();
        $variants = Variant::where('name', 'not like', "%Распродажа%")->get()->toArray();
        $prices = Price::all()->toArray();
        $stocks = Stock::all()->toArray();
        $images = Image::where('updated_at', '>',$formatted_date)->get()->toArray();
        $characteristics = Characteristic::all()->toArray();
        $product_to_category = ProductToCategory::all()->toArray();
        $formed_products = [];
        $group_images = [];

        foreach ($prices as $price) {
            $formed_products[$price["product_uuid"]]["prices"][$all_price_types[$price["name"]]] = $price["value"];
        }

        foreach ($products as $product) {
            $formed_products[$product["uuid"]]["product"] = $product;
        }

        foreach ($characteristics as $characteristic) {
            $formed_products[$characteristic["entity_uuid"]]["characteristics"][$characteristic["name"]]
                = $characteristic["value"];
        }

        foreach ($images as $image) {
            $formed_products[$image["relative_uuid"]]["image"][] = $image;
        }

        foreach ($variants as $variant) {
            $formed_products[$variant["uuid"]]["product"]["name"] = $variant["name"];
            $formed_products[$variant["uuid"]]["product"]["code"] = $variant["code"];
            if (isset($formed_products[$variant["uuid"]]["characteristics"]["размер"])) {
                $length_of_size_characteristics = (strlen(explode("-", $formed_products[$variant["uuid"]]["characteristics"]["размер"])[0]));
            } else {
                $length_of_size_characteristics = 0;
            }
            $formed_products[$variant["uuid"]]["product"]["group"] = substr(
                $variant["code"],
                0,
                -($length_of_size_characteristics));
            $formed_products[$variant["uuid"]]["product"]["description"] = $formed_products[$variant["relative_product"]]["product"]["description"];

            if(!empty($formed_products[$variant["uuid"]]["image"])){
                foreach ($formed_products[$variant["uuid"]]["image"] as $group_image){
                    $group_images[$formed_products[$variant["uuid"]]["product"]["group"]][] = $group_image;
                }
            }
            else{
                $formed_products[$variant["uuid"]]["image"] = [];
            }
        }

        foreach ($formed_products as $formed_product => $value){
            if(isset($value["product"]["group"], $group_images[$value["product"]["group"]])){
                $formed_products[$formed_product]["image"] =
                    array_unique(array_merge($value["image"], $group_images[$value["product"]["group"]]), SORT_REGULAR);
            }
        }

        foreach ($stocks as $stock) {
            $formed_products[$stock["uuid"]]["stock"] = $stock;
        }

        foreach ($product_to_category as $cat) {
            $cat["category_id"] =
                array_search($cat["category_uuid"], array_column($categories, "uuid"), true);
            $formed_products[$cat["product_uuid"]]["category_id"] = $cat["category_id"];
        }

        $response = response()->view(
            'pages.xml',
            [
                'products' => $formed_products,
                'categories' => $categories,
            ]
        )->header('Content-Type', 'text/xml');

        file_put_contents("/var/www/html/public/doc/map.xml", $response->getContent());

        foreach ($formed_products as $formed_product){
            $grouped_products[$formed_product["product"]["group"]][] = $formed_product;
        }

        $response_prom = response()->view(
            'pages.xml-prom',
            [
                'products' => $formed_products,
                'categories' => $categories,
            ]
        )->header('Content-Type', 'text/xml');

        file_put_contents("/var/www/html/public/doc/prom.xml", $response_prom->getContent());

        return $response;
    }
}
