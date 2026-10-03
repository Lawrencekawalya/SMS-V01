@extends('layouts.app')

@section('title', 'Mark Sheet: ' . $sheet->courseUnit->code . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Assessment Sheet Details')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('assessment.list') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $sheet->courseUnit->code }}</li>
@endsection

@section('content')
  <!-- Sheet Overview Card -->
  <div class="card card-outline card-primary mb-4 shadow-sm">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <span class="badge text-bg-primary fs-6">{{ $sheet->courseUnit->code }}</span>
          <span class="badge {{ $sheet->status_badge_class }} fs-6 ms-1">{{ $sheet->status_label }}</span>
          <h4 class="mb-0 mt-2 fw-bold">{{ $sheet->courseUnit->name }}</h4>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
          @if (in_array($sheet->status, ['draft', 'returned_for_revision']))
            <a href="{{ route('assessment.edit', $sheet) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-pencil-square me-1"></i> Enter / Edit Marks
            </a>
            <form action="{{ route('assessment.submit', $sheet) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to submit this mark sheet to the Head of Department for moderation?');">
              @csrf
              <button type="submit" class="btn btn-success btn-sm">
                <i class="bi bi-send-check me-1"></i> Submit to HoD
              </button>
            </form>
          @endif
          <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#gradingScaleReferenceModal">
            <i class="bi bi-award me-1"></i> Grading Scale Reference
          </button>
          @if ($sheet->status !== 'draft')
            <a href="{{ route('moderation.show', $sheet) }}" class="btn btn-warning btn-sm text-dark">
              <i class="bi bi-shield-shaded me-1"></i> Moderation Desk
            </a>
          @endif
          <a href="{{ route('assessment.list') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Directory
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Academic Term</div>
          <div class="fw-semibold">{{ $sheet->semester->name }} ({{ $sheet->academicYear->name }})</div>
        </div>
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Department</div>
          <div class="fw-semibold">{{ $sheet->courseUnit->department->name ?? 'General' }}</div>
        </div>
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Instructor / Lecturer</div>
          <div class="fw-semibold">{{ $sheet->instructor->name ?? 'Unassigned' }}</div>
          @if ($sheet->instructor)
            <small class="text-muted">{{ $sheet->instructor->email }}</small>
          @endif
        </div>
        <div class="col-12 col-md-3">
          <div class="text-body-secondary small">Assessment Weighting Policy</div>
          <div class="d-flex gap-1 mt-1">
            <span class="badge text-bg-info">CA: {{ number_format($sheet->ca_weight, 0) }}%</span>
            <span class="badge text-bg-primary">Exam: {{ number_format($sheet->exam_weight, 0) }}%</span>
            <span class="badge text-bg-success">Pass: {{ number_format($sheet->pass_mark, 0) }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KPI Metrics Row -->
  @php
    $totalStudents = $sheet->studentMarks->count();
    $gradedStudents = $sheet->studentMarks->whereNotNull('final_score')->count();
    $passedStudents = $sheet->studentMarks->where('is_passed', true)->count();
    $failedStudents = $sheet->studentMarks->where('is_passed', false)->whereNotNull('final_score')->count();
  @endphp
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-people-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Registered Students</span>
          <span class="info-box-number fs-4">{{ $totalStudents }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-check2-all"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Fully Graded</span>
          <span class="info-box-number fs-4">{{ $gradedStudents }} / {{ $totalStudents }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-graph-up"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Class Pass Rate</span>
          <span class="info-box-number fs-4">{{ number_format($sheet->pass_rate, 1) }}%</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-calculator"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Class Average Score</span>
          <span class="info-box-number fs-4">{{ number_format($sheet->average_score, 1) }}%</span>
        </div>
      </div>
    </div>
  </div>

  @if ($sheet->moderation_remarks)
    <div class="alert alert-info shadow-sm mb-4">
      <div class="d-flex align-items-center">
        <i class="bi bi-chat-quote-fill fs-4 me-3"></i>
        <div>
          <div class="fw-bold">Moderation & Academic Governance Notes</div>
          <div>{{ $sheet->moderation_remarks }}</div>
          @if ($sheet->moderatedBy)
            <small class="text-muted d-block mt-1">Moderated by: {{ $sheet->moderatedBy->name }} on {{ $sheet->moderated_at?->format('d M Y, H:i') }}</small>
          @endif
        </div>
      </div>
    </div>
  @endif

  <!-- Class Marks Roster Table Card -->
  <div class="card card-outline card-secondary shadow-sm">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">
          <i class="bi bi-card-checklist me-1"></i> Student Assessment Marks Roster
        </h3>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-sm" style="width: 15rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="marks-filter" class="form-control" placeholder="Search student..." autocomplete="off">
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="marks-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="marks-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="marks-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <table id="marks-table" class="table table-hover table-striped align-middle mb-0">
        <thead>
          <tr>
            <th tabulator-formatter="plaintext" width="50">#</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Registration No</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Student Name</th>
            <th tabulator-formatter="html">Programme</th>
            <th tabulator-formatter="html" hozAlign="center">CA [/{{ number_format($sheet->ca_weight, 0) }}]</th>
            <th tabulator-formatter="html" hozAlign="center">Exam [/{{ number_format($sheet->exam_weight, 0) }}]</th>
            <th tabulator-formatter="html" hozAlign="center">Total [/100]</th>
            <th tabulator-formatter="html" hozAlign="center">Grade</th>
            <th tabulator-formatter="html" hozAlign="center">GP</th>
            <th tabulator-formatter="html">Result</th>
            <th tabulator-formatter="html">Remarks & Audit</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($sheet->studentMarks as $mark)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <span class="font-monospace fw-bold">{{ $mark->student->registration_number }}</span>
              </td>
              <td>
                <div class="fw-bold">{{ $mark->student->full_name }}</div>
                <small class="text-muted font-monospace">{{ $mark->student->student_number }}</small>
              </td>
              <td>
                <span class="badge text-bg-light border">{{ $mark->student->programme->code ?? '-' }}</span>
              </td>
              <td class="text-center font-monospace">
                {{ $mark->ca_score !== null ? number_format($mark->ca_score, 1) : '-' }}
              </td>
              <td class="text-center font-monospace">
                {{ $mark->exam_score !== null ? number_format($mark->exam_score, 1) : '-' }}
              </td>
              <td class="text-center font-monospace fw-bold">
                {{ $mark->final_score !== null ? number_format($mark->final_score, 1) : '-' }}
              </td>
              <td class="text-center">
                @if ($mark->grade_letter)
                  <span class="badge {{ $mark->grade_badge_class }}">{{ $mark->grade_letter }}</span>
                @else
                  <span class="text-muted small">-</span>
                @endif
              </td>
              <td class="text-center font-monospace">
                {{ $mark->grade_point !== null ? number_format($mark->grade_point, 2) : '-' }}
              </td>
              <td>
                @if ($mark->final_score !== null)
                  @if ($mark->is_passed)
                    <span class="badge text-bg-success">Passed</span>
                  @else
                    <span class="badge text-bg-danger">Failed / Retake</span>
                  @endif
                @else
                  <span class="badge text-bg-secondary">Pending</span>
                @endif
              </td>
              <td>
                <div>{{ $mark->lecturer_remarks ?? '-' }}</div>
                @if ($mark->auditLogs->isNotEmpty())
                  <div class="mt-1">
                    <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning" data-bs-toggle="tooltip" title="{{ $mark->auditLogs->count() }} score modification(s) logged">
                      <i class="bi bi-clock-history me-1"></i> {{ $mark->auditLogs->count() }} Audit Log(s)
                    </span>
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="11" class="text-center py-4 text-muted">
                <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                No student marks recorded yet on this assessment sheet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @include('academic.assessments.partials.grading-scale-modal')
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      initAdminLteDataTable('#marks-table', {
        filterInput: '#marks-filter',
        btnCsv: '#marks-export-csv',
        btnJson: '#marks-export-json',
        btnPrint: '#marks-print',
        filename: 'course_assessment_{{ $sheet->courseUnit->code }}_export',
      });
    });
  </script>
@endpush
