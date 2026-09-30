@extends('layouts.app')

@section('title', 'HoD Moderation Desk - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Head of Department Moderation Desk')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.assessments.index') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item active" aria-current="page">HoD Moderation Desk</li>
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

  <!-- Governance KPI Row -->
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-hourglass-split"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Pending HoD Review</span>
          <span class="info-box-number fs-4">{{ $stats['pending_hod'] }}</span>
          <span class="progress-description text-muted small">Sheets submitted by lecturers</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-shield-check"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Department Moderated</span>
          <span class="info-box-number fs-4">{{ $stats['moderated'] }}</span>
          <span class="progress-description text-muted small">Awaiting Senate sign-off</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-patch-check-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Senate Published</span>
          <span class="info-box-number fs-4">{{ $stats['published'] }}</span>
          <span class="progress-description text-muted small">Locked &amp; GPAs calculated</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-arrow-counterclockwise"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Returned for Revision</span>
          <span class="info-box-number fs-4">{{ $stats['returned'] }}</span>
          <span class="progress-description text-muted small">With lecturer revision notes</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter & Quick Status Filter Card -->
  <div class="card card-outline card-secondary mb-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h3 class="card-title mb-0">
        <i class="bi bi-funnel me-1"></i> Filter Moderation Mark Sheets
      </h3>
      <div class="btn-group btn-group-sm" role="group">
        <a href="{{ route('academic.assessments.moderation.index', array_merge(request()->except('status'), [])) }}"
           class="btn {{ empty($status) ? 'btn-secondary' : 'btn-outline-secondary' }}">
          All ({{ $stats['total'] }})
        </a>
        <a href="{{ route('academic.assessments.moderation.index', array_merge(request()->except('status'), ['status' => 'submitted_to_hod'])) }}"
           class="btn {{ $status === 'submitted_to_hod' ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning' }}">
          Pending Review ({{ $stats['pending_hod'] }})
        </a>
        <a href="{{ route('academic.assessments.moderation.index', array_merge(request()->except('status'), ['status' => 'department_moderated'])) }}"
           class="btn {{ $status === 'department_moderated' ? 'btn-info text-white fw-bold' : 'btn-outline-info' }}">
          Moderated ({{ $stats['moderated'] }})
        </a>
        <a href="{{ route('academic.assessments.moderation.index', array_merge(request()->except('status'), ['status' => 'published'])) }}"
           class="btn {{ $status === 'published' ? 'btn-success fw-bold' : 'btn-outline-success' }}">
          Published ({{ $stats['published'] }})
        </a>
        <a href="{{ route('academic.assessments.moderation.index', array_merge(request()->except('status'), ['status' => 'returned_for_revision'])) }}"
           class="btn {{ $status === 'returned_for_revision' ? 'btn-danger fw-bold' : 'btn-outline-danger' }}">
          Returned ({{ $stats['returned'] }})
        </a>
      </div>
    </div>
    <div class="card-body">
      <form action="{{ route('academic.assessments.moderation.index') }}" method="GET" class="row g-3">
        @if ($status)
          <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <div class="col-12 col-md-4">
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

        <div class="col-12 col-md-4">
          <label for="semester_id" class="form-label small fw-semibold">Semester</label>
          <select name="semester_id" id="semester_id" class="form-select form-select-sm">
            <option value="">-- All Semesters --</option>
            @foreach ($semesters as $sem)
              <option value="{{ $sem->id }}" {{ (string) $semesterId === (string) $sem->id ? 'selected' : '' }}>
                {{ $sem->name }} ({{ $sem->academicYear->name ?? 'Year' }}) {{ $sem->is_active ? '(Active)' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-4">
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

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('academic.assessments.moderation.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
          </a>
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-funnel-fill me-1"></i> Apply Filter
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Moderation Sheets Table Card -->
  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h3 class="card-title mb-0">
        <i class="bi bi-shield-check me-1"></i> Assessment Sheets For Moderation
      </h3>
      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm" style="width: 15rem;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" id="sheets-filter" class="form-control" placeholder="Search course, code..." autocomplete="off">
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="sheets-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="sheets-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0" id="sheets-table">
          <thead class="table-light">
            <tr>
              <th tabulator-formatter="plaintext" width="50">#</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Course Unit &amp; Department</th>
              <th tabulator-formatter="html">Academic Term</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Instructor / Lecturer</th>
              <th tabulator-formatter="html" hozAlign="center">Assessment Roster</th>
              <th tabulator-formatter="html" hozAlign="center">Class Performance</th>
              <th tabulator-formatter="html">Status</th>
              <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="130" hozAlign="right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($sheets as $sheet)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <div class="d-flex flex-column">
                    <div class="d-flex align-items-center gap-1">
                      <span class="badge text-bg-primary font-monospace">{{ $sheet->courseUnit->code }}</span>
                      <strong class="text-body">{{ $sheet->courseUnit->name }}</strong>
                    </div>
                    <small class="text-muted mt-1">
                      <i class="bi bi-building me-1"></i> {{ $sheet->courseUnit->department->name ?? 'General Department' }}
                      &bull; {{ $sheet->courseUnit->credit_units }} CU
                    </small>
                  </div>
                </td>
                <td>
                  <div>{{ $sheet->semester->name }}</div>
                  <small class="text-muted">{{ $sheet->academicYear->name }}</small>
                </td>
                <td>
                  @if ($sheet->instructor)
                    <div class="fw-semibold text-body">{{ $sheet->instructor->name }}</div>
                    <small class="text-muted">{{ $sheet->instructor->email }}</small>
                  @else
                    <span class="text-muted fst-italic">Unassigned</span>
                  @endif
                </td>
                <td class="text-center">
                  <span class="badge bg-body-secondary text-body border">
                    {{ $sheet->graded_students_count }} / {{ $sheet->total_students_count }} Graded
                  </span>
                  @if ($sheet->total_students_count > 0)
                    @php
                      $pct = round(($sheet->graded_students_count / $sheet->total_students_count) * 100);
                    @endphp
                    <div class="progress mt-1" style="height: 4px;">
                      <div class="progress-bar {{ $pct === 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $pct }}%;"></div>
                    </div>
                  @endif
                </td>
                <td class="text-center">
                  @if ($sheet->graded_students_count > 0)
                    @php
                      $passRate = $sheet->pass_rate;
                      $avg = $sheet->average_score;
                    @endphp
                    <span class="badge {{ $passRate >= 75.0 ? 'text-bg-success' : ($passRate >= 50.0 ? 'text-bg-warning' : 'text-bg-danger') }} fs-7">
                      {{ number_format($passRate, 1) }}% Pass
                    </span>
                    <small class="d-block text-muted mt-1">Mean: {{ number_format($avg, 1) }}%</small>
                  @else
                    <span class="text-muted small fst-italic">No Marks Yet</span>
                  @endif
                </td>
                <td>
                  <span class="badge {{ $sheet->status_badge_class }}">
                    {{ $sheet->status_label }}
                  </span>
                  @if ($sheet->moderation_remarks && in_array($sheet->status, ['returned_for_revision', 'submitted_to_hod', 'department_moderated']))
                    <i class="bi bi-chat-left-dots text-primary ms-1" data-bs-toggle="tooltip" title="{{ $sheet->moderation_remarks }}"></i>
                  @endif
                </td>
                <td class="text-end text-nowrap">
                  <a href="{{ route('academic.assessments.moderation.show', $sheet) }}" class="btn btn-sm btn-primary" title="Inspect &amp; Moderate Mark Sheet">
                    <i class="bi bi-shield-shaded me-1"></i> Moderate
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary"></i>
                  <div class="fw-semibold">No assessment mark sheets found matching the current moderation criteria.</div>
                  <div class="small">When lecturers submit completed mark sheets, they will appear here for HoD review and endorsement.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      initAdminLteDataTable('#sheets-table', {
        filterInput: '#sheets-filter',
        btnCsv: '#sheets-export-csv',
        btnPrint: '#sheets-print',
        filename: 'hod_moderation_mark_sheets_export',
      });
    });
  </script>
@endpush
