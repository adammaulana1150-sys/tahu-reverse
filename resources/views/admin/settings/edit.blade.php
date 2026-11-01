@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('heading', 'Pengaturan website')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="max-w-3xl space-y-5 rounded-xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
    @csrf
    @method('PUT')
    <div>
        <label class="block text-sm font-medium" for="site_title">Judul website</label>
        <input class="mt-1 w-full rounded-lg border-stone-300" id="site_title" name="site_title" value="{{ old('site_title', $settings['site_title'] ?? '') }}" required maxlength="120">
        @error('site_title')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="whatsapp_number">Nomor WhatsApp</label>
        <input class="mt-1 w-full rounded-lg border-stone-300" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" placeholder="6281234567890" maxlength="20">
        <p class="mt-1 text-xs text-stone-500">Gunakan kode negara, misalnya 6281234567890.</p>
        @error('whatsapp_number')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="instagram_url">URL Instagram</label>
        <input class="mt-1 w-full rounded-lg border-stone-300" id="instagram_url" name="instagram_url" type="url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" placeholder="https://instagram.com/akun">
        @error('instagram_url')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium" for="contact_email">Email kontak</label>
        <input class="mt-1 w-full rounded-lg border-stone-300" id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" placeholder="email@example.com">
        @error('contact_email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <div class="border-t border-stone-200 pt-5">
        <h2 class="font-semibold">QRIS resmi</h2>
        <p class="mt-1 text-sm text-stone-600">Unggah gambar QRIS merchant resmi saja. Jangan unggah QR percobaan atau QR milik pihak lain.</p>
        @if($qrisImageUrl)
            <img class="mt-4 h-48 w-48 rounded-lg border border-stone-200 bg-white object-contain p-2" src="{{ $qrisImageUrl }}" alt="QRIS merchant yang sedang digunakan">
            <label class="mt-3 flex items-center gap-2 text-sm">
                <input name="remove_qris_image" type="checkbox" value="1">
                Hapus gambar QRIS saat ini
            </label>
        @else
            <p class="mt-3 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Gambar QRIS belum dikonfigurasi. Checkout akan menampilkan pemberitahuan setup.</p>
        @endif
        <label class="mt-4 block text-sm font-medium" for="qris_image">Gambar QRIS (JPG, PNG, atau WebP; maks. 4 MB)</label>
        <input class="mt-1 block w-full text-sm" id="qris_image" name="qris_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        @error('qris_image')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('remove_qris_image')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
    </div>
    <button class="rounded-lg bg-stone-900 px-5 py-3 font-semibold text-white hover:bg-stone-700" type="submit">Simpan pengaturan</button>
</form>
@endsection
