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

        @php
          $isAcademicStructureActive = request()->routeIs([
            'campus.*',
            'faculty.*',
            'department.*',
            'programme.*',
            'curriculum.*',
            'course.*',
            'academic-year.*',
            'semester.*',
            'event.*',
            'student.*',
            'university.*',
          ]);

          $isCourseRegistrationActive = request()->routeIs([
            'registration.*',
            'approval.*',
          ]);

          $pendingApprovalsCount = \Illuminate\Support\Facades\Schema::hasTable('course_registrations')
            ? \App\Models\CourseRegistration::whereIn('status', ['submitted', 'add_drop_pending'])->count()
            : 0;
        @endphp

        <li class="nav-header">ACADEMIC CORE</li>

        <!--begin::Academic Structure Treeview-->
        <li class="nav-item {{ $isAcademicStructureActive ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ $isAcademicStructureActive ? 'active' : '' }}">
            <i class="nav-icon bi bi-diagram-3-fill"></i>
            <p>
              Academic Structure
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('campus.list') }}" class="nav-link {{ request()->routeIs('campus.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-buildings"></i>
                <p>Campuses</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('faculty.list') }}" class="nav-link {{ request()->routeIs('faculty.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-mortarboard"></i>
                <p>Faculties</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('department.list') }}" class="nav-link {{ request()->routeIs('department.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-diagram-3"></i>
                <p>Departments</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('programme.list') }}" class="nav-link {{ request()->routeIs('programme.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-award"></i>
                <p>Programmes</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('curriculum.list') }}" class="nav-link {{ request()->routeIs('curriculum.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-mortarboard-fill"></i>
                <p>Curriculums</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('course.list') }}" class="nav-link {{ request()->routeIs('course.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-journal-bookmark-fill"></i>
                <p>Course Unit Catalog</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic-year.list') }}" class="nav-link {{ request()->routeIs('academic-year.*') || request()->routeIs('semester.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-calendar3"></i>
                <p>Academic Calendar</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('event.list') }}" class="nav-link {{ request()->routeIs('event.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-calendar-event"></i>
                <p>Events & Almanac</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('student.list') }}" class="nav-link {{ request()->routeIs('student.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-people"></i>
                <p>Students</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('university.edit') }}" class="nav-link {{ request()->routeIs('university.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-bank"></i>
                <p>Institution Profile</p>
              </a>
            </li>
          </ul>
        </li>
        <!--end::Academic Structure Treeview-->

        <!--begin::Course Registration Treeview-->
        <li class="nav-item {{ $isCourseRegistrationActive ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ $isCourseRegistrationActive ? 'active' : '' }}">
            <i class="nav-icon bi bi-journal-check"></i>
            <p>
              Course Registration
              @if ($pendingApprovalsCount > 0)
                <span class="badge text-bg-warning ms-1">{{ $pendingApprovalsCount }}</span>
              @endif
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('registration.list') }}" class="nav-link {{ request()->routeIs('registration.list') || (request()->routeIs('registration.show') && !request()->routeIs('registration.print')) ? 'active' : '' }}">
                <i class="nav-icon bi bi-journal-text"></i>
                <p>Registration Slips</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('registration.create') }}" class="nav-link {{ request()->routeIs('registration.create') ? 'active' : '' }}">
                <i class="nav-icon bi bi-pencil-square"></i>
                <p>Register Courses</p>
              </a>
            </li>
            @if (config('academic.enforce_prerequisites', false))
              <li class="nav-item">
                <a href="{{ route('registration.eligibility') }}" class="nav-link {{ request()->routeIs('registration.eligibility') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-shield-check"></i>
                  <p>Course Eligibility</p>
                </a>
              </li>
            @endif
            @if (config('academic.require_registration_approval', false))
              <li class="nav-item">
                <a href="{{ route('approval.list') }}" class="nav-link {{ request()->routeIs('approval.*') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-clipboard-check"></i>
                  <p>
                    Advisor Approvals
                    @if ($pendingApprovalsCount > 0)
                      <span class="badge text-bg-warning ms-1">{{ $pendingApprovalsCount }}</span>
                    @endif
                  </p>
                </a>
              </li>
            @endif
            <li class="nav-item">
              <a href="{{ route('registration.active-session') }}" class="nav-link {{ request()->routeIs('registration.active-session') ? 'active' : '' }}">
                <i class="nav-icon bi bi-calendar2-check"></i>
                <p>Active Session Cohorts</p>
              </a>
            </li>
          </ul>
        </li>
        <!--end::Course Registration Treeview-->

        @php
          $isAssessmentActive = request()->routeIs([
            'assessment.*',
            'moderation.*',
            'result.*',
            'broad-sheet.*',
            'analytics.*',
          ]);

          $pendingModerationCount = \Illuminate\Support\Facades\Schema::hasTable('course_assessment_sheets')
            ? \App\Models\CourseAssessmentSheet::where('status', 'submitted_to_hod')->count()
            : 0;
        @endphp

        <!--begin::Examinations & Grading Treeview-->
        <li class="nav-item {{ $isAssessmentActive ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ $isAssessmentActive ? 'active' : '' }}">
            <i class="nav-icon bi bi-award-fill"></i>
            <p>
              Examinations &amp; Grading
              @if ($pendingModerationCount > 0)
                <span class="badge text-bg-warning ms-1">{{ $pendingModerationCount }}</span>
              @endif
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('assessment.policy') }}" class="nav-link {{ request()->routeIs('assessment.policy') ? 'active' : '' }}">
                <i class="nav-icon bi bi-sliders"></i>
                <p>Grading Policy &amp; Scale</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('assessment.list') }}" class="nav-link {{ request()->routeIs('assessment.list') || (request()->routeIs('assessment.show') && !request()->routeIs('moderation.*')) ? 'active' : '' }}">
                <i class="nav-icon bi bi-card-checklist"></i>
                <p>Course Mark Sheets</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('moderation.list') }}" class="nav-link {{ request()->routeIs('moderation.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-person-check-fill"></i>
                <p>
                  HoD Moderation Desk
                  @if ($pendingModerationCount > 0)
                    <span class="badge text-bg-warning ms-1">{{ $pendingModerationCount }}</span>
                  @endif
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('result.list') }}" class="nav-link {{ request()->routeIs('result.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-file-earmark-text-fill"></i>
                <p>Results &amp; Transcripts</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('broad-sheet.list') }}" class="nav-link {{ request()->routeIs('broad-sheet.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-table"></i>
                <p>Senate Broad-Sheets</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('analytics.list') }}" class="nav-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-graph-up-arrow"></i>
                <p>Academic Analytics</p>
              </a>
            </li>
          </ul>
        </li>
        <!--end::Examinations & Grading Treeview-->

        @php
          $isGraduationActive = request()->routeIs('graduation.*');
        @endphp

        <!--begin::Graduation & Clearance Treeview-->
        <li class="nav-item {{ $isGraduationActive ? 'menu-open' : '' }}">
          <a href="#" class="nav-link {{ $isGraduationActive ? 'active' : '' }}">
            <i class="nav-icon bi bi-mortarboard-fill"></i>
            <p>
              Graduation &amp; Clearance
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="{{ route('graduation.list') }}" class="nav-link {{ request()->routeIs('graduation.list') || request()->routeIs('graduation.audit') ? 'active' : '' }}">
                <i class="nav-icon bi bi-shield-check"></i>
                <p>Clearance Audit</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('graduation.honors-roll') }}" class="nav-link {{ request()->routeIs('graduation.honors-roll') ? 'active' : '' }}">
                <i class="nav-icon bi bi-journal-bookmark-fill"></i>
                <p>Honors Roll Gazette</p>
              </a>
            </li>
          </ul>
        </li>
        <!--end::Graduation & Clearance Treeview-->

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
