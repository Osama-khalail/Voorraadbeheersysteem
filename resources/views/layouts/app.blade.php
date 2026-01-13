<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Voorraadbeheersysteem')</title>

    {{-- Google Font (Roboto) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Roboto', sans-serif; }
    </style>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}"/>

    <style>
        /* Simple page transition styles */
        .page-shell { will-change: transform, opacity; transition: transform 420ms cubic-bezier(.2,.9,.2,1), opacity 420ms cubic-bezier(.2,.9,.2,1); }
        .page-enter { opacity: 0; transform: translateY(12px) scale(0.992); }
        .page-exit { opacity: 0; transform: translateY(-12px) scale(0.992); }
        /* Title/subtitle transitions */
        .page-title, .page-subtitle { will-change: transform, opacity; }
        .title-enter { opacity: 0; transform: translateY(-6px); }
        .title-exit { opacity: 0; transform: translateY(6px); }
        .page-title { transition: transform 420ms cubic-bezier(.2,.9,.2,1), opacity 420ms cubic-bezier(.2,.9,.2,1); transition-delay: 90ms; }
        .page-subtitle { transition: transform 460ms cubic-bezier(.2,.9,.2,1), opacity 460ms cubic-bezier(.2,.9,.2,1); transition-delay: 140ms; }
        /* When exiting, remove delays to make exit feel snappy */
        .title-exit.page-title, .title-exit.page-subtitle, .page-exit .page-title, .page-exit .page-subtitle { transition-delay: 0ms !important; }
    </style>

</head>

<body class="bg-neutral-100 text-neutral-900">
    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <aside class="w-64 bg-[var(--bg-zijbalk)] border-r border-neutral-200 px-4 py-6 flex flex-col">
            <div class="flex items-center gap-3 mb-8">
                <div class="h-10 w-10 rounded ">
                    <img src="{{ asset('favicon.png') }}" alt="Reme Techniek" class="h-10 w-10 p-1"/>
                </div>
                <div>
                    <p class="  font-bold leading-tight">Voorraad</p>
                    <p class="text-xs text-neutral-500">Reme Techniek</p>
                </div>
            </div>

            <nav class="space-y-3">
                <a href="/dashboard"
                   class="text-[var(--text-algemeen)] flex items-center gap-4 rounded-lg px-4 py-3 text-base hover:bg-neutral-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="/materialen"
                   class="text-[var(--text-algemeen)] flex items-center gap-4 rounded-lg px-4 py-3 text-base hover:bg-neutral-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Materialen
                </a>

                <a href="/logboek"
                   class="text-[var(--text-algemeen)] flex items-center gap-4 rounded-lg px-4 py-3 text-base hover:bg-neutral-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Logboek
                </a>

                @auth
                    @if((auth()->user()->role ?? '') === 'admin')
                        <a href="/gebruikers"
                           class="text-[var(--text-algemeen)] flex items-center gap-4 rounded-lg px-4 py-3 text-base hover:bg-neutral-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                            Gebruikers
                        </a>
                    @endif
                @endauth
            </nav>

                <div class="mt-auto pt-6 border-t border-neutral-200 space-y-3">
                <a href="/account" class="text-[var(--text-algemeen)] flex items-center gap-4 w-full text-left rounded-lg px-4 py-3 text-base hover:bg-neutral-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Account
                </a>
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" class="text-[var(--text-algemeen)] flex items-center gap-4 w-full text-left rounded-lg px-4 py-3 text-base hover:bg-neutral-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Uitloggen
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col">

            {{-- Topbar --}}
            <header class="h-16 bg-white border-b border-neutral-200 px-6 flex items-center justify-between">
                <div>
                    <h1 id="page-title" class="page-title title-enter text-[var(--text-algemeen)] text-lg font-semibold">
                        @yield('page_title', 'Pagina')
                    </h1>
                    <p id="page-subtitle" class="page-subtitle title-enter text-xs text-neutral-500">
                        @yield('page_subtitle', 'Welkom terug')
                    </p>
                </div>
                <form action="{{ route('materialen.index') }}" method="GET" class="hidden sm:flex items-center">
                    <div class="flex items-center bg-white border border-neutral-200 rounded-lg overflow-hidden">
                        <input name="q" type="search" placeholder="Zoeken in type, leverancier, omschrijving, categorie, voorraad, min. voorraad" value="{{ request('q') }}" class="text-[var(--text-algemeen)] px-4 py-2 w-56 text-sm sm:w-80 focus:outline-none" />
                        <button type="submit" class="px-4 py-2 bg-[#EA5521] text-white text-sm font-medium hover:opacity-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                            </svg>
                        </button>
                    </div>
                </form>
                <div class="flex items-center gap-3">
                    <div class="text-sm text-neutral-600 flex items-center gap-4">
                        @auth
                            <div>Ingelogd als: <span class="font-medium">{{ auth()->user()->name }}</span></div>
                        @endauth
                    </div>

                    <div class="h-9 w-9 rounded-full bg-neutral-200"></div>
                </div>
            </header>

            {{-- Content area --}}
            <main class="flex-1 p-8">
                <div class="max-w-9xl mx-auto">
                    <div id="page-shell" class="page-shell page-enter bg-white border border-neutral-200 rounded-xl shadow-sm p-6">
                        @yield('content')
                    </div>
                </div>
            </main>

        </div>
    </div>
    <script>
        // Page transition: animate in on load, animate out on internal link clicks
        (function(){
            const shell = document.getElementById('page-shell');
            const title = document.getElementById('page-title');
            const subtitle = document.getElementById('page-subtitle');
            if (!shell) return;
            // play enter animation: small timeout ensures browser paints initial state first
            requestAnimationFrame(()=>{
                setTimeout(()=>{
                    shell.classList.remove('page-enter');
                    if (title) title.classList.remove('title-enter');
                    if (subtitle) subtitle.classList.remove('title-enter');
                }, 40);
            });

            // Intercept internal link clicks to animate exit
            document.addEventListener('click', function(e){
                const a = e.target.closest('a');
                if (!a) return;
                // ignore external, anchors, targets, downloads, JS links
                const href = a.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
                if (a.target && a.target !== '_self') return;
                // same origin only
                try {
                    const url = new URL(href, window.location.href);
                    if (url.origin !== window.location.origin) return;
                } catch (err) { return; }

                // allow Ctrl/Cmd click, middle click, modifiers
                if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

                e.preventDefault();
                // animate title/subtitle out slightly, then shell exit with small stagger for smoothness
                if (title) title.classList.add('title-exit');
                if (subtitle) subtitle.classList.add('title-exit');
                // small stagger so title moves first
                setTimeout(()=>{ shell.classList.add('page-exit'); }, 100);
                // wait for the full exit transition before navigating
                setTimeout(()=>{ window.location.href = href; }, 560);
            });
        })();
    </script>
</body>
</html>
