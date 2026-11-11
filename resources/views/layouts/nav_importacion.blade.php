{{-- Barra superior (nueva UI). Se mantiene el nombre del parcial porque todas las vistas lo incluyen. --}}
<header class="sac-top">
  <button class="sac-ib" type="button" aria-label="Ocultar o mostrar menú lateral" title="Ocultar/mostrar menú (Ctrl+B)" aria-controls="sacSide" aria-expanded="true" data-sac-toggle>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M9 3v18"></path></svg>
  </button>

  <img class="sac-top-logo" src="{{ asset('images/logo-20-iso.png') }}" alt="Unimark 20 años" width="36" height="36">
  <div class="sac-top-title">Unimark S.A. · <strong id="sacTopTitle">@yield('sac_title', 'SAC')</strong></div>

  <div class="ms-auto d-flex align-items-center gap-2">
    <button class="sac-ib" type="button" data-sac-theme-toggle aria-pressed="false" aria-label="Cambiar a modo oscuro" title="Modo oscuro">
      <svg class="sac-ico-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>
      <svg class="sac-ico-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2m-7.07-14.07 1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>
    </button>
    <div class="dropdown">
      <button class="btn d-flex align-items-center gap-2" id="navbarDropdownUser" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border: 1px solid var(--sac-input-border); background: var(--sac-surface); color: var(--sac-ink)">
        <span class="text-start lh-sm d-none d-sm-block">
          <span class="d-block fw-semi-bold" style="font-size: 13px">{{ Session::get('name_session') }}</span>
          <span class="d-block" style="font-size: 12px; color: var(--sac-muted)">{{ Session::get('Rutas') }}</span>
        </span>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
      </button>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownUser">
        @if( Session::get('rol') == '1' || Session::get('rol') == '2')
          <a class="dropdown-item" href="{{ route('ArticuloFavorito') }}">Art. favoritos</a>
          <!-- <a class="dropdown-item" href="{{ route('CalendarPromocion') }}">Calendario de promociones</a> -->
          <a class="dropdown-item" href="{{ route('Usuarios') }}">Usuarios</a>
          <a class="dropdown-item" href="{{ route('monitoring') }}">Monitoreo</a>
          <div class="dropdown-divider"></div>
        @endif
        <a class="dropdown-item" href="{{ route('logout') }}">Cerrar sesión</a>
      </div>
    </div>
  </div>
</header>
