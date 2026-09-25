@extends('layouts.app')

@section('title', 'Students Directory - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Students Directory')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item active" aria-current="page">Students</li>
@endsection

@section('content')
  <!-- Metrics KPI Row -->
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-md-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-people-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Students</span>
          <span class="info-box-number fs-4">{{ $stats['total'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Active Enrolled</span>
          <span class="info-box-number fs-4">{{ $stats['active'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-mortarboard-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Freshers (Yr 1 Sem 1)</span>
          <span class="info-box-number fs-4">{{ $stats['freshers'] }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-warning shadow-sm"><i class="bi bi-person-lines-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Continuing Students</span>
          <span class="info-box-number fs-4">{{ $stats['continuing'] }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="card card-outline card-secondary mb-4">
    <div class="card-header">
      <h3 class="card-title">
        <i class="bi bi-funnel me-1"></i> Filter Students
      </h3>
    </div>
    <div class="card-body">
      <form action="{{ route('academic.students.index') }}" method="GET" class="row g-3">
        <div class="col-12 col-md-4">
          <label for="programme_id" class="form-label small fw-semibold">Academic Programme</label>
          <select name="programme_id" id="programme_id" class="form-select form-select-sm">
            <option value="">-- All Programmes --</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ (string) $programmeId === (string) $prog->id ? 'selected' : '' }}>
                {{ $prog->name }} ({{ $prog->code }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-6 col-md-3">
          <label for="study_year" class="form-label small fw-semibold">Study Year</label>
          <select name="study_year" id="study_year" class="form-select form-select-sm">
            <option value="">-- All Years --</option>
            <option value="1" {{ (string) $studyYear === '1' ? 'selected' : '' }}>Year 1</option>
            <option value="2" {{ (string) $studyYear === '2' ? 'selected' : '' }}>Year 2</option>
            <option value="3" {{ (string) $studyYear === '3' ? 'selected' : '' }}>Year 3</option>
            <option value="4" {{ (string) $studyYear === '4' ? 'selected' : '' }}>Year 4</option>
          </select>
        </div>

        <div class="col-6 col-md-3">
          <label for="status" class="form-label small fw-semibold">Academic Status</label>
          <select name="status" id="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
            <option value="probation" {{ $status === 'probation' ? 'selected' : '' }}>Probation</option>
            <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended</option>
            <option value="graduated" {{ $status === 'graduated' ? 'selected' : '' }}>Graduated</option>
          </select>
        </div>

        <div class="col-12 col-md-2 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
            <i class="bi bi-search me-1"></i> Filter
          </button>
          <a href="{{ route('academic.students.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Students Directory Table Card -->
  <div class="card card-outline card-primary mb-4">
    <div class="card-header">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0">
          <i class="bi bi-people-fill me-1 text-primary"></i> Students Directory ({{ $students->count() }})
        </h3>
        <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0">
          <div class="input-group input-group-sm" style="width: 15rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="students-filter" class="form-control" placeholder="Search students..." autocomplete="off">
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="students-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="students-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="students-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <table id="students-table" class="table table-hover table-striped align-middle mb-0">
        <thead>
          <tr>
            <th tabulator-formatter="plaintext" width="50">#</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Reg Number</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Student Name</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Programme</th>
            <th tabulator-formatter="html">Curriculum Version</th>
            <th tabulator-formatter="html">Stage</th>
            <th tabulator-formatter="html">Study Mode</th>
            <th tabulator-formatter="html">Status</th>
            <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="100" hozAlign="right">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($students as $student)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>
                <span class="fw-bold font-monospace text-primary">{{ $student->registration_number }}</span>
                <small class="d-block text-muted">{{ $student->student_number }}</small>
              </td>
              <td>
                <div class="fw-bold">{{ $student->full_name }}</div>
                <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $student->email }}</small>
              </td>
              <td>
                <span class="badge text-bg-primary">{{ $student->programme->code }}</span>
                <span class="d-block small text-muted text-truncate" style="max-width: 180px;" title="{{ $student->programme->name }}">
                  {{ $student->programme->name }}
                </span>
              </td>
              <td>
                <span class="badge bg-body-secondary text-body border">
                  {{ $student->curriculum->version_name }}
                </span>
              </td>
              <td>
                <span class="badge text-bg-secondary">{{ $student->academic_stage }}</span>
              </td>
              <td>
                <span class="badge bg-body-secondary text-body border">{{ $student->study_mode }}</span>
              </td>
              <td>
                <span class="badge {{ $student->status_badge_class }}">
                  {{ ucfirst($student->status) }}
                </span>
              </td>
              <td class="text-end">
                <a href="{{ route('academic.students.show', $student) }}" class="btn btn-sm btn-outline-primary" title="View Student Profile">
                  <i class="bi bi-eye"></i>
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
      initAdminLteDataTable('#students-table', {
        filterInput: '#students-filter',
        btnCsv: '#students-export-csv',
        btnJson: '#students-export-json',
        btnPrint: '#students-print',
        filename: 'students_directory_export',
      });
    });
  </script>
@endpush
