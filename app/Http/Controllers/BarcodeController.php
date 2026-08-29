<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeController extends Controller
{
    public function show(Product $product): Response
    {
        $generator = new BarcodeGeneratorPNG();
        $code = substr($product->barcode, 0, 12);
        $barcode = $generator->getBarcode($code, 'EAN13', 2, 60);

        return response($barcode)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
