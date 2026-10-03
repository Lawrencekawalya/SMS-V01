@extends('layouts.app')

@section('title', 'Course Add / Drop Workspace - ' . $registration->student->full_name)

@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row align-items-center mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 fs-3 fw-bold">
          <i class="bi bi-arrow-left-right text-primary me-2"></i>Course Add / Drop Adjustment
        </h1>
        <p class="text-muted small mb-0">Official elective course swap and load modification workspace</p>
      </div>
      <div class="col-sm-6">
        <div class="float-sm-end d-flex gap-2">
          <a href="{{ route('registration.show', $registration) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Return to Slip Details
          </a>
          <a href="{{ route('registration.list') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-list-columns me-1"></i> Slips Directory
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    {{-- Session Alerts --}}
    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session('error'))
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if (session('warning'))
      <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i>
        <strong>Adjustment Submission Failed:</strong>
        <ul class="mb-0 mt-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    {{-- Add/Drop Deadline Callout Banner --}}
    @if ($isAddDropOpen)
      <div class="callout callout-warning shadow-sm mb-4 bg-body-secondary border-start border-4 border-warning">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <h5 class="fw-bold mb-1 text-warning-emphasis">
              <i class="bi bi-clock-history me-1"></i> Active Course Add / Drop Adjustment Window
            </h5>
            <p class="mb-0 small text-body-secondary">
              Elective course adjustments are permitted until the institutional deadline: 
              <strong>{{ $semester->add_drop_deadline ? $semester->add_drop_deadline->format('l, d F Y') : 'N/A' }}</strong>.
              Mandatory core courses cannot be dropped. Any changes will place this registration into 
              <span class="badge text-bg-info">Add/Drop Pending</span> for academic advisor re-approval.
            </p>
          </div>
          <div class="text-end">
            @php
              $daysRemaining = $semester->add_drop_deadline ? now()->diffInDays($semester->add_drop_deadline, false) : 0;
            @endphp
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
              <i class="bi bi-hourglass-split me-1"></i>
              @if ($daysRemaining > 0)
                {{ $daysRemaining }} Day{{ $daysRemaining > 1 ? 's' : '' }} Remaining
              @elseif ($daysRemaining === 0)
                Closes Today at Midnight
              @else
                Deadline Closing
              @endif
            </span>
          </div>
        </div>
      </div>
    @else
      <div class="callout callout-danger shadow-sm mb-4 bg-body-secondary border-start border-4 border-danger">
        <h5 class="fw-bold mb-1 text-danger">
          <i class="bi bi-lock-fill me-1"></i> Add / Drop Window Closed
        </h5>
        <p class="mb-0 small text-body-secondary">
          The official add/drop deadline for <strong>{{ $semester->name }}</strong> was 
          <strong>{{ $semester->add_drop_deadline ? $semester->add_drop_deadline->format('d M Y') : 'N/A' }}</strong>.
          The semester enrollment roster is finalized. All course modifications, adds, and drops are strictly locked.
        </p>
      </div>
    @endif

    {{-- Student Profile & Credit Summary Header --}}
    <div class="card card-outline card-primary shadow-sm mb-4">
      <div class="card-body">
        <div class="row align-items-center g-3">
          <div class="col-md-2 text-center text-md-start">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle" style="width: 72px; height: 72px;">
              <i class="bi bi-person-badge fs-1"></i>
            </div>
          </div>
          <div class="col-md-6">
            <h4 class="fw-bold mb-1">{{ $registration->student->full_name }}</h4>
            <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
              <span class="font-monospace fw-semibold text-primary">{{ $registration->student->registration_number }}</span>
              <span>&bull;</span>
              <span>{{ $registration->student->programme->name ?? 'N/A' }} ({{ $registration->student->programme->code ?? 'N/A' }})</span>
              <span>&bull;</span>
              <span>Study Year {{ $registration->study_year }}, Semester {{ $registration->semester_number }}</span>
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2">
              <span class="badge {{ $registration->status_badge_class }}">
                Status: {{ $registration->status_label }}
              </span>
              <span class="badge bg-secondary font-monospace">
                Slip #REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}
              </span>
              <span class="badge bg-body-secondary text-body border">
                Curriculum: {{ $registration->student->curriculum->version_name ?? 'Default' }}
              </span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 bg-body-secondary border rounded text-center">
              <span class="text-muted small text-uppercase fw-semibold d-block">Current Active Load</span>
              <div class="fs-2 fw-bold text-primary">{{ number_format($currentCredits, 1) }} <span class="fs-6 text-muted">CU</span></div>
              <div class="small text-muted mt-1">
                Institutional Bounds: <strong>{{ number_format($minCredits, 1) }}</strong> &mdash; <strong>{{ number_format($maxCredits, 1) }} CU</strong>
              </div>
              @php
                $dropBuffer = $currentCredits - $minCredits;
                $addCapacity = $maxCredits - $currentCredits;
              @endphp
              <div class="mt-2 d-flex justify-content-center gap-2 small">
                <span class="badge bg-info-subtle text-info border">
                  Drop Buffer: {{ number_format(max(0, $dropBuffer), 1) }} CU
                </span>
                <span class="badge bg-success-subtle text-success border">
                  Add Margin: {{ number_format(max(0, $addCapacity), 1) }} CU
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      {{-- Enrolled Courses (Drop Section) --}}
      <div class="col-lg-7">
        <div class="card card-outline card-danger shadow-sm h-100">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
              <h3 class="card-title fw-bold mb-0">
                <i class="bi bi-dash-circle text-danger me-1"></i> Currently Enrolled Courses (Drop Options)
              </h3>
              <span class="badge bg-danger">{{ $activeItems->count() }} Active Courses</span>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-body-secondary">
                  <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Course Code & Title</th>
                    <th style="width: 90px;" class="text-center">Type</th>
                    <th style="width: 70px;" class="text-center">CU</th>
                    <th style="width: 140px;" class="text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($activeItems as $index => $item)
                    <tr>
                      <td class="text-center text-muted small">{{ $index + 1 }}</td>
                      <td>
                        <div class="fw-bold font-monospace text-primary">{{ $item->courseUnit->code ?? 'N/A' }}</div>
                        <div class="small">{{ $item->courseUnit->name ?? 'N/A' }}</div>
                        <div class="text-muted small">{{ $item->courseUnit->department->name ?? '' }}</div>
                      </td>
                      <td class="text-center">
                        <span class="badge {{ $item->type_badge_class }}">{{ $item->course_type }}</span>
                      </td>
                      <td class="text-center fw-bold">{{ number_format($item->credit_units, 1) }}</td>
                      <td class="text-end">
                        @if ($item->isCore())
                          <button type="button" class="btn btn-outline-secondary btn-sm" disabled data-bs-toggle="tooltip" title="Mandatory Core courses cannot be dropped under academic policy">
                            <i class="bi bi-lock-fill me-1"></i> Locked Core
                          </button>
                        @elseif (! $isAddDropOpen || ! $canModify)
                          <button type="button" class="btn btn-outline-secondary btn-sm" disabled data-bs-toggle="tooltip" title="Add/Drop adjustments are currently locked">
                            <i class="bi bi-dash-circle me-1"></i> Drop
                          </button>
                        @else
                          @php
                            $projectedRemaining = $currentCredits - $item->credit_units;
                            $isUnderMin = $projectedRemaining < $minCredits;
                          @endphp
                          <button type="button" 
                                  class="btn btn-outline-danger btn-sm drop-course-btn" 
                                  data-bs-toggle="modal" 
                                  data-bs-target="#dropCourseModal"
                                  data-item-id="{{ $item->id }}"
                                  data-course-code="{{ $item->courseUnit->code ?? '' }}"
                                  data-course-title="{{ $item->courseUnit->name ?? '' }}"
                                  data-credit-units="{{ number_format($item->credit_units, 1) }}"
                                  data-drop-url="{{ route('registration.add-drop.drop', [$registration, $item]) }}"
                                  data-new-total="{{ number_format($projectedRemaining, 1) }}"
                                  data-is-under-min="{{ $isUnderMin ? '1' : '0' }}">
                            <i class="bi bi-dash-circle me-1"></i> Drop
                          </button>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-3 d-block mb-1"></i> No active courses enrolled in this registration.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
                <tfoot class="bg-body-secondary fw-semibold">
                  <tr>
                    <td colspan="3" class="text-end">Active Semester Total:</td>
                    <td class="text-center text-primary fs-6">{{ number_format($currentCredits, 1) }}</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          <div class="card-footer bg-body-tertiary small text-muted">
            <i class="bi bi-info-circle me-1"></i> Mandatory Core courses are locked and protected. Only elective units may be dropped.
          </div>
        </div>
      </div>

      {{-- Available Elective Courses (Add Section) --}}
      <div class="col-lg-5">
        <div class="card card-outline card-success shadow-sm h-100">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center w-100">
              <h3 class="card-title fw-bold mb-0">
                <i class="bi bi-plus-circle text-success me-1"></i> Available Electives to Add
              </h3>
              <span class="badge bg-success">{{ $availableElectives->count() }} Available</span>
            </div>
          </div>
          <div class="card-body p-0">
            @if ($availableElectives->isEmpty())
              <div class="text-center py-5 text-muted">
                <i class="bi bi-check2-all fs-1 text-success d-block mb-2"></i>
                <p class="mb-0">No eligible elective courses currently available to add for this curriculum stage.</p>
              </div>
            @else
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="bg-body-secondary">
                    <tr>
                      <th>Course</th>
                      <th style="width: 60px;" class="text-center">CU</th>
                      <th style="width: 100px;" class="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($availableElectives as $course)
                      @php
                        $exceedsMax = ($currentCredits + $course->credit_units) > $maxCredits;
                      @endphp
                      <tr>
                        <td>
                          <div class="fw-bold font-monospace text-success">{{ $course->code }}</div>
                          <div class="small fw-semibold">{{ $course->name }}</div>
                          <div class="text-muted small">{{ $course->department->name ?? '' }}</div>
                          @if ($course->prerequisites->isNotEmpty())
                            <div class="small text-muted mt-1">
                              <i class="bi bi-link-45deg"></i> Prereq{{ !config('academic.enforce_prerequisites', false) ? ' (Advisory)' : '' }}: 
                              @foreach ($course->prerequisites as $prereq)
                                <span class="badge text-bg-light border font-monospace">{{ $prereq->code }}</span>
                              @endforeach
                            </div>
                          @endif
                        </td>
                        <td class="text-center fw-bold">{{ number_format($course->credit_units, 1) }}</td>
                        <td class="text-end">
                          @if (! $isAddDropOpen || ! $canModify)
                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Add/Drop adjustments are locked">
                              <i class="bi bi-plus-circle me-1"></i> Add
                            </button>
                          @elseif ($exceedsMax)
                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled data-bs-toggle="tooltip" title="Adding this course would exceed the max ceiling of {{ $maxCredits }} CU (Projected: {{ $currentCredits + $course->credit_units }} CU)">
                              <i class="bi bi-ban me-1"></i> Max
                            </button>
                          @else
                            <form action="{{ route('registration.add-drop.add', $registration) }}" method="POST" class="d-inline">
                              @csrf
                              <input type="hidden" name="course_unit_id" value="{{ $course->id }}">
                              <button type="submit" class="btn btn-success btn-sm fw-semibold">
                                <i class="bi bi-plus-circle me-1"></i> Add
                              </button>
                            </form>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
          <div class="card-footer bg-body-tertiary small text-muted">
            @if (config('academic.enforce_prerequisites', false))
              <i class="bi bi-shield-check me-1"></i> Courses shown have all prerequisite requirements satisfied for this student.
            @else
              <i class="bi bi-info-circle me-1"></i> Electives available for selection under your programme curriculum.
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Dropped Courses Audit Trail Log --}}
    <div class="card card-outline card-secondary shadow-sm mt-4">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center w-100">
          <h3 class="card-title fw-bold mb-0">
            <i class="bi bi-clock-history text-secondary me-1"></i> Dropped Courses Audit Trail
          </h3>
          <span class="badge bg-secondary">{{ $droppedItems->count() }} Recorded</span>
        </div>
      </div>
      <div class="card-body p-0">
        @if ($droppedItems->isEmpty())
          <div class="p-3 text-center text-muted small">
            <i class="bi bi-journal-check me-1"></i> No course units have been dropped from this registration slip.
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-body-secondary small">
                <tr>
                  <th style="width: 40px;" class="text-center">#</th>
                  <th>Course Code & Title</th>
                  <th style="width: 100px;" class="text-center">Original Type</th>
                  <th style="width: 70px;" class="text-center">CU</th>
                  <th style="width: 180px;">Dropped Timestamp</th>
                  <th>Recorded Justification / Reason</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($droppedItems as $index => $dropped)
                  <tr class="text-muted">
                    <td class="text-center small">{{ $index + 1 }}</td>
                    <td>
                      <span class="fw-bold font-monospace text-decoration-line-through text-danger me-2">
                        {{ $dropped->courseUnit->code ?? 'N/A' }}
                      </span>
                      <span class="text-decoration-line-through">
                        {{ $dropped->courseUnit->name ?? 'N/A' }}
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="badge bg-secondary text-decoration-line-through">{{ $dropped->course_type }}</span>
                    </td>
                    <td class="text-center fw-bold">{{ number_format($dropped->credit_units, 1) }}</td>
                    <td class="small">
                      <i class="bi bi-calendar-event me-1"></i>
                      {{ $dropped->dropped_at ? $dropped->dropped_at->format('d M Y, H:i') : 'N/A' }}
                    </td>
                    <td class="small fst-italic">
                      &ldquo;{{ $dropped->drop_reason ?? 'No specific reason provided' }}&rdquo;
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>

  </div>
