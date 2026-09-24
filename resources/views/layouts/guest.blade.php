<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'SMS-V01'))</title>

    <!--begin::Theme Init-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;
        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }
        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {}
        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="{{ asset('vendor/source-sans-3/index.css') }}"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->

    @stack('styles')
  </head>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="login-page bg-body-secondary">
    <main class="login-box">
      <div class="card card-outline card-primary">
        <div class="card-header text-center">
          <a href="{{ url('/') }}" class="link-dark text-center link-offset-2 link-opacity-100 link-opacity-50-hover text-decoration-none">
            <h1 class="mb-0"><b>{{ config('app.name', 'SMS-V01') }}</b></h1>
          </a>
        </div>
        <div class="card-body login-card-body">
          @yield('content')
        </div>
      </div>
    </main>

    <!--begin::Required Plugin(Bootstrap 5 with Popper)-->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!--end::Required Plugin(Bootstrap 5 with Popper)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('vendor/adminlte/js/adminlte.min.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)-->

    @stack('scripts')
  </body>
  <!--end::Body-->
</html>
