@extends('layouts.app')

@section('content')
@php
    $siteTitle     = $settings['site_title'] ?? 'Tahu Reverse';
    $priceFmt      = number_format($product->price, 0, ',', '.');
    $canBuy        = $product->is_active && $availableStock >= 1;

    // Contact details are driven exclusively from the site settings table.
    // Never hardcode personal phone numbers, email addresses, or social
    // handles in the public view.
    $whatsappNumber = $settings['whatsapp_number'] ?? null;
    $whatsappUrl    = null;
    if ($whatsappNumber && preg_match('/^[1-9][0-9]{7,14}$/', $whatsappNumber)) {
        $whatsappUrl = 'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode('Halo, saya tertarik untuk memesan '.$product->name.');
    }
    $instagramUrl = filter_var($settings['instagram_url'] ?? null, FILTER_VALIDATE_URL)
        ? $settings['instagram_url']
        : null;
@endphp

<a class="skip" href="#produk">Lewati ke produk</a>

<div class="promo" role="note">Flip the Ordinary<span>✦</span>QRIS, diverifikasi manual<span>✦</span>{{ $canBuy ? 'Stok tersedia: ' . $availableStock . ' porsi' : 'Stok sedang habis' }}</div>

<header class="nav" id="site-nav"><div class="wrap">
    <a class="logo" href="#top" aria-label="{{ $product->name }}, ke atas"><i aria-hidden="true">↻</i><span>{{ strtoupper($siteTitle) }}</span></a>
    <nav id="site-menu" aria-label="Navigasi utama"><ul>
        <li><a href="#kenali">Kenali</a></li>
        <li><a href="#produk">Produk</a></li>
        <li><a href="#cerita">Why Reverse?</a></li>
        <li><a href="#contact">Contact</a></li>
    </ul></nav>
    <div class="nav-right">
        <a class="btn primary" href="#pesan">Pesan Sekarang</a>
        <button class="menu-btn" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="site-menu" data-menu-toggle><span></span></button>
    </div>
</div></header>

