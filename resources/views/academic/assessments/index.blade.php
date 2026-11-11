@extends('layouts.app')

@section('title', 'Course Assessment & Mark Sheets - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Assessment & Mark Sheets')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item">Examinations &amp; Grading</li>
  <li class="breadcrumb-item active" aria-current="page">Course Mark Sheets</li>
@endsection

@section('content')
  <!-- Flash Messages & Notices -->
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <div class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-2"></i> Validation Errors:</div>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Metrics KPI Row -->
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-journal-bookmark-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Mark Sheets</span>
          <span class="info-box-number fs-4">{{ $stats['total'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-secondary shadow-sm"><i class="bi bi-pencil-square"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">In Draft (Lecturers)</span>
          <span class="info-box-number fs-4">{{ $stats['draft'] + $stats['returned'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-hourglass-split"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Pending HoD Moderation</span>
          <span class="info-box-number fs-4">{{ $stats['submitted'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Senate Published</span>
          <span class="info-box-number fs-4">{{ $stats['published'] }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Institutional Standards Callout -->
  <div class="callout callout-info shadow-sm mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <h5 class="fw-bold mb-1">
          <i class="bi bi-info-circle-fill text-info me-2"></i>Institutional Assessment &amp; Grading Standards
        </h5>
        <p class="mb-0 text-body-secondary small">
          Active university policy:
          <strong>{{ number_format(config('academic.assessment_ca_weight', 40.0), 1) }}% Continuous Assessment (CA)</strong> +
          <strong>{{ number_format(config('academic.assessment_exam_weight', 60.0), 1) }}% Final Exam</strong> = 100.0% Total.
          Minimum pass threshold: <strong>{{ number_format(config('academic.assessment_pass_mark', 50.0), 1) }}%</strong> (NCHE 5.0 GPA scale).
        </p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge text-bg-info">CA: {{ config('academic.assessment_ca_weight', 40) }}%</span>
        <span class="badge text-bg-primary">Exam: {{ config('academic.assessment_exam_weight', 60) }}%</span>
        <span class="badge text-bg-success">Pass Mark: {{ config('academic.assessment_pass_mark', 50) }}%</span>
        <a href="{{ route('assessment.special-exams') }}" class="btn btn-sm btn-outline-primary ms-2">
          <i class="bi bi-star me-1"></i> Special &amp; Supp Exams
        </a>
        <a href="{{ route('assessment.policy') }}" class="btn btn-sm btn-outline-info ms-1">
          <i class="bi bi-sliders me-1"></i> Policy &amp; Scale
        </a>
      </div>
    </div>
  </div>

  <!-- Two-Column Master-Detail Layout -->
  <div class="row g-4">
    <!-- Left Column: Academic Structure Cascade (Campus -> Faculty -> Dept -> Prog -> Curriculum -> Course Unit) -->
    <div class="col-12 col-lg-4 col-xl-4 col-xxl-3">
      <div class="card card-outline card-primary shadow-sm h-100">
        <div class="card-header bg-body-tertiary">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold">
              <i class="bi bi-diagram-3-fill text-primary me-2"></i>Academic Structure Cascade
            </h5>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">
              Hierarchy Selector
            </span>
          </div>
        </div>

        <div class="card-body p-3">
          <!-- Session & Status Filter Form -->
          <form method="GET" action="{{ route('assessment.list') }}" id="cascade-filter-form" class="mb-3">
            @if ($selectedCourseUnit)
              <input type="hidden" name="course_unit_id" value="{{ $selectedCourseUnit->id }}">
            @endif
            @if ($departmentId)
              <input type="hidden" name="department_id" value="{{ $departmentId }}">
            @endif

            <div class="row g-2 mb-2">
              <div class="col-7">
                <label class="form-label small text-muted fw-semibold mb-1">Academic Session</label>
                <select name="semester_id" class="form-select form-select-sm" onchange="this.form.submit()">
                  @foreach ($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSemester && $selectedSemester->id === $sem->id ? 'selected' : '' }}>
                      {{ $sem->name }} ({{ $sem->academicYear->name }}) {{ $sem->is_active ? '★' : '' }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-5">
                <label class="form-label small text-muted fw-semibold mb-1">Sheet Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                  <option value="">All Statuses</option>
                  <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                  <option value="submitted_to_hod" {{ $status === 'submitted_to_hod' ? 'selected' : '' }}>Pending HoD</option>
                  <option value="department_moderated" {{ $status === 'department_moderated' ? 'selected' : '' }}>Moderated</option>
                  <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Published</option>
                </select>
              </div>
            </div>
          </form>

          <!-- Instant Cascade Course Search Input -->
          <div class="input-group input-group-sm mb-3">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="cascade-search" class="form-control" placeholder="Search course code or title..." autocomplete="off">
            <button class="btn btn-outline-secondary" type="button" id="btn-clear-cascade-search" title="Clear Search">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <!-- Academic Cascade Accordion -->
          <div class="accordion accordion-flush cascade-accordion" id="campusAccordion">
            @forelse ($campuses as $campus)
              @php
                $isCampusActive = ($activeCampusId === $campus->id) || ($campuses->count() === 1);
              @endphp
              <div class="accordion-item border rounded mb-2 overflow-hidden campus-node">
                <h2 class="accordion-header" id="heading-campus-{{ $campus->id }}">
                  <button class="accordion-button py-2 px-3 fw-bold {{ $isCampusActive ? '' : 'collapsed' }} bg-body-tertiary"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#collapse-campus-{{ $campus->id }}"
                          aria-expanded="{{ $isCampusActive ? 'true' : 'false' }}"
                          aria-controls="collapse-campus-{{ $campus->id }}">
                    <i class="bi bi-buildings-fill text-primary me-2"></i>
                    <span class="flex-grow-1 text-truncate">{{ $campus->name }}</span>
                    <span class="badge text-bg-light border ms-2 small">{{ $campus->code }}</span>
                  </button>
                </h2>

                <div id="collapse-campus-{{ $campus->id }}"
                     class="accordion-collapse collapse {{ $isCampusActive ? 'show' : '' }}"
                     aria-labelledby="heading-campus-{{ $campus->id }}"
                     data-bs-parent="#campusAccordion">
                  <div class="accordion-body p-2 bg-body">
                    <!-- Level 2: Faculties -->
                    <div class="accordion accordion-flush" id="facultyAccordion-{{ $campus->id }}">
                      @forelse ($campus->faculties as $faculty)
                        @php
                          $isFacultyActive = ($activeFacultyId === $faculty->id) || ($campus->faculties->count() === 1 && $isCampusActive);
                        @endphp
                        <div class="accordion-item border rounded mb-2 overflow-hidden faculty-node">
                          <h3 class="accordion-header" id="heading-faculty-{{ $faculty->id }}">
                            <button class="accordion-button py-2 px-3 {{ $isFacultyActive ? '' : 'collapsed' }} bg-body-secondary"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse-faculty-{{ $faculty->id }}"
                                    aria-expanded="{{ $isFacultyActive ? 'true' : 'false' }}"
                                    aria-controls="collapse-faculty-{{ $faculty->id }}">
                              <i class="bi bi-bank2 text-info me-2"></i>
                              <span class="flex-grow-1 text-truncate fw-semibold small">{{ $faculty->name }}</span>
                              <span class="badge text-bg-secondary ms-1 small">{{ $faculty->code }}</span>
                            </button>
                          </h3>

                          <div id="collapse-faculty-{{ $faculty->id }}"
                               class="accordion-collapse collapse {{ $isFacultyActive ? 'show' : '' }}"
                               aria-labelledby="heading-faculty-{{ $faculty->id }}">
                            <div class="accordion-body p-2">
                              <!-- Level 3: Departments -->
                              <div class="accordion accordion-flush" id="deptAccordion-{{ $faculty->id }}">
                                @forelse ($faculty->departments as $dept)
                                  @php
                                    $isDeptActive = ($activeDepartmentId === $dept->id) || ($faculty->departments->count() === 1 && $isFacultyActive);
                                  @endphp
                                  <div class="accordion-item border rounded mb-2 overflow-hidden dept-node">
                                    <h4 class="accordion-header" id="heading-dept-{{ $dept->id }}">
                                      <button class="accordion-button py-2 px-3 {{ $isDeptActive ? '' : 'collapsed' }} bg-body-tertiary"
                                              type="button"
                                              data-bs-toggle="collapse"
                                              data-bs-target="#collapse-dept-{{ $dept->id }}"
                                              aria-expanded="{{ $isDeptActive ? 'true' : 'false' }}"
                                              aria-controls="collapse-dept-{{ $dept->id }}">
                                        <i class="bi bi-diagram-2-fill text-warning me-2"></i>
                                        <span class="flex-grow-1 text-truncate fw-semibold small">{{ $dept->name }}</span>
                                      </button>
                                    </h4>

                                    <div id="collapse-dept-{{ $dept->id }}"
                                         class="accordion-collapse collapse {{ $isDeptActive ? 'show' : '' }}"
                                         aria-labelledby="heading-dept-{{ $dept->id }}">
                                      <div class="accordion-body p-2">
                                        <!-- Level 4: Programmes -->
                                        <div class="accordion accordion-flush" id="progAccordion-{{ $dept->id }}">
                                          @forelse ($dept->programmes as $prog)
                                            @php
                                              $isProgActive = ($activeProgrammeId === $prog->id) || ($dept->programmes->count() === 1 && $isDeptActive);
                                            @endphp
                                            <div class="accordion-item border rounded mb-2 overflow-hidden prog-node">
                                              <h5 class="accordion-header" id="heading-prog-{{ $prog->id }}">
                                                <button class="accordion-button py-1 px-2 {{ $isProgActive ? '' : 'collapsed' }} bg-body"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapse-prog-{{ $prog->id }}"
                                                        aria-expanded="{{ $isProgActive ? 'true' : 'false' }}"
                                                        aria-controls="collapse-prog-{{ $prog->id }}">
                                                  <i class="bi bi-mortarboard-fill text-success me-2"></i>
                                                  <span class="flex-grow-1 text-truncate small fw-bold">{{ $prog->code }} - {{ $prog->name }}</span>
                                                </button>
                                              </h5>

                                              <div id="collapse-prog-{{ $prog->id }}"
                                                   class="accordion-collapse collapse {{ $isProgActive ? 'show' : '' }}"
                                                   aria-labelledby="heading-prog-{{ $prog->id }}">
                                                <div class="accordion-body p-2">
                                                  <!-- Level 5: Curriculums -->
                                                  @php
                                                    $curriculums = $prog->curriculums;
                                                  @endphp

                                                  @if ($curriculums->isNotEmpty())
                                                    <div class="accordion accordion-flush" id="currAccordion-{{ $prog->id }}">
                                                      @foreach ($curriculums as $curr)
                                                        @php
                                                          $isCurrActive = ($activeCurriculumId === $curr->id) || ($curriculums->count() === 1 && $isProgActive);
                                                          // Mapped course units for this curriculum
                                                          $mappedCourses = $curr->curriculumCourses->map->courseUnit->filter()->unique('id');
                                                          if ($mappedCourses->isEmpty()) {
                                                              $mappedCourses = $dept->courseUnits;
                                                          }
                                                        @endphp
                                                        <div class="accordion-item border rounded mb-2 overflow-hidden curr-node">
                                                          <h6 class="accordion-header" id="heading-curr-{{ $curr->id }}">
                                                            <button class="accordion-button py-1 px-2 {{ $isCurrActive ? '' : 'collapsed' }} bg-body-secondary small"
                                                                    type="button"
                                                                    data-bs-toggle="collapse"
                                                                    data-bs-target="#collapse-curr-{{ $curr->id }}"
                                                                    aria-expanded="{{ $isCurrActive ? 'true' : 'false' }}"
                                                                    aria-controls="collapse-curr-{{ $curr->id }}">
                                                              <i class="bi bi-journal-code text-primary me-2"></i>
                                                              <span class="flex-grow-1 text-truncate fw-semibold">{{ $curr->version_name ?? 'Curriculum ' . ($curr->effective_year ?? '') }}</span>
                                                              <span class="badge text-bg-light border ms-1">{{ $mappedCourses->count() }} Units</span>
                                                            </button>
                                                          </h6>

                                                          <div id="collapse-curr-{{ $curr->id }}"
                                                               class="accordion-collapse collapse {{ $isCurrActive ? 'show' : '' }}"
                                                               aria-labelledby="heading-curr-{{ $curr->id }}">
                                                            <div class="accordion-body p-1">
                                                              <!-- Level 6: Course Units List -->
                                                              <div class="list-group list-group-flush">
                                                                @forelse ($mappedCourses as $cu)
                                                                  @php
                                                                    $sheetForUnit = $existingSheets->get($cu->id);
                                                                    $sheetStatus = $sheetForUnit?->status ?? 'not_started';
                                                                    if ($status && $sheetStatus !== $status) {
                                                                        continue;
                                                                    }
                                                                    $isCourseSelected = $selectedCourseUnit && $selectedCourseUnit->id === $cu->id;
                                                                    $badgeClass = match ($sheetStatus) {
                                                                        'published' => 'text-bg-success',
                                                                        'department_moderated' => 'text-bg-info',
                                                                        'submitted_to_hod' => 'text-bg-warning text-dark',
                                                                        'draft' => 'text-bg-secondary',
                                                                        'returned_for_revision' => 'text-bg-danger',
                                                                        'reopened_for_amendment' => 'text-bg-warning text-dark',
                                                                        default => 'text-bg-light border text-muted',
                                                                    };
                                                                    $statusLabel = match ($sheetStatus) {
                                                                        'published' => 'Published',
                                                                        'department_moderated' => 'Moderated',
                                                                        'submitted_to_hod' => 'Pending HoD',
                                                                        'draft' => 'Draft',
                                                                        'returned_for_revision' => 'Revision',
                                                                        'reopened_for_amendment' => 'Reopened',
                                                                        default => 'Not Started',
                                                                    };
                                                                  @endphp
                                                                  <a href="{{ route('assessment.list', array_filter(['course_unit_id' => $cu->id, 'semester_id' => $selectedSemester?->id, 'academic_year_id' => $selectedAcademicYear?->id, 'department_id' => $departmentId, 'status' => $status])) }}"
                                                                     class="list-group-item list-group-item-action py-2 px-2 d-flex justify-content-between align-items-center course-unit-item {{ $isCourseSelected ? 'active border-primary shadow-sm' : '' }}"
                                                                     data-code="{{ strtolower($cu->code) }}"
                                                                     data-name="{{ strtolower($cu->name) }}">
                                                                    <div class="text-truncate me-2" style="max-width: 190px;">
                                                                      <div class="fw-bold font-monospace small {{ $isCourseSelected ? 'text-white' : 'text-primary' }}">{{ $cu->code }}</div>
                                                                      <div class="small text-truncate {{ $isCourseSelected ? 'text-white-50' : 'text-body-secondary' }}" title="{{ $cu->name }}">{{ $cu->name }}</div>
                                                                      @if ($sheetForUnit && $sheetForUnit->instructor)
                                                                        <small class="d-block {{ $isCourseSelected ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.72rem;">
                                                                          <i class="bi bi-person-badge"></i> {{ $sheetForUnit->instructor->name }}
                                                                        </small>
                                                                      @endif
                                                                    </div>
                                                                    <div class="text-end text-nowrap">
                                                                      <span class="badge {{ $badgeClass }} small d-block mb-1">{{ $statusLabel }}</span>
                                                                      <small class="font-monospace {{ $isCourseSelected ? 'text-white-50' : 'text-muted' }}">{{ number_format($cu->credit_units, 1) }} CU</small>
                                                                      @if ($sheetForUnit)
                                                                        <span class="d-none">
                                                                          <a href="{{ route('assessment.edit', $sheetForUnit) }}">Edit</a>
                                                                        </span>
                                                                      @endif
                                                                    </div>
                                                                  </a>
                                                                @empty
                                                                  <div class="text-muted small py-2 px-3 text-center">No mapped course units.</div>
                                                                @endforelse
                                                              </div>
                                                            </div>
                                                          </div>
                                                        </div>
                                                      @endforeach
                                                    </div>
                                                  @else
                                                    <!-- Direct Department Course Units Fallback -->
                                                    <div class="list-group list-group-flush">
                                                      @forelse ($dept->courseUnits as $cu)
                                                        @php
                                                          $sheetForUnit = $existingSheets->get($cu->id);
                                                          $sheetStatus = $sheetForUnit?->status ?? 'not_started';
                                                          if ($status && $sheetStatus !== $status) {
                                                              continue;
                                                          }
                                                          $isCourseSelected = $selectedCourseUnit && $selectedCourseUnit->id === $cu->id;
                                                          $badgeClass = match ($sheetStatus) {
                                                              'published' => 'text-bg-success',
                                                              'department_moderated' => 'text-bg-info',
                                                              'submitted_to_hod' => 'text-bg-warning text-dark',
                                                              'draft' => 'text-bg-secondary',
                                                              'returned_for_revision' => 'text-bg-danger',
                                                              default => 'text-bg-light border text-muted',
                                                          };
                                                        @endphp
                                                        <a href="{{ route('assessment.list', array_filter(['course_unit_id' => $cu->id, 'semester_id' => $selectedSemester?->id, 'academic_year_id' => $selectedAcademicYear?->id, 'department_id' => $departmentId, 'status' => $status])) }}"
                                                           class="list-group-item list-group-item-action py-2 px-2 d-flex justify-content-between align-items-center course-unit-item {{ $isCourseSelected ? 'active border-primary shadow-sm' : '' }}"
                                                           data-code="{{ strtolower($cu->code) }}"
                                                           data-name="{{ strtolower($cu->name) }}">
                                                          <div class="text-truncate me-2" style="max-width: 190px;">
                                                            <div class="fw-bold font-monospace small {{ $isCourseSelected ? 'text-white' : 'text-primary' }}">{{ $cu->code }}</div>
                                                            <div class="small text-truncate {{ $isCourseSelected ? 'text-white-50' : 'text-body-secondary' }}">{{ $cu->name }}</div>
                                                            @if ($sheetForUnit && $sheetForUnit->instructor)
                                                              <small class="d-block {{ $isCourseSelected ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.72rem;">
                                                                <i class="bi bi-person-badge"></i> {{ $sheetForUnit->instructor->name }}
                                                              </small>
                                                            @endif
                                                          </div>
                                                          <div class="text-end text-nowrap">
                                                            <span class="badge {{ $badgeClass }} small d-block mb-1">{{ ucfirst(str_replace('_', ' ', $sheetStatus)) }}</span>
                                                            <small class="font-monospace {{ $isCourseSelected ? 'text-white-50' : 'text-muted' }}">{{ number_format($cu->credit_units, 1) }} CU</small>
                                                            @if ($sheetForUnit)
                                                              <span class="d-none">
                                                                <a href="{{ route('assessment.edit', $sheetForUnit) }}">Edit</a>
                                                              </span>
                                                            @endif
                                                          </div>
                                                        </a>
                                                      @empty
                                                        <div class="text-muted small py-2 px-3 text-center">No course units found.</div>
                                                      @endforelse
                                                    </div>
                                                  @endif
                                                </div>
                                              </div>
                                            </div>
                                          @empty
                                            @if ($dept->courseUnits->isNotEmpty())
                                              <div class="list-group list-group-flush">
                                                @foreach ($dept->courseUnits as $cu)
                                                  @php
                                                    $sheetForUnit = $existingSheets->get($cu->id);
                                                    $sheetStatus = $sheetForUnit?->status ?? 'not_started';
                                                    if ($status && $sheetStatus !== $status) {
                                                        continue;
                                                    }
                                                    $isCourseSelected = $selectedCourseUnit && $selectedCourseUnit->id === $cu->id;
                                                    $badgeClass = match ($sheetStatus) {
                                                        'published' => 'text-bg-success',
                                                        'department_moderated' => 'text-bg-info',
                                                        'submitted_to_hod' => 'text-bg-warning text-dark',
                                                        'draft' => 'text-bg-secondary',
                                                        'returned_for_revision' => 'text-bg-danger',
                                                        default => 'text-bg-light border text-muted',
                                                    };
                                                  @endphp
                                                  <a href="{{ route('assessment.list', array_filter(['course_unit_id' => $cu->id, 'semester_id' => $selectedSemester?->id, 'academic_year_id' => $selectedAcademicYear?->id, 'department_id' => $departmentId, 'status' => $status])) }}"
                                                     class="list-group-item list-group-item-action py-2 px-2 d-flex justify-content-between align-items-center course-unit-item {{ $isCourseSelected ? 'active border-primary shadow-sm' : '' }}"
                                                     data-code="{{ strtolower($cu->code) }}"
                                                     data-name="{{ strtolower($cu->name) }}">
                                                    <div class="text-truncate me-2" style="max-width: 190px;">
                                                      <div class="fw-bold font-monospace small {{ $isCourseSelected ? 'text-white' : 'text-primary' }}">{{ $cu->code }}</div>
                                                      <div class="small text-truncate {{ $isCourseSelected ? 'text-white-50' : 'text-body-secondary' }}">{{ $cu->name }}</div>
                                                      @if ($sheetForUnit && $sheetForUnit->instructor)
                                                        <small class="d-block {{ $isCourseSelected ? 'text-white-50' : 'text-muted' }}" style="font-size: 0.72rem;">
                                                          <i class="bi bi-person-badge"></i> {{ $sheetForUnit->instructor->name }}
                                                        </small>
                                                      @endif
                                                    </div>
                                                    <div class="text-end text-nowrap">
                                                      <span class="badge {{ $badgeClass }} small d-block mb-1">{{ ucfirst(str_replace('_', ' ', $sheetStatus)) }}</span>
                                                      <small class="font-monospace {{ $isCourseSelected ? 'text-white-50' : 'text-muted' }}">{{ number_format($cu->credit_units, 1) }} CU</small>
                                                      @if ($sheetForUnit)
                                                        <span class="d-none">
                                                          <a href="{{ route('assessment.edit', $sheetForUnit) }}">Edit</a>
                                                        </span>
                                                      @endif
                                                    </div>
                                                  </a>
                                                @endforeach
                                              </div>
                                            @else
                                              <div class="text-muted small py-2 px-3 text-center">No programmes listed.</div>
                                            @endif
                                          @endforelse
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                @empty
                                  <div class="text-muted small py-2 px-3 text-center">No departments listed.</div>
                                @endforelse
                              </div>
                            </div>
                          </div>
                        </div>
                      @empty
                        <div class="text-muted small py-2 px-3 text-center">No faculties found at this campus.</div>
                      @endforelse
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center py-4 text-muted">
                <i class="bi bi-buildings fs-1 d-block mb-2"></i>
                No campuses configured yet.
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Student Mark Entry Spreadsheet Workspace -->
    <div class="col-12 col-lg-8 col-xl-8 col-xxl-9">
      @if ($selectedCourseUnit && $selectedSheet)
        <!-- Selected Course Unit Header Banner -->
        <div class="card card-outline card-primary shadow-sm mb-4">
          <div class="card-header bg-body-tertiary">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary fs-6">{{ $selectedCourseUnit->code }}</span>
                <span class="badge text-bg-secondary fs-6">{{ number_format($selectedCourseUnit->credit_units, 1) }} CU</span>
                <span class="badge {{ $selectedSheet->status_badge_class }} fs-6">{{ $selectedSheet->status_label }}</span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#gradingScaleReferenceModal">
                  <i class="bi bi-award me-1"></i> Grading Scale
                </button>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-printer me-1"></i> Print Sheet
                </button>
                <a href="{{ route('assessment.show', $selectedSheet) }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-eye me-1"></i> Details
                </a>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div class="row g-3">
              <div class="col-12 col-md-5">
                <div class="text-body-secondary small">Course Unit &amp; Department</div>
                <h5 class="fw-bold mb-1">{{ $selectedCourseUnit->name }}</h5>
                <div class="text-muted small">
                  {{ $selectedCourseUnit->department->name ?? 'General Department' }} &bull;
                  {{ $selectedCourseUnit->department->faculty->name ?? '' }}
                </div>
              </div>
              <div class="col-12 col-md-4">
                <div class="text-body-secondary small">Academic Term &amp; Lecturer</div>
                <div class="fw-semibold">{{ $selectedSheet->semester->name }} ({{ $selectedSheet->academicYear->name }})</div>
                <div class="text-muted small">
                  <i class="bi bi-person-badge me-1"></i> {{ $selectedSheet->instructor->name ?? 'Unassigned Lecturer' }}
                </div>
              </div>
              <div class="col-12 col-md-3">
                <div class="text-body-secondary small">Weights &amp; Threshold</div>
                <div class="d-flex gap-1 flex-wrap mt-1">
                  <span class="badge text-bg-info small">CA: {{ number_format($selectedSheet->ca_weight, 0) }}%</span>
                  <span class="badge text-bg-primary small">Exam: {{ number_format($selectedSheet->exam_weight, 0) }}%</span>
                  <span class="badge text-bg-success small">Pass: &ge;{{ number_format($selectedSheet->pass_mark, 0) }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Real-Time Metrics KPI Tickers -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="p-3 bg-body border rounded shadow-sm text-center">
              <span class="text-muted small d-block mb-1">Enrolled Students</span>
              <h4 class="fw-bold mb-0 text-primary" id="ticker-total">{{ $studentMarks->count() }}</h4>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-body border rounded shadow-sm text-center">
              <span class="text-muted small d-block mb-1">Marks Graded</span>
              <h4 class="fw-bold mb-0 text-info" id="ticker-completed">{{ $studentMarks->whereNotNull('final_score')->count() }}</h4>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-body border rounded shadow-sm text-center">
              <span class="text-muted small d-block mb-1">Class Average</span>
              <h4 class="fw-bold mb-0 text-success" id="ticker-average">
                {{ number_format($studentMarks->whereNotNull('final_score')->avg('final_score') ?? 0, 1) }}%
              </h4>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="p-3 bg-body border rounded shadow-sm text-center">
              <span class="text-muted small d-block mb-1">Pass Rate</span>
              <h4 class="fw-bold mb-0 text-success" id="ticker-pass-rate">
                @php
                  $gradedCount = $studentMarks->whereNotNull('final_score')->count();
                  $passedCount = $studentMarks->where('is_passed', true)->count();
                  $passRate = $gradedCount > 0 ? ($passedCount / $gradedCount) * 100 : 0.0;
                @endphp
                {{ number_format($passRate, 1) }}%
              </h4>
            </div>
          </div>
        </div>

        <!-- Student Marks Entry Table Card -->
        <form action="{{ route('assessment.update', $selectedSheet) }}" method="POST" id="mark-entry-form">
          @csrf
          @method('PUT')
          <input type="hidden" name="return_to" value="{{ request()->fullUrl() }}">
          <input type="hidden" name="action" id="form-action-input" value="save_draft">

          <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-body-tertiary">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                  <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-table me-2 text-primary"></i>Student Score Sheet Workspace
                  </h5>
                  <span class="badge bg-body-secondary text-body border" id="missing-counter-badge">
                    Calculating status...
                  </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <div class="input-group input-group-sm" style="width: 14rem;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="search" id="roster-search" class="form-control" placeholder="Search student..." autocomplete="off">
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-filter" title="Clear Search">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="card-body p-0">
              @if ($studentMarks->isEmpty())
                <div class="p-5 text-center text-muted">
                  <i class="bi bi-person-x fs-1 d-block mb-3 text-secondary"></i>
                  <h5>No Registered Students Found</h5>
                  <p class="mb-0">There are currently no active registered students confirmed for this course unit in {{ $selectedSheet->semester->name }}.</p>
                </div>
              @else
                <div class="table-responsive">
                  <table class="table table-sm table-hover table-striped align-middle mb-0" id="mark-entry-table" style="font-size: 0.8125rem;">
                    <thead class="table-light sticky-top border-bottom text-center">
                      <tr>
                        <th style="width: 32px;">#</th>
                        <th class="text-start" style="min-width: 170px;">Student Details</th>
                        <th style="width: 68px;">
                          CA <small class="text-info d-block font-monospace fw-semibold" style="font-size: 0.7rem;">/{{ number_format($selectedSheet->ca_weight, 0) }}</small>
                        </th>
                        <th style="width: 88px;">Sitting</th>
                        <th style="width: 68px;">
                          Exam <small class="text-primary d-block font-monospace fw-semibold" style="font-size: 0.7rem;">/{{ number_format($selectedSheet->exam_weight, 0) }}</small>
                        </th>
                        <th style="width: 52px;">Total</th>
                        <th style="width: 42px;">Grade</th>
                        <th style="width: 45px;">GP</th>
                        <th style="width: 58px;">Result</th>
                        <th class="text-start" style="min-width: 110px;">Remarks</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($studentMarks as $index => $mark)
                        @php
                          $student = $mark->student;
                          $isGraduated = $student && $student->isGraduated();
                          $studentInitials = strtoupper(substr($student->first_name ?? 'S', 0, 1) . substr($student->last_name ?? 'M', 0, 1));
                        @endphp
                        <tr class="student-mark-row {{ $isGraduated ? 'table-secondary opacity-75' : '' }}" data-index="{{ $index }}">
                          <td class="text-center text-muted small fw-bold px-1">
                            {{ $index + 1 }}
                            <input type="hidden" name="marks[{{ $index }}][student_mark_id]" value="{{ $mark->id }}">
                          </td>
                          <td class="text-start py-2">
                            <div class="d-flex align-items-center">
                              <div class="avatar-circle me-2 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.72rem;">
                                {{ $studentInitials }}
                              </div>
                              <div class="text-truncate lh-sm" style="max-width: 220px;">
                                <div class="fw-bold student-name-text text-truncate mb-1" style="font-size: 0.8125rem;">{{ $student->full_name }}</div>
                                <div class="d-flex align-items-center gap-1 font-monospace text-muted flex-wrap" style="font-size: 0.7rem;">
                                  <span class="text-primary fw-semibold student-reg-text">{{ $student->registration_number }}</span>
                                  <span>&bull; {{ $student->student_number }}</span>
                                  @if ($student->programme)
                                    <span class="badge text-bg-light border text-muted px-1" style="font-size: 0.65rem;">{{ $student->programme->code }}</span>
                                  @endif
                                  @if ($isGraduated)
                                    <span class="badge text-bg-secondary" style="font-size: 0.65rem;">
                                      <i class="bi bi-mortarboard-fill"></i> Conferred
                                    </span>
                                  @endif
                                </div>
                              </div>
                            </div>
                          </td>
                          <td class="text-center px-1">
                            <input
                              type="number"
                              name="marks[{{ $index }}][ca_score]"
                              step="0.1"
                              min="0"
                              max="{{ $selectedSheet->ca_weight }}"
                              value="{{ old("marks.{$index}.ca_score", $mark->ca_score !== null ? number_format($mark->ca_score, 1, '.', '') : '') }}"
                              class="form-control form-control-sm text-end score-input ca-input py-1 px-1 font-monospace {{ $isGraduated ? 'bg-secondary-subtle border-0' : '' }}"
                              style="font-size: 0.8125rem; width: 64px; margin: 0 auto;"
                              placeholder="0.0"
                              data-row="{{ $index }}"
                              data-field="ca"
                              autocomplete="off"
                              {{ $isGraduated ? 'disabled readonly' : '' }}
                            >
                          </td>
                          <td class="text-center px-1">
                            <select name="marks[{{ $index }}][exam_status]"
                                    class="form-select form-select-sm exam-status-select py-1 px-1 text-center {{ $isGraduated ? 'bg-secondary-subtle border-0' : '' }}"
                                    style="font-size: 0.72rem; width: 84px; margin: 0 auto;"
                                    data-row="{{ $index }}"
                                    {{ $isGraduated ? 'disabled' : '' }}>
                              <option value="present" {{ old("marks.{$index}.exam_status", $mark->exam_status ?? 'present') === 'present' ? 'selected' : '' }}>Present</option>
                              <option value="missed_permission" {{ old("marks.{$index}.exam_status", $mark->exam_status) === 'missed_permission' ? 'selected' : '' }}>Missed</option>
                              <option value="absent" {{ old("marks.{$index}.exam_status", $mark->exam_status) === 'absent' ? 'selected' : '' }}>Absent</option>
                            </select>
                          </td>
                          <td class="text-center px-1">
                            <input
                              type="number"
                              name="marks[{{ $index }}][exam_score]"
                              step="0.1"
                              min="0"
                              max="{{ $selectedSheet->exam_weight }}"
                              value="{{ old("marks.{$index}.exam_score", $mark->exam_score !== null ? number_format($mark->exam_score, 1, '.', '') : '') }}"
                              class="form-control form-control-sm text-end score-input exam-input py-1 px-1 font-monospace {{ $isGraduated ? 'bg-secondary-subtle border-0' : '' }}"
                              style="font-size: 0.8125rem; width: 64px; margin: 0 auto;"
                              placeholder="0.0"
                              data-row="{{ $index }}"
                              data-field="exam"
                              autocomplete="off"
                              {{ $isGraduated ? 'disabled' : '' }}
                            >
                          </td>
                          <td class="text-center font-monospace fw-bold px-1" style="font-size: 0.8125rem;">
                            <span class="total-score-display">
                              {{ $mark->final_score !== null ? number_format($mark->final_score, 1) : '-' }}
                            </span>
                          </td>
                          <td class="text-center px-1">
                            <span class="badge grade-badge {{ $mark->grade_badge_class ?? 'bg-secondary' }}" style="font-size: 0.72rem;">
                              {{ $mark->grade_letter ?? '-' }}
                            </span>
                          </td>
                          <td class="text-center font-monospace px-1" style="font-size: 0.78rem;">
                            <span class="grade-point-display fw-semibold">
                              {{ $mark->grade_point !== null ? number_format($mark->grade_point, 1) : '-' }}
                            </span>
                          </td>
                          <td class="text-center px-1">
                            <span class="badge status-badge {{ $mark->final_score !== null ? ($mark->is_passed ? 'text-bg-success' : 'text-bg-danger') : 'bg-secondary-subtle text-secondary' }}" style="font-size: 0.68rem;">
                              {{ $mark->final_score !== null ? ($mark->is_passed ? 'Pass' : 'Retake') : 'Pending' }}
                            </span>
                          </td>
                          <td class="px-1">
                            <input
                              type="text"
                              name="marks[{{ $index }}][lecturer_remarks]"
                              value="{{ old("marks.{$index}.lecturer_remarks", $mark->lecturer_remarks) }}"
                              class="form-control form-control-sm py-1 px-2 {{ $isGraduated ? 'bg-secondary-subtle border-0' : '' }}"
                              style="font-size: 0.75rem;"
                              placeholder="{{ $isGraduated ? 'Conferred' : 'Remarks...' }}"
                              maxlength="255"
                              {{ $isGraduated ? 'disabled readonly' : '' }}
                            >
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>

            <!-- Action Footer -->
            <div class="card-footer bg-body-tertiary d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
              <span class="text-muted small">
                <i class="bi bi-info-circle me-1"></i> Tip: Use <kbd>&uarr;</kbd> <kbd>&darr;</kbd> arrow keys to rapidly navigate score cells.
              </span>
              <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-primary px-3" id="btn-save-draft">
                  <i class="bi bi-save me-1"></i> Save as Draft
                </button>
                @if ($selectedSheet->isReopenedForAmendment())
                  <button type="button" class="btn btn-success px-4" id="btn-submit-senate" onclick="document.getElementById('form-action-input').value = 'submit_senate'; document.getElementById('mark-entry-form').submit();">
                    <i class="bi bi-send-check me-1"></i> Submit to Senate
                  </button>
                @else
                  <button type="button" class="btn btn-primary px-4" id="btn-submit-hod">
                    <i class="bi bi-send-check me-1"></i> Submit to HoD
                  </button>
                @endif
              </div>
            </div>
          </div>
        </form>
      @else
        <!-- Friendly Guide Card when no Course Unit is Selected -->
        <div class="card card-outline card-secondary shadow-sm">
          <div class="card-body text-center py-5">
            <div class="mb-3">
              <i class="bi bi-diagram-3 text-primary" style="font-size: 3.5rem;"></i>
            </div>
            <h4 class="fw-bold text-body">Select a Course Unit to Enter Marks</h4>
            <p class="text-muted mx-auto mb-4" style="max-width: 540px;">
              Drill down the academic cascade hierarchy on the left panel:<br>
              <span class="badge bg-body-secondary text-body border mt-2 py-2 px-3 fs-7">
                Campus &rarr; Faculty &rarr; Department &rarr; Programme &rarr; Curriculum &rarr; Course Unit
              </span><br>
              <span class="small text-muted d-block mt-2">
                Clicking on any course unit immediately loads its registered student roster and enables live score entry with real-time grading and GP computation.
              </span>
            </p>
            <div class="d-inline-flex gap-2">
              <a href="{{ route('assessment.policy') }}" class="btn btn-sm btn-outline-info">
                <i class="bi bi-sliders me-1"></i> Assessment Policy &amp; Scale
              </a>
              <a href="{{ route('assessment.special-exams') }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-star me-1"></i> Special &amp; Supp Exams
              </a>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>

  <!-- Reusable Grading Scale Reference Modal -->
  @include('academic.assessments.partials.grading-scale-modal')
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // 1. Instant Cascade Filter Search
      const cascadeSearchInput = document.getElementById('cascade-search');
      const clearCascadeSearchBtn = document.getElementById('btn-clear-cascade-search');
      const courseUnitItems = document.querySelectorAll('.course-unit-item');

      if (cascadeSearchInput) {
        cascadeSearchInput.addEventListener('input', function (e) {
          const query = e.target.value.toLowerCase().trim();

          courseUnitItems.forEach(item => {
            const code = item.getAttribute('data-code') || '';
            const name = item.getAttribute('data-name') || '';
            const matches = !query || code.includes(query) || name.includes(query);

            item.style.display = matches ? '' : 'none';

            if (matches && query) {
              // Expand all parent accordion collapses
              let parentCollapse = item.closest('.accordion-collapse');
              while (parentCollapse) {
                if (!parentCollapse.classList.contains('show')) {
                  const bsCollapse = bootstrap.Collapse.getOrCreateInstance(parentCollapse, { toggle: false });
                  bsCollapse.show();
                }
                const parentNode = parentCollapse.parentElement.closest('.accordion-collapse');
                parentCollapse = parentNode;
              }
            }
          });
        });

        if (clearCascadeSearchBtn) {
          clearCascadeSearchBtn.addEventListener('click', function () {
            cascadeSearchInput.value = '';
            cascadeSearchInput.dispatchEvent(new Event('input'));
          });
        }
      }

      @if ($selectedCourseUnit && $selectedSheet)
        // 2. Real-Time Mark Entry Calculations
        const caMax = {{ (float) $selectedSheet->ca_weight }};
        const examMax = {{ (float) $selectedSheet->exam_weight }};
        const passMark = {{ (float) $selectedSheet->pass_mark }};
        const gradingScaleTiers = @json($gradingScaleTiers);

        const rows = document.querySelectorAll('.student-mark-row');
        const rosterSearchInput = document.getElementById('roster-search');
        const clearRosterSearchBtn = document.getElementById('btn-clear-filter');
        const form = document.getElementById('mark-entry-form');
        const actionInput = document.getElementById('form-action-input');
        const btnSaveDraft = document.getElementById('btn-save-draft');
        const btnSubmitHod = document.getElementById('btn-submit-hod');

        // Tickers
        const tickerTotal = document.getElementById('ticker-total');
        const tickerCompleted = document.getElementById('ticker-completed');
        const tickerAverage = document.getElementById('ticker-average');
        const tickerPassRate = document.getElementById('ticker-pass-rate');
        const missingCounterBadge = document.getElementById('missing-counter-badge');

        function resolveTier(score) {
          if (score === null || isNaN(score)) return null;

          for (const tier of gradingScaleTiers) {
            if (score >= tier.min_score && score <= tier.max_score) {
              return tier;
            }
          }

          if (score >= 80) return { grade_letter: 'A', grade_point: 5.0, badge_class: 'text-bg-success', is_pass: true };
          if (score >= 75) return { grade_letter: 'B+', grade_point: 4.5, badge_class: 'text-bg-success', is_pass: true };
          if (score >= 70) return { grade_letter: 'B', grade_point: 4.0, badge_class: 'text-bg-primary', is_pass: true };
          if (score >= 65) return { grade_letter: 'C+', grade_point: 3.5, badge_class: 'text-bg-info', is_pass: true };
          if (score >= 60) return { grade_letter: 'C', grade_point: 3.0, badge_class: 'text-bg-info', is_pass: true };
          if (score >= 55) return { grade_letter: 'D+', grade_point: 2.5, badge_class: 'text-bg-warning', is_pass: true };
          if (score >= 50) return { grade_letter: 'D', grade_point: 2.0, badge_class: 'text-bg-warning', is_pass: true };
          return { grade_letter: 'F', grade_point: 0.0, badge_class: 'text-bg-danger', is_pass: false };
        }

        function updateRow(row) {
          const caInput = row.querySelector('.ca-input');
          const examInput = row.querySelector('.exam-input');
          const statusSelect = row.querySelector('.exam-status-select');
          const totalSpan = row.querySelector('.total-score-display');
          const gradeBadge = row.querySelector('.grade-badge');
          const gpSpan = row.querySelector('.grade-point-display');
          const statusBadge = row.querySelector('.status-badge');

          if (!caInput || !examInput) return;

          const examStatus = statusSelect ? statusSelect.value : 'present';
          const caVal = parseFloat(caInput.value);
          const examVal = parseFloat(examInput.value);

          const hasCa = !isNaN(caVal) && caInput.value.trim() !== '';
          const hasExam = !isNaN(examVal) && examInput.value.trim() !== '';

          if (examStatus === 'absent') {
            totalSpan.textContent = '0.0';
            gradeBadge.textContent = 'F';
            gradeBadge.className = 'badge grade-badge text-bg-danger';
            gpSpan.textContent = '0.0';
            statusBadge.textContent = 'Absent';
            statusBadge.className = 'badge status-badge text-bg-danger';
            return;
          }

          if (examStatus === 'missed_permission') {
            totalSpan.textContent = hasCa ? caVal.toFixed(1) : '-';
            gradeBadge.textContent = 'I';
            gradeBadge.className = 'badge grade-badge text-bg-warning text-dark';
            gpSpan.textContent = '-';
            statusBadge.textContent = 'Missed';
            statusBadge.className = 'badge status-badge text-bg-warning text-dark';
            return;
          }

          if (hasCa && hasExam) {
            const composite = Math.min(100, Math.max(0, caVal + examVal));
            const tier = resolveTier(composite);

            totalSpan.textContent = composite.toFixed(1);

            if (tier) {
              gradeBadge.textContent = tier.grade_letter;
              gradeBadge.className = 'badge grade-badge ' + (tier.badge_class || 'text-bg-secondary');
              gpSpan.textContent = tier.grade_point.toFixed(1);

              if (tier.is_pass) {
                statusBadge.textContent = 'Pass';
                statusBadge.className = 'badge status-badge text-bg-success';
              } else {
                statusBadge.textContent = 'Retake';
                statusBadge.className = 'badge status-badge text-bg-danger';
              }
            }
          } else {
            totalSpan.textContent = '-';
            gradeBadge.textContent = '-';
            gradeBadge.className = 'badge grade-badge bg-secondary';
            gpSpan.textContent = '-';
            statusBadge.textContent = 'Pending';
            statusBadge.className = 'badge status-badge bg-secondary-subtle text-secondary';
          }
        }

        function recalculateAll() {
          let totalCount = rows.length;
          let completedCount = 0;
          let totalScoresSum = 0;
          let passCount = 0;

          rows.forEach(row => {
            updateRow(row);

            const caInput = row.querySelector('.ca-input');
            const examInput = row.querySelector('.exam-input');
            const statusSelect = row.querySelector('.exam-status-select');
            const examStatus = statusSelect ? statusSelect.value : 'present';

            const caVal = parseFloat(caInput ? caInput.value : '');
            const examVal = parseFloat(examInput ? examInput.value : '');

            const hasCa = !isNaN(caVal) && caInput.value.trim() !== '';
            const hasExam = !isNaN(examVal) && examInput.value.trim() !== '';

            if (examStatus === 'absent') {
              completedCount++;
            } else if (hasCa && hasExam) {
              completedCount++;
              const composite = caVal + examVal;
              totalScoresSum += composite;
              if (composite >= passMark) {
                passCount++;
              }
            }
          });

          if (tickerTotal) tickerTotal.textContent = totalCount;
          if (tickerCompleted) tickerCompleted.textContent = completedCount;

          if (tickerAverage) {
            tickerAverage.textContent = completedCount > 0
              ? (totalScoresSum / completedCount).toFixed(1) + '%'
              : '0.0%';
          }

          if (tickerPassRate) {
            tickerPassRate.textContent = completedCount > 0
              ? ((passCount / completedCount) * 100).toFixed(1) + '%'
              : '0.0%';
          }

          if (missingCounterBadge) {
            const pending = totalCount - completedCount;
            if (pending === 0) {
              missingCounterBadge.textContent = 'All ' + totalCount + ' Students Evaluated';
              missingCounterBadge.className = 'badge text-bg-success';
            } else {
              missingCounterBadge.textContent = pending + ' / ' + totalCount + ' Pending Marks';
              missingCounterBadge.className = 'badge text-bg-warning text-dark';
            }
          }
        }

        // Score Input Event Listeners
        rows.forEach(row => {
          const caInput = row.querySelector('.ca-input');
          const examInput = row.querySelector('.exam-input');
          const statusSelect = row.querySelector('.exam-status-select');

          if (caInput) {
            caInput.addEventListener('input', recalculateAll);
          }
          if (examInput) {
            examInput.addEventListener('input', recalculateAll);
          }
          if (statusSelect) {
            statusSelect.addEventListener('change', recalculateAll);
          }
        });

        // Keyboard arrow navigation
        document.querySelectorAll('.score-input').forEach(input => {
          input.addEventListener('keydown', function (e) {
            const currentRow = parseInt(this.getAttribute('data-row'), 10);
            const field = this.getAttribute('data-field');

            if (e.key === 'ArrowDown' || e.key === 'Enter') {
              e.preventDefault();
              const nextInput = document.querySelector(`.score-input[data-row="${currentRow + 1}"][data-field="${field}"]`);
              if (nextInput) {
                nextInput.focus();
                nextInput.select();
              }
            } else if (e.key === 'ArrowUp') {
              e.preventDefault();
              const prevInput = document.querySelector(`.score-input[data-row="${currentRow - 1}"][data-field="${field}"]`);
              if (prevInput) {
                prevInput.focus();
                prevInput.select();
              }
            }
          });
        });

        // Roster filter search
        if (rosterSearchInput) {
          rosterSearchInput.addEventListener('input', function (e) {
            const val = e.target.value.toLowerCase().trim();
            rows.forEach(row => {
              const nameEl = row.querySelector('.student-name-text');
              const regEl = row.querySelector('.student-reg-text');
              const name = nameEl ? nameEl.textContent.toLowerCase() : '';
              const reg = regEl ? regEl.textContent.toLowerCase() : '';
              row.style.display = (!val || name.includes(val) || reg.includes(val)) ? '' : 'none';
            });
          });

          if (clearRosterSearchBtn) {
            clearRosterSearchBtn.addEventListener('click', function () {
              rosterSearchInput.value = '';
              rosterSearchInput.dispatchEvent(new Event('input'));
            });
          }
        }

        // Form submission handlers
        if (btnSaveDraft && form) {
          btnSaveDraft.addEventListener('click', function () {
            actionInput.value = 'save_draft';
            form.submit();
          });
        }

        if (btnSubmitHod && form) {
          btnSubmitHod.addEventListener('click', function () {
            if (confirm('Are you sure you want to formally submit this mark sheet to the Head of Department for moderation? Once submitted, marks become locked against editing.')) {
              actionInput.value = 'submit_hod';
              form.submit();
            }
          });
        }

        // Initial computation pass
        recalculateAll();
      @endif
    });
  </script>
@endpush
