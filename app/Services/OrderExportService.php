<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;
use App\Models\Order;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class OrderExportService
{
   public static function generatePng(Order $order): string
   {
      $order->load(['items.product']);

      $html = View::make('pdf.order', ['order' => $order])->render();

      $filename = 'order-' . $order->id . '.png';
      $path = storage_path('app/public/' . $filename);

      Browsershot::html($html)
         ->windowSize(1200, 800)
         ->waitUntilNetworkIdle()
         ->setOption('args', ['--no-sandbox']) // Important for many servers
         ->save($path);

      return $path;
   }
}