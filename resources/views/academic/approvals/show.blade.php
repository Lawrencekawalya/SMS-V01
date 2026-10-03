@extends('layouts.app')

@section('title', 'Verify Registration Slip #REG-' . str_pad($registration->id, 5, '0', STR_PAD_LEFT) . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Verify Registration Slip')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item"><a href="{{ route('approval.list') }}">Advisor Approvals</a></li>
  <li class="breadcrumb-item active" aria-current="page">#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</li>
@endsection

@section('content')
  {{-- Session Notifications --}}
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
      <i class="bi bi-x-circle-fill me-2"></i>
      <strong>Action Failed:</strong>
      <ul class="mb-0 mt-1">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Top Action & Navigation Bar -->
  <div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
        <div class="d-flex align-items-center gap-2">
          <span class="fs-4 fw-bold font-monospace text-primary">#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</span>
          <span class="badge {{ $registration->status_badge_class }} fs-6">
            {{ $registration->status_label }}
          </span>
          @if ($registration->status === 'add_drop_pending')
            <span class="badge text-bg-info fs-6">
              <i class="bi bi-arrow-left-right me-1"></i> Add / Drop Revision
            </span>
          @endif
        </div>
        <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0 ms-auto">
          <a href="{{ route('approval.list') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Approvals
          </a>
          <a href="{{ route('registration.print', $registration) }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Official Print Slip
          </a>
          @if (in_array($registration->status, ['submitted', 'add_drop_pending']))
            <button type="button" class="btn btn-danger btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectSlipModal">
              <i class="bi bi-x-octagon me-1"></i> Request Changes / Reject
            </button>
            <button type="button" class="btn btn-success btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#approveSlipModal">
              <i class="bi bi-check-circle-fill me-1"></i> Approve Registration
            </button>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: Student Academic Standing -->
    <div class="col-lg-4">
      <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header">
          <h3 class="card-title fw-bold">
            <i class="bi bi-person-badge text-info me-1"></i> Student Academic Standing
          </h3>
        </div>
        <div class="card-body">
          <div class="text-center mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-info-subtle text-info rounded-circle mb-2" style="width: 80px; height: 80px;">
              <i class="bi bi-person-fill fs-1"></i>
            </div>
            <h5 class="fw-bold mb-0">{{ $registration->student->full_name }}</h5>
            <div class="font-monospace text-primary fw-semibold">{{ $registration->student->registration_number }}</div>
            <div class="small text-muted">Student ID: {{ $registration->student->student_number }}</div>
          </div>

          <ul class="list-group list-group-flush small">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Programme:</span>
              <span class="fw-semibold text-end">{{ $registration->student->programme->name ?? 'N/A' }} ({{ $registration->student->programme->code ?? 'N/A' }})</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Department:</span>
              <span class="fw-semibold text-end">{{ $registration->student->programme->department->name ?? 'N/A' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Faculty / Campus:</span>
              <span class="fw-semibold text-end">{{ $registration->student->programme->department->faculty->name ?? '' }} &bull; {{ $registration->student->campus->name ?? '' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Curriculum Version:</span>
              <span class="badge bg-body-secondary text-body border">{{ $registration->student->curriculum->version_name ?? 'Standard' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Study Mode / Gender:</span>
              <span class="fw-semibold">{{ $registration->student->study_mode }} &bull; {{ ucfirst($registration->student->gender) }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Enrollment Stage:</span>
              <span class="fw-semibold text-primary">Study Year {{ $registration->study_year }}, Semester {{ $registration->semester_number }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Academic Term:</span>
              <span class="fw-semibold">{{ $registration->semester->name }} ({{ $registration->academicYear->name }})</span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted">Cumulative GPA:</span>
              <span class="fw-bold fs-6 {{ $registration->student->cumulative_gpa >= 3.6 ? 'text-success' : ($registration->student->cumulative_gpa >= 2.0 ? 'text-primary' : 'text-danger') }}">
                {{ number_format($registration->student->cumulative_gpa, 2) }}
              </span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Slip Audit & Status Log -->
      <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header">
          <h3 class="card-title fw-bold">
            <i class="bi bi-shield-check text-secondary me-1"></i> Verification History
          </h3>
        </div>
        <div class="card-body small">
          <div class="mb-2">
            <span class="text-muted d-block">Submitted At:</span>
            <strong>{{ $registration->submitted_at ? $registration->submitted_at->format('d M Y, H:i:s') : 'Not submitted yet' }}</strong>
          </div>
          @if ($registration->approved_at)
            <div class="mb-2">
              <span class="text-muted d-block">Approved At:</span>
              <strong class="text-success">{{ $registration->approved_at->format('d M Y, H:i:s') }}</strong>
              <div class="text-muted">By: {{ $registration->approvedBy->name ?? 'Advisor' }}</div>
            </div>
          @endif
          @if ($registration->advisor_remarks)
            <div class="p-2 border rounded bg-body-secondary mt-3">
              <span class="text-muted fw-bold d-block mb-1">
                <i class="bi bi-chat-left-quote me-1"></i> Advisor Remarks / Feedback:
              </span>
              <span class="fst-italic">&ldquo;{{ $registration->advisor_remarks }}&rdquo;</span>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Right Column: Course Units Inspection -->
    <div class="col-lg-8">
      <!-- Credit Load Summary Widget -->
      <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-body">
          <div class="row align-items-center g-3">
            <div class="col-md-4 text-center border-end">
              <span class="text-muted small text-uppercase fw-semibold d-block">Total Semester Load</span>
              <div class="fs-2 fw-bold {{ $registration->total_credits >= 12.0 && $registration->total_credits <= 24.0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($registration->total_credits, 1) }} <span class="fs-6 text-muted">CU</span>
              </div>
              <span class="badge {{ $registration->total_credits >= 12.0 && $registration->total_credits <= 24.0 ? 'bg-success-subtle text-success border' : 'bg-danger-subtle text-danger border' }}">
                {{ $registration->total_credits >= 12.0 && $registration->total_credits <= 24.0 ? 'Within Limits' : 'Limits Breached' }}
              </span>
            </div>
            <div class="col-md-4 text-center border-end">
              <span class="text-muted small text-uppercase fw-semibold d-block">Load Distribution</span>
              <div class="fw-semibold mt-1">Core: <strong class="text-primary">{{ number_format($coreCredits, 1) }} CU</strong></div>
              <div class="fw-semibold">Electives: <strong class="text-info">{{ number_format($electiveCredits, 1) }} CU</strong></div>
            </div>
            <div class="col-md-4 text-center">
              <span class="text-muted small text-uppercase fw-semibold d-block">Institutional Standards</span>
              <div class="small mt-1 text-muted">Minimum Required: <strong>12.0 CU</strong></div>
              <div class="small text-muted">Maximum Allowed: <strong>24.0 CU</strong></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Mandatory Core Courses -->
      <div class="card card-outline card-primary shadow-sm mb-4">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between w-100">
            <h3 class="card-title fw-bold mb-0">
              <i class="bi bi-lock-fill text-primary me-1"></i> Mandatory Core Courses ({{ $coreItems->count() }})
            </h3>
            <span class="badge bg-primary">{{ number_format($coreCredits, 1) }} Credit Units</span>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-body-secondary small">
                <tr>
                  <th style="width: 40px;" class="text-center">#</th>
                  <th style="width: 110px;">Code</th>
                  <th>Course Title</th>
                  <th>Department</th>
                  <th style="width: 70px;" class="text-center">CU</th>
                  <th style="width: 90px;" class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($coreItems as $index => $item)
                  <tr>
                    <td class="text-center small text-muted">{{ $loop->iteration }}</td>
                    <td class="font-monospace fw-bold text-primary">{{ $item->courseUnit->code ?? 'N/A' }}</td>
                    <td class="fw-semibold">{{ $item->courseUnit->name ?? 'N/A' }}</td>
                    <td class="small text-muted">{{ $item->courseUnit->department->name ?? 'N/A' }}</td>
                    <td class="text-center fw-bold">{{ number_format($item->credit_units, 1) }}</td>
                    <td class="text-center">
                      <span class="badge {{ $item->status_badge_class }}">{{ ucfirst($item->status) }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center py-3 text-muted">No mandatory core courses registered.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Optional Elective Courses -->
      <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between w-100">
            <h3 class="card-title fw-bold mb-0">
              <i class="bi bi-mortarboard text-info me-1"></i> Enrolled Elective Courses ({{ $electiveItems->count() }})
            </h3>
            <span class="badge bg-info">{{ number_format($electiveCredits, 1) }} Credit Units</span>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-body-secondary small">
                <tr>
                  <th style="width: 40px;" class="text-center">#</th>
                  <th style="width: 110px;">Code</th>
                  <th>Course Title</th>
                  <th>Department</th>
                  <th style="width: 70px;" class="text-center">CU</th>
                  <th style="width: 90px;" class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($electiveItems as $index => $item)
                  <tr>
                    <td class="text-center small text-muted">{{ $loop->iteration }}</td>
                    <td class="font-monospace fw-bold text-info">{{ $item->courseUnit->code ?? 'N/A' }}</td>
                    <td class="fw-semibold">
                      {{ $item->courseUnit->name ?? 'N/A' }}
                      @if ($item->courseUnit->prerequisites->isNotEmpty())
                        <div class="small text-muted">
                          <i class="bi bi-link-45deg"></i> Prereq:
                          @foreach ($item->courseUnit->prerequisites as $prereq)
                            <span class="badge text-bg-light border font-monospace">{{ $prereq->code }}</span>
                          @endforeach
                        </div>
                      @endif
                    </td>
                    <td class="small text-muted">{{ $item->courseUnit->department->name ?? 'N/A' }}</td>
                    <td class="text-center fw-bold">{{ number_format($item->credit_units, 1) }}</td>
                    <td class="text-center">
                      <span class="badge {{ $item->status_badge_class }}">{{ ucfirst($item->status) }}</span>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center py-3 text-muted">No elective courses selected for this registration.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Dropped Courses Audit Trail (if any) -->
      @if ($droppedItems->isNotEmpty())
        <div class="card card-outline card-secondary shadow-sm mb-4">
          <div class="card-header">
            <h3 class="card-title fw-bold text-muted">
              <i class="bi bi-clock-history me-1"></i> Dropped Courses Audit Log ({{ $droppedItems->count() }})
            </h3>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-body-secondary">
                  <tr>
                    <th style="width: 40px;" class="text-center">#</th>
                    <th>Course Code & Title</th>
                    <th style="width: 80px;" class="text-center">CU</th>
                    <th style="width: 150px;">Dropped At</th>
                    <th>Reason / Justification</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($droppedItems as $dropped)
                    <tr class="text-muted">
                      <td class="text-center">{{ $loop->iteration }}</td>
                      <td>
                        <span class="font-monospace fw-bold text-decoration-line-through text-danger me-1">
                          {{ $dropped->courseUnit->code ?? 'N/A' }}
                        </span>
                        <span class="text-decoration-line-through">{{ $dropped->courseUnit->name ?? 'N/A' }}</span>
                      </td>
                      <td class="text-center fw-bold">{{ number_format($dropped->credit_units, 1) }}</td>
                      <td>{{ $dropped->dropped_at ? $dropped->dropped_at->format('d M Y, H:i') : 'N/A' }}</td>
                      <td class="fst-italic">&ldquo;{{ $dropped->drop_reason ?? 'None' }}&rdquo;</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @endif

    </div>
  </div>

  {{-- Approve Slip Modal --}}
  <div class="modal fade" id="approveSlipModal" tabindex="-1" aria-labelledby="approveSlipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('approval.approve', $registration) }}">
          @csrf
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title fw-bold" id="approveSlipModalLabel">
              <i class="bi bi-check-circle-fill me-2"></i> Approve Course Registration
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>
              Confirm approval of the course registration slip for <strong>{{ $registration->student->full_name }}</strong>.
            </p>
            <div class="alert alert-secondary p-2 small">
              <div>Total Load: <strong>{{ number_format($registration->total_credits, 1) }} CU</strong> ({{ $registration->items->where('status', '!=', 'dropped')->count() }} Course Units)</div>
              <div>Stage: <strong>Study Year {{ $registration->study_year }}, Semester {{ $registration->semester_number }}</strong></div>
            </div>
            <div class="mb-3">
              <label for="modal_advisor_remarks" class="form-label small fw-semibold text-muted">Advisor Remarks (Optional)</label>
              <textarea name="advisor_remarks" id="modal_advisor_remarks" rows="2" class="form-control" placeholder="Registration verified and approved.">{{ $registration->advisor_remarks }}</textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success fw-semibold">
              <i class="bi bi-check-lg me-1"></i> Confirm & Approve Slip
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Reject Slip Modal --}}
  <div class="modal fade" id="rejectSlipModal" tabindex="-1" aria-labelledby="rejectSlipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('approval.reject', $registration) }}">
          @csrf
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title fw-bold" id="rejectSlipModalLabel">
              <i class="bi bi-exclamation-octagon-fill me-2"></i> Request Changes / Reject Registration
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small mb-2">
              Rejecting registration for: <strong class="text-danger">{{ $registration->student->full_name }}</strong>
            </p>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Quick Reason Presets</label>
              <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm show-preset-btn" data-text="Credit underload: Selected credits fall below the mandatory 12.0 CU minimum. Please add the required elective courses.">Credit Underload</button>
                <button type="button" class="btn btn-outline-secondary btn-sm show-preset-btn" data-text="Missing Core Course: Mandatory core curriculum courses are missing from your course selection.">Missing Core</button>
                <button type="button" class="btn btn-outline-secondary btn-sm show-preset-btn" data-text="Prerequisite Requirement Not Met: Selected course requires prior passed unit. Please adjust elective selection.">Prerequisite Issue</button>
                <button type="button" class="btn btn-outline-secondary btn-sm show-preset-btn" data-text="Credit overload: Selected credits exceed maximum allowed 24.0 CU limit. Please drop an elective.">Credit Overload</button>
              </div>
            </div>

            <div class="mb-3">
              <label for="show_advisor_remarks" class="form-label fw-semibold">
                Corrective Feedback Remarks <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" 
                        id="show_advisor_remarks" 
                        name="advisor_remarks" 
                        rows="3" 
                        minlength="10" 
                        maxlength="1000" 
                        required 
                        placeholder="Provide clear instructions on which courses must be added, dropped, or rectified (min 10 characters)..."></textarea>
              <div class="form-text text-muted small">
                This message will be prominently displayed on the student's portal with an "Edit Registration" prompt.
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger fw-semibold">
              <i class="bi bi-x-circle me-1"></i> Send Feedback & Reject
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
      const presetButtons = document.querySelectorAll('.show-preset-btn');
      const remarksTextarea = document.getElementById('show_advisor_remarks');

      presetButtons.forEach(btn => {
        btn.addEventListener('click', function () {
          remarksTextarea.value = this.getAttribute('data-text');
        });
      });
    });
  </script>
@endpush
