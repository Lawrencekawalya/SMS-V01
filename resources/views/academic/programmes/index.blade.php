@extends('layouts.app')

@section('title', 'Programmes - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Degree & Diploma Programmes')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item active" aria-current="page">Programmes</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Filter Bar -->
      <div class="card mb-3">
        <div class="card-body py-2">
          <form action="{{ route('academic.programmes.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
              <label for="dept_filter" class="col-form-label fw-bold">
                <i class="bi bi-funnel me-1"></i> Filter by Department:
              </label>
            </div>
            <div class="col-auto">
              <select name="department_id" id="dept_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Departments --</option>
                @foreach ($departments as $dept)
                  <option value="{{ $dept->id }}" {{ (string) $departmentId === (string) $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }} ({{ $dept->code }})
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-auto ms-md-3">
              <label for="award_filter" class="col-form-label fw-bold">
                <i class="bi bi-award me-1"></i> Award Level:
              </label>
            </div>
            <div class="col-auto">
              <select name="award_type" id="award_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Levels --</option>
                @foreach ($awardTypes as $type)
                  <option value="{{ $type }}" {{ $awardType === $type ? 'selected' : '' }}>
                    {{ $type }}
                  </option>
                @endforeach
              </select>
            </div>

            @if ($departmentId || $awardType)
              <div class="col-auto">
                <a href="{{ route('academic.programmes.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x-circle me-1"></i> Clear Filter
                </a>
              </div>
            @endif
          </form>
        </div>
      </div>

      <!-- Main Programmes Table -->
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Academic Programmes</h3>
          <div class="card-tools d-flex align-items-center gap-2 me-0">
            <div class="input-group input-group-sm" style="width: 16rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="programmes-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
            </div>
            <a href="{{ route('academic.programmes.create', $departmentId ? ['department_id' => $departmentId] : []) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Programme
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="programmes-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="programmes-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="programmes-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="programmes-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="50">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Code</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Programme Name</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Department</th>
                <th tabulator-formatter="html">Faculty & Campus</th>
                <th tabulator-formatter="html">Award Level</th>
                <th tabulator-formatter="plaintext">Duration</th>
                <th tabulator-formatter="html">Required Credits</th>
                <th tabulator-formatter="html">Status</th>
                <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="150" hozAlign="right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($programmes as $programme)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><span class="badge text-bg-secondary">{{ $programme->code }}</span></td>
                  <td class="fw-bold">
                    <a href="{{ route('academic.programmes.show', $programme) }}" class="text-decoration-none">
                      {{ $programme->name }}
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-body-secondary text-body border">
                      <i class="bi bi-diagram-3 me-1"></i> {{ $programme->department->name }}
                    </span>
                  </td>
                  <td>
                    <small class="text-muted d-block">
                      <i class="bi bi-mortarboard me-1"></i> {{ $programme->department->faculty->name }}
                    </small>
                    <small class="text-muted">
                      <i class="bi bi-building me-1"></i> {{ $programme->department->faculty->campus->name ?? '' }}
                    </small>
                  </td>
                  <td>
                    @php
                      $awardBadge = match($programme->award_type) {
                        'Doctorate' => 'text-bg-danger',
                        'Masters' => 'text-bg-primary',
                        'Postgraduate Diploma' => 'text-bg-warning',
                        'Bachelors' => 'text-bg-success',
                        'Diploma' => 'text-bg-info',
                        default => 'text-bg-secondary',
                      };
                    @endphp
                    <span class="badge {{ $awardBadge }}">{{ $programme->award_type }}</span>
                  </td>
                  <td>{{ $programme->duration_years }} {{ Str::plural('Year', $programme->duration_years) }} ({{ $programme->duration_semesters }} Sem)</td>
                  <td><span class="badge text-bg-light border">{{ $programme->total_credit_units_required }} CU</span></td>
                  <td>
                    @if ($programme->status === 'active')
                      <span class="badge text-bg-success">Active</span>
                    @else
                      <span class="badge text-bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('academic.programmes.show', $programme) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('academic.programmes.edit', $programme) }}" class="btn btn-sm btn-outline-warning" title="Edit Programme">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('academic.programmes.destroy', $programme) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this programme?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Programme">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      initAdminLteDataTable('#programmes-table', {
        filterInput: '#programmes-filter',
        btnCsv: '#programmes-export-csv',
        btnJson: '#programmes-export-json',
        btnPrint: '#programmes-print',
        filename: 'programmes_export',
      });
    });
  </script>
@endpush
