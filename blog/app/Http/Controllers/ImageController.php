<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Jobs\ProcessImageThumbnails;
use Illuminate\Support\Facades\Http;
use JsonException;

class ImageController extends Controller
{
    public string $username;
    public string $password;

    public function __construct()
    {
        $this->username = config('ms.ms_login');
        $this->password = config('ms.ms_password');
    }

    /**
     * Upload Image
     *
     * @param string $uuid
     * @param string $entityName
     * @return void
     * @throws JsonException
     */
    public function upload(string $uuid = "0129decc-c184-11ec-0a80-0d1400120194", string $entityName = "product"): void
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->get('https://online.moysklad.ru/api/remap/1.2/entity/' . $entityName . "/" . $uuid . '/images', []);

        $images = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

        foreach ($images->rows as $row){
            $img_uuid_array = explode("/", $row->meta->downloadHref);
            $img_uuid = end($img_uuid_array);

            $image = Image::updateOrCreate(
                ['uuid' => $img_uuid],
                ['entity' => $entityName, 'relative_uuid' => $uuid]
            );
            ProcessImageThumbnails::dispatch($image);
        }
    }
}