<main id="top">
    {{-- ================= HERO ================= --}}
    <section class="hero" aria-labelledby="hero-title">
        <span class="shape cube s1" data-par aria-hidden="true"><i></i></span>
        <span class="shape star s2" data-par aria-hidden="true"><i>✦</i></span>
        <span class="shape cube s3" data-par aria-hidden="true"><i></i></span>
        <span class="shape star s4" data-par aria-hidden="true"><i>✦</i></span>
        <span class="shape dot s5" data-par aria-hidden="true"><i></i></span>
        <span class="shape dot s6" data-par aria-hidden="true"><i></i></span>

        <div class="wrap hero-grid">
            <div class="hero-copy">
                <span class="pill">↻ Flip the Ordinary</span>
                <h1 id="hero-title"><span class="ln" style="--i:0">Yang biasa,</span><span class="ln" style="--i:1">kami <span class="flip">balik.</span></span></h1>
                <p class="lead">Terinspirasi dari tahu walik: tahu dibalik, lalu bagian yang terbuka diisi adonan tepung berbumbu. Makanan yang akrab, sudut pandang yang baru.</p>
                <div class="cta-row">
                    <button class="btn primary lg" type="button" data-open-checkout @disabled(!$canBuy)>Beli Sekarang · Rp {{ $priceFmt }}</button>
                    <a class="btn lg" href="#kenali">Kenali Produknya</a>
                </div>
                <ul class="trust">
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V5l-9-4Zm-1.2 15.2-3.5-3.5 1.4-1.4 2.1 2.1 4.6-4.6 1.4 1.4-6 6Z"/></svg>QRIS dengan verifikasi manual</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.2 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2Z"/></svg>Rasa lokal, perspektif baru</li>
                </ul>
            </div>

            <div class="stage" data-tilt>
                <svg class="ring" viewBox="0 0 200 200" aria-hidden="true"><defs><path id="ringpath" d="M100,100 m-90,0 a90,90 0 1,1 180,0 a90,90 0 1,1 -180,0"/></defs><text><textPath href="#ringpath" textLength="562" lengthAdjust="spacing">TAHU REVERSE ✦ FLIP THE ORDINARY ✦ RASA LOKAL ✦ </textPath></text></svg>
                <div class="disc"><img src="{{ $productImage }}" alt="{{ $product->name }} dalam kotak kertas dengan saus di samping" width="800" height="512" fetchpriority="high"></div>
                <div class="steam" aria-hidden="true">
                    <span class="puff" style="--x:6%;--w:46%;--t:6s;--d:0s;--drift:-10%"></span>
                    <span class="puff" style="--x:34%;--w:52%;--t:7.5s;--d:-2s;--drift:8%"></span>
                    <span class="puff" style="--x:56%;--w:40%;--t:6.5s;--d:-4s;--drift:16%"></span>
                    <span class="puff" style="--x:20%;--w:36%;--t:8s;--d:-5.5s;--drift:-16%"></span>
                </div>
                <div class="burst" aria-hidden="true"><div><small>cuma</small><b>Rp {{ $priceFmt }}</b><small>/ porsi</small></div></div>
                <div class="sticker" aria-hidden="true">Dibalik!</div>
            </div>
        </div>
    </section>
    <div class="scallop" aria-hidden="true"></div>

    {{-- ================= RIBBONS ================= --}}
    <div class="ribbons" aria-hidden="true">
        <div class="ribbon a"><div class="track">
            <span>Flip the ordinary</span><em>✦</em><span>Rasa lokal, perspektif baru</span><em>✦</em><span>Yang di dalam, jadi di luar</span><em>✦</em>
            <span>Flip the ordinary</span><em>✦</em><span>Rasa lokal, perspektif baru</span><em>✦</em><span>Yang di dalam, jadi di luar</span><em>✦</em>
            <span>Flip the ordinary</span><em>✦</em><span>Rasa lokal, perspektif baru</span><em>✦</em><span>Yang di dalam, jadi di luar</span><em>✦</em>
            <span>Flip the ordinary</span><em>✦</em><span>Rasa lokal, perspektif baru</span><em>✦</em><span>Yang di dalam, jadi di luar</span><em>✦</em>
        </div></div>
        <div class="ribbon b"><div class="track">
            <span>Tahu walik versi kami</span><em>↻</em><span>Dibalik dulu, baru dinikmati</span><em>↻</em><span>Akrab di lidah, baru di mata</span><em>↻</em>
            <span>Tahu walik versi kami</span><em>↻</em><span>Dibalik dulu, baru dinikmati</span><em>↻</em><span>Akrab di lidah, baru di mata</span><em>↻</em>
            <span>Tahu walik versi kami</span><em>↻</em><span>Dibalik dulu, baru dinikmati</span><em>↻</em><span>Akrab di lidah, baru di mata</span><em>↻</em>
            <span>Tahu walik versi kami</span><em>↻</em><span>Dibalik dulu, baru dinikmati</span><em>↻</em><span>Akrab di lidah, baru di mata</span><em>↻</em>
        </div></div>
    </div>

    {{-- ================= KENALI ================= --}}
    <section id="kenali" class="kenali"><div class="wrap kenali-grid">
        <div>
            <h2>Bukan tahu biasa.<br>Ini tahu yang dibalik.</h2>
            <p class="intro">{{ $product->name }} berangkat dari tahu walik, jajanan yang sudah akrab di Indonesia. Keunikannya ada di caranya dibuat: tiga langkah singkat.</p>
            <ol class="steps">
                <li class="step"><span class="n">1</span><div><h3>Keluarkan</h3><p>Bagian dalam tahu dikeluarkan.</p></div></li>
                <li class="step"><span class="n">2</span><div><h3>Balik</h3><p>Kulit tahu dibalik. Yang tadinya di dalam, kini jadi di luar.</p></div></li>
                <li class="step"><span class="n">3</span><div><h3>Isi</h3><p>Bagian yang terbuka diisi adonan tepung berbumbu, lalu dimasak.</p></div></li>
            </ol>
        </div>

        <div class="flipper" id="flipper">
            <div class="flipper-inner" data-flip-card>
                <figure class="face front" data-face="front">
                    <div class="im" style="background:#f2cd75"><img src="{{ asset('images/before.jpg') }}" alt="Potongan tahu goreng biasa" loading="lazy" width="500" height="364"></div>
                    <figcaption>Tahu <small>Sebelum</small></figcaption>
                </figure>
                <figure class="face back" data-face="back" aria-hidden="true">
                    <div class="im" style="background:#fde6db"><img src="{{ asset('images/after.jpg') }}" alt="{{ $product->name }}: tahu yang dibalik dan diisi adonan" loading="lazy" width="600" height="566"></div>
                    <figcaption>{{ $product->name }} <small>Sesudah</small></figcaption>
                </figure>
            </div>
            <button class="btn sun lg flip-btn" type="button" aria-pressed="false" data-flip-btn><i aria-hidden="true">↻</i><span>Balik!</span></button>
        </div>
    </div></section>

    {{-- ================= PRODUK ================= --}}
    <section id="produk" class="show"><div class="wrap">
        <figure>
            <div class="frame"><img src="{{ $productImage }}" alt="{{ $product->name }} disajikan di piring putih" width="500" height="375" loading="lazy"></div>
            <div class="tag" aria-hidden="true"><span>Baru<br>dibalik</span></div>
        </figure>
        <div>
            <h2>{{ $product->name }}</h2>
            <p class="desc">{!! nl2br(e($product->description)) !!}</p>
            <div class="price"><b>Rp {{ $priceFmt }}</b><span>1 porsi</span></div>
            <p class="note {{ $canBuy ? '' : 'off' }}">{{ $canBuy ? 'Stok tersedia: ' . $availableStock : 'Stok sedang habis' }}</p>
            <div class="cta-row">
                <button class="btn primary lg" type="button" data-open-checkout @disabled(!$canBuy)>Beli Sekarang</button>
                <a class="btn lg cream" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Tanya via WhatsApp</a>
            </div>
            <ul class="chips" aria-label="Ciri khas">
                <li>Tahu dibalik</li><li>Isi adonan tepung berbumbu</li><li>Rasa lokal</li>
            </ul>
            <p class="secure"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 1 3 5v6c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V5l-9-4Z"/></svg>Pembayaran QRIS diverifikasi manual oleh admin.</p>
        </div>
    </div></section>

    {{-- ================= WHY REVERSE ================= --}}
    <section id="cerita" class="story">
        <div class="mirror" data-par style="--k:-.12" aria-hidden="true">REVERSE</div>
        <div class="wrap story-grid">
            <div class="copy">
                <span class="pill">↻ Why Reverse?</span>
                <h2>Membalik<br>ekspektasi.</h2>
                <p class="big">Yang di dalam, jadi <em>di luar.</em></p>
                <p>Tahu sudah ada di meja kita sejak lama. Kami tidak mengganti makanan yang akrab ini; kami hanya melihatnya dari sisi lain.</p>
                <p>"Reverse" adalah proses membalik tahu, sekaligus cara pandang: sesuatu yang sudah dikenal bisa terasa seperti pengalaman baru.</p>
                <p class="motto">Rasa lokal, perspektif baru.</p>
            </div>
            <figure class="story-photo">
                <div class="story-frame">
                    <img src="{{ asset('images/hero.jpg') }}" alt="{{ $product->name }} disajikan hangat" loading="lazy" width="800" height="512">
                </div>
            </figure>
        </div>
    </section>

    {{-- ================= PESAN ================= --}}
    <section id="pesan" class="order"><div class="wrap">
        <h2>Siap buat<br>balik selera?</h2>
        <p class="sub">Pesan langsung lewat WhatsApp atau checkout online dengan pembayaran aman.</p>
        <div class="cta-row">
            <a class="btn cream lg" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Pesan lewat WhatsApp</a>
            <button class="btn dark lg" type="button" data-open-checkout @disabled(!$canBuy)>Pesan Online</button>
        </div>
        <ol class="flow" aria-label="Alur pemesanan">
            <li><b>1</b>Isi data &amp; alamat</li>
            <li><b>2</b>Bayar via QRIS</li>
            <li><b>3</b>Pesanan diproses</li>
        </ol>
    </div></section>

    {{-- ================= CONTACT ================= --}}
    <section id="contact" class="contact"><div class="wrap contact-grid">
        <div>
            <h2>Ada yang mau ditanyakan?</h2>
            <p class="lede">Punya pertanyaan, mau pesan, atau sekadar ingin menyapa? Pilih cara yang paling nyaman buatmu.</p>
        </div>
        <ul class="clist">
            <li><a class="wa" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Hubungi Tahu Reverse melalui WhatsApp">
                <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 14.95L2 22l5.2-1.5A9.9 9.9 0 1 0 12.04 2Zm5.8 14.1c-.25.7-1.4 1.3-1.95 1.35-.5.05-1.1.07-1.8-.12a14 14 0 0 1-1.6-.6c-2.8-1.2-4.6-4-4.75-4.2-.14-.2-1.1-1.5-1.1-2.8s.7-2 .95-2.25a1 1 0 0 1 .7-.3h.5c.17 0 .38-.06.6.46l.85 2.1c.07.15.1.3 0 .5l-.3.45-.4.45c-.13.14-.27.3-.12.58.15.27.7 1.15 1.5 1.85 1 .9 1.9 1.2 2.15 1.33.27.14.43.12.6-.07l.9-1.1c.2-.27.4-.2.65-.1l2 .95c.27.14.45.2.5.32.07.1.07.65-.18 1.35Z"/></svg></span>
                <span><small>WhatsApp</small><strong>{{ $whatsappText }}</strong></span>
                <svg class="go" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a></li>
            <li><a class="ig" href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="Kunjungi Instagram Tahu Reverse">
                <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9a5.5 5.5 0 0 1-5.5 5.5h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2Zm0 2A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9a3.5 3.5 0 0 0 3.5-3.5v-9A3.5 3.5 0 0 0 16.5 4h-9ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm5.2-3.2a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2Z"/></svg></span>
                <span><small>Instagram</small><strong>{{ $instagramText }}</strong></span>
                <svg class="go" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a></li>
            <li><a class="ml" href="mailto:{{ $contactEmail }}" aria-label="Kirim email ke Tahu Reverse">
                <span class="ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm0 2v.5l8 5 8-5V6H4Zm16 12V8.85l-7.47 4.67a1 1 0 0 1-1.06 0L4 8.85V18h16Z"/></svg></span>
                <span><small>Email</small><strong>{{ $contactEmail }}</strong></span>
                <svg class="go" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 17 17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a></li>
        </ul>
    </div></section>
