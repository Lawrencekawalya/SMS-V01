<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'SMS-V01'))</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load)-->
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

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="{{ asset('vendor/overlayscrollbars/css/overlayscrollbars.min.css') }}"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::Plugin(Tabulator / DataTables for AdminLTE 4)-->
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/css/tabulator_bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendor/tabulator/css/adminlte-tabulator.css') }}" />
    <!--end::Plugin(Tabulator / DataTables for AdminLTE 4)-->

    @stack('styles')
  </head>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      @include('layouts.partials.navbar')
      @include('layouts.partials.sidebar')

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <div class="container-fluid">
            <div class="row">
              <div class="col-sm-6">
                <h3 class="mb-0">@yield('page-title', 'Dashboard')</h3>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                  @section('breadcrumb')
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">@yield('page-title', 'Dashboard')</li>
                  @show
                </ol>
              </div>
            </div>
          </div>
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <div class="container-fluid">
            @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if (session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-circle-fill me-2"></i>
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @yield('content')
          </div>
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      @include('layouts.partials.footer')
    </div>
    <!--end::App Wrapper-->

    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script src="{{ asset('vendor/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js') }}"></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Required Plugin(Bootstrap 5 with Popper)-->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!--end::Required Plugin(Bootstrap 5 with Popper)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('vendor/adminlte/js/adminlte.min.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined' && !isMobile) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!--begin::Plugin(Tabulator / DataTables for AdminLTE 4)-->
    <script src="{{ asset('vendor/tabulator/js/tabulator.min.js') }}"></script>
    <script src="{{ asset('vendor/tabulator/js/adminlte-tabulator.js') }}"></script>
    <!--end::Plugin(Tabulator / DataTables for AdminLTE 4)-->

    @stack('scripts')
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
