@extends('layouts.app')

@section('title', 'Departments - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Departments')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item active" aria-current="page">Departments</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Filter Bar -->
      <div class="card mb-3">
        <div class="card-body py-2">
          <form action="{{ route('academic.departments.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
              <label for="faculty_filter" class="col-form-label fw-bold">
                <i class="bi bi-funnel me-1"></i> Filter by Faculty:
              </label>
            </div>
            <div class="col-auto">
              <select name="faculty_id" id="faculty_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Faculties --</option>
                @foreach ($faculties as $faculty)
                  <option value="{{ $faculty->id }}" {{ (string) $facultyId === (string) $faculty->id ? 'selected' : '' }}>
                    {{ $faculty->name }} ({{ $faculty->code }}) &bull; {{ $faculty->campus->name ?? '' }}
                  </option>
                @endforeach
              </select>
            </div>
            @if ($facultyId)
              <div class="col-auto">
                <a href="{{ route('academic.departments.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x-circle me-1"></i> Clear Filter
                </a>
              </div>
            @endif
          </form>
        </div>
      </div>

      <!-- Main Department Table -->
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Academic Departments</h3>
          <div class="card-tools d-flex align-items-center gap-2 me-0">
            <div class="input-group input-group-sm" style="width: 16rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="departments-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
            </div>
            <a href="{{ route('academic.departments.create', $facultyId ? ['faculty_id' => $facultyId] : []) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Department
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="departments-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="departments-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="departments-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="departments-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="60">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Department Code</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Department Name</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Faculty</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Head of Department (HOD)</th>
                <th tabulator-formatter="html">Programmes</th>
                <th tabulator-formatter="html">Status</th>
                <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="160" hozAlign="right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($departments as $department)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><span class="badge text-bg-secondary">{{ $department->code }}</span></td>
                  <td class="fw-bold">
                    <a href="{{ route('academic.departments.show', $department) }}" class="text-decoration-none">
                      {{ $department->name }}
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-body-secondary text-body border">
                      <i class="bi bi-mortarboard me-1"></i> {{ $department->faculty->name }}
                    </span>
                  </td>
                  <td>
                    @if ($department->hod)
                      <i class="bi bi-person-badge text-primary me-1"></i> {{ $department->hod->name }}
                    @else
                      <span class="text-muted fst-italic">Not Assigned</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge text-bg-info">
                      {{ $department->programmes_count }} {{ Str::plural('Programme', $department->programmes_count) }}
                    </span>
                  </td>
                  <td>
                    @if ($department->status === 'active')
                      <span class="badge text-bg-success">Active</span>
                    @else
                      <span class="badge text-bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('academic.departments.show', $department) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('academic.departments.edit', $department) }}" class="btn btn-sm btn-outline-warning" title="Edit Department">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('academic.departments.destroy', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this department?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Department" {{ $department->programmes_count > 0 ? 'disabled' : '' }}>
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
      initAdminLteDataTable('#departments-table', {
        filterInput: '#departments-filter',
        btnCsv: '#departments-export-csv',
        btnJson: '#departments-export-json',
        btnPrint: '#departments-print',
        filename: 'departments_export',
      });
    });
  </script>
@endpush
