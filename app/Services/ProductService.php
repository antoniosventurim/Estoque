<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function create(array $data, ?UploadedFile $image = null): Product
    {
        if (empty($data['barcode'])) {
            $data['barcode'] = Product::generateUniqueBarcode();
        }

        $data['image'] = $this->storeImage($image);

        return Product::create($data);
    }

    public function update(Product $product, array $data, ?UploadedFile $image = null, bool $removeImage = false): Product
    {
        if ($removeImage) {
            $this->deleteImage($product);
            $data['image'] = null;
        } else {
            $data['image'] = $this->storeImage($image) ?? $product->image;
        }

        if ($data['image'] !== $product->image) {
            $this->deleteImage($product);
        }

        $product->update($data);

        return $product->refresh();
    }

    public function destroy(Product $product): void
    {
        $this->deleteImage($product);
        $product->delete();
    }

    public function destroyMany(array $ids): int
    {
        $products = Product::whereIn('id', $ids)->get();

        foreach ($products as $product) {
            $this->destroy($product);
        }

        return $products->count();
    }

    protected function storeImage(?UploadedFile $image): ?string
    {
        if (! $image) {
            return null;
        }

        return $image->store('produtos', 'public');
    }

    protected function deleteImage(Product $product): void
    {
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }
    }
}
