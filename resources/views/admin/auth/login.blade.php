<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-6">
        <form method="POST" action="{{ route('admin.login.store') }}" class="w-full rounded-2xl bg-white p-8 shadow-sm">
            @csrf
            <p class="text-sm font-semibold uppercase tracking-widest text-orange-700">Tahu Reverse</p>
            <h1 class="mt-2 text-2xl font-bold">Admin login</h1>
            <p class="mt-2 text-sm text-stone-600">Masuk untuk mengelola pesanan dan produk.</p>
            <label class="mt-6 block text-sm font-medium" for="email">Email</label>
            <input class="mt-1 w-full rounded-lg border-stone-300" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            <label class="mt-4 block text-sm font-medium" for="password">Password</label>
            <input class="mt-1 w-full rounded-lg border-stone-300" id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            <label class="mt-4 flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="rounded border-stone-300">
                Ingat saya
            </label>
            <button class="mt-6 w-full rounded-lg bg-stone-900 px-4 py-3 font-semibold text-white hover:bg-stone-700" type="submit">Masuk</button>
        </form>
    </main>
</body>
</html>
