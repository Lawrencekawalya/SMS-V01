@extends('layouts.app')

@section('title', 'Edit Course Registration Slip #' . str_pad($registration->id, 5, '0', STR_PAD_LEFT) . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Edit Registration Slip #' . str_pad($registration->id, 5, '0', STR_PAD_LEFT))

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item"><a href="{{ route('registration.list') }}">Course Registrations</a></li>
  <li class="breadcrumb-item"><a href="{{ route('registration.show', $registration) }}">#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</a></li>
  <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
  @if ($registration->status === 'rejected' && $registration->advisor_remarks)
    <div class="callout callout-danger mb-4 shadow-sm">
      <h5 class="fw-bold mb-1 text-danger">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Advisor Changes Requested / Remarks:
      </h5>
      <p class="mb-0 text-body">{{ $registration->advisor_remarks }}</p>
    </div>
  @endif

  @php
    $selectedItemIds = $registration->items->pluck('course_unit_id')->toArray();
  @endphp

  <form action="{{ route('registration.update', $registration) }}" method="POST" id="course-registration-edit-form">
    @csrf
    @method('PUT')
    <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
    <input type="hidden" name="semester_id" value="{{ $activeSemester->id }}">
    <input type="hidden" name="total_credits" id="form-total-credits" value="{{ old('total_credits', $registration->total_credits) }}">
    <input type="hidden" name="action_status" id="form-action-status" value="submitted">

    <div class="row">
      <!-- Left Column: Course Selection Workspace (8 cols) -->
      <div class="col-12 col-lg-8">
        <!-- Student Profile Summary Card -->
        <div class="card card-outline card-primary mb-4 shadow-sm">
          <div class="card-body">
            <div class="row g-3">
              <div class="col-12 col-md-6 border-end">
                <span class="badge text-bg-primary mb-1">Student Profile</span>
                <div class="fs-5 fw-bold text-body-emphasis">{{ $selectedStudent->full_name }}</div>
                <div class="font-monospace text-primary fw-semibold">{{ $selectedStudent->registration_number }}</div>
                <small class="text-body-secondary">Stage: {{ $selectedStudent->academic_stage }} &bull; {{ $selectedStudent->study_mode }}</small>
              </div>
              <div class="col-12 col-md-6">
                <span class="badge text-bg-secondary mb-1">Academic Programme</span>
                <div class="fw-bold text-body-emphasis">{{ $selectedStudent->programme->name }} ({{ $selectedStudent->programme->code }})</div>
                <small class="text-body-secondary d-block">Curriculum: {{ $selectedStudent->curriculum->version_name }}</small>
              </div>
            </div>
          </div>
        </div>

        <!-- Mandatory Core Courses Section -->
        <div class="card card-outline card-primary mb-4 shadow-sm">
          <div class="card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
              <h3 class="card-title mb-0">
                <i class="bi bi-shield-lock-fill me-1 text-primary"></i> 1. Mandatory Core Courses (Pre-Selected)
              </h3>
              <span class="badge text-bg-primary">
                {{ $eligibility['mandatory_core_courses']->count() }} Units &bull; {{ number_format($eligibility['summary']['core_credits'], 1) }} CU
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
                    <th class="text-center">Credit Units</th>
                    <th>Enrollment Status</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($eligibility['mandatory_core_courses'] as $course)
                    <tr>
                      <td class="text-center">
                        <input type="checkbox" checked disabled class="form-check-input bg-primary border-primary">
                        <input type="hidden" name="course_unit_ids[]" value="{{ $course->id }}">
                      </td>
                      <td>{{ $loop->iteration }}</td>
                      <td>
                        <span class="fw-bold font-monospace text-primary">{{ $course->code }}</span>
                      </td>
                      <td>
                        <div class="fw-semibold text-body-emphasis">{{ $course->name }}</div>
                        <small class="text-body-secondary">{{ $course->department->name ?? '' }}</small>
                      </td>
                      <td class="text-center fw-bold text-primary">
                        {{ number_format($course->credit_units, 1) }} CU
                      </td>
                      <td>
                        <span class="badge text-bg-primary">
                          <i class="bi bi-lock me-1"></i> Core &bull; Required
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted py-3">No mandatory core courses assigned for this stage.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Available Electives Selection Section -->
        <div class="card card-outline card-info mb-4 shadow-sm">
          <div class="card-header">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
              <h3 class="card-title mb-0">
                <i class="bi bi-plus-square-dotted me-1 text-info"></i> 2. Available Electives Pool (Select Courses)
              </h3>
              <span class="badge text-bg-info">
                {{ $eligibility['available_electives']->count() }} Available
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
                      <th class="text-center">Credit Units</th>
                      <th>Prerequisite Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($eligibility['available_electives'] as $course)
                      <tr>
                        <td class="text-center">
                          <input type="checkbox"
                                 name="course_unit_ids[]"
                                 value="{{ $course->id }}"
                                 id="elective-{{ $course->id }}"
                                 class="form-check-input elective-checkbox"
                                 {{ in_array($course->id, $selectedItemIds, true) ? 'checked' : '' }}
                                 data-credits="{{ (float) $course->credit_units }}">
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
                          <small class="text-body-secondary d-block">{{ $course->department->name ?? '' }}</small>
                        </td>
                        <td class="text-center fw-bold">
                          {{ number_format($course->credit_units, 1) }} CU
                        </td>
                        <td>
                          <span class="badge text-bg-success">
                            <i class="bi bi-check-circle me-1"></i> Satisfied
                          </span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <div class="p-4 text-center text-muted">
                <i class="bi bi-check2-all fs-2 text-success d-block mb-1"></i>
                No optional electives are required for this stage. All requirements are covered by the core courses.
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Right Column: Sticky Credit Meter & Submission Action Box (4 cols) -->
      <div class="col-12 col-lg-4">
        <div class="card card-outline card-success mb-4 sticky-top shadow-sm" style="top: 1rem; z-index: 10;">
          <div class="card-header">
            <h3 class="card-title">
              <i class="bi bi-card-checklist me-1 text-success"></i> Slip Summary & Credit Meter
            </h3>
          </div>
          <div class="card-body">
            <div class="mb-3 text-center p-3 rounded bg-body-secondary border border-secondary-subtle">
              <span class="small text-body-secondary fw-semibold d-block">TOTAL ENROLLED LOAD</span>
              <span class="fs-1 fw-bold text-success" id="meter-total-credits">{{ number_format($registration->total_credits, 1) }} CU</span>
              <small class="text-body-secondary d-block mt-1" id="meter-status-note">Evaluating credit bounds...</small>
            </div>

            <!-- Visual Capacity Progress Bar -->
            <div class="mb-3">
              <div class="d-flex justify-content-between small text-body-secondary mb-1">
                <span>Capacity</span>
                <span id="meter-percentage" class="fw-bold">0%</span>
              </div>
              <div class="progress" style="height: 12px;">
                <div id="meter-progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <div class="d-flex justify-content-between small text-body-secondary mt-1">
                <span>Min: {{ number_format($eligibility['summary']['min_credits'] ?? \App\Services\CourseEligibilityService::MIN_SEMESTER_CREDITS, 1) }} CU</span>
                <span>Max: {{ number_format($eligibility['summary']['max_credits'] ?? \App\Services\CourseEligibilityService::MAX_SEMESTER_CREDITS, 1) }} CU</span>
              </div>
            </div>

            <!-- Rules Compliance Checklist -->
            <ul class="list-group list-group-flush mb-4 small">
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span>Mandatory Core Courses</span>
                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Included</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span>Minimum Floor (&ge; {{ number_format($eligibility['summary']['min_credits'] ?? \App\Services\CourseEligibilityService::MIN_SEMESTER_CREDITS, 1) }} CU)</span>
                <span id="checklist-floor" class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> Met</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span>Maximum Ceiling (&le; {{ number_format($eligibility['summary']['max_credits'] ?? \App\Services\CourseEligibilityService::MAX_SEMESTER_CREDITS, 1) }} CU)</span>
                <span id="checklist-ceiling" class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i> Valid</span>
              </li>
            </ul>

            <!-- Submission Action Buttons -->
            <div class="d-grid gap-2">
              <button type="submit"
                      onclick="document.getElementById('form-action-status').value = 'submitted';"
                      id="submit-slip-btn"
                      class="btn btn-success py-2 fw-semibold">
                @if (config('academic.require_registration_approval', false))
                  <i class="bi bi-send-check me-1"></i> Submit for Advisor Approval
                @else
                  <i class="bi bi-check-circle-fill me-1"></i> Confirm & Complete Registration
                @endif
              </button>

              <button type="submit"
                      onclick="document.getElementById('form-action-status').value = 'draft';"
                      class="btn btn-outline-secondary">
                <i class="bi bi-save me-1"></i> Update Draft
              </button>

              <a href="{{ route('registration.show', $registration) }}" class="btn btn-link btn-sm text-secondary">
                Cancel and return to slip
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
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
      const checklistFloor = document.getElementById('checklist-floor');
      const checklistCeiling = document.getElementById('checklist-ceiling');
      const submitSlipBtn = document.getElementById('submit-slip-btn');
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

        const formTotalCreditsInput = document.getElementById('form-total-credits');
        if (formTotalCreditsInput) {
          formTotalCreditsInput.value = totalCredits.toFixed(1);
        }

        const percentage = Math.min(100, Math.round((totalCredits / maxCredits) * 100));
        if (meterPercentEl) {
          meterPercentEl.textContent = percentage + '%';
        }

        const isUnderload = totalCredits < minCredits;
        const isOverload = totalCredits > maxCredits;

        if (checklistFloor) {
          checklistFloor.innerHTML = isUnderload
            ? '<i class="bi bi-x-circle-fill text-danger me-1"></i> Below Floor'
            : '<i class="bi bi-check-circle-fill text-success me-1"></i> Met';
        }

        if (checklistCeiling) {
          checklistCeiling.innerHTML = isOverload
            ? '<i class="bi bi-x-circle-fill text-danger me-1"></i> Exceeded'
            : '<i class="bi bi-check-circle-fill text-success me-1"></i> Valid';
        }

        if (meterProgressBar) {
          meterProgressBar.style.width = percentage + '%';
          meterProgressBar.setAttribute('aria-valuenow', percentage);

          if (isUnderload) {
            meterProgressBar.className = 'progress-bar bg-warning';
            if (meterTotalEl) meterTotalEl.className = 'fs-1 fw-bold text-warning';
            if (meterStatusNote) meterStatusNote.textContent = 'Underload: Needs ' + (minCredits - totalCredits).toFixed(1) + ' more CU to meet minimum floor';
            if (submitSlipBtn) submitSlipBtn.disabled = true;
          } else if (isOverload) {
            meterProgressBar.className = 'progress-bar bg-danger';
            if (meterTotalEl) meterTotalEl.className = 'fs-1 fw-bold text-danger';
            if (meterStatusNote) meterStatusNote.textContent = 'Overload: Exceeds max ' + maxCredits + ' CU limit by ' + (totalCredits - maxCredits).toFixed(1) + ' CU';
            if (submitSlipBtn) submitSlipBtn.disabled = true;
          } else {
            meterProgressBar.className = 'progress-bar bg-success';
            if (meterTotalEl) meterTotalEl.className = 'fs-1 fw-bold text-success';
            if (meterStatusNote) meterStatusNote.textContent = 'Optimal Load: Valid institutional range (' + minCredits + ' - ' + maxCredits + ' CU)';
            if (submitSlipBtn) submitSlipBtn.disabled = false;
          }
        }
      }

      electiveCheckboxes.forEach(cb => {
        cb.addEventListener('change', recalculateLoad);
      });

      recalculateLoad();
    });
  </script>
@endpush
