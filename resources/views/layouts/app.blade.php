<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $settings['site_title'] ?? 'Tahu Reverse' }} — Yang Biasa, Kami Balik</title>
    <meta name="description" content="Olahan tahu terinspirasi tahu walik. Pesan langsung lewat WhatsApp atau checkout online.">
    <link rel="icon" type="image/jpeg" href="{{ asset('images/tahu%20reverse%20logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
