@extends('layouts.lyt_gumadesk')
@section('noshell', '1')
@section('content')
<main class="sac-login">
  <button class="sac-ib sac-login-theme" type="button" data-sac-theme-toggle aria-pressed="false" aria-label="Cambiar a modo oscuro" title="Modo oscuro">
    <svg class="sac-ico-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>
    <svg class="sac-ico-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2m-7.07-14.07 1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>
  </button>
  <section class="sac-login-brand" aria-label="Unimark SAC">
    <div class="sac-login-logo">
      <img src="{{ asset('images/logo-20-white.png') }}" alt="Unimark 20 años · Supliendo Salud" width="300" height="83">
      <span class="sac-login-app">SAC · Servicio de Atención al Cliente</span>
    </div>

    <div style="max-width: 480px; display: flex; flex-direction: column; gap: 18px">
      <h1>Atención al cliente, en un solo lugar.</h1>
      <p>Pedidos, promociones, inventario, clientes y liquidación de lotes para tu ruta.</p>
      <div class="d-flex flex-column gap-2 mt-2">
        <span class="sac-pill">Pedidos y comentarios en tiempo real</span>
        <span class="sac-pill">Existencias por bodega y lote</span>
        <span class="sac-pill">Crédito y mora de cada cliente</span>
      </div>
    </div>

    <div style="font-size: 12.5px; color: #64748B">© {{ date('Y') }} Unimark S.A. · Informática</div>
  </section>

  <section class="sac-login-form" aria-label="Iniciar sesión">
    <form method="POST" action="{{ route('login') }}" novalidate>
      @csrf
      <div>
        <h2 style="margin: 0; font-size: 28px; font-weight: 650; letter-spacing: -0.022em">Iniciar sesión</h2>
        <p style="margin: 6px 0 0; font-size: 14.5px; color: #64748B">Bienvenido de nuevo. Ingresa con tu usuario.</p>
      </div>

      <div class="sac-fld">
        <label class="sac-fl" for="username">Usuario</label>
        <span class="sac-inw @error('username') is-invalid @enderror">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          <input id="username" type="text" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus placeholder="Nombre de usuario" @error('username') aria-invalid="true" aria-describedby="username-error" @enderror>
        </span>
        @error('username')
          <span class="sac-err" id="username-error" role="alert">{{ $message }}</span>
        @enderror
      </div>

      <div class="sac-fld">
        <label class="sac-fl" for="password">Contraseña</label>
        <span class="sac-inw @error('password') is-invalid @enderror">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
          <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
          <button type="button" class="sac-eye" id="sacTogglePw" aria-label="Mostrar contraseña" aria-pressed="false">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </button>
        </span>
        @error('password')
          <span class="sac-err" id="password-error" role="alert">{{ $message }}</span>
        @enderror
      </div>

      <button class="sac-go" type="submit" name="submit">Entrar</button>

      <p style="margin: 0; font-size: 13px; color: #64748B; text-align: center">
        ¿Problemas para ingresar? Contacta a soporte de TI.
        @if (ENV('APP_VERSION'))<br><span style="font-size: 12px">Versión {{ ENV('APP_VERSION') }}</span>@endif
      </p>
    </form>
  </section>
</main>
@endsection

@section('metodosjs')
<script>
  (function () {
    var btn = document.getElementById('sacTogglePw');
    var pw = document.getElementById('password');
    if (!btn || !pw) return;
    btn.addEventListener('click', function () {
      var show = pw.type === 'password';
      pw.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    });
  })();
</script>
@endsection
