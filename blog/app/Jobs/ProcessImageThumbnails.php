<?php

namespace App\Jobs;

use App\Models\Image as ImageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Http;

class ProcessImageThumbnails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ImageModel $image;

    public static string $entityName = 'download';
    public string $username;
    public string $password;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ImageModel $image)
    {
        $this->image = $image;
        $this->username = config('ms.ms_login');
        $this->password = config('ms.ms_password');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $image = $this->image;
        $uuid = $image->uuid;
        $filename = 'processing';
        $tempImage = tempnam(sys_get_temp_dir(), $filename);
        $response = Http::timeout(180)
            ->sink($tempImage)
            ->withBasicAuth($this->username, $this->password)
            ->get('https://online.moysklad.ru/api/remap/1.2/' . self::$entityName . '/' . $uuid);
        file_put_contents("/var/www/html/public/img/" . $uuid . '.jpg', $response->getBody()->getContents());
    }
}
