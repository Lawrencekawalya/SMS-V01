@extends('layouts.app')

@section('title', 'Academic Advisor & Registrar Course Approvals - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Registration Approvals')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item"><a href="{{ route('academic.registrations.index') }}">Course Registrations</a></li>
  <li class="breadcrumb-item active" aria-current="page">Advisor Approvals</li>
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

  @if (! config('academic.require_registration_approval', false))
    <div class="alert alert-info d-flex align-items-center mb-4 shadow-sm" role="alert">
      <i class="bi bi-info-circle-fill fs-4 me-3 flex-shrink-0"></i>
      <div>
        <div class="fw-bold">Advisor Approval Policy Inactive</div>
        <div class="small">The university is currently operating in direct-registration mode (<code>REQUIRE_REGISTRATION_APPROVAL=false</code>). Course registrations and add/drop adjustments are automatically confirmed upon student submission without requiring manual advisor sign-off.</div>
      </div>
    </div>
  @endif

  {{-- Summary KPI Info-Boxes --}}
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm h-100">
        <span class="info-box-icon bg-warning text-dark"><i class="bi bi-clock-history"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Pending Review</span>
          <span class="info-box-number fs-4">{{ number_format($kpi['total_pending']) }}</span>
          <span class="progress-description text-muted small">Awaiting verification</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm h-100">
        <span class="info-box-icon bg-success"><i class="bi bi-check-all"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Approved This Term</span>
          <span class="info-box-number fs-4">{{ number_format($kpi['approved_term']) }}</span>
          <span class="progress-description text-muted small">Roster finalized</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm h-100">
        <span class="info-box-icon bg-info text-white"><i class="bi bi-arrow-repeat"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Add / Drop Pending</span>
          <span class="info-box-number fs-4">{{ number_format($kpi['add_drop_pending']) }}</span>
          <span class="progress-description text-muted small">Elective load changes</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm h-100">
        <span class="info-box-icon bg-danger"><i class="bi bi-exclamation-octagon"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Rejected Slips</span>
          <span class="info-box-number fs-4">{{ number_format($kpi['rejected_count']) }}</span>
          <span class="progress-description text-muted small">Requiring correction</span>
        </div>
      </div>
    </div>
  </div>

  {{-- Filter Toolbar Card --}}
  <div class="card card-outline card-secondary mb-4 shadow-sm">
    <div class="card-header py-2">
      <h3 class="card-title fs-6 fw-bold mb-0">
        <i class="bi bi-funnel me-1"></i> Filter Submissions
      </h3>
    </div>
    <div class="card-body">
      <form method="GET" action="{{ route('academic.approvals.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Department</label>
          <select name="department_id" class="form-select form-select-sm">
            <option value="">All Academic Departments</option>
            @foreach ($departments as $dept)
              <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                {{ $dept->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label small text-muted mb-1">Academic Programme</label>
          <select name="programme_id" class="form-select form-select-sm">
            <option value="">All Programmes</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ request('programme_id') == $prog->id ? 'selected' : '' }}>
                {{ $prog->code }} - {{ $prog->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Study Year</label>
          <select name="study_year" class="form-select form-select-sm">
            <option value="">All Years</option>
            @foreach ($studyYears as $year)
              <option value="{{ $year }}" {{ request('study_year') == $year ? 'selected' : '' }}>
                Year {{ $year }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Approval Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending Review (All)</option>
            <option value="submitted" {{ $statusFilter === 'submitted' ? 'selected' : '' }}>Submitted (Normal)</option>
            <option value="add_drop_pending" {{ $statusFilter === 'add_drop_pending' ? 'selected' : '' }}>Add / Drop Pending</option>
            <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Records</option>
          </select>
        </div>

        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-fill">
            <i class="bi bi-filter me-1"></i> Apply
          </button>
          <a href="{{ route('academic.approvals.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Approvals Directory Card --}}
  <div class="card card-outline card-primary mb-4 shadow-sm">
    <div class="card-header">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0 fw-bold">
          <i class="bi bi-clipboard-check text-primary me-1"></i> Course Registrations Awaiting Verification ({{ $registrations->count() }})
        </h3>
        <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0">
          <button type="button" class="btn btn-success btn-sm" id="btn-batch-approve" disabled data-bs-toggle="modal" data-bs-target="#batchApproveModal">
            <i class="bi bi-check-circle me-1"></i> Approve Selected (<span id="selected-count">0</span>)
          </button>
          <div class="input-group input-group-sm" style="width: 15rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="approvals-filter" class="form-control" placeholder="Search students, slips..." autocomplete="off">
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      {{-- DataTables / Tabulator Controls --}}
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="approvals-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="approvals-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="approvals-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <form id="batchApproveForm" method="POST" action="{{ route('academic.approvals.batch-approve') }}">
        @csrf
        <table id="approvals-table" class="table table-hover table-striped align-middle mb-0">
          <thead>
            <tr>
              <th width="40" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" class="text-center">
                <input type="checkbox" class="form-check-input" id="select-all-slips">
              </th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Slip ID</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Student</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Programme & Stage</th>
              <th tabulator-formatter="html" hozAlign="center">Credits</th>
              <th tabulator-formatter="html" hozAlign="center">Type</th>
              <th tabulator-formatter="html">Status</th>
              <th tabulator-formatter="html">Submitted Date</th>
              <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="140" hozAlign="right">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($registrations as $reg)
              @php
                $isPending = in_array($reg->status, ['submitted', 'add_drop_pending']);
                $isAddDrop = $reg->status === 'add_drop_pending';
              @endphp
              <tr>
                <td class="text-center">
                  @if ($isPending)
                    <input type="checkbox" name="registration_ids[]" value="{{ $reg->id }}" class="form-check-input slip-checkbox">
                  @else
                    <input type="checkbox" class="form-check-input" disabled>
                  @endif
                </td>
                <td>
                  <a href="{{ route('academic.approvals.show', $reg) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                    #REG-{{ str_pad($reg->id, 5, '0', STR_PAD_LEFT) }}
                  </a>
                </td>
                <td>
                  <div class="fw-bold">{{ $reg->student->full_name }}</div>
                  <small class="text-muted font-monospace">{{ $reg->student->registration_number }}</small>
                </td>
                <td>
                  <span class="badge text-bg-primary">{{ $reg->student->programme->code }}</span>
                  <span class="badge text-bg-secondary ms-1">Year {{ $reg->study_year }}, Sem {{ $reg->semester_number }}</span>
                  <small class="d-block text-muted text-truncate" style="max-width: 170px;">
                    {{ $reg->student->programme->department->name ?? 'Department' }}
                  </small>
                </td>
                <td class="text-center">
                  <span class="fw-bold fs-6 {{ $reg->total_credits >= 12.0 && $reg->total_credits <= 24.0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($reg->total_credits, 1) }} CU
                  </span>
                  <small class="d-block text-muted">{{ $reg->items->where('status', '!=', 'dropped')->count() }} Units</small>
                </td>
                <td class="text-center">
                  @if ($isAddDrop)
                    <span class="badge text-bg-info"><i class="bi bi-arrow-left-right me-1"></i> Add / Drop</span>
                  @else
                    <span class="badge text-bg-light border">Standard</span>
                  @endif
                </td>
                <td>
                  <span class="badge {{ $reg->status_badge_class }}">
                    {{ $reg->status_label }}
                  </span>
                </td>
                <td>
                  <small class="text-muted">
                    {{ $reg->submitted_at ? $reg->submitted_at->format('d M Y, H:i') : $reg->updated_at->format('d M Y') }}
                  </small>
                </td>
                <td class="text-end text-nowrap">
                  <a href="{{ route('academic.approvals.show', $reg) }}" class="btn btn-sm btn-outline-primary" title="Inspect & Verify Slip">
                    <i class="bi bi-search"></i>
                  </a>
                  @if ($isPending)
                    <button type="button" class="btn btn-sm btn-success quick-approve-btn" data-slip-id="{{ $reg->id }}" data-student-name="{{ $reg->student->full_name }}" data-bs-toggle="modal" data-bs-target="#quickApproveModal" title="Quick Approve">
                      <i class="bi bi-check-lg"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger quick-reject-btn" data-slip-id="{{ $reg->id }}" data-student-name="{{ $reg->student->full_name }}" data-reject-url="{{ route('academic.approvals.reject', $reg) }}" data-bs-toggle="modal" data-bs-target="#rejectSlipModal" title="Request Changes / Reject">
                      <i class="bi bi-x-lg"></i>
                    </button>
                  @else
                    <a href="{{ route('academic.registrations.print', $reg) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Slip">
                      <i class="bi bi-printer"></i>
                    </a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </form>
    </div>
  </div>

  {{-- Quick Approve Confirmation Modal --}}
  <div class="modal fade" id="quickApproveModal" tabindex="-1" aria-labelledby="quickApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="quickApproveForm" method="POST" action="">
          @csrf
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title fw-bold" id="quickApproveModalLabel">
              <i class="bi bi-check-circle-fill me-2"></i> Confirm Registration Approval
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>
              Are you sure you want to officially approve the course registration slip for:
            </p>
            <div class="alert alert-secondary p-3">
              <strong id="approveModalStudentName" class="fs-6 text-primary">Student Name</strong>
            </div>
            <div class="mb-3">
              <label for="approve_remarks" class="form-label small fw-semibold text-muted">Advisor Remarks (Optional)</label>
              <textarea name="advisor_remarks" id="approve_remarks" rows="2" class="form-control" placeholder="Registration verified and approved."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success fw-semibold">
              <i class="bi bi-check-lg me-1"></i> Approve Registration
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Batch Approve Confirmation Modal --}}
  <div class="modal fade" id="batchApproveModal" tabindex="-1" aria-labelledby="batchApproveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title fw-bold" id="batchApproveModalLabel">
            <i class="bi bi-check-all me-2"></i> Batch Approve Selected Slips
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>
            You are about to batch approve <strong id="modal-batch-count" class="text-primary">0</strong> selected course registration slip(s).
          </p>
          <div class="alert alert-info small mb-0">
            <i class="bi bi-info-circle me-1"></i> All enrolled course units on these slips will be marked as officially approved.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success fw-semibold" onclick="document.getElementById('batchApproveForm').submit();">
            <i class="bi bi-check-all me-1"></i> Confirm Batch Approval
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Rejection Feedback Modal --}}
  <div class="modal fade" id="rejectSlipModal" tabindex="-1" aria-labelledby="rejectSlipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="rejectSlipForm" method="POST" action="">
          @csrf
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title fw-bold" id="rejectSlipModalLabel">
              <i class="bi bi-exclamation-octagon-fill me-2"></i> Request Changes / Reject Registration
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small mb-2">
              Rejecting registration for: <strong id="rejectModalStudentName" class="text-danger">Student Name</strong>
            </p>

            <div class="mb-3">
              <label class="form-label small fw-semibold">Quick Reason Presets</label>
              <div class="d-flex flex-wrap gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-text="Credit underload: Selected credits fall below the mandatory 12.0 CU minimum. Please add the required elective courses.">Credit Underload</button>
                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-text="Missing Core Course: Mandatory core curriculum courses are missing from your course selection.">Missing Core</button>
                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-text="Prerequisite Requirement Not Met: Selected course requires prior passed unit. Please adjust elective selection.">Prerequisite Issue</button>
                <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" data-text="Credit overload: Selected credits exceed maximum allowed 24.0 CU limit. Please drop an elective.">Credit Overload</button>
              </div>
            </div>

            <div class="mb-3">
              <label for="advisor_remarks" class="form-label fw-semibold">
                Corrective Feedback Remarks <span class="text-danger">*</span>
              </label>
              <textarea class="form-control" 
                        id="advisor_remarks" 
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
      initAdminLteDataTable('#approvals-table', {
        filterInput: '#approvals-filter',
        btnCsv: '#approvals-export-csv',
        btnJson: '#approvals-export-json',
        btnPrint: '#approvals-print',
        filename: 'course_registration_approvals_export',
      });

      // Handle Select All and Batch Selection
      const selectAll = document.getElementById('select-all-slips');
      const checkboxes = document.querySelectorAll('.slip-checkbox');
      const batchBtn = document.getElementById('btn-batch-approve');
      const selectedCountSpan = document.getElementById('selected-count');
      const modalBatchCount = document.getElementById('modal-batch-count');

      function updateBatchButton() {
        const checked = document.querySelectorAll('.slip-checkbox:checked');
        const count = checked.length;
        selectedCountSpan.textContent = count;
        modalBatchCount.textContent = count;
        if (count > 0) {
          batchBtn.removeAttribute('disabled');
        } else {
          batchBtn.setAttribute('disabled', 'disabled');
        }
      }

      if (selectAll) {
        selectAll.addEventListener('change', function () {
          checkboxes.forEach(cb => cb.checked = selectAll.checked);
          updateBatchButton();
        });
      }

      checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBatchButton);
      });

      // Quick Approve modal handler
      const quickApproveButtons = document.querySelectorAll('.quick-approve-btn');
      const quickApproveForm = document.getElementById('quickApproveForm');
      const approveModalStudentName = document.getElementById('approveModalStudentName');

      quickApproveButtons.forEach(btn => {
        btn.addEventListener('click', function () {
          const slipId = this.getAttribute('data-slip-id');
          const studentName = this.getAttribute('data-student-name');
          quickApproveForm.action = '{{ url("academic/approvals") }}/' + slipId + '/approve';
          approveModalStudentName.textContent = studentName + ' (#REG-' + String(slipId).padStart(5, '0') + ')';
        });
      });

      // Quick Reject modal handler
      const quickRejectButtons = document.querySelectorAll('.quick-reject-btn');
      const rejectSlipForm = document.getElementById('rejectSlipForm');
      const rejectModalStudentName = document.getElementById('rejectModalStudentName');
      const remarksTextarea = document.getElementById('advisor_remarks');

      quickRejectButtons.forEach(btn => {
        btn.addEventListener('click', function () {
          const rejectUrl = this.getAttribute('data-reject-url');
          const studentName = this.getAttribute('data-student-name');
          rejectSlipForm.action = rejectUrl;
          rejectModalStudentName.textContent = studentName;
          remarksTextarea.value = '';
        });
      });

      // Preset chips for rejection remarks
      const presetButtons = document.querySelectorAll('.preset-btn');
      presetButtons.forEach(btn => {
        btn.addEventListener('click', function () {
          remarksTextarea.value = this.getAttribute('data-text');
        });
      });
    });
  </script>
@endpush
