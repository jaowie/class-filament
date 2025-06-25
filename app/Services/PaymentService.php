<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

/**
 * Class PaymentService.
 */
class PaymentService
{
    private $apiEndpoint;

    public function __construct()
    {
        $this->apiEndpoint = config('services.pops_url');
    }

    public function connect()
    {
        $response = Http::get("{$this->apiEndpoint}/connection");

        if ($response['white_ip']) {
            return 'Success.';
        }

        return 'Failed.';
    }
    
    public function createPayment(object $payload)
    {
        $items = $payload->items;

        $itemsJson = urlencode(json_encode($items, JSON_UNESCAPED_SLASHES));
  
        $oprefid = $payload->oprefid;
        $name = $payload->name;
        $address = $payload->address;
        $postedby = $payload->postedby;
        $duedate = $payload->duedate;

        $url = "{$this->apiEndpoint}/saveop"
            . "?oprefid={$oprefid}"
            . '&opsysid=CLASS'
            . '&name=' . urlencode($name)
            . '&address=' . urlencode($address)
            . '&postedby=' . urlencode($postedby)
            . '&duedate=' . urlencode($duedate)
            . "&items={$itemsJson}";


        $response = Http::get($url);

        return $response->body();
    }
}