</main>

<footer><div class="wrap">
    <div class="cols">
        <div>
            <div class="logo"><i aria-hidden="true">↻</i><span>{{ strtoupper($siteTitle) }}</span></div>
            <p class="tag">Flip the Ordinary.</p>
        </div>
        <ul>
            <li><a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">WhatsApp: {{ $whatsappText }}</a></li>
            <li><a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">Instagram: {{ $instagramText }}</a></li>
            <li><a href="mailto:{{ $contactEmail }}">Email: {{ $contactEmail }}</a></li>
            <li><a href="#contact">Contact Us</a></li>
        </ul>
    </div>
    <div class="wordmark" aria-hidden="true">Tahu Reverse</div>
    <p class="credit">© {{ now()->year }} {{ $siteTitle }}.<br>Created by XL PPLG 3 — Kelompok Tahu Reverse<br>SMK Amaliah</p>
</div></footer>

{{-- Tombol beli cepat (mobile) --}}
<div class="buybar" data-buybar>
    <div><b>{{ $product->name }}</b><span>Rp {{ $priceFmt }} / porsi</span></div>
    <button class="btn sun" type="button" data-open-checkout @disabled(!$canBuy)>Beli</button>
</div>

<dialog id="checkout-dialog" aria-labelledby="checkout-title">
    <div class="dlg" style="position:relative">
        <button class="x" type="button" data-close-checkout aria-label="Tutup">×</button>
        <span class="demo">Pembayaran QRIS</span>
        <h2 id="checkout-title">Checkout</h2>
        <form id="checkout-form" action="{{ route('checkout.store') }}" method="post">
            @csrf
            <div class="row">
                <div><b>{{ $product->name }}</b><br><small>Rp {{ $priceFmt }} / porsi</small></div>
                <div class="qty"><button type="button" id="quantity-minus" aria-label="Kurangi jumlah">−</button><output id="quantity-output" aria-live="polite">1</output><button type="button" id="quantity-plus" aria-label="Tambah jumlah">+</button></div>
            </div>
            <input type="hidden" id="quantity" name="quantity" value="1">
            <div class="row total"><span>Total</span><b id="checkout-total"></b></div>
            <label class="checkout-field">Nama<input name="name" autocomplete="name" required maxlength="120"></label>
            <label class="checkout-field">Nomor WhatsApp / Telepon<input name="phone" type="tel" autocomplete="tel" inputmode="tel" required maxlength="20" placeholder="08xxxxxxxxxx"></label>
            <label class="checkout-field">Alamat pengiriman<textarea name="address" autocomplete="street-address" required maxlength="1000" rows="3"></textarea></label>
            <label class="checkout-field">Catatan (opsional)<textarea name="note" maxlength="1000" rows="2"></textarea></label>
            <p class="err" id="checkout-error" role="alert" aria-live="polite"></p>
            <button class="btn primary" id="checkout-submit" type="submit" style="width:100%;justify-content:center">Pay Now</button>
        </form>
    </div>
