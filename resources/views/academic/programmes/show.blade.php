@extends('layouts.app')

@section('title', $programme->name . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', $programme->name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.programmes.index') }}">Programmes</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $programme->code }}</li>
@endsection

@section('content')
  <div class="row">
    <!-- Programme Profile -->
    <div class="col-12 col-lg-5">
      <div class="card card-primary card-outline mb-4">
        <div class="card-body box-profile">
          <div class="text-center mb-3">
            <span class="rounded-circle bg-primary-subtle p-3 d-inline-block text-primary">
              <i class="bi bi-award-fill fs-1"></i>
            </span>
          </div>
          <h3 class="profile-username text-center mb-1">{{ $programme->name }}</h3>
          <p class="text-muted text-center mb-3">Code: <strong>{{ $programme->code }}</strong></p>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Department
              <span class="badge bg-body-secondary text-body border">
                <i class="bi bi-diagram-3 me-1"></i> {{ $programme->department->name }}
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Faculty
              <span class="fw-semibold">{{ $programme->department->faculty->name ?? 'N/A' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Campus
              <span class="fw-semibold">{{ $programme->department->faculty->campus->name ?? 'N/A' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Award Level
              @if ($programme->award_type === 'Bachelors' || $programme->award_type === 'Degree')
                <span class="badge text-bg-primary">{{ $programme->award_type }}</span>
              @elseif ($programme->award_type === 'Masters' || $programme->award_type === 'Doctorate')
                <span class="badge text-bg-purple bg-indigo text-white">{{ $programme->award_type }}</span>
              @elseif ($programme->award_type === 'Diploma')
                <span class="badge text-bg-info">{{ $programme->award_type }}</span>
              @else
                <span class="badge text-bg-secondary">{{ $programme->award_type }}</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Standard Duration
              <span class="badge bg-body-secondary text-body border">
                {{ $programme->duration_years }} {{ Str::plural('Year', $programme->duration_years) }}
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Graduation Credits
              <span class="fw-bold text-success">{{ $programme->required_credits_to_graduate }} CU</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Operational Status
              @if ($programme->status === 'active')
                <span class="badge text-bg-success">Active</span>
              @else
                <span class="badge text-bg-secondary">Inactive</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Registered Date
              <span class="text-muted">{{ $programme->created_at->format('M d, Y') }}</span>
            </li>
          </ul>

          <div class="callout callout-info mb-3">
            <h6 class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Programme Overview</h6>
            <p class="mb-0 text-muted small">
              {{ $programme->description ?? 'No detailed description provided for this academic programme.' }}
            </p>
          </div>

          <div class="d-grid gap-2">
            <a href="{{ route('academic.programmes.edit', $programme) }}" class="btn btn-warning">
              <i class="bi bi-pencil me-1"></i> Edit Programme
            </a>
            <a href="{{ route('academic.programmes.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Programmes
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Curriculum & Course Map -->
    <div class="col-12 col-lg-7">
      <div class="card card-outline card-primary mb-4">
        <div class="card-header">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h3 class="card-title mb-0">
              <i class="bi bi-mortarboard-fill me-1 text-primary"></i> Curriculum & Course Map
            </h3>
            <div class="card-tools d-flex align-items-center gap-2 me-0">
              @if ($programme->curriculums->isNotEmpty())
                <span class="badge text-bg-secondary">
                  {{ $programme->curriculums->count() }} {{ Str::plural('Version', $programme->curriculums->count()) }}
                </span>
                <a href="{{ route('academic.curriculums.create', ['programme_id' => $programme->id]) }}" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-plus-circle me-1"></i> New Version
                </a>
              @else
                <a href="{{ route('academic.curriculums.create', ['programme_id' => $programme->id]) }}" class="btn btn-sm btn-primary">
                  <i class="bi bi-plus-circle me-1"></i> Add Curriculum
                </a>
              @endif
            </div>
          </div>
        </div>

        <div class="card-body">
          @if ($activeCurriculum)
            <!-- Active Curriculum Header Bar -->
            <div class="p-3 mb-3 rounded bg-body-tertiary border">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div>
                  <h5 class="fw-bold mb-0 text-primary">
                    {{ $activeCurriculum->version_name }}
                  </h5>
                  <small class="text-muted">
                    Valid for Academic Sessions: <strong>{{ $activeCurriculum->start_academic_year }} &ndash; {{ $activeCurriculum->end_academic_year ?? 'Present' }}</strong>
                  </small>
                </div>
                <div class="d-flex align-items-center gap-2">
                  @if ($activeCurriculum->is_active)
                    <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i> Active Structure</span>
                  @else
                    <span class="badge text-bg-secondary">Draft / Inactive</span>
                  @endif
                  <a href="{{ route('academic.curriculums.show', $activeCurriculum) }}" class="btn btn-sm btn-primary" title="Open Full Matrix">
                    <i class="bi bi-diagram-3-fill me-1"></i> Full Matrix
                  </a>
                </div>
              </div>

              <!-- Credit Units Progress -->
              @php
                $targetCredits = $programme->required_credits_to_graduate ?: 1;
                $pct = min(100, round(($totalMappedCredits / $targetCredits) * 100));
              @endphp
              <div class="d-flex justify-content-between small text-muted mb-1">
                <span>Total Mapped Credits: <strong class="text-body">{{ $totalMappedCredits }} CU</strong></span>
                <span>Graduation Requirement: <strong class="text-body">{{ $programme->required_credits_to_graduate }} CU</strong> ({{ $pct }}%)</span>
              </div>
              <div class="progress" style="height: 6px;">
                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>

            <!-- Version Switcher Pills (If multiple curriculums exist) -->
            @if ($programme->curriculums->count() > 1)
              <div class="mb-3">
                <small class="fw-bold text-muted d-block mb-1">Available Curriculum Versions:</small>
                <div class="d-flex flex-wrap gap-1">
                  @foreach ($programme->curriculums as $c)
                    <a href="{{ route('academic.curriculums.show', $c) }}" class="badge {{ $c->id === $activeCurriculum->id ? 'text-bg-primary' : 'bg-body-secondary text-body border' }} text-decoration-none py-1 px-2">
                      {{ $c->version_name }} ({{ $c->curriculumCourses->count() }} courses)
                    </a>
                  @endforeach
                </div>
              </div>
            @endif

            <!-- Progression Matrix: Study Years & Semesters -->
            <h6 class="fw-bold mb-3 text-secondary">
              <i class="bi bi-layers me-1"></i> Course Progression Matrix
            </h6>

            @if ($activeCurriculum->curriculumCourses->isEmpty())
              <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                <i class="bi bi-info-circle fs-4"></i>
                <div class="flex-grow-1">
                  No course units mapped to this curriculum yet.
                </div>
                <a href="{{ route('academic.curriculums.show', $activeCurriculum) }}" class="btn btn-sm btn-info text-white">
                  <i class="bi bi-plus-circle me-1"></i> Allocate Courses
                </a>
              </div>
            @else
              <!-- Accordion Breakdown for Course Map -->
              <div class="accordion" id="curriculumAccordion">
                @for ($y = 1; $y <= ($programme->duration_years ?: 3); $y++)
                  @php
                    $yearData = $matrix[$y] ?? null;
                    $sem1Courses = $yearData[1]['courses'] ?? collect();
                    $sem2Courses = $yearData[2]['courses'] ?? collect();
                    $yearCredits = $yearData['year_credits'] ?? 0;
                  @endphp
                  <div class="accordion-item mb-2 border rounded overflow-hidden">
                    <h2 class="accordion-header" id="headingYear{{ $y }}">
                      <button class="accordion-button {{ $y === 1 ? '' : 'collapsed' }} py-2 px-3 fw-semibold bg-body-tertiary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseYear{{ $y }}" aria-expanded="{{ $y === 1 ? 'true' : 'false' }}" aria-controls="collapseYear{{ $y }}">
                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                          <span>
                            <i class="bi bi-calendar-check me-2 text-primary"></i> Study Year {{ $y }}
                          </span>
                          <span class="badge bg-body text-body border font-monospace">
                            {{ $sem1Courses->count() + $sem2Courses->count() }} Courses &bull; {{ $yearCredits }} CU
                          </span>
                        </div>
                      </button>
                    </h2>
                    <div id="collapseYear{{ $y }}" class="accordion-collapse collapse {{ $y === 1 ? 'show' : '' }}" aria-labelledby="headingYear{{ $y }}" data-bs-parent="#curriculumAccordion">
                      <div class="accordion-body p-3">
                        <div class="row g-3">
                          <!-- Semester 1 -->
                          <div class="col-12 col-md-6">
                            <div class="border rounded p-2 bg-body">
                              <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-primary">Semester 1</span>
                                <span class="badge text-bg-light border small">{{ $yearData[1]['credits'] ?? 0 }} CU</span>
                              </div>
                              @if ($sem1Courses->isEmpty())
                                <small class="text-muted fst-italic d-block py-2 text-center">No courses assigned</small>
                              @else
                                <div class="list-group list-group-flush">
                                  @foreach ($sem1Courses as $mapping)
                                    <div class="list-group-item px-1 py-1 d-flex justify-content-between align-items-center border-0 small">
                                      <div class="text-truncate me-2">
                                        <span class="fw-bold">{{ $mapping->courseUnit->code }}</span>
                                        <span class="text-muted d-block text-truncate" style="max-width: 170px;" title="{{ $mapping->courseUnit->name }}">{{ $mapping->courseUnit->name }}</span>
                                      </div>
                                      <div class="text-end text-nowrap">
                                        <span class="badge {{ $mapping->course_type === 'Core' ? 'text-bg-primary' : 'text-bg-secondary' }} py-0 px-1" style="font-size: 0.7rem;">{{ $mapping->course_type }}</span>
                                        <small class="fw-bold d-block text-muted">{{ $mapping->courseUnit->credit_units }} CU</small>
                                      </div>
                                    </div>
                                  @endforeach
                                </div>
                              @endif
                            </div>
                          </div>

                          <!-- Semester 2 -->
                          <div class="col-12 col-md-6">
                            <div class="border rounded p-2 bg-body">
                              <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                <span class="fw-bold small text-primary">Semester 2</span>
                                <span class="badge text-bg-light border small">{{ $yearData[2]['credits'] ?? 0 }} CU</span>
                              </div>
                              @if ($sem2Courses->isEmpty())
                                <small class="text-muted fst-italic d-block py-2 text-center">No courses assigned</small>
                              @else
                                <div class="list-group list-group-flush">
                                  @foreach ($sem2Courses as $mapping)
                                    <div class="list-group-item px-1 py-1 d-flex justify-content-between align-items-center border-0 small">
                                      <div class="text-truncate me-2">
                                        <span class="fw-bold">{{ $mapping->courseUnit->code }}</span>
                                        <span class="text-muted d-block text-truncate" style="max-width: 170px;" title="{{ $mapping->courseUnit->name }}">{{ $mapping->courseUnit->name }}</span>
                                      </div>
                                      <div class="text-end text-nowrap">
                                        <span class="badge {{ $mapping->course_type === 'Core' ? 'text-bg-primary' : 'text-bg-secondary' }} py-0 px-1" style="font-size: 0.7rem;">{{ $mapping->course_type }}</span>
                                        <small class="fw-bold d-block text-muted">{{ $mapping->courseUnit->credit_units }} CU</small>
                                      </div>
                                    </div>
                                  @endforeach
                                </div>
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                @endfor
              </div>

              <div class="mt-3 text-end">
                <a href="{{ route('academic.curriculums.show', $activeCurriculum) }}" class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-pencil-square me-1"></i> Edit Progression &amp; Courses in Matrix &rarr;
                </a>
              </div>
            @endif

          @else
            <!-- Empty State when no curriculum is configured -->
            <div class="text-center text-muted py-5">
              <i class="bi bi-journal-plus fs-1 text-primary opacity-50 d-block mb-3"></i>
              <h5 class="fw-bold text-body">No Curriculum Defined for {{ $programme->code }}</h5>
              <p class="text-muted max-w-sm mx-auto mb-4">
                Curriculums define the course progression matrix across Study Years (Year 1 to Year {{ $programme->duration_years }}), Semesters (1 &amp; 2), and graduation credit thresholds.
              </p>
              <div class="d-flex justify-content-center gap-2 mb-4">
                <span class="badge bg-body-secondary text-body border p-2">
                  <i class="bi bi-calendar-range me-1"></i> {{ $programme->duration_years }} Study Years
                </span>
                <span class="badge bg-body-secondary text-body border p-2">
                  <i class="bi bi-layers me-1"></i> 2 Semesters / Year
                </span>
                <span class="badge bg-body-secondary text-body border p-2">
                  <i class="bi bi-check2-circle me-1"></i> {{ $programme->required_credits_to_graduate }} Target CU
                </span>
              </div>
              <a href="{{ route('academic.curriculums.create', ['programme_id' => $programme->id]) }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create Curriculum Version
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
