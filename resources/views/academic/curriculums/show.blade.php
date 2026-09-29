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
                  @php
                    $stage1Bounds = $curriculum->getStageCreditBounds($year, 1);
                  @endphp
                  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                      <i class="bi bi-1-circle me-1 text-primary"></i> Semester 1 Schedule
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                      <form class="stage-credit-form d-flex align-items-center"
                            data-url="{{ route('academic.curriculums.stage-credit-limits.update', $curriculum) }}"
                            data-year="{{ $year }}"
                            data-semester="1">
                        @csrf
                        <div class="input-group input-group-sm" style="width: 230px;">
                          <span class="input-group-text py-0 px-2 text-body-secondary small" title="Minimum required semester credits">Min</span>
                          <input type="number"
                                 name="min_credits"
                                 class="form-control form-control-sm text-center px-1"
                                 value="{{ number_format($stage1Bounds['min'], 1) }}"
                                 step="0.5" min="0" max="40"
                                 title="Minimum semester credits"
                                 required>
                          <span class="input-group-text py-0 px-2 text-body-secondary small" title="Maximum allowed semester credits">Max</span>
                          <input type="number"
                                 name="max_credits"
                                 class="form-control form-control-sm text-center px-1"
                                 value="{{ number_format($stage1Bounds['max'], 1) }}"
                                 step="0.5" min="0" max="60"
                                 title="Maximum semester credits"
                                 required>
                          <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2 btn-save-bounds" title="Save Credit Limits">
                            <i class="bi bi-check2"></i>
                          </button>
                        </div>
                      </form>
                      <span class="badge text-bg-light border fw-bold text-nowrap">
                        {{ number_format($matrix[$year][1]['credits'] ?? 0, 1) }} CU
                      </span>
                    </div>
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
                  @php
                    $stage2Bounds = $curriculum->getStageCreditBounds($year, 2);
                  @endphp
                  <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                      <i class="bi bi-2-circle me-1 text-success"></i> Semester 2 Schedule
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                      <form class="stage-credit-form d-flex align-items-center"
                            data-url="{{ route('academic.curriculums.stage-credit-limits.update', $curriculum) }}"
                            data-year="{{ $year }}"
                            data-semester="2">
                        @csrf
                        <div class="input-group input-group-sm" style="width: 230px;">
                          <span class="input-group-text py-0 px-2 text-body-secondary small" title="Minimum required semester credits">Min</span>
                          <input type="number"
                                 name="min_credits"
                                 class="form-control form-control-sm text-center px-1"
                                 value="{{ number_format($stage2Bounds['min'], 1) }}"
                                 step="0.5" min="0" max="40"
                                 title="Minimum semester credits"
                                 required>
                          <span class="input-group-text py-0 px-2 text-body-secondary small" title="Maximum allowed semester credits">Max</span>
                          <input type="number"
                                 name="max_credits"
                                 class="form-control form-control-sm text-center px-1"
                                 value="{{ number_format($stage2Bounds['max'], 1) }}"
                                 step="0.5" min="0" max="60"
                                 title="Maximum semester credits"
                                 required>
                          <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 btn-save-bounds" title="Save Credit Limits">
                            <i class="bi bi-check2"></i>
                          </button>
                        </div>
                      </form>
                      <span class="badge text-bg-light border fw-bold text-nowrap">
                        {{ number_format($matrix[$year][2]['credits'] ?? 0, 1) }} CU
                      </span>
                    </div>
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
        <form action="{{ route('academic.curriculums.courses.store', $curriculum) }}" method="POST" id="allocateCourseForm">
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
                  <label class="form-label fw-bold">Select Course Unit <span class="text-danger">*</span></label>

                  {{-- Hidden input for form submission --}}
                  <input type="hidden" name="course_unit_id" id="modal_course_unit_id" value="{{ old('course_unit_id') }}" required>

                  {{-- Searchable Input & Dropdown Container --}}
                  <div class="position-relative" id="course_search_container">
                    <div class="input-group">
                      <span class="input-group-text bg-body-secondary"><i class="bi bi-search"></i></span>
                      <input type="text"
                             id="course_search_input"
                             class="form-control @error('course_unit_id') is-invalid @enderror"
                             placeholder="Type course code (e.g. DIT, CSC) or course title to search..."
                             autocomplete="off">
                      <button class="btn btn-outline-secondary d-none" type="button" id="course_search_clear" title="Clear search text">
                        <i class="bi bi-x-lg"></i>
                      </button>
                    </div>

                    {{-- Live Dropdown Results Menu --}}
                    <div id="course_search_results"
                         class="dropdown-menu w-100 shadow border p-0 mt-1"
                         style="max-height: 280px; overflow-y: auto; display: none; z-index: 1060;">
                      <div class="p-2 border-bottom bg-body-tertiary small text-muted d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-book me-1"></i> Course Catalog</span>
                        <span id="course_search_count" class="badge bg-secondary-subtle text-secondary">{{ $availableCourses->count() }} available</span>
                      </div>
                      <div id="course_search_list" class="list-group list-group-flush">
                        @foreach ($availableCourses as $c)
                          <button type="button"
                                  class="list-group-item list-group-item-action course-search-item px-3 py-2 text-start border-bottom-0"
                                  data-id="{{ $c->id }}"
                                  data-code="{{ $c->code }}"
                                  data-name="{{ $c->name }}"
                                  data-credits="{{ number_format($c->credit_units, 1) }}"
                                  data-dept="{{ $c->department->name ?? 'General' }}"
                                  data-search="{{ strtolower($c->code . ' ' . $c->name . ' ' . ($c->department->name ?? '')) }}">
                            <div class="d-flex justify-content-between align-items-center">
                              <div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace me-1">{{ $c->code }}</span>
                                <span class="fw-semibold">{{ $c->name }}</span>
                              </div>
                              <span class="badge bg-secondary-subtle text-secondary">{{ number_format($c->credit_units, 1) }} CU</span>
                            </div>
                            <div class="small text-muted mt-1 ps-1">
                              <i class="bi bi-building me-1"></i>{{ $c->department->name ?? 'General Department' }}
                            </div>
                          </button>
                        @endforeach
                      </div>
                      <div id="course_search_empty" class="p-3 text-center text-muted d-none">
                        <i class="bi bi-search text-secondary d-block fs-4 mb-1"></i>
                        <span>No matching course units found</span>
                      </div>
                    </div>
                  </div>

                  {{-- Selected Course Display Card --}}
                  <div id="selected_course_card" class="card border-primary-subtle bg-primary-subtle bg-opacity-10 mt-2 d-none">
                    <div class="card-body p-2 d-flex align-items-center justify-content-between">
                      <div class="d-flex align-items-center gap-2">
                        <div class="rounded bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                          <i class="bi bi-journal-check fs-5"></i>
                        </div>
                        <div>
                          <div class="d-flex align-items-center gap-1">
                            <span class="badge bg-primary font-monospace" id="selected_course_code"></span>
                            <strong class="text-body" id="selected_course_name"></strong>
                          </div>
                          <div class="small text-muted" id="selected_course_meta"></div>
                        </div>
                      </div>
                      <button type="button" class="btn btn-sm btn-outline-danger" id="btn_change_course" title="Change Course">
                        <i class="bi bi-arrow-repeat me-1"></i> Change
                      </button>
                    </div>
                  </div>

                  @error('course_unit_id')
                    <div class="text-danger small mt-1"><i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}</div>
                  @enderror
                  <div class="text-danger small mt-1 d-none" id="course_required_feedback">
                    <i class="bi bi-exclamation-triangle me-1"></i> Please search and select a course unit to allocate.
                  </div>
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

    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('course_search_input');
      const searchWrapper = document.getElementById('course_search_container');
      const searchResults = document.getElementById('course_search_results');
      const searchList = document.getElementById('course_search_list');
      const searchEmpty = document.getElementById('course_search_empty');
      const searchCount = document.getElementById('course_search_count');
      const searchClear = document.getElementById('course_search_clear');
      const hiddenIdInput = document.getElementById('modal_course_unit_id');
      const selectedCourseCard = document.getElementById('selected_course_card');
      const selectedCourseCode = document.getElementById('selected_course_code');
      const selectedCourseName = document.getElementById('selected_course_name');
      const selectedCourseMeta = document.getElementById('selected_course_meta');
      const btnChangeCourse = document.getElementById('btn_change_course');
      const requiredFeedback = document.getElementById('course_required_feedback');
      const assignModal = document.getElementById('assignCourseModal');
      const allocateForm = document.getElementById('allocateCourseForm');

      if (!searchInput || !searchList) return;

      const items = Array.from(searchList.querySelectorAll('.course-search-item'));
      let activeIndex = -1;

      function openDropdown() {
        searchResults.style.display = 'block';
      }

      function closeDropdown() {
        searchResults.style.display = 'none';
        activeIndex = -1;
        clearHighlight();
      }

      function clearHighlight() {
        items.forEach(el => el.classList.remove('active'));
      }

      function setHighlight(index) {
        clearHighlight();
        const visible = items.filter(el => !el.classList.contains('d-none'));
        if (index >= 0 && index < visible.length) {
          visible[index].classList.add('active');
          visible[index].scrollIntoView({ block: 'nearest' });
          activeIndex = index;
        }
      }

      function filterCourses(query) {
        const q = query.trim().toLowerCase();
        let matchCount = 0;

        items.forEach(item => {
          const text = item.dataset.search || '';
          if (!q || text.includes(q)) {
            item.classList.remove('d-none');
            matchCount++;
          } else {
            item.classList.add('d-none');
          }
        });

        if (searchCount) {
          searchCount.textContent = q ? `${matchCount} match${matchCount === 1 ? '' : 'es'}` : `${matchCount} available`;
        }

        if (searchEmpty) {
          if (matchCount === 0) {
            searchEmpty.classList.remove('d-none');
          } else {
            searchEmpty.classList.add('d-none');
          }
        }

        if (searchClear) {
          if (q.length > 0) {
            searchClear.classList.remove('d-none');
          } else {
            searchClear.classList.add('d-none');
          }
        }

        openDropdown();
      }

      function selectCourse(item) {
        const id = item.dataset.id;
        const code = item.dataset.code;
        const name = item.dataset.name;
        const credits = item.dataset.credits;
        const dept = item.dataset.dept;

        hiddenIdInput.value = id;
        selectedCourseCode.textContent = code;
        selectedCourseName.textContent = name;
        selectedCourseMeta.textContent = `${credits} CU • ${dept}`;

        searchWrapper.classList.add('d-none');
        selectedCourseCard.classList.remove('d-none');
        if (requiredFeedback) requiredFeedback.classList.add('d-none');
        searchInput.classList.remove('is-invalid');
        closeDropdown();
      }

      function resetSelection() {
        hiddenIdInput.value = '';
        selectedCourseCard.classList.add('d-none');
        searchWrapper.classList.remove('d-none');
        searchInput.value = '';
        filterCourses('');
        searchInput.focus();
      }

      // Check if there was a previously selected ID (e.g. from validation redirect)
      if (hiddenIdInput.value) {
        const preselected = items.find(el => el.dataset.id === hiddenIdInput.value);
        if (preselected) {
          selectCourse(preselected);
        }
      }

      searchInput.addEventListener('focus', function () {
        openDropdown();
        filterCourses(this.value);
      });

      searchInput.addEventListener('input', function () {
        filterCourses(this.value);
        activeIndex = -1;
      });

      if (searchClear) {
        searchClear.addEventListener('click', function () {
          searchInput.value = '';
          filterCourses('');
          searchInput.focus();
        });
      }

      if (btnChangeCourse) {
        btnChangeCourse.addEventListener('click', resetSelection);
      }

      searchList.addEventListener('click', function (e) {
        const btn = e.target.closest('.course-search-item');
        if (btn) {
          selectCourse(btn);
        }
      });

      searchInput.addEventListener('keydown', function (e) {
        const visible = items.filter(el => !el.classList.contains('d-none'));
        if (searchResults.style.display !== 'block') {
          if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            openDropdown();
          }
          return;
        }

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const next = activeIndex + 1 < visible.length ? activeIndex + 1 : 0;
          setHighlight(next);
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prev = activeIndex - 1 >= 0 ? activeIndex - 1 : visible.length - 1;
          setHighlight(prev);
        } else if (e.key === 'Enter') {
          e.preventDefault();
          if (activeIndex >= 0 && activeIndex < visible.length) {
            selectCourse(visible[activeIndex]);
          }
        } else if (e.key === 'Escape') {
          closeDropdown();
        }
      });

      document.addEventListener('click', function (e) {
        if (searchWrapper && !searchWrapper.contains(e.target)) {
          closeDropdown();
        }
      });

      if (allocateForm) {
        allocateForm.addEventListener('submit', function (e) {
          if (!hiddenIdInput.value) {
            e.preventDefault();
            if (requiredFeedback) requiredFeedback.classList.remove('d-none');
            searchInput.classList.add('is-invalid');
            searchInput.focus();
            openDropdown();
          }
        });
      }

      if (assignModal) {
        assignModal.addEventListener('shown.bs.modal', function () {
          if (!hiddenIdInput.value) {
            searchInput.focus();
            openDropdown();
          }
        });

        assignModal.addEventListener('hidden.bs.modal', function () {
          closeDropdown();
          if (!hiddenIdInput.value) {
            resetSelection();
          }
        });
      }

      // Inline Stage Credit Limits AJAX Handler
      document.querySelectorAll('.stage-credit-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
          e.preventDefault();
          const submitBtn = this.querySelector('.btn-save-bounds');
          const minInput = this.querySelector('input[name="min_credits"]');
          const maxInput = this.querySelector('input[name="max_credits"]');
          const url = this.dataset.url;
          const year = this.dataset.year;
          const semester = this.dataset.semester;

          const minVal = parseFloat(minInput.value);
          const maxVal = parseFloat(maxInput.value);

          if (isNaN(minVal) || isNaN(maxVal)) {
            alert('Please enter valid numeric credit values.');
            return;
          }

          if (maxVal < minVal) {
            alert('Maximum credits cannot be less than minimum credits.');
            maxInput.focus();
            return;
          }

          const originalHtml = submitBtn.innerHTML;
          submitBtn.disabled = true;
          submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

          try {
            const response = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
              },
              body: JSON.stringify({
                study_year: parseInt(year),
                semester: parseInt(semester),
                min_credits: minVal,
                max_credits: maxVal
              })
            });

            const data = await response.json();

            if (response.ok && data.success) {
              submitBtn.classList.remove('btn-outline-primary', 'btn-outline-success');
              submitBtn.classList.add('btn-success');
              submitBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i>';

              setTimeout(() => {
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add(semester === '1' ? 'btn-outline-primary' : 'btn-outline-success');
                submitBtn.innerHTML = originalHtml;
                submitBtn.disabled = false;
              }, 1500);
            } else {
              alert(data.message || 'Failed to update credit bounds.');
              submitBtn.innerHTML = originalHtml;
              submitBtn.disabled = false;
            }
          } catch (err) {
            alert('A network error occurred while updating credit limits.');
            submitBtn.innerHTML = originalHtml;
            submitBtn.disabled = false;
          }
        });
      });
    });
  </script>
@endpush
