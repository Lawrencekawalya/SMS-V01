@extends('layouts.app')

@section('title', 'Semester Course Registrations - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Semester Course Registrations')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item active" aria-current="page">Course Registrations</li>
@endsection

@section('content')
  <!-- Metrics KPI Row -->
  @php
    $requireApproval = config('academic.require_registration_approval', false);
    $kpiColClass = $requireApproval ? 'col-12 col-sm-6 col-md-3' : 'col-12 col-sm-6';
  @endphp
  <div class="row mb-4">
    <div class="{{ $kpiColClass }}">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-journal-check"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Registration Slips</span>
          <span class="info-box-number fs-4">{{ $stats['total'] }}</span>
        </div>
      </div>
    </div>
    @if ($requireApproval)
      <div class="{{ $kpiColClass }}">
        <div class="info-box shadow-sm">
          <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-hourglass-split"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Pending Advisor Review</span>
            <span class="info-box-number fs-4">{{ $stats['submitted'] + $stats['add_drop_pending'] }}</span>
          </div>
        </div>
      </div>
    @endif
    <div class="{{ $kpiColClass }}">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">{{ $requireApproval ? 'Approved Enrollments' : 'Confirmed Enrollments' }}</span>
          <span class="info-box-number fs-4">{{ $stats['approved'] }}</span>
        </div>
      </div>
    </div>
    @if ($requireApproval)
      <div class="{{ $kpiColClass }}">
        <div class="info-box shadow-sm">
          <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-x-circle-fill"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Changes Requested / Rejected</span>
            <span class="info-box-number fs-4">{{ $stats['rejected'] }}</span>
          </div>
        </div>
      </div>
    @endif
  </div>

  <!-- Filter Card -->
  <div class="card card-outline card-secondary mb-4">
    <div class="card-header">
      <h3 class="card-title">
        <i class="bi bi-funnel me-1"></i> Filter Course Registrations
      </h3>
    </div>
    <div class="card-body">
      <form action="{{ route('registration.list') }}" method="GET" class="row g-3">
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
          <label for="programme_id" class="form-label small fw-semibold">Programme</label>
          <select name="programme_id" id="programme_id" class="form-select form-select-sm">
            <option value="">-- All Programmes --</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ (string) $programmeId === (string) $prog->id ? 'selected' : '' }}>
                {{ $prog->name }} ({{ $prog->code }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-md-2">
          <label for="status" class="form-label small fw-semibold">Registration Status</label>
          <select name="status" id="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
            @if ($requireApproval)
              <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Submitted (Pending)</option>
              <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
              <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
              <option value="add_drop_pending" {{ $status === 'add_drop_pending' ? 'selected' : '' }}>Add/Drop Review</option>
            @else
              <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Confirmed</option>
            @endif
          </select>
        </div>

        <div class="col-6 col-md-1 d-flex align-items-end gap-1">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Apply Filters">
            <i class="bi bi-search"></i>
          </button>
          <a href="{{ route('registration.list') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Registrations Directory Table Card -->
  <div class="card card-outline card-primary mb-4">
    <div class="card-header">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0">
          <i class="bi bi-journal-text me-1 text-primary"></i> Course Registration Slips ({{ $registrations->count() }})
        </h3>
        <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0">
          <a href="{{ route('registration.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Register Student
          </a>
          @if (config('academic.enforce_prerequisites', false))
            <a href="{{ route('registration.eligibility') }}" class="btn btn-outline-primary btn-sm">
              <i class="bi bi-shield-check me-1"></i> Eligibility Inspector
            </a>
          @endif
          <a href="{{ route('registration.active-session') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-people-fill me-1"></i> Active Session Cohorts
          </a>
          <div class="input-group input-group-sm" style="width: 15rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="registrations-filter" class="form-control" placeholder="Search slips, students..." autocomplete="off">
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="registrations-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="registrations-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="registrations-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <table id="registrations-table" class="table table-hover table-striped align-middle mb-0">
        <thead>
          <tr>
            <th tabulator-formatter="plaintext" width="50">#</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Slip ID</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Student</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Programme & Stage</th>
            <th tabulator-formatter="html">Academic Term</th>
            <th tabulator-formatter="html" hozAlign="center">Courses</th>
            <th tabulator-formatter="html" hozAlign="center">Total Credits</th>
            <th tabulator-formatter="html">Status</th>
            <th tabulator-formatter="html">Submitted / Updated</th>
            <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="90" hozAlign="right">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($registrations as $reg)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <span class="fw-bold font-monospace text-primary">#REG-{{ str_pad($reg->id, 5, '0', STR_PAD_LEFT) }}</span>
              </td>
              <td>
                <div class="fw-bold">{{ $reg->student->full_name }}</div>
                <small class="text-muted font-monospace">{{ $reg->student->registration_number }}</small>
              </td>
              <td>
                <span class="badge text-bg-primary">{{ $reg->student->programme->code }}</span>
                <span class="badge text-bg-secondary ms-1">Year {{ $reg->study_year }}, Sem {{ $reg->semester_number }}</span>
                <small class="d-block text-muted text-truncate" style="max-width: 180px;" title="{{ $reg->student->programme->name }}">
                  {{ $reg->student->programme->name }}
                </small>
              </td>
              <td>
                <div class="fw-semibold">{{ $reg->semester->name }}</div>
                <small class="text-muted">{{ $reg->academicYear->name }}</small>
              </td>
              <td class="text-center">
                <span class="badge bg-body-secondary text-body border">{{ $reg->items->count() }} Units</span>
              </td>
              <td class="text-center">
                <span class="fw-bold fs-6 {{ $reg->total_credits >= 12.0 ? 'text-success' : 'text-danger' }}">
                  {{ number_format($reg->total_credits, 1) }} CU
                </span>
              </td>
              <td>
                <span class="badge {{ $reg->status_badge_class }}">
                  {{ $reg->status_label }}
                </span>
                @if ($reg->status === 'rejected' && $reg->advisor_remarks)
                  <i class="bi bi-info-circle text-danger ms-1" data-bs-toggle="tooltip" title="{{ $reg->advisor_remarks }}"></i>
                @endif
              </td>
              <td>
                <small class="text-muted">
                  {{ $reg->submitted_at ? $reg->submitted_at->format('d M Y, H:i') : $reg->updated_at->format('d M Y') }}
                </small>
              </td>
              <td class="text-end text-nowrap">
                <a href="{{ route('registration.show', $reg) }}" class="btn btn-sm btn-outline-primary" title="View Registration Slip">
                  <i class="bi bi-eye"></i>
                </a>
                @if ($reg->canBeEdited())
                  <a href="{{ route('registration.edit', $reg) }}" class="btn btn-sm btn-outline-warning" title="Edit Draft Slip">
                    <i class="bi bi-pencil"></i>
                  </a>
                @endif
                @if ($reg->canAddDrop())
                  <a href="{{ route('registration.add-drop.edit', $reg) }}" class="btn btn-sm btn-outline-warning" title="Add / Drop Courses">
                    <i class="bi bi-arrow-left-right"></i>
                  </a>
                @endif
                <a href="{{ route('registration.print', $reg) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Official Slip">
                  <i class="bi bi-printer"></i>
                </a>
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
      initAdminLteDataTable('#registrations-table', {
        filterInput: '#registrations-filter',
        btnCsv: '#registrations-export-csv',
        btnJson: '#registrations-export-json',
        btnPrint: '#registrations-print',
        filename: 'course_registrations_directory_export',
      });
    });
  </script>
@endpush
