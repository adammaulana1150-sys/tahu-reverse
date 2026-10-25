<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use finfo;

class ProductController extends Controller
{
    public function edit(): View
    {
        $product = Product::query()->firstOrFail();
        $imageUrl = str_starts_with($product->image, 'images/')
            ? asset($product->image)
            : Storage::disk('public')->url($product->image);

        return view('admin.products.edit', compact('product', 'imageUrl'));
    }

    public function update(UpdateProductRequest $request): RedirectResponse
    {
        $product = Product::query()->firstOrFail();
        $validated = $request->validated();
        $oldImage = $product->image;

        $product->fill([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('image')) {
            $uploadedFile = $request->file('image');
            $tmpPath = $uploadedFile->getRealPath();
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tmpPath);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (! in_array($mimeType, $allowedMimes, true)) {
                return back()->withErrors([
                    'image' => 'File yang diunggah bukan gambar yang valid.',
                ])->withInput();
            }

            $imagePath = $uploadedFile->store('products', 'public');
            if ($imagePath === false) {
                return back()->withErrors(['image' => 'Foto gagal disimpan. Silakan coba lagi.'])->withInput();
            }

            $product->image = $imagePath;
        }

        $product->save();

        if ($request->hasFile('image') && ! str_starts_with($oldImage, 'images/')) {
            Storage::disk('public')->delete($oldImage);
        }

        return back()->with('status', 'Produk berhasil diperbarui.');
    }
}
