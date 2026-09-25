@extends('layouts.app')

@section('title', $curriculum->version_name . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Curriculum Progression Matrix')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.curriculums.index') }}">Curriculums</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $curriculum->programme->code }}</li>
@endsection

@section('content')
  <!-- Programme & Curriculum Header Card -->
  <div class="card card-outline card-primary mb-4">
    <div class="card-body">
      <div class="row align-items-center">
        <div class="col-12 col-lg-7">
          <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge text-bg-primary fs-6">{{ $curriculum->programme->code }}</span>
            <span class="badge bg-body-secondary text-body border">{{ $curriculum->programme->award_type }} &bull; {{ $curriculum->programme->duration_years }} Years</span>
            @if ($curriculum->is_active)
              <span class="badge text-bg-success">Active Curriculum</span>
            @else
              <span class="badge text-bg-secondary">Archived</span>
            @endif
          </div>
          <h3 class="fw-bold mb-1">{{ $curriculum->programme->name }}</h3>
          <p class="text-muted mb-2">
            <i class="bi bi-diagram-3 me-1"></i> {{ $curriculum->programme->department->name }} &bull;
            <i class="bi bi-mortarboard me-1"></i> {{ $curriculum->programme->department->faculty->name }}
            ({{ $curriculum->programme->department->faculty->campus->name ?? '' }})
          </p>
          <div class="d-flex flex-wrap gap-3 small text-body-secondary">
            <span><i class="bi bi-tag me-1"></i> Version: <strong>{{ $curriculum->version_name }}</strong></span>
            <span><i class="bi bi-calendar-range me-1"></i> Effective: <strong>{{ $curriculum->start_academic_year }} &ndash; {{ $curriculum->end_academic_year ?? 'Present' }}</strong></span>
            <span><i class="bi bi-award me-1"></i> Graduation Requirement: <strong>{{ $curriculum->min_graduation_credits }} Credit Units</strong></span>
          </div>
        </div>

        <div class="col-12 col-lg-5 mt-3 mt-lg-0 text-lg-end">
          <div class="d-flex flex-column align-items-lg-end gap-2">
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignCourseModal">
                <i class="bi bi-plus-circle me-1"></i> Assign Course Unit
              </button>
              <a href="{{ route('academic.curriculums.edit', $curriculum) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil me-1"></i> Edit
              </a>
              <a href="{{ route('academic.curriculums.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
              </a>
            </div>

            <!-- Credit Progress Summary -->
            <div class="w-100 text-start mt-2 p-2 rounded bg-body-tertiary border">
              @php
                $pct = $curriculum->min_graduation_credits > 0 ? min(100, round(($totalMappedCredits / $curriculum->min_graduation_credits) * 100)) : 0;
              @endphp
              <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-bold">Curriculum Credit Load:</span>
                <span class="badge text-bg-light border fw-bold">{{ number_format($totalMappedCredits, 1) }} / {{ $curriculum->min_graduation_credits }} CU ({{ $pct }}%)</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Study Progression Matrix Tabs (Years 1, 2, 3...) -->
  <div class="card card-outline card-info">
    <div class="card-header p-2">
      <ul class="nav nav-pills" id="curriculumStagesTab" role="tablist">
        @for ($year = 1; $year <= $durationYears; $year++)
          <li class="nav-item" role="presentation">
            <button
              class="nav-link {{ $year === 1 ? 'active' : '' }}"
              id="year-{{ $year }}-tab"
              data-bs-toggle="tab"
              data-bs-target="#year-{{ $year }}-content"
              type="button"
              role="tab"
              aria-controls="year-{{ $year }}-content"
              aria-selected="{{ $year === 1 ? 'true' : 'false' }}"
            >
              <i class="bi bi-mortarboard me-1"></i> Year {{ $year }}
              <span class="badge bg-body-secondary text-body border ms-1">
                {{ number_format($matrix[$year]['year_credits'] ?? 0, 1) }} CU
              </span>
            </button>
          </li>
        @endfor
      </ul>
    </div>

    <div class="card-body">
      <div class="tab-content" id="curriculumStagesTabContent">
        @for ($year = 1; $year <= $durationYears; $year++)
          <div
            class="tab-pane fade {{ $year === 1 ? 'show active' : '' }}"
            id="year-{{ $year }}-content"
            role="tabpanel"
            aria-labelledby="year-{{ $year }}-tab"
          >
            <!-- Year Header Banner -->
            <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded bg-body-tertiary border">
              <div>
                <h5 class="mb-0 fw-bold">Study Year {{ $year }} Curriculum Schedule</h5>
                <span class="text-muted small">
                  Dynamic progression boundary for Year {{ $year }} students.
                </span>
              </div>
              <span class="badge text-bg-primary fs-6">
                Year {{ $year }} Total: {{ number_format($matrix[$year]['year_credits'] ?? 0, 1) }} Credit Units
              </span>
            </div>

            <div class="row g-4">
              <!-- Semester 1 Column -->
              <div class="col-12 col-xl-6">
                <div class="card card-outline card-primary h-100">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                      <i class="bi bi-1-circle me-1 text-primary"></i> Semester 1 Schedule
                    </h5>
                    <span class="badge text-bg-light border fw-bold">
                      {{ number_format($matrix[$year][1]['credits'] ?? 0, 1) }} CU
                    </span>
                  </div>
                  <div class="card-body p-0">
                    @php
                      $sem1Courses = $matrix[$year][1]['courses'] ?? collect();
                    @endphp

                    @if ($sem1Courses->isEmpty())
                      <div class="text-center text-muted py-5 px-3">
                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                        No courses allocated to Year {{ $year }}, Semester 1 yet.
                        <div class="mt-3">
                          <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignCourseModal" onclick="presetStage({{ $year }}, 1)">
                            <i class="bi bi-plus-circle me-1"></i> Add Course to Year {{ $year }}, Sem 1
                          </button>
                        </div>
                      </div>
                    @else
                      <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                              <th width="40">#</th>
                              <th>Code</th>
                              <th>Course Title</th>
                              <th>CU</th>
                              <th>Type</th>
                              <th width="50" class="text-end">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($sem1Courses as $mapping)
                              <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                  <span class="badge text-bg-secondary">{{ $mapping->courseUnit->code }}</span>
                                </td>
                                <td>
                                  <a href="{{ route('academic.courses.show', $mapping->courseUnit) }}" class="fw-bold text-decoration-none" target="_blank">
                                    {{ $mapping->courseUnit->name }}
                                  </a>
                                  <small class="text-muted d-block">{{ $mapping->courseUnit->department->name ?? '' }}</small>
                                </td>
                                <td>
                                  <span class="badge text-bg-light border fw-bold">
                                    {{ number_format($mapping->courseUnit->credit_units, 1) }}
                                  </span>
                                </td>
                                <td>
                                  @if ($mapping->course_type === 'Core')
                                    <span class="badge text-bg-primary">Core</span>
                                  @elseif ($mapping->course_type === 'Elective')
                                    <span class="badge text-bg-secondary">Elective</span>
                                  @else
                                    <span class="badge text-bg-info">Audited</span>
                                  @endif
                                </td>
                                <td class="text-end">
                                  <form action="{{ route('academic.curriculums.courses.destroy', [$curriculum, $mapping]) }}" method="POST" onsubmit="return confirm('Remove course {{ $mapping->courseUnit->code }} from Year {{ $year }}, Semester 1?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Course from Curriculum">
                                      <i class="bi bi-trash"></i>
                                    </button>
                                  </form>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  </div>
                  <div class="card-footer bg-body-tertiary d-flex justify-content-between align-items-center">
                    <span class="small text-muted">{{ $sem1Courses->count() }} Course(s)</span>
                    <span class="fw-bold small">Semester 1 Load: {{ number_format($matrix[$year][1]['credits'] ?? 0, 1) }} CU</span>
                  </div>
                </div>
              </div>

              <!-- Semester 2 Column -->
              <div class="col-12 col-xl-6">
                <div class="card card-outline card-success h-100">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                      <i class="bi bi-2-circle me-1 text-success"></i> Semester 2 Schedule
                    </h5>
                    <span class="badge text-bg-light border fw-bold">
                      {{ number_format($matrix[$year][2]['credits'] ?? 0, 1) }} CU
                    </span>
                  </div>
                  <div class="card-body p-0">
                    @php
                      $sem2Courses = $matrix[$year][2]['courses'] ?? collect();
                    @endphp

                    @if ($sem2Courses->isEmpty())
                      <div class="text-center text-muted py-5 px-3">
                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                        No courses allocated to Year {{ $year }}, Semester 2 yet.
                        <div class="mt-3">
                          <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#assignCourseModal" onclick="presetStage({{ $year }}, 2)">
                            <i class="bi bi-plus-circle me-1"></i> Add Course to Year {{ $year }}, Sem 2
                          </button>
                        </div>
                      </div>
                    @else
                      <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                          <thead class="table-light">
                            <tr>
                              <th width="40">#</th>
                              <th>Code</th>
                              <th>Course Title</th>
                              <th>CU</th>
                              <th>Type</th>
                              <th width="50" class="text-end">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach ($sem2Courses as $mapping)
                              <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                  <span class="badge text-bg-secondary">{{ $mapping->courseUnit->code }}</span>
                                </td>
                                <td>
                                  <a href="{{ route('academic.courses.show', $mapping->courseUnit) }}" class="fw-bold text-decoration-none" target="_blank">
                                    {{ $mapping->courseUnit->name }}
                                  </a>
                                  <small class="text-muted d-block">{{ $mapping->courseUnit->department->name ?? '' }}</small>
                                </td>
                                <td>
                                  <span class="badge text-bg-light border fw-bold">
                                    {{ number_format($mapping->courseUnit->credit_units, 1) }}
                                  </span>
                                </td>
                                <td>
                                  @if ($mapping->course_type === 'Core')
                                    <span class="badge text-bg-primary">Core</span>
                                  @elseif ($mapping->course_type === 'Elective')
                                    <span class="badge text-bg-secondary">Elective</span>
                                  @else
                                    <span class="badge text-bg-info">Audited</span>
                                  @endif
                                </td>
                                <td class="text-end">
                                  <form action="{{ route('academic.curriculums.courses.destroy', [$curriculum, $mapping]) }}" method="POST" onsubmit="return confirm('Remove course {{ $mapping->courseUnit->code }} from Year {{ $year }}, Semester 2?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Course from Curriculum">
                                      <i class="bi bi-trash"></i>
                                    </button>
                                  </form>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @endif
                  </div>
                  <div class="card-footer bg-body-tertiary d-flex justify-content-between align-items-center">
                    <span class="small text-muted">{{ $sem2Courses->count() }} Course(s)</span>
                    <span class="fw-bold small">Semester 2 Load: {{ number_format($matrix[$year][2]['credits'] ?? 0, 1) }} CU</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endfor
      </div>
    </div>
  </div>

  <!-- Assign Course Unit to Curriculum Modal -->
  <div class="modal fade" id="assignCourseModal" tabindex="-1" aria-labelledby="assignCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route('academic.curriculums.courses.store', $curriculum) }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="assignCourseModalLabel">
              <i class="bi bi-plus-circle me-1 text-primary"></i> Allocate Course Unit to Curriculum Stage
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            @if ($availableCourses->isEmpty())
              <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-2"></i> All active catalog course units are already assigned to this curriculum structure. You can add more courses in the <a href="{{ route('academic.courses.create') }}" class="alert-link">Course Catalog</a>.
              </div>
            @else
              <div class="row g-3">
                <div class="col-12">
                  <label for="modal_course_unit_id" class="form-label fw-bold">Select Course Unit <span class="text-danger">*</span></label>
                  <select name="course_unit_id" id="modal_course_unit_id" class="form-select @error('course_unit_id') is-invalid @enderror" required>
                    <option value="">-- Choose Course Unit from Master Catalog --</option>
                    @foreach ($availableCourses as $c)
                      <option value="{{ $c->id }}">
                        {{ $c->code }} &mdash; {{ $c->name }} ({{ number_format($c->credit_units, 1) }} CU &bull; {{ $c->department->name ?? '' }})
                      </option>
                    @endforeach
                  </select>
                  @error('course_unit_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="modal_study_year" class="form-label fw-bold">Study Year <span class="text-danger">*</span></label>
                  <select name="study_year" id="modal_study_year" class="form-select" required>
                    @for ($y = 1; $y <= $durationYears; $y++)
                      <option value="{{ $y }}">Year {{ $y }}</option>
                    @endfor
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="modal_semester" class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                  <select name="semester" id="modal_semester" class="form-select" required>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="modal_course_type" class="form-label fw-bold">Course Type <span class="text-danger">*</span></label>
                  <select name="course_type" id="modal_course_type" class="form-select" required>
                    <option value="Core" selected>Core (Compulsory)</option>
                    <option value="Elective">Elective</option>
                    <option value="Audited">Audited</option>
                  </select>
                </div>
              </div>

              <div class="mt-3 p-2 bg-body-tertiary rounded small text-muted">
                <i class="bi bi-info-circle me-1"></i> Once allocated, this course will automatically be eligible for enrollment by students active in the corresponding Study Year and Semester.
              </div>
            @endif
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            @if ($availableCourses->isNotEmpty())
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Allocate Course to Stage
              </button>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function presetStage(year, semester) {
      const yearSelect = document.getElementById('modal_study_year');
      const semSelect = document.getElementById('modal_semester');
      if (yearSelect) yearSelect.value = year;
      if (semSelect) semSelect.value = semester;
    }
  </script>
@endpush