</dialog>
<div id="toast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
@php
    $checkoutConfig = [
        'price' => $product->price,
        'stock' => $availableStock,
        'csrfToken' => csrf_token(),
    ];
@endphp
<script>
    window.checkoutConfig = @json($checkoutConfig);
</script>
<script>
(function () {
    var root = document.documentElement;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Menu mobile */
    var nav = document.getElementById('site-nav');
    var toggle = document.querySelector('[data-menu-toggle]');
    function setMenu(open) {
        nav.classList.toggle('open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
    }
    if (toggle) {
        toggle.addEventListener('click', function () { setMenu(!nav.classList.contains('open')); });
        nav.querySelectorAll('nav a').forEach(function (a) { a.addEventListener('click', function () { setMenu(false); }); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setMenu(false); });
    }

    /* Scroll: progress bar, nav shadow, buy bar, parallax */
    var hero = document.querySelector('.hero');
    var buybar = document.querySelector('[data-buybar]');
    var pars = [].slice.call(document.querySelectorAll('[data-par]'));
    var ticking = false;
    function onScroll() {
        var y = window.scrollY || 0;
        var max = root.scrollHeight - window.innerHeight;
        root.style.setProperty('--p', max > 0 ? (y / max).toFixed(4) : 0);
        nav.classList.toggle('scrolled', y > 8);
        if (buybar && hero) buybar.classList.toggle('on', y > hero.offsetHeight * 0.75);
        if (!reduce) {
            var mid = window.innerHeight / 2;
            pars.forEach(function (el) {
                var r = el.getBoundingClientRect();
                if (r.bottom < -200 || r.top > window.innerHeight + 200) return;
                el.style.setProperty('--d', Math.round(r.top + r.height / 2 - mid));
            });
        }
        ticking = false;
    }
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    window.addEventListener('resize', onScroll);
    onScroll();

    /* Tilt produk di hero */
    var stage = document.querySelector('[data-tilt]');
    if (stage && !reduce && window.matchMedia('(hover: hover)').matches) {
        stage.addEventListener('pointermove', function (e) {
            var r = stage.getBoundingClientRect();
            var x = (e.clientX - r.left) / r.width - 0.5;
            var y = (e.clientY - r.top) / r.height - 0.5;
            stage.style.setProperty('--ry', (x * 12).toFixed(2) + 'deg');
            stage.style.setProperty('--rx', (-y * 12).toFixed(2) + 'deg');
        });
        stage.addEventListener('pointerleave', function () {
            stage.style.setProperty('--ry', '0deg');
            stage.style.setProperty('--rx', '0deg');
        });
    }

    /* Kartu balik: tahu sebelum / sesudah */
    var flipper = document.getElementById('flipper');
    if (flipper) {
        var btn = flipper.querySelector('[data-flip-btn]');
        var card = flipper.querySelector('[data-flip-card]');
        var label = btn.querySelector('span');
        var front = flipper.querySelector('[data-face="front"]');
        var back = flipper.querySelector('[data-face="back"]');
        var flipped = false, timer = null;
        function setFlip(v) {
            flipped = v;
            flipper.classList.toggle('is-flipped', v);
            btn.setAttribute('aria-pressed', v ? 'true' : 'false');
            label.textContent = v ? 'Balik lagi' : 'Balik!';
            front.setAttribute('aria-hidden', v ? 'true' : 'false');
            back.setAttribute('aria-hidden', v ? 'false' : 'true');
        }
        function stop() { if (timer) { clearInterval(timer); timer = null; } }
        function manual() { stop(); setFlip(!flipped); }
        btn.addEventListener('click', manual);
        card.addEventListener('click', manual);
        if (!reduce && 'IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting && !timer && !flipper.dataset.touched) {
                        timer = setInterval(function () { setFlip(!flipped); }, 3800);
                    } else if (!en.isIntersecting) { stop(); }
                });
            }, { threshold: 0.5 }).observe(flipper);
            [btn, card].forEach(function (el) { el.addEventListener('click', function () { flipper.dataset.touched = '1'; }); });
        }
    }
})();
</script>
<script src="{{ asset('js/site.js') }}" defer></script>
@endpush