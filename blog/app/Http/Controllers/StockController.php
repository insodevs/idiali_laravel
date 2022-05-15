<?php

namespace App\Http\Controllers;

use App\Models\ProductToCategory;
use App\Models\Stock;
use Illuminate\Support\Facades\Http;
use JsonException;

class StockController extends Controller
{
    public static string $reportName = 'stock';
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
        $limit = 500;
        $offset = 0;

        $reports_array = [];

        do {
            $response = Http::withBasicAuth($this->username, $this->password)
                ->get('https://online.moysklad.ru/api/remap/1.2/report/' . self::$reportName . '/all',
                    [
                        "limit" => $limit,
                        "offset" => $offset,
                    ]);

            $reports = json_decode($response->body(), false, 512, JSON_THROW_ON_ERROR);

            foreach ($reports->rows as $report) {
                $report_uuid_array = explode("/", $report->meta->href);
                $report_uuid = strtok(end($report_uuid_array), "?");

                $reports_array[$report_uuid]["uuid"] = $report_uuid;
                $reports_array[$report_uuid]["quantity"] = $report->quantity;
                $reports_array[$report_uuid]["code"] = $report->code;

                $report_folder_array = explode("/", $report->folder->meta->href);
                $report_folder = end($report_folder_array);

                $reports_array[$report_uuid]["folder"] = $report_folder;
            }

            $rows_count = count($reports->rows);
            $offset += $limit;

        } while ($rows_count === $limit);

        foreach ($reports_array as $report) {
            Stock::updateOrCreate(
                ['uuid' => (string)$report["uuid"]],
                [
                    'value' => $reports_array[$report["uuid"]]["quantity"],
                    'code' => $reports_array[$report["uuid"]]["code"],
                ]
            );
            ProductToCategory::updateOrCreate(
                [
                    'product_uuid' => (string)$report["uuid"],
                    'category_uuid' => $reports_array[$report["uuid"]]["folder"]
                ], []
            );
        }
        return true;
    }
}
