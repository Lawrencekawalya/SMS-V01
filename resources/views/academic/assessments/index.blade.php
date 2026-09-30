@extends('layouts.app')

@section('title', 'Course Assessment & Mark Sheets - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Assessment & Mark Sheets')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item">Examinations &amp; Grading</li>
  <li class="breadcrumb-item active" aria-current="page">Course Mark Sheets</li>
@endsection

@section('content')
  <!-- Metrics KPI Row -->
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-journal-bookmark-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Mark Sheets</span>
          <span class="info-box-number fs-4">{{ $stats['total'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-secondary shadow-sm"><i class="bi bi-pencil-square"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">In Draft (Lecturers)</span>
          <span class="info-box-number fs-4">{{ $stats['draft'] + $stats['returned'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-hourglass-split"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Pending HoD Moderation</span>
          <span class="info-box-number fs-4">{{ $stats['submitted'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Senate Published</span>
          <span class="info-box-number fs-4">{{ $stats['published'] }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Institutional Grading Policy Callout -->
  <div class="callout callout-info shadow-sm mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <h5 class="fw-bold mb-1">
          <i class="bi bi-info-circle-fill text-info me-2"></i>Institutional Assessment & Grading Standards
        </h5>
        <p class="mb-0 text-body-secondary small">
          Active university policy:
          <strong>{{ number_format(config('academic.assessment_ca_weight', 40.0), 1) }}% Continuous Assessment (CA)</strong> +
          <strong>{{ number_format(config('academic.assessment_exam_weight', 60.0), 1) }}% Final Exam</strong> = 100.0% Total.
          Minimum pass threshold: <strong>{{ number_format(config('academic.assessment_pass_mark', 50.0), 1) }}%</strong> (NCHE 5.0 GPA scale).
        </p>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="badge text-bg-info">CA: {{ config('academic.assessment_ca_weight', 40) }}%</span>
        <span class="badge text-bg-primary">Exam: {{ config('academic.assessment_exam_weight', 60) }}%</span>
        <span class="badge text-bg-success">Pass Mark: {{ config('academic.assessment_pass_mark', 50) }}%</span>
        <a href="{{ route('academic.assessments.policy') }}" class="btn btn-sm btn-outline-info ms-2">
          <i class="bi bi-sliders me-1"></i> Policy &amp; Scale
        </a>
      </div>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="card card-outline card-secondary mb-4 shadow-sm">
    <div class="card-header">
      <h3 class="card-title">
        <i class="bi bi-funnel me-1"></i> Filter Assessment Mark Sheets
      </h3>
    </div>
    <div class="card-body">
      <form action="{{ route('academic.assessments.index') }}" method="GET" class="row g-3">
        <div class="col-12 col-md-3">
          <label for="academic_year_id" class="form-label small fw-semibold">Academic Year</label>
          <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm">
            <option value="">-- All Academic Years --</option>
            @foreach ($academicYears as $year)
              <option value="{{ $year->id }}" {{ (string) $academicYearId === (string) $year->id ? 'selected' : '' }}>
                {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-3">
          <label for="semester_id" class="form-label small fw-semibold">Semester</label>
          <select name="semester_id" id="semester_id" class="form-select form-select-sm">
            <option value="">-- All Semesters --</option>
            @foreach ($semesters as $sem)
              <option value="{{ $sem->id }}" {{ (string) $semesterId === (string) $sem->id ? 'selected' : '' }}>
                {{ $sem->name }} ({{ $sem->academicYear->name }}) {{ $sem->is_active ? '★ Active' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-3">
          <label for="department_id" class="form-label small fw-semibold">Department</label>
          <select name="department_id" id="department_id" class="form-select form-select-sm">
            <option value="">-- All Departments --</option>
            @foreach ($departments as $dept)
              <option value="{{ $dept->id }}" {{ (string) $departmentId === (string) $dept->id ? 'selected' : '' }}>
                {{ $dept->name }} ({{ $dept->code }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-3">
          <label for="status" class="form-label small fw-semibold">Sheet Status</label>
          <select name="status" id="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft (Lecturer Entry)</option>
            <option value="submitted_to_hod" {{ $status === 'submitted_to_hod' ? 'selected' : '' }}>Pending HoD Moderation</option>
            <option value="department_moderated" {{ $status === 'department_moderated' ? 'selected' : '' }}>Department Moderated</option>
            <option value="published" {{ $status === 'published' ? 'selected' : '' }}>Senate Published</option>
            <option value="returned_for_revision" {{ $status === 'returned_for_revision' ? 'selected' : '' }}>Returned for Revision</option>
          </select>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('academic.assessments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i> Clear Filter
          </a>
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-search me-1"></i> Apply Filter
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Assessment Sheets Table Card -->
  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">
          <i class="bi bi-table me-1"></i> Master Course Mark Sheets Directory
        </h3>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-sm" style="width: 15rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="sheets-filter" class="form-control" placeholder="Search course, code..." autocomplete="off">
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="sheets-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="sheets-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="sheets-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <table id="sheets-table" class="table table-hover table-striped align-middle mb-0">
        <thead>
          <tr>
            <th tabulator-formatter="plaintext" width="50">#</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Sheet ID</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Course Unit</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Department</th>
            <th tabulator-formatter="html">Academic Term</th>
            <th tabulator-formatter="html">Instructor</th>
            <th tabulator-formatter="html" hozAlign="center">Students / Graded</th>
            <th tabulator-formatter="html" hozAlign="center">Class Pass Rate</th>
            <th tabulator-formatter="html">Status</th>
            <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="150" hozAlign="right">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($sheets as $sheet)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <a href="{{ route('academic.assessments.show', $sheet) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                  #AS-{{ str_pad($sheet->id, 5, '0', STR_PAD_LEFT) }}
                </a>
              </td>
              <td>
                <span class="badge text-bg-primary">{{ $sheet->courseUnit->code }}</span>
                <span class="badge text-bg-secondary ms-1">{{ number_format($sheet->courseUnit->credit_units, 1) }} CU</span>
                <a href="{{ route('academic.assessments.show', $sheet) }}" class="fw-bold mt-1 text-decoration-none text-body d-block">
                  {{ $sheet->courseUnit->name }}
                </a>
              </td>
              <td>
                <div class="fw-semibold">{{ $sheet->courseUnit->department->name ?? 'General' }}</div>
                <small class="text-muted">{{ $sheet->courseUnit->department->code ?? '-' }}</small>
              </td>
              <td>
                <div class="fw-semibold">{{ $sheet->semester->name }}</div>
                <small class="text-muted">{{ $sheet->academicYear->name }}</small>
              </td>
              <td>
                @if ($sheet->instructor)
                  <div class="fw-semibold">{{ $sheet->instructor->name }}</div>
                  <small class="text-muted">{{ $sheet->instructor->email }}</small>
                @else
                  <span class="text-muted fst-italic">Unassigned</span>
                @endif
              </td>
              <td class="text-center">
                <span class="badge bg-body-secondary text-body border">
                  {{ $sheet->graded_students_count }} / {{ $sheet->total_students_count }} Graded
                </span>
              </td>
              <td class="text-center">
                @if ($sheet->graded_students_count > 0)
                  <span class="badge {{ $sheet->pass_rate >= 70.0 ? 'text-bg-success' : ($sheet->pass_rate >= 50.0 ? 'text-bg-warning' : 'text-bg-danger') }} fs-7">
                    {{ number_format($sheet->pass_rate, 1) }}%
                  </span>
                  <small class="d-block text-muted">Avg: {{ number_format($sheet->average_score, 1) }}%</small>
                @else
                  <span class="text-muted small fst-italic">Pending</span>
                @endif
              </td>
              <td>
                <span class="badge {{ $sheet->status_badge_class }}">
                  {{ $sheet->status_label }}
                </span>
                @if ($sheet->moderation_remarks && in_array($sheet->status, ['returned_for_revision', 'submitted_to_hod', 'department_moderated']))
                  <i class="bi bi-info-circle text-primary ms-1" data-bs-toggle="tooltip" title="{{ $sheet->moderation_remarks }}"></i>
                @endif
              </td>
              <td class="text-end text-nowrap">
                <div class="d-inline-flex gap-1 justify-content-end align-items-center">
                  @if (in_array($sheet->status, ['draft', 'returned_for_revision']))
                    <a href="{{ route('academic.assessments.edit', $sheet) }}" class="btn btn-sm btn-primary text-nowrap" title="Enter / Edit Marks">
                      <i class="bi bi-pencil-square me-1"></i> Marks
                    </a>
                  @endif
                  @if (in_array($sheet->status, ['submitted_to_hod', 'department_moderated']))
                    <a href="{{ route('academic.assessments.moderation.show', $sheet) }}" class="btn btn-sm btn-warning text-dark text-nowrap" title="HoD Moderation Desk">
                      <i class="bi bi-shield-check me-1"></i> Moderate
                    </a>
                  @endif
                  <a href="{{ route('academic.assessments.show', $sheet) }}" class="btn btn-sm btn-outline-primary" title="View Assessment Sheet">
                    <i class="bi bi-eye"></i>
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      initAdminLteDataTable('#sheets-table', {
        filterInput: '#sheets-filter',
        btnCsv: '#sheets-export-csv',
        btnJson: '#sheets-export-json',
        btnPrint: '#sheets-print',
        filename: 'course_assessment_sheets_export',
      });
    });
  </script>
@endpush
