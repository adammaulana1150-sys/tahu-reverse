<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
    <div class="min-h-screen md:flex">
        <aside class="bg-stone-950 px-5 py-6 text-white md:min-h-screen md:w-64 md:shrink-0">
            <a class="text-lg font-bold tracking-wide" href="{{ route('admin.dashboard') }}">TAHU REVERSE</a>
            <p class="mt-1 text-xs text-stone-400">Panel administrasi</p>
            <nav class="mt-8 flex gap-2 overflow-x-auto text-sm md:flex-col">
                <a class="rounded-lg px-3 py-2 hover:bg-stone-800" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="rounded-lg px-3 py-2 hover:bg-stone-800" href="{{ route('admin.product.edit') }}">Produk</a>
                <a class="rounded-lg px-3 py-2 hover:bg-stone-800" href="{{ route('admin.orders.index') }}">Pesanan</a>
                <a class="rounded-lg px-3 py-2 hover:bg-stone-800" href="{{ route('admin.customers.index') }}">Pelanggan</a>
                <a class="rounded-lg px-3 py-2 hover:bg-stone-800" href="{{ route('admin.settings.edit') }}">Pengaturan</a>
            </nav>
            <form class="mt-8 md:mt-12" method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="w-full rounded-lg border border-stone-700 px-3 py-2 text-left text-sm hover:bg-stone-800" type="submit">Keluar</button>
            </form>
        </aside>
        <main class="min-w-0 flex-1 p-5 sm:p-8">
            <div class="mx-auto max-w-7xl">
                <header class="mb-6">
                    <p class="text-sm text-stone-500">Halo, {{ auth()->user()->name }}</p>
                    <h1 class="mt-1 text-2xl font-bold">@yield('heading')</h1>
                </header>
                @if(session('status'))
                    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                        <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
