@extends('layouts.app')

@section('title', (config('academic.enforce_prerequisites', false) ? 'Course Eligibility & Prerequisite Inspector - ' : 'Course Eligibility Inspector - ') . config('app.name', 'SMS-V01'))
@section('page-title', config('academic.enforce_prerequisites', false) ? 'Course Eligibility & Prerequisite Inspector' : 'Course Eligibility Inspector')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item"><a href="{{ route('academic.registrations.index') }}">Course Registrations</a></li>
  <li class="breadcrumb-item active" aria-current="page">Eligibility Inspector</li>
@endsection

@section('content')
  <!-- Student Persona Simulator Toolbar -->
  <div class="card card-outline card-secondary mb-4 shadow-sm">
    <div class="card-header">
      <h3 class="card-title">
        <i class="bi bi-person-bounding-box me-1 text-primary"></i> Select Student for Live Eligibility Diagnostic
      </h3>
    </div>
    <div class="card-body">
      <form action="{{ route('academic.registrations.eligibility') }}" method="GET" class="row g-3 align-items-center">
        <div class="col-12 col-md-8">
          <label for="student_id" class="form-label small fw-semibold">Select Student Persona:</label>
          <select name="student_id" id="student_id" class="form-select" onchange="this.form.submit()">
            @foreach ($students as $s)
              <option value="{{ $s->id }}" {{ $selectedStudent && $selectedStudent->id === $s->id ? 'selected' : '' }}>
                {{ $s->registration_number }} &mdash; {{ $s->full_name }} ({{ $s->programme->code }}, Year {{ $s->current_study_year }} Sem {{ $s->current_semester }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-4 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">
            <i class="bi bi-arrow-repeat me-1"></i> Inspect Eligibility
          </button>
          <a href="{{ route('academic.registrations.index') }}" class="btn btn-outline-secondary" title="Back to Directory">
            <i class="bi bi-journal-text me-1"></i> Slips Directory
          </a>
        </div>
      </form>
    </div>
  </div>

  @if ($selectedStudent && $eligibility)
    <!-- Student Stage Profile Header Card -->
    <div class="card card-outline card-primary mb-4 shadow-sm">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-4 border-end">
            <h6 class="text-uppercase text-body-secondary fw-bold small mb-2">Student Profile</h6>
            <div class="fs-5 fw-bold text-body-emphasis">{{ $selectedStudent->full_name }}</div>
            <div class="font-monospace text-primary fw-semibold">{{ $selectedStudent->registration_number }}</div>
            <div class="small text-body-secondary mb-2">Student No: {{ $selectedStudent->student_number }}</div>
            <span class="badge {{ $selectedStudent->status_badge_class }}">
              <i class="bi bi-check-circle me-1"></i> {{ ucfirst($selectedStudent->status) }}
            </span>
            <span class="badge text-bg-secondary ms-1">{{ $selectedStudent->academic_stage }}</span>
          </div>

          <div class="col-12 col-md-4 border-end">
            <h6 class="text-uppercase text-body-secondary fw-bold small mb-2">Programme & Curriculum</h6>
            <div class="fw-bold text-body-emphasis">{{ $selectedStudent->programme->name }} ({{ $selectedStudent->programme->code }})</div>
            <div class="small text-body-secondary mb-2">
              <i class="bi bi-mortarboard-fill me-1"></i>{{ $selectedStudent->curriculum->version_name ?? 'No Curriculum Assigned' }}
            </div>
            <div class="small text-body-secondary">
              Min Graduation Credits: <span class="badge bg-body-secondary text-body border">{{ $selectedStudent->curriculum->min_graduation_credits ?? 0 }} CU</span>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <h6 class="text-uppercase text-body-secondary fw-bold small mb-2">Active Semester & Session</h6>
            <div class="fw-semibold text-body-emphasis">{{ $activeSemester->name }} &mdash; {{ $activeSemester->academicYear->name }}</div>
            <div class="small text-body-secondary mb-2">
              Registration Period: {{ $activeSemester->registration_start_date?->format('M d') }} &ndash; {{ $activeSemester->registration_end_date?->format('M d, Y') }}
            </div>
            @if ($eligibility['summary']['is_registration_open'])
              <span class="badge text-bg-success">
                <i class="bi bi-door-open me-1"></i> Registration Open
              </span>
            @else
              <span class="badge text-bg-danger">
                <i class="bi bi-lock me-1"></i> Registration Closed
              </span>
            @endif

            @if ($eligibility['summary']['existing_registration'])
              <div class="small mt-2 text-info">
                <i class="bi bi-info-circle me-1"></i> Slip already created:
                <a href="{{ route('academic.registrations.show', $eligibility['summary']['existing_registration']) }}" class="fw-bold text-decoration-none">
                  #REG-{{ str_pad($eligibility['summary']['existing_registration']->id, 5, '0', STR_PAD_LEFT) }}
                  ({{ $eligibility['summary']['existing_registration']->status_label }})
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Credit Threshold Gauge Widget Card -->
    <div class="card card-outline card-info mb-4 shadow-sm">
      <div class="card-header">
        <div class="d-flex align-items-center justify-content-between w-100">
          <h3 class="card-title mb-0">
            <i class="bi bi-speedometer me-1 text-info"></i> Semester Credit Load Bounds & Meter
          </h3>
          <span class="badge bg-body-secondary text-body border fs-6">
            Bounds: {{ \App\Services\CourseEligibilityService::MIN_SEMESTER_CREDITS }} CU &ndash; {{ \App\Services\CourseEligibilityService::MAX_SEMESTER_CREDITS }} CU
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row align-items-center g-3">
          <div class="col-12 col-md-4">
            <div class="p-3 rounded bg-body-secondary border border-secondary-subtle">
              <div class="small text-body-secondary fw-semibold">Mandatory Core Credits:</div>
              <div class="fs-4 fw-bold text-primary">{{ number_format($eligibility['summary']['core_credits'], 1) }} CU</div>
              <small class="text-body-secondary">Pre-selected and locked by stage</small>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="p-3 rounded bg-body-secondary border border-secondary-subtle">
              <div class="small text-body-secondary fw-semibold">Total Projected Credits:</div>
              <div class="fs-4 fw-bold text-success" id="meter-total-credits">{{ number_format($eligibility['summary']['core_credits'], 1) }} CU</div>
              <small class="text-body-secondary" id="meter-status-note">Floor: {{ number_format($eligibility['summary']['min_credits'] ?? \App\Services\CourseEligibilityService::MIN_SEMESTER_CREDITS, 1) }} CU &bull; Ceiling: {{ number_format($eligibility['summary']['max_credits'] ?? \App\Services\CourseEligibilityService::MAX_SEMESTER_CREDITS, 1) }} CU</small>
            </div>
          </div>

          <div class="col-12 col-md-4">
            <div class="small text-body-secondary fw-semibold mb-1 d-flex justify-content-between">
              <span>Credit Capacity Utilization</span>
              <span id="meter-percentage" class="fw-bold">0%</span>
            </div>
            <div class="progress" style="height: 14px;">
              <div id="meter-progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between small text-body-secondary mt-1">
              <span>Min: {{ number_format($eligibility['summary']['min_credits'] ?? \App\Services\CourseEligibilityService::MIN_SEMESTER_CREDITS, 1) }} CU</span>
              <span>Max: {{ number_format($eligibility['summary']['max_credits'] ?? \App\Services\CourseEligibilityService::MAX_SEMESTER_CREDITS, 1) }} CU</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mandatory Core Courses Matrix Card -->
    <div class="card card-outline card-primary mb-4 shadow-sm">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
          <h3 class="card-title mb-0">
            <i class="bi bi-shield-lock-fill me-1 text-primary"></i> Mandatory Core Courses (Stage: {{ $selectedStudent->academic_stage }})
          </h3>
          <span class="badge text-bg-primary">
            {{ $eligibility['mandatory_core_courses']->count() }} Courses &bull; {{ number_format($eligibility['summary']['core_credits'], 1) }} CU
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped align-middle mb-0">
            <thead class="bg-body-secondary border-bottom">
              <tr>
                <th style="width: 40px;" class="text-center"><i class="bi bi-lock-fill text-muted"></i></th>
                <th style="width: 50px;">#</th>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Teaching Department</th>
                <th class="text-center">Credit Units</th>
                @if (config('academic.enforce_prerequisites', false))
                  <th>Prerequisite Status</th>
                @endif
                <th>Enrolment Policy</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($eligibility['mandatory_core_courses'] as $course)
                <tr>
                  <td class="text-center">
                    <input type="checkbox" checked disabled class="form-check-input bg-primary border-primary" title="Mandatory Core - cannot be unchecked">
                  </td>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="fw-bold font-monospace text-primary">{{ $course->code }}</span>
                  </td>
                  <td>
                    <div class="fw-semibold text-body-emphasis">{{ $course->name }}</div>
                  </td>
                  <td>
                    <small class="text-body-secondary">{{ $course->department->name ?? 'N/A' }}</small>
                  </td>
                  <td class="text-center fw-bold text-primary">
                    {{ number_format($course->credit_units, 1) }} CU
                  </td>
                  @if (config('academic.enforce_prerequisites', false))
                    <td>
                      @if ($course->prerequisites->isEmpty())
                        <span class="badge bg-body-secondary text-body border">None</span>
                      @else
                        <span class="badge text-bg-success">
                          <i class="bi bi-check-circle me-1"></i> {{ $course->prerequisites->pluck('code')->implode(', ') }} Satisfied
                        </span>
                      @endif
                    </td>
                  @endif
                  <td>
                    <span class="badge text-bg-primary">
                      <i class="bi bi-lock me-1"></i> Core &bull; Auto-Selected
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ config('academic.enforce_prerequisites', false) ? 8 : 7 }}" class="text-center text-muted py-4">No mandatory core courses defined for this stage in the curriculum.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Available Electives Pool Card -->
    <div class="card card-outline card-info mb-4 shadow-sm">
      <div class="card-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
          <h3 class="card-title mb-0">
            <i class="bi bi-collection-play me-1 text-info"></i> Available Electives Pool (Select to add to term load)
          </h3>
          <span class="badge text-bg-info">
            {{ $eligibility['available_electives']->count() }} Electives Available
          </span>
        </div>
      </div>
      <div class="card-body p-0">
        @if ($eligibility['available_electives']->isNotEmpty())
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="bg-body-secondary border-bottom">
                <tr>
                  <th style="width: 40px;" class="text-center">Select</th>
                  <th style="width: 50px;">#</th>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th>Teaching Department</th>
                  <th class="text-center">Credit Units</th>
                  @if (config('academic.enforce_prerequisites', false))
                    <th>Prerequisite Status</th>
                  @endif
                  <th>Course Type</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($eligibility['available_electives'] as $course)
                  <tr>
                    <td class="text-center">
                      <input type="checkbox"
                             class="form-check-input elective-checkbox"
                             id="elective-{{ $course->id }}"
                             data-credits="{{ (float) $course->credit_units }}"
                             data-code="{{ $course->code }}">
                    </td>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <label for="elective-{{ $course->id }}" class="fw-bold font-monospace text-primary mb-0 cursor-pointer">
                        {{ $course->code }}
                      </label>
                    </td>
                    <td>
                      <label for="elective-{{ $course->id }}" class="fw-semibold text-body-emphasis mb-0 cursor-pointer">
                        {{ $course->name }}
                      </label>
                    </td>
                    <td>
                      <small class="text-body-secondary">{{ $course->department->name ?? 'N/A' }}</small>
                    </td>
                    <td class="text-center fw-bold">
                      {{ number_format($course->credit_units, 1) }} CU
                    </td>
                    @if (config('academic.enforce_prerequisites', false))
                      <td>
                        @if ($course->prerequisites->isEmpty())
                          <span class="badge bg-body-secondary text-body border">None</span>
                        @else
                          <span class="badge text-bg-success">
                            <i class="bi bi-check-circle me-1"></i> {{ $course->prerequisites->pluck('code')->implode(', ') }} Satisfied
                          </span>
                        @endif
                      </td>
                    @endif
                    <td>
                      <span class="badge text-bg-info">Elective</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @else
          <div class="p-4 text-center text-muted">
            <i class="bi bi-check2-circle fs-3 text-success d-block mb-2"></i>
            No optional electives are required for this stage; all prescribed courses are covered by the Core Curriculum.
          </div>
        @endif
      </div>
    </div>

    @if (config('academic.enforce_prerequisites', false))
      <!-- Blocked & Ineligible Courses (Stage Constraints & Prerequisite Violations) -->
      <div class="card card-outline card-secondary mb-4 shadow-sm">
        <div class="card-header">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
            <h3 class="card-title mb-0">
              <i class="bi bi-slash-circle me-1 text-danger"></i> Restricted / Ineligible Courses ({{ $eligibility['blocked_courses']->count() }})
            </h3>
            <div class="card-tools me-0 ms-auto">
              <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="callout callout-danger mb-3">
            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Automated Academic Progression Guardrails Active</h6>
            <p class="small mb-0">
              The courses below are mapped to other stages in the student's curriculum or have unmet prerequisite dependencies. Students cannot self-enroll in higher study year courses or jump curriculum sequences.
            </p>
          </div>

          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="bg-body-secondary border-bottom">
                <tr>
                  <th style="width: 50px;">#</th>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th>Curriculum Stage</th>
                  <th class="text-center">Credit Units</th>
                  <th>Blocking Rule / Reason</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($eligibility['blocked_courses'] as $blocked)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <span class="fw-bold font-monospace text-muted">{{ $blocked['course']->code }}</span>
                    </td>
                    <td>
                      <div class="text-muted fw-semibold">{{ $blocked['course']->name }}</div>
                    </td>
                    <td>
                      <span class="badge bg-body-secondary text-body border">{{ $blocked['stage_string'] }}</span>
                    </td>
                    <td class="text-center text-muted fw-bold">
                      {{ number_format($blocked['course']->credit_units, 1) }} CU
                    </td>
                    <td>
                      <span class="badge text-bg-danger">
                        <i class="bi bi-x-circle me-1"></i> {{ $blocked['reason'] }}
                      </span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted py-3">No blocked courses found.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @endif
  @endif
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const baseCoreCredits = {{ $eligibility ? (float) $eligibility['summary']['core_credits'] : 0.0 }};
      const minCredits = {{ $eligibility ? (float) ($eligibility['summary']['min_credits'] ?? \App\Services\CourseEligibilityService::MIN_SEMESTER_CREDITS) : \App\Services\CourseEligibilityService::MIN_SEMESTER_CREDITS }};
      const maxCredits = {{ $eligibility ? (float) ($eligibility['summary']['max_credits'] ?? \App\Services\CourseEligibilityService::MAX_SEMESTER_CREDITS) : \App\Services\CourseEligibilityService::MAX_SEMESTER_CREDITS }};

      const meterTotalEl = document.getElementById('meter-total-credits');
      const meterPercentEl = document.getElementById('meter-percentage');
      const meterProgressBar = document.getElementById('meter-progress-bar');
      const meterStatusNote = document.getElementById('meter-status-note');
      const electiveCheckboxes = document.querySelectorAll('.elective-checkbox');

      function recalculateLoad() {
        let selectedElectiveCredits = 0;
        electiveCheckboxes.forEach(cb => {
          if (cb.checked) {
            selectedElectiveCredits += parseFloat(cb.dataset.credits || 0);
          }
        });

        const totalCredits = baseCoreCredits + selectedElectiveCredits;
        if (meterTotalEl) {
          meterTotalEl.textContent = totalCredits.toFixed(1) + ' CU';
        }

        const percentage = Math.min(100, Math.round((totalCredits / maxCredits) * 100));
        if (meterPercentEl) {
          meterPercentEl.textContent = percentage + '%';
        }

        if (meterProgressBar) {
          meterProgressBar.style.width = percentage + '%';
          meterProgressBar.setAttribute('aria-valuenow', percentage);

          if (totalCredits < minCredits) {
            meterProgressBar.className = 'progress-bar bg-warning';
            if (meterTotalEl) meterTotalEl.className = 'fs-4 fw-bold text-warning';
            if (meterStatusNote) meterStatusNote.textContent = 'Underload: Needs at least ' + minCredits + ' CU';
          } else if (totalCredits > maxCredits) {
            meterProgressBar.className = 'progress-bar bg-danger';
            if (meterTotalEl) meterTotalEl.className = 'fs-4 fw-bold text-danger';
            if (meterStatusNote) meterStatusNote.textContent = 'Overload: Exceeds max ' + maxCredits + ' CU limit!';
          } else {
            meterProgressBar.className = 'progress-bar bg-success';
            if (meterTotalEl) meterTotalEl.className = 'fs-4 fw-bold text-success';
            if (meterStatusNote) meterStatusNote.textContent = 'Optimal Load: In standard institutional range (' + minCredits + ' - ' + maxCredits + ' CU)';
          }
        }
      }

      electiveCheckboxes.forEach(cb => {
        cb.addEventListener('change', recalculateLoad);
      });

      // Initial calculation
      recalculateLoad();
    });
  </script>
@endpush
