@extends('layouts.app')

@section('title', 'Moderate Sheet: ' . $sheet->courseUnit->code . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Assessment Moderation Workspace')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('assessment.list') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item"><a href="{{ route('moderation.list') }}">HoD Moderation Desk</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $sheet->courseUnit->code }}</li>
@endsection

@section('content')
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
      <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Action Validation Failed:</h6>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Header Card & Governance Action Bar -->
  <div class="card card-outline card-primary mb-4 shadow-sm">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-primary fs-6 font-monospace">{{ $sheet->courseUnit->code }}</span>
            <span class="badge {{ $sheet->status_badge_class }} fs-6">{{ $sheet->status_label }}</span>
          </div>
          <h4 class="mb-0 mt-2 fw-bold text-body">{{ $sheet->courseUnit->name }}</h4>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
          {{-- HoD & Senate Actions --}}
          @if ($sheet->status === 'submitted_to_hod')
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Return for Revision
            </button>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#endorseModal">
              <i class="bi bi-shield-check me-1"></i> Endorse &amp; Submit to Senate
            </button>
          @elseif ($sheet->status === 'department_moderated')
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Return for Revision
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#publishModal">
              <i class="bi bi-patch-check-fill me-1"></i> Senate Approve &amp; Publish
            </button>
          @elseif ($sheet->status === 'returned_for_revision')
            <a href="{{ route('assessment.edit', $sheet) }}" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-pencil-square me-1"></i> Enter / Edit Marks
            </a>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#endorseModal">
              <i class="bi bi-shield-check me-1"></i> Endorse Mark Sheet
            </button>
          @elseif ($sheet->status === 'published')
            <span class="badge text-bg-success px-3 py-2 fs-7">
              <i class="bi bi-lock-fill me-1"></i> Officially Published &amp; Locked
            </span>
          @endif

          <a href="{{ route('moderation.list') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Moderation Desk
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Academic Session</div>
          <div class="fw-semibold">{{ $sheet->semester->name }} ({{ $sheet->academicYear->name }})</div>
          <small class="text-muted">{{ $sheet->courseUnit->credit_units }} Credit Units</small>
        </div>
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Department</div>
          <div class="fw-semibold">{{ $sheet->courseUnit->department->name ?? 'General' }}</div>
          <small class="text-muted">Code: {{ $sheet->courseUnit->department->code ?? 'N/A' }}</small>
        </div>
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Instructor / Lecturer</div>
          <div class="fw-semibold">{{ $sheet->instructor->name ?? 'Unassigned' }}</div>
          <small class="text-muted">{{ $sheet->instructor->email ?? 'No email recorded' }}</small>
        </div>
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Grading Weighting &amp; Pass Mark</div>
          <div class="d-flex gap-1 mt-1">
            <span class="badge text-bg-info">CA: {{ number_format($sheet->ca_weight, 0) }}%</span>
            <span class="badge text-bg-primary">Exam: {{ number_format($sheet->exam_weight, 0) }}%</span>
            <span class="badge text-bg-success">Pass: {{ number_format($sheet->pass_mark, 0) }}%</span>
          </div>
        </div>
      </div>

      {{-- Governance Timeline Info --}}
      <hr class="my-3 text-secondary opacity-25">
      <div class="row g-3 align-items-center small">
        <div class="col-12 col-md-4">
          <span class="text-muted">Submitted to HoD:</span>
          <strong>{{ $sheet->submitted_at ? $sheet->submitted_at->format('M d, Y H:i') : 'Pending Submission' }}</strong>
        </div>
        <div class="col-12 col-md-4">
          <span class="text-muted">Department Moderated:</span>
          <strong>
            @if ($sheet->moderated_at)
              {{ $sheet->moderated_at->format('M d, Y H:i') }}
              @if ($sheet->moderatedBy)
                <span class="text-body-secondary">by {{ $sheet->moderatedBy->name }}</span>
              @endif
            @else
              Not Yet Moderated
            @endif
          </strong>
        </div>
        <div class="col-12 col-md-4">
          <span class="text-muted">Senate Published:</span>
          <strong>
            @if ($sheet->published_at)
              {{ $sheet->published_at->format('M d, Y H:i') }}
              @if ($sheet->publishedBy)
                <span class="text-body-secondary">by {{ $sheet->publishedBy->name }}</span>
              @endif
            @else
              Pending Senate Sign-off
            @endif
          </strong>
        </div>
      </div>
    </div>
  </div>

  {{-- Moderation Remarks Callout --}}
  @if ($sheet->moderation_remarks)
    <div class="callout {{ $sheet->status === 'returned_for_revision' ? 'callout-danger' : 'callout-info' }} shadow-sm mb-4">
      <h6 class="fw-bold mb-1">
        <i class="bi {{ $sheet->status === 'returned_for_revision' ? 'bi-exclamation-octagon-fill text-danger' : 'bi-chat-quote-fill text-info' }} me-2"></i>
        Moderation Remarks &amp; Notes
        @if ($sheet->moderated_at)
          <span class="small text-muted fw-normal">({{ $sheet->moderated_at->diffForHumans() }})</span>
        @endif
      </h6>
      <p class="mb-0 text-body">{{ $sheet->moderation_remarks }}</p>
    </div>
  @endif

  <!-- Class Statistical Analytics KPI Row -->
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-graph-up"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Class Mean &amp; Variance</span>
          <span class="info-box-number fs-4">{{ number_format($statistics['average_score'], 1) }}%</span>
          <span class="progress-description text-muted small">Std Dev: &plusmn;{{ number_format($statistics['standard_deviation'], 2) }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon {{ $statistics['pass_rate'] >= 75.0 ? 'text-bg-success' : ($statistics['pass_rate'] >= 50.0 ? 'text-bg-warning' : 'text-bg-danger') }} shadow-sm">
          <i class="bi bi-check-circle-fill"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Pass Rate</span>
          <span class="info-box-number fs-4">{{ number_format($statistics['pass_rate'], 1) }}%</span>
          <span class="progress-description text-muted small">{{ $statistics['pass_count'] }} of {{ $statistics['graded_count'] }} passed</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon {{ $statistics['fail_rate'] > 20.0 ? 'text-bg-danger' : 'text-bg-secondary' }} shadow-sm">
          <i class="bi bi-x-circle-fill"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Failure Rate</span>
          <span class="info-box-number fs-4">{{ number_format($statistics['fail_rate'], 1) }}%</span>
          <span class="progress-description text-muted small">{{ $statistics['fail_count'] }} candidate(s) to retake</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-arrows-expand"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Score Range</span>
          <span class="info-box-number fs-4">{{ number_format($statistics['highest_score'], 1) }}%</span>
          <span class="progress-description text-muted small">Lowest: {{ number_format($statistics['lowest_score'], 1) }}% ({{ $statistics['graded_count'] }} graded)</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Anomaly Detection Panel & Grade Distribution Curve -->
  <div class="row g-4 mb-4">
    <!-- Left Column: Anomaly Detection Engine -->
    <div class="col-12 col-lg-5">
      <div class="card card-outline {{ count($statistics['anomalies']) > 0 ? 'card-warning' : 'card-success' }} shadow-sm h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">
            <i class="bi bi-shield-exclamation me-1"></i> Academic Moderation Anomaly Detector
          </h5>
        </div>
        <div class="card-body">
          @if (count($statistics['anomalies']) > 0)
            <div class="d-flex flex-column gap-3">
              @foreach ($statistics['anomalies'] as $anomaly)
                <div class="alert alert-{{ $anomaly['level'] }} d-flex align-items-start gap-2 mb-0 shadow-sm" role="alert">
                  <i class="bi {{ $anomaly['icon'] }} fs-5 mt-1 flex-shrink-0"></i>
                  <div>
                    <strong class="d-block">{{ $anomaly['title'] }}</strong>
                    <div class="small">{{ $anomaly['description'] }}</div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="alert alert-success d-flex align-items-center gap-2 mb-0 shadow-sm" role="alert">
              <i class="bi bi-check-circle-fill fs-4 flex-shrink-0"></i>
              <div>
                <strong class="d-block">Statutory Moderation Standards Met</strong>
                <span class="small">The class score curve complies with standard institutional parameters. Variance, failure rates, and top grades are within normal academic bounds.</span>
              </div>
            </div>
          @endif

          <div class="border-top pt-3 mt-4">
            <div class="small text-muted fw-semibold mb-2">Institutional Moderation Guidance:</div>
            <ul class="small text-body-secondary mb-0 ps-3">
              <li>Failure rates exceeding <strong>20.0%</strong> trigger automatic HoD review for question difficulty or syllabus misalignment.</li>
              <li>Standard deviations under <strong>3.5</strong> indicate abnormally high mark clustering.</li>
              <li>Endorsing this sheet certifies departmental acceptance before Senate gazetting.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Grade Distribution Histogram -->
    <div class="col-12 col-lg-7">
      <div class="card card-outline card-info shadow-sm h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">
            <i class="bi bi-bar-chart-fill me-1"></i> Class Grade Distribution Histogram
          </h5>
          <span class="badge text-bg-light border">{{ $statistics['graded_count'] }} Graded Candidates</span>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 15%;">Grade Tier</th>
                  <th style="width: 20%;">Score Range</th>
                  <th style="width: 15%;" class="text-center">Count</th>
                  <th style="width: 15%;" class="text-center">Share</th>
                  <th style="width: 35%;">Distribution Visual</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($statistics['grade_distribution'] as $letter => $tier)
                  <tr>
                    <td>
                      <span class="badge {{ $tier['badge_class'] }} font-monospace fs-7">{{ $letter }}</span>
                      <small class="text-muted d-block">{{ number_format($tier['grade_point'], 1) }} GP</small>
                    </td>
                    <td class="font-monospace small">
                      {{ number_format($tier['min_score'], 0) }}% - {{ number_format($tier['max_score'], 0) }}%
                    </td>
                    <td class="text-center fw-bold">{{ $tier['count'] }}</td>
                    <td class="text-center small">{{ number_format($tier['percentage'], 1) }}%</td>
                    <td>
                      <div class="progress" style="height: 18px;" role="progressbar" aria-valuenow="{{ $tier['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar {{ $letter === 'F' ? 'bg-danger' : ($tier['percentage'] > 0 ? 'bg-primary' : 'bg-secondary') }}"
                             style="width: {{ $tier['percentage'] }}%;">
                          @if ($tier['percentage'] >= 10)
                            {{ number_format($tier['percentage'], 1) }}%
                          @endif
                        </div>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Student Marks Roster Card -->
  <div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="card-title mb-0">
        <i class="bi bi-people-fill me-1"></i> Candidate Assessment Roster
      </h5>
      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm" style="width: 15rem;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" id="roster-filter" class="form-control" placeholder="Search student name, reg no..." autocomplete="off">
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" id="roster-export-csv" class="btn btn-sm btn-outline-secondary" title="Export CSV">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" id="roster-print" class="btn btn-sm btn-outline-secondary" title="Print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0" id="roster-table">
          <thead class="table-light">
            <tr>
              <th tabulator-formatter="plaintext" width="50" hozAlign="center">#</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Student Details</th>
              <th tabulator-formatter="html">Programme</th>
              <th tabulator-formatter="html" hozAlign="center">CA ({{ number_format($sheet->ca_weight, 0) }}%)</th>
              <th tabulator-formatter="html" hozAlign="center">Exam ({{ number_format($sheet->exam_weight, 0) }}%)</th>
              <th tabulator-formatter="html" hozAlign="center">Final (100%)</th>
              <th tabulator-formatter="html" hozAlign="center">Grade &amp; GP</th>
              <th tabulator-formatter="html" hozAlign="center">Result</th>
            </tr>
          </thead>
        <tbody>
          @forelse ($sheet->studentMarks as $index => $mark)
            <tr>
              <td class="text-center text-muted small">{{ $index + 1 }}</td>
              <td>
                <div class="fw-semibold text-body">{{ $mark->student->user->name ?? $mark->student->registration_number }}</div>
                <div class="font-monospace small text-muted">{{ $mark->student->registration_number }}</div>
              </td>
              <td>
                <div class="small text-body">{{ $mark->student->programme->name ?? 'General Programme' }}</div>
                <small class="text-muted">{{ $mark->student->programme->code ?? '' }}</small>
              </td>
              <td class="text-center font-monospace">
                @if ($mark->ca_score !== null)
                  {{ number_format($mark->ca_score, 1) }}
                @else
                  <span class="text-muted fst-italic">-</span>
                @endif
              </td>
              <td class="text-center font-monospace">
                @if ($mark->exam_score !== null)
                  {{ number_format($mark->exam_score, 1) }}
                @else
                  <span class="text-muted fst-italic">-</span>
                @endif
              </td>
              <td class="text-center font-monospace fw-bold">
                @if ($mark->final_score !== null)
                  {{ number_format($mark->final_score, 1) }}
                @else
                  <span class="text-muted fst-italic">-</span>
                @endif
              </td>
              <td class="text-center">
                @if ($mark->grade_letter)
                  <span class="badge {{ $mark->grade_badge_class }} font-monospace fs-7">
                    {{ $mark->grade_letter }}
                  </span>
                  <small class="text-muted d-block">{{ number_format($mark->grade_point, 1) }} GP</small>
                @else
                  <span class="badge text-bg-light border text-muted">Ungraded</span>
                @endif
              </td>
              <td class="text-center">
                @if ($mark->final_score !== null)
                  @if ($mark->is_passed)
                    <span class="badge text-bg-success"><i class="bi bi-check me-1"></i> Pass</span>
                  @else
                    <span class="badge text-bg-danger"><i class="bi bi-x me-1"></i> Retake</span>
                  @endif
                @else
                  <span class="badge text-bg-secondary">Pending</span>
                @endif

                @if ($mark->auditLogs->isNotEmpty())
                  <span class="badge text-bg-warning ms-1" data-bs-toggle="tooltip" title="{{ $mark->auditLogs->count() }} score modification(s) recorded">
                    <i class="bi bi-clock-history"></i>
                  </span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                No students enrolled in this course unit for this semester session.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- Audit Trail History Table (if modifications exist) -->
  @php
    $allAudits = $sheet->studentMarks->flatMap->auditLogs->sortByDesc('created_at');
  @endphp
  @if ($allAudits->isNotEmpty())
    <div class="card card-outline card-secondary shadow-sm mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i class="bi bi-clock-history me-1"></i> Examination Score Revision Audit Trail
        </h5>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 15%;">Timestamp</th>
              <th style="width: 25%;">Student</th>
              <th style="width: 12%;">Component</th>
              <th style="width: 18%;">Score Adjustment</th>
              <th style="width: 15%;">Revised By</th>
              <th style="width: 15%;">Justification / Reason</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($allAudits as $log)
              <tr>
                <td class="small font-monospace">{{ $log->created_at->format('M d, Y H:i') }}</td>
                <td>
                  <strong>{{ $log->studentMark->student->user->name ?? $log->studentMark->student->registration_number }}</strong>
                  <div class="small font-monospace text-muted">{{ $log->studentMark->student->registration_number }}</div>
                </td>
                <td>
                  <span class="badge text-bg-secondary text-uppercase">{{ $log->score_type }} Score</span>
                </td>
                <td class="font-monospace">
                  <span class="text-danger text-decoration-line-through">{{ $log->old_score !== null ? number_format($log->old_score, 1) : 'None' }}</span>
                  &rarr;
                  <span class="text-success fw-bold">{{ number_format($log->new_score, 1) }}</span>
                </td>
                <td>{{ $log->changedBy->name ?? 'System' }}</td>
                <td class="small text-muted">{{ $log->reason }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  <!-- Action Modal: Endorse Mark Sheet -->
  <div class="modal fade" id="endorseModal" tabindex="-1" aria-labelledby="endorseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('moderation.endorse', $sheet) }}" method="POST">
          @csrf
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="endorseModalLabel">
              <i class="bi bi-shield-check me-2"></i> Endorse Assessment Mark Sheet
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info small mb-3">
              <i class="bi bi-info-circle-fill me-1"></i>
              You are endorsing the marks for <strong>{{ $sheet->courseUnit->code }} - {{ $sheet->courseUnit->name }}</strong>.
              This confirms that departmental moderation has been completed and recommends this sheet for final Senate publication.
            </div>

            <div class="mb-3">
              <label for="endorse_remarks" class="form-label fw-semibold">Departmental Endorsement Remarks (Optional)</label>
              <textarea name="remarks" id="endorse_remarks" rows="3" class="form-control" placeholder="Enter any departmental notes, moderation findings, or recommendations for Senate..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-shield-check me-1"></i> Confirm &amp; Endorse Sheet
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Action Modal: Return to Lecturer for Revision -->
  <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('moderation.return', $sheet) }}" method="POST">
          @csrf
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="returnModalLabel">
              <i class="bi bi-arrow-counterclockwise me-2"></i> Return Mark Sheet to Lecturer
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-warning small mb-3">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              Returning this mark sheet unlocks it for <strong>{{ $sheet->instructor->name ?? 'the instructor' }}</strong> to edit marks.
              A mandatory revision instruction note is required so the lecturer knows what changes are requested.
            </div>

            <div class="mb-3">
              <label for="return_remarks" class="form-label fw-semibold">Moderation Revision Instructions <span class="text-danger">*</span></label>
              <textarea name="remarks" id="return_remarks" rows="4" class="form-control @error('remarks') is-invalid @enderror" required minlength="10" maxlength="1000" placeholder="Detail the required adjustments (e.g., re-examine question 3 scoring, investigate high failure rate, reconcile coursework totals)...">{{ old('remarks') }}</textarea>
              <div class="form-text small">Minimum 10 characters required. Maximum 1000 characters.</div>
              @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Return for Revision
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Action Modal: Senate Approve & Publish -->
  <div class="modal fade" id="publishModal" tabindex="-1" aria-labelledby="publishModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('moderation.publish', $sheet) }}" method="POST">
          @csrf
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="publishModalLabel">
              <i class="bi bi-patch-check-fill me-2"></i> Official Senate Approval &amp; Publication
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-success small mb-3">
              <i class="bi bi-info-circle-fill me-1"></i>
              You are about to officially publish the results for <strong>{{ $sheet->courseUnit->code }} ({{ $sheet->semester->name }})</strong>.
            </div>

            <div class="p-3 bg-body-secondary rounded border mb-3">
              <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-cpu-fill me-1"></i> Automated Post-Publication Actions:</h6>
              <ul class="small mb-0 ps-3 text-body">
                <li>All mark entries will be permanently locked against unauthorized modifications.</li>
                <li><strong>Automated GPA &amp; CGPA Engine</strong> will immediately recalculate Semester GPA, Cumulative CGPA, Earned Credit Units, and Academic Standing (Normal Progress / Probation) for all <strong>{{ $statistics['enrolled_count'] }}</strong> enrolled students.</li>
                <li>Results will become visible on official student performance transcripts.</li>
              </ul>
            </div>

            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="confirm_publish" required>
              <label class="form-check-label small fw-semibold" for="confirm_publish">
                I confirm that departmental moderation has been validated and this mark sheet is authorized for official university publication.
              </label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-patch-check-fill me-1"></i> Officially Publish Results
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
      initAdminLteDataTable('#roster-table', {
        filterInput: '#roster-filter',
        btnCsv: '#roster-export-csv',
        btnPrint: '#roster-print',
        filename: 'course_roster_{{ $sheet->courseUnit->code }}_export',
      });
    });
  </script>
@endpush
