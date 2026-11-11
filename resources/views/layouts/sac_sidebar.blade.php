@php
    $rol      = (string) Session::get('rol');
    $esSAC    = in_array($rol, ['1', '2', '9']);
    $esAdmin  = in_array($rol, ['1', '2']);
    $enHome   = request()->routeIs('Home');
    $nombre   = trim((string) Session::get('name_session'));
    $partes   = preg_split('/\s+/', $nombre, -1, PREG_SPLIT_NO_EMPTY);
    $iniciales = strtoupper(mb_substr($partes[0] ?? 'U', 0, 1) . mb_substr($partes[1] ?? '', 0, 1));
    $homeUrl  = route('Home');

    // Icono SVG (lucide) por clave
    $ico = [
        'pedidos'  => '<rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><path d="M12 11h4M12 16h4M8 11h.01M8 16h.01"></path>',
        'promo'    => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"></path><circle cx="7.5" cy="7.5" r=".5"></circle>',
        'inv'      => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path><path d="M12 22V12"></path><path d="m3.3 7 8.7 5 8.7-5"></path>',
        'cli'      => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'liq'      => '<path d="M5 22h14M5 2h14M17 22v-4.172a2 2 0 0 0-.586-1.414L12 12l-4.414 4.414A2 2 0 0 0 7 17.828V22M7 2v4.172a2 2 0 0 0 .586 1.414L12 12l4.414-4.414A2 2 0 0 0 17 6.172V2"></path>',
        'stats'    => '<path d="M3 3v16a2 2 0 0 0 2 2h16"></path><path d="M18 17V9M13 17V5M8 17v-3"></path>',
        'fav'      => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path>',
        'cal'      => '<rect x="3" y="4" width="18" height="18" rx="2"></rect><path d="M16 2v4M8 2v4M3 10h18"></path>',
        'users'    => '<path d="M2 21a8 8 0 0 1 13.292-6"></path><circle cx="10" cy="8" r="5"></circle><path d="m16 19 2 2 4-4"></path>',
        'monitor'  => '<rect x="2" y="3" width="20" height="14" rx="2"></rect><path d="M8 21h8M12 17v4"></path>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="m16 17 5-5-5-5M21 12H9"></path>',
    ];
    $svg = function ($k) use ($ico) {
        return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $ico[$k] . '</svg>';
    };
@endphp

<aside class="sac-side" id="sacSide" aria-label="Navegación principal">
    <a class="sac-brand" href="{{ $homeUrl }}" aria-label="SAC Unimark · inicio">
        <img class="sac-brand-full" src="{{ asset('images/logo-20-white.png') }}" alt="Unimark 20 años · Supliendo Salud" width="196" height="54">
        <img class="sac-brand-iso" src="{{ asset('images/logo-20-iso-white.png') }}" alt="Unimark 20 años" width="44" height="44">
    </a>

    <nav class="sac-nav" aria-label="Secciones">
        @if ($esSAC)
            <a class="sac-ni" data-sac-section="pedidos" href="{{ $homeUrl }}#pedidos" title="Pedidos">{!! $svg('pedidos') !!}<span class="sac-lbl">Pedidos</span></a>
            <a class="sac-ni" data-sac-section="promociones" href="{{ $homeUrl }}#promociones" title="Promociones">{!! $svg('promo') !!}<span class="sac-lbl">Promociones</span></a>
        @endif
        <a class="sac-ni" data-sac-section="inventario" href="{{ $homeUrl }}#inventario" title="Inventario">{!! $svg('inv') !!}<span class="sac-lbl">Inventario</span></a>
        @if ($esSAC)
            <a class="sac-ni" data-sac-section="clientes" href="{{ $homeUrl }}#clientes" title="Clientes">{!! $svg('cli') !!}<span class="sac-lbl">Clientes</span></a>
            <a class="sac-ni" data-sac-section="liquidacion" href="{{ $homeUrl }}#liquidacion" title="Liquidación">{!! $svg('liq') !!}<span class="sac-lbl">Liquidación</span></a>
        @endif
        <a class="sac-ni {{ request()->routeIs('estadisticas') ? 'on' : '' }}" href="{{ route('estadisticas') }}" @if(request()->routeIs('estadisticas')) aria-current="page" @endif title="Estadísticas">{!! $svg('stats') !!}<span class="sac-lbl">Estadísticas</span></a>
    </nav>

    @if ($esAdmin)
        <div class="sac-navsec"><span class="sac-lbl">Administración</span></div>
        <nav class="sac-nav" aria-label="Administración">
            <a class="sac-ni {{ request()->routeIs('ArticuloFavorito') ? 'on' : '' }}" href="{{ route('ArticuloFavorito') }}" title="Art. favoritos">{!! $svg('fav') !!}<span class="sac-lbl">Art. favoritos</span></a>
            <a class="sac-ni {{ request()->routeIs('CalendarPromocion') ? 'on' : '' }}" href="{{ route('CalendarPromocion') }}" title="Calendario promociones">{!! $svg('cal') !!}<span class="sac-lbl">Calendario promociones</span></a>
            <a class="sac-ni {{ request()->routeIs('Usuarios') ? 'on' : '' }}" href="{{ route('Usuarios') }}" title="Usuarios">{!! $svg('users') !!}<span class="sac-lbl">Usuarios</span></a>
            <a class="sac-ni {{ request()->routeIs('monitoring') ? 'on' : '' }}" href="{{ route('monitoring') }}" title="Monitoreo">{!! $svg('monitor') !!}<span class="sac-lbl">Monitoreo</span></a>
        </nav>
    @endif

    <nav class="sac-nav" aria-label="Sesión" style="margin-top: 8px">
        <a class="sac-ni" href="{{ route('logout') }}" title="Cerrar sesión">{!! $svg('logout') !!}<span class="sac-lbl">Cerrar sesión</span></a>
    </nav>

    <div class="sac-foot">
        <span class="sac-av" aria-hidden="true">{{ $iniciales ?: 'U' }}</span>
        <div class="sac-lbl" style="min-width: 0">
            <div class="sac-foot-name">{{ $nombre ?: 'Mi usuario' }}</div>
            <div class="sac-foot-sub">{{ Session::get('Rutas') ?: 'Sin ruta asignada' }}</div>
        </div>
    </div>
</aside>
<div class="sac-scrim" data-sac-close></div>
<script>
    // Aplica el estado colapsado antes de pintar para evitar parpadeo
    try { if (localStorage.getItem('sacSideCollapsed') === '1') document.getElementById('sacApp').classList.add('side-collapsed'); } catch (e) {}
</script>
