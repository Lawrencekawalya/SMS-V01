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
            'academic.campuses.*',
            'academic.faculties.*',
            'academic.departments.*',
            'academic.programmes.*',
            'academic.curriculums.*',
            'academic.courses.*',
            'academic.academic-years.*',
            'academic.semesters.*',
            'academic.events.*',
            'academic.students.*',
            'academic.university.*',
          ]);

          $isCourseRegistrationActive = request()->routeIs([
            'academic.registrations.*',
            'academic.approvals.*',
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
              <a href="{{ route('academic.departments.index') }}" class="nav-link {{ request()->routeIs('academic.departments.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-diagram-3"></i>
                <p>Departments</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.programmes.index') }}" class="nav-link {{ request()->routeIs('academic.programmes.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-award"></i>
                <p>Programmes</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.curriculums.index') }}" class="nav-link {{ request()->routeIs('academic.curriculums.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-mortarboard-fill"></i>
                <p>Curriculums</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.courses.index') }}" class="nav-link {{ request()->routeIs('academic.courses.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-journal-bookmark-fill"></i>
                <p>Course Unit Catalog</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.academic-years.index') }}" class="nav-link {{ request()->routeIs('academic.academic-years.*') || request()->routeIs('academic.semesters.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-calendar3"></i>
                <p>Academic Calendar</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.events.index') }}" class="nav-link {{ request()->routeIs('academic.events.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-calendar-event"></i>
                <p>Events & Almanac</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.students.index') }}" class="nav-link {{ request()->routeIs('academic.students.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-people"></i>
                <p>Students</p>
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
              <a href="{{ route('academic.registrations.index') }}" class="nav-link {{ request()->routeIs('academic.registrations.index') || (request()->routeIs('academic.registrations.show') && !request()->routeIs('academic.registrations.print')) ? 'active' : '' }}">
                <i class="nav-icon bi bi-journal-text"></i>
                <p>Registration Slips</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.registrations.create') }}" class="nav-link {{ request()->routeIs('academic.registrations.create') ? 'active' : '' }}">
                <i class="nav-icon bi bi-pencil-square"></i>
                <p>Register Courses</p>
              </a>
            </li>
            @if (config('academic.enforce_prerequisites', false))
              <li class="nav-item">
                <a href="{{ route('academic.registrations.eligibility') }}" class="nav-link {{ request()->routeIs('academic.registrations.eligibility') ? 'active' : '' }}">
                  <i class="nav-icon bi bi-shield-check"></i>
                  <p>Course Eligibility</p>
                </a>
              </li>
            @endif
            @if (config('academic.require_registration_approval', false))
              <li class="nav-item">
                <a href="{{ route('academic.approvals.index') }}" class="nav-link {{ request()->routeIs('academic.approvals.*') ? 'active' : '' }}">
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
              <a href="{{ route('academic.registrations.active-session') }}" class="nav-link {{ request()->routeIs('academic.registrations.active-session') ? 'active' : '' }}">
                <i class="nav-icon bi bi-calendar2-check"></i>
                <p>Active Session Cohorts</p>
              </a>
            </li>
          </ul>
        </li>
        <!--end::Course Registration Treeview-->

        @php
          $isAssessmentActive = request()->routeIs([
            'academic.assessments.*',
            'academic.results.*',
            'academic.reports.*',
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
              <a href="{{ route('academic.assessments.policy') }}" class="nav-link {{ request()->routeIs('academic.assessments.policy') ? 'active' : '' }}">
                <i class="nav-icon bi bi-sliders"></i>
                <p>Grading Policy &amp; Scale</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.assessments.index') }}" class="nav-link {{ request()->routeIs('academic.assessments.index') || (request()->routeIs('academic.assessments.show') && !request()->routeIs('academic.assessments.moderation.*')) ? 'active' : '' }}">
                <i class="nav-icon bi bi-card-checklist"></i>
                <p>Course Mark Sheets</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.assessments.moderation.index') }}" class="nav-link {{ request()->routeIs('academic.assessments.moderation.*') ? 'active' : '' }}">
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
              <a href="{{ route('academic.results.index') }}" class="nav-link {{ request()->routeIs('academic.results.*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-file-earmark-text-fill"></i>
                <p>Results &amp; Transcripts</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.reports.broad-sheet') }}" class="nav-link {{ request()->routeIs('academic.reports.broad-sheet*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-table"></i>
                <p>Senate Broad-Sheets</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.reports.analytics') }}" class="nav-link {{ request()->routeIs('academic.reports.analytics*') ? 'active' : '' }}">
                <i class="nav-icon bi bi-graph-up-arrow"></i>
                <p>Academic Analytics</p>
              </a>
            </li>
          </ul>
        </li>
        <!--end::Examinations & Grading Treeview-->

        @php
          $isGraduationActive = request()->routeIs('academic.graduation.*');
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
              <a href="{{ route('academic.graduation.index') }}" class="nav-link {{ request()->routeIs('academic.graduation.index') || request()->routeIs('academic.graduation.audit') ? 'active' : '' }}">
                <i class="nav-icon bi bi-shield-check"></i>
                <p>Clearance Audit</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('academic.graduation.honors-roll') }}" class="nav-link {{ request()->routeIs('academic.graduation.honors-roll') ? 'active' : '' }}">
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
