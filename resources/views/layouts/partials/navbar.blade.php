<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Start Navbar Links-->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
          <i class="bi bi-list"></i>
        </a>
      </li>
      <li class="nav-item d-none d-md-block">
        <a href="{{ url('/') }}" class="nav-link">Dashboard</a>
      </li>
    </ul>
    <!--end::Start Navbar Links-->

    <!--begin::End Navbar Links-->
    <ul class="navbar-nav ms-auto">
      <!--begin::Fullscreen Toggle-->
      <li class="nav-item">
        <a class="nav-link" href="#" data-lte-toggle="fullscreen" aria-label="Toggle fullscreen">
          <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
          <i data-lte-icon="minimize" class="bi bi-fullscreen-exit d-none"></i>
        </a>
      </li>
      <!--end::Fullscreen Toggle-->

      <!--begin::Color Mode Toggle-->
      <li class="nav-item dropdown">
        <a
          class="nav-link"
          href="#"
          id="bd-theme"
          aria-label="Toggle color scheme"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
          <i class="bi bi-sun-fill" data-lte-theme-icon="light"></i>
          <i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark"></i>
          <i class="bi bi-circle-half d-none" data-lte-theme-icon="auto"></i>
        </a>
        <ul
          class="dropdown-menu dropdown-menu-end"
          aria-labelledby="bd-theme"
          style="--bs-dropdown-min-width: 8rem"
        >
          <li>
            <button
              type="button"
              class="dropdown-item d-flex align-items-center"
              data-bs-theme-value="light"
              aria-pressed="false"
            >
              <i class="bi bi-sun-fill me-2"></i>
              Light
              <i class="bi bi-check-lg ms-auto d-none"></i>
            </button>
          </li>
          <li>
            <button
              type="button"
              class="dropdown-item d-flex align-items-center"
              data-bs-theme-value="dark"
              aria-pressed="false"
            >
              <i class="bi bi-moon-fill me-2"></i>
              Dark
              <i class="bi bi-check-lg ms-auto d-none"></i>
            </button>
          </li>
          <li>
            <button
              type="button"
              class="dropdown-item d-flex align-items-center active"
              data-bs-theme-value="auto"
              aria-pressed="true"
            >
              <i class="bi bi-circle-half me-2"></i>
              Auto
              <i class="bi bi-check-lg ms-auto d-none"></i>
            </button>
          </li>
        </ul>
      </li>
      <!--end::Color Mode Toggle-->

      <!--begin::Academic Events Notification Dropdown-->
      <li class="nav-item dropdown">
        <a
          class="nav-link position-relative"
          data-bs-toggle="dropdown"
          href="#"
          role="button"
          aria-expanded="false"
          title="Scheduled Academic Events & University Almanac"
        >
          <i class="bi bi-calendar3"></i>
          @if (($navbarEventsCount ?? 0) > 0)
            <span class="navbar-badge badge text-bg-warning rounded-pill">
              {{ $navbarEventsCount > 99 ? '99+' : $navbarEventsCount }}
            </span>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow">
          <div class="dropdown-item dropdown-header d-flex justify-content-between align-items-center py-2 bg-body-secondary fw-bold">
            <span><i class="bi bi-calendar-event me-1 text-primary"></i> Academic Events</span>
            <span class="badge text-bg-primary rounded-pill">{{ $navbarEventsCount ?? 0 }} Upcoming</span>
          </div>
          <div class="dropdown-divider m-0"></div>

          @forelse ($navbarUpcomingEvents ?? [] as $event)
            <a href="{{ route('academic.events.index') }}" class="dropdown-item py-2 px-3">
              <div class="d-flex align-items-start gap-2">
                <span class="mt-1">
                  @if ($event->event_type === 'examination')
                    <i class="bi bi-pencil-square text-danger"></i>
                  @elseif ($event->event_type === 'academic_deadline')
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                  @elseif ($event->event_type === 'holiday')
                    <i class="bi bi-calendar-heart text-success"></i>
                  @elseif ($event->event_type === 'ceremony')
                    <i class="bi bi-mortarboard-fill text-info"></i>
                  @elseif ($event->event_type === 'governance')
                    <i class="bi bi-building-check text-secondary"></i>
                  @else
                    <i class="bi bi-book-half text-primary"></i>
                  @endif
                </span>
                <div class="flex-grow-1 text-truncate">
                  <div class="fw-semibold text-truncate small">{{ $event->title }}</div>
                  <div class="text-body-secondary" style="font-size: 0.75rem;">
                    <i class="bi bi-clock me-1"></i>{{ $event->start_date->format('M d, Y') }}
                    @if ($event->end_date && $event->end_date->ne($event->start_date))
                      &ndash; {{ $event->end_date->format('M d') }}
                    @endif
                    <span class="badge {{ $event->badge_class }} ms-1 py-0 px-1" style="font-size: 0.65rem;">
                      {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                    </span>
                  </div>
                </div>
              </div>
            </a>
            <div class="dropdown-divider m-0"></div>
          @empty
            <div class="dropdown-item text-center text-muted py-3">
              <i class="bi bi-calendar-check text-secondary d-block fs-4 mb-1"></i>
              <span class="small">No upcoming academic events</span>
            </div>
            <div class="dropdown-divider m-0"></div>
          @endforelse

          <a href="{{ route('academic.events.index') }}" class="dropdown-item dropdown-footer text-center fw-bold text-primary py-2">
            See All Events &amp; Almanac <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </li>
      <!--end::Academic Events Notification Dropdown-->

      <!--begin::User Menu Dropdown-->
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
          <img
            src="{{ asset('vendor/adminlte/assets/img/user2-160x160.jpg') }}"
            class="user-image rounded-circle shadow"
            alt="User Image"
          />
          <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'Administrator' }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
          <!--begin::User Image-->
          <li class="user-header text-bg-primary">
            <img
              src="{{ asset('vendor/adminlte/assets/img/user2-160x160.jpg') }}"
              class="rounded-circle shadow"
              alt="User Image"
            />
            <p>
              {{ auth()->user()->name ?? 'Administrator' }}
              <small>{{ auth()->user()->email ?? 'admin@sms-v01.local' }}</small>
            </p>
          </li>
          <!--end::User Image-->
          <!--begin::Menu Footer-->
          <li class="user-footer">
            <a href="#" class="btn btn-outline-secondary">Profile</a>
            <a href="#" class="btn btn-outline-danger float-end">Sign out</a>
          </li>
          <!--end::Menu Footer-->
        </ul>
      </li>
      <!--end::User Menu Dropdown-->
    </ul>
    <!--end::End Navbar Links-->
  </div>
  <!--end::Container-->
</nav>
<!--end::Header-->