</section>

{{-- Course Drop Confirmation Modal --}}
<div class="modal fade" id="dropCourseModal" tabindex="-1" aria-labelledby="dropCourseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="dropCourseForm" method="POST" action="">
        @csrf
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title fw-bold" id="dropCourseModalLabel">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Course Drop
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-3">
            You are about to drop the following course unit from this registration slip:
          </p>

          <div class="alert alert-secondary d-flex align-items-center justify-content-between p-3 mb-3">
            <div>
              <div class="fw-bold fs-5 font-monospace text-primary" id="modalCourseCode">CSC1201</div>
              <div class="small fw-semibold text-body" id="modalCourseTitle">Advanced Systems</div>
            </div>
            <div class="text-end">
              <span class="badge bg-danger fs-6" id="modalCreditUnits">-3.0 CU</span>
            </div>
          </div>

          {{-- Floor Breach Warning --}}
          <div id="floorBreachWarning" class="alert alert-danger d-none mb-3">
            <div class="fw-bold"><i class="bi bi-x-octagon-fill me-1"></i> Drop Prohibited: Minimum Credit Floor Breach</div>
            <div class="small mt-1">
              Dropping this course would leave <strong id="modalProjectedCredits">9.0</strong> CU. 
              The institutional policy requires a minimum semester load of <strong>{{ number_format($minCredits, 1) }} CU</strong>.
              You must add an alternative elective course before or concurrent with dropping this unit.
            </div>
          </div>

          {{-- Normal Warning --}}
          <div id="normalDropNotice" class="alert alert-warning mb-3 small">
            <i class="bi bi-info-circle me-1"></i>
            Projected remaining load after drop: <strong id="modalValidProjectedCredits">15.0 CU</strong>.
            This action will update the registration status to <strong>Add/Drop Pending</strong>.
          </div>

          <div class="mb-3">
            <label for="drop_reason" class="form-label fw-semibold">
              Drop Justification / Official Reason <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" 
                      id="drop_reason" 
                      name="drop_reason" 
                      rows="3" 
                      minlength="10" 
                      maxlength="500" 
                      required 
                      placeholder="Enter specific academic reason or timetable clash explanation (minimum 10 characters)..."></textarea>
            <div class="form-text text-muted small">
              This reason will be recorded on the official audit log and presented to the academic advisor.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" id="confirmDropBtn" class="btn btn-danger fw-semibold">
            <i class="bi bi-dash-circle me-1"></i> Confirm & Drop Course
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle Drop Modal setup
    const dropButtons = document.querySelectorAll('.drop-course-btn');
    const dropForm = document.getElementById('dropCourseForm');
    const modalCourseCode = document.getElementById('modalCourseCode');
    const modalCourseTitle = document.getElementById('modalCourseTitle');
    const modalCreditUnits = document.getElementById('modalCreditUnits');
    const modalProjectedCredits = document.getElementById('modalProjectedCredits');
    const modalValidProjectedCredits = document.getElementById('modalValidProjectedCredits');
    const floorBreachWarning = document.getElementById('floorBreachWarning');
    const normalDropNotice = document.getElementById('normalDropNotice');
    const confirmDropBtn = document.getElementById('confirmDropBtn');
    const dropReasonInput = document.getElementById('drop_reason');

    dropButtons.forEach(btn => {
      btn.addEventListener('click', function () {
        const dropUrl = this.getAttribute('data-drop-url');
        const courseCode = this.getAttribute('data-course-code');
        const courseTitle = this.getAttribute('data-course-title');
        const creditUnits = this.getAttribute('data-credit-units');
        const newTotal = this.getAttribute('data-new-total');
        const isUnderMin = this.getAttribute('data-is-under-min') === '1';

        dropForm.setAttribute('action', dropUrl);
        modalCourseCode.textContent = courseCode;
        modalCourseTitle.textContent = courseTitle;
        modalCreditUnits.textContent = '-' + creditUnits + ' CU';
        dropReasonInput.value = '';

        if (isUnderMin) {
          modalProjectedCredits.textContent = newTotal;
          floorBreachWarning.classList.remove('d-none');
          normalDropNotice.classList.add('d-none');
          confirmDropBtn.setAttribute('disabled', 'disabled');
        } else {
          modalValidProjectedCredits.textContent = newTotal + ' CU';
          floorBreachWarning.classList.add('d-none');
          normalDropNotice.classList.remove('d-none');
          confirmDropBtn.removeAttribute('disabled');
        }
      });
    });
  });
</script>
@endpush
