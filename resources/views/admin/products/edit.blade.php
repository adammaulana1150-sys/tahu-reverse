@extends('layouts.admin')

@section('title', 'Produk')
@section('heading', 'Kelola produk')

@section('content')
<form method="POST" action="{{ route('admin.product.update') }}" enctype="multipart/form-data" class="max-w-3xl space-y-5 rounded-xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm font-medium" for="name">Nama produk</label>
        <input class="mt-1 w-full rounded-lg border-stone-300" id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255">
        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="description">Deskripsi</label>
        <textarea class="mt-1 w-full rounded-lg border-stone-300" id="description" name="description" rows="5" required maxlength="10000">{{ old('description', $product->description) }}</textarea>
        @error('description')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium" for="price">Harga (Rupiah)</label>
            <input class="mt-1 w-full rounded-lg border-stone-300" id="price" name="price" type="number" min="1" value="{{ old('price', $product->price) }}" required>
            @error('price')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium" for="stock">Stok</label>
            <input class="mt-1 w-full rounded-lg border-stone-300" id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock) }}" required>
            @error('stock')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
    </div>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2 text-sm"><input class="rounded border-stone-300" type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $product->is_active))> Produk aktif dan dapat dipesan</label>
    <div>
        <p class="text-sm font-medium">Foto saat ini</p>
        <img class="mt-2 h-40 w-52 rounded-lg border object-cover" src="{{ $imageUrl }}" alt="{{ $product->name }}">
        <label class="mt-4 block text-sm font-medium" for="image">Ganti foto (maks. 2 MB)</label>
        <input class="mt-1 block w-full text-sm" id="image" name="image" type="file" accept="image/*">
        @error('image')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <button class="rounded-lg bg-stone-900 px-5 py-3 font-semibold text-white hover:bg-stone-700" type="submit">Simpan produk</button>
</form>
@endsection
