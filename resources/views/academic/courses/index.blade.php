@extends('layouts.app')

@section('title', 'Course Catalog - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Master Course Catalog')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item active" aria-current="page">Course Catalog</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Filter Bar -->
      <div class="card mb-3">
        <div class="card-body py-2">
          <form action="{{ route('academic.courses.index') }}" method="GET" class="row g-2 align-items-center">
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
                    {{ $dept->name }} ({{ $dept->code }}) &bull; {{ $dept->faculty->campus->name ?? '' }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-auto ms-md-3">
              <label for="status_filter" class="col-form-label fw-bold">
                <i class="bi bi-toggle-on me-1"></i> Status:
              </label>
            </div>
            <div class="col-auto">
              <select name="status" id="status_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Statuses --</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="archived" {{ $status === 'archived' ? 'selected' : '' }}>Archived</option>
              </select>
            </div>

            @if ($departmentId || $status)
              <div class="col-auto">
                <a href="{{ route('academic.courses.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x-circle me-1"></i> Clear Filter
                </a>
              </div>
            @endif
          </form>
        </div>
      </div>

      <!-- Main Course Units Card with Tabulator DataTable -->
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Course Units ({{ $courseUnits->count() }})</h3>
          <div class="card-tools d-flex align-items-center gap-2 me-0">
            <div class="input-group input-group-sm" style="width: 16rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="courses-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
            </div>
            <a href="{{ route('academic.courses.create', $departmentId ? ['department_id' => $departmentId] : []) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Course Unit
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="courses-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="courses-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="courses-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="courses-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="50">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Code</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Course Title</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Department</th>
                <th tabulator-formatter="html">Faculty & Campus</th>
                <th tabulator-formatter="html">Credit Units</th>
                <th tabulator-formatter="html">Status</th>
                <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="160" hozAlign="right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($courseUnits as $course)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="badge text-bg-secondary">{{ $course->code }}</span>
                  </td>
                  <td class="fw-bold">
                    <a href="{{ route('academic.courses.show', $course) }}" class="text-decoration-none">
                      {{ $course->name }}
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-body-secondary text-body border">
                      <i class="bi bi-diagram-3 me-1"></i> {{ $course->department->name }}
                    </span>
                  </td>
                  <td>
                    <small class="text-muted d-block">
                      <i class="bi bi-mortarboard me-1"></i> {{ $course->department->faculty->name }}
                    </small>
                    <small class="text-muted">
                      <i class="bi bi-building me-1"></i> {{ $course->department->faculty->campus->name ?? '' }}
                    </small>
                  </td>
                  <td>
                    <span class="badge text-bg-light border fw-bold">
                      {{ number_format($course->credit_units, 1) }} CU
                    </span>
                  </td>
                  <td>
                    @if ($course->status === 'active')
                      <span class="badge text-bg-success">Active</span>
                    @else
                      <span class="badge text-bg-secondary">Archived</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('academic.courses.show', $course) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('academic.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning" title="Edit Course Unit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('academic.courses.destroy', $course) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete course {{ $course->code }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Course Unit">
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
      initAdminLteDataTable('#courses-table', {
        filterInput: '#courses-filter',
        btnCsv: '#courses-export-csv',
        btnJson: '#courses-export-json',
        btnPrint: '#courses-print',
        filename: 'course_catalog_export',
      });
    });
  </script>
@endpush
