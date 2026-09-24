<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="{{ url('/') }}" class="brand-link">
      <!--begin::Brand Image-->
      <img
        src="{{ asset('vendor/adminlte/assets/img/AdminLTELogo.png') }}"
        alt="AdminLTE Logo"
        class="brand-image opacity-75 shadow"
      />
      <!--end::Brand Image-->
      <!--begin::Brand Text-->
      <span class="brand-text fw-light">{{ config('app.name', 'SMS-V01') }}</span>
      <!--end::Brand Text-->
    </a>
    <!--end::Brand Link-->
  </div>
  <!--end::Sidebar Brand-->

  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2" aria-label="Main navigation">
      <!--begin::Sidebar Menu-->
      <ul
        class="nav sidebar-menu flex-column"
        data-lte-toggle="treeview"
        role="menu"
        data-accordion="false"
      >
        <li class="nav-header">NAVIGATION</li>
        <li class="nav-item">
          <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="nav-icon bi bi-speedometer2"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-header">ACADEMIC CORE</li>
        <li class="nav-item {{ request()->is('academic*') ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ request()->is('academic*') ? 'active' : '' }}">
            <i class="nav-icon bi bi-diagram-3-fill"></i>
            <p>
              Academic Structure
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('academic.campuses.index') }}" class="nav-link {{ request()->routeIs('academic.campuses.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-buildings"></i>
                <p>Campuses</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.faculties.index') }}" class="nav-link {{ request()->routeIs('academic.faculties.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-mortarboard"></i>
                <p>Faculties</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.university.edit') }}" class="nav-link {{ request()->routeIs('academic.university.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-bank"></i>
                <p>Institution Profile</p>
              </a>
            </li>
          </ul>
        </li>

        <li class="nav-header">SYSTEM</li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>Users</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-gear-fill"></i>
            <p>Settings</p>
          </a>
        </li>
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
