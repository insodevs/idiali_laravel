<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Http;
use JsonException;

class CategoryController extends Controller
{
    public static string $entityName = 'productfolder';
    public string $username;
    public string $password;

    public function __construct()
    {
        $this->username = config('ms.ms_login');
        $this->password = config('ms.ms_password');
    }

    /**
     * @throws JsonException
     */
    public function fetch(): bool
    {
        Category::truncate();

        $response = Http::withBasicAuth($this->username, $this->password)
            ->get('https://online.moysklad.ru/api/remap/1.2/entity/' . self::$entityName . '/', []);

        $categories = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

        $categories_array = [];
        $i = 0;

        foreach ($categories->rows as $cat) {
            $categories_array[$cat->id]["id"] = $i++;
            $categories_array[$cat->id]["name"] = $cat->name;
            if ($cat->pathName) {
                $categories_array[$cat->id]["pathName"] = $cat->pathName;
            }
        }

        foreach ($categories_array as $cat => $value) {
            if (isset($value["pathName"])) {
                $categories_array[$cat]["parentId"] =
                    array_search($value["pathName"], array_column($categories_array, "name"), true);
            } else {
                $categories_array[$cat]["parentId"] = null;
            }

            Category::updateOrCreate(
                ['uuid' => (string)$cat],
                [
                    'id' => $categories_array[$cat]["id"],
                    'parent_id' => $categories_array[$cat]["parentId"],
                    'title' => $categories_array[$cat]["name"],
                ]
            );
        }

        return true;
    }
}
