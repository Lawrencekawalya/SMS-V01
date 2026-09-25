@extends('layouts.app')

@section('title', 'Faculties - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Faculties')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item active" aria-current="page">Faculties</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Filter Bar -->
      <div class="card mb-3">
        <div class="card-body py-2">
          <form action="{{ route('academic.faculties.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
              <label for="campus_filter" class="col-form-label fw-bold"><i class="bi bi-funnel me-1"></i> Filter by Campus:</label>
            </div>
            <div class="col-auto">
              <select name="campus_id" id="campus_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Campuses --</option>
                @foreach ($campuses as $campus)
                  <option value="{{ $campus->id }}" {{ (string) $campusId === (string) $campus->id ? 'selected' : '' }}>
                    {{ $campus->name }} ({{ $campus->code }})
                  </option>
                @endforeach
              </select>
            </div>
            @if ($campusId)
              <div class="col-auto">
                <a href="{{ route('academic.faculties.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x-circle me-1"></i> Clear Filter
                </a>
              </div>
            @endif
          </form>
        </div>
      </div>

      <!-- Main Faculty Table -->
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Academic Faculties</h3>
          <div class="card-tools d-flex align-items-center gap-2 me-0">
            <div class="input-group input-group-sm" style="width: 16rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="faculties-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
            </div>
            <a href="{{ route('academic.faculties.create', $campusId ? ['campus_id' => $campusId] : []) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Faculty
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="faculties-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="faculties-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="faculties-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="faculties-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="60">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Faculty Code</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Faculty Name</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Campus</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Dean</th>
                <th tabulator-formatter="html">Departments</th>
                <th tabulator-formatter="html">Status</th>
                <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="160" hozAlign="right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($faculties as $faculty)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><span class="badge text-bg-secondary">{{ $faculty->code }}</span></td>
                  <td class="fw-bold">
                    <a href="{{ route('academic.faculties.show', $faculty) }}" class="text-decoration-none">
                      {{ $faculty->name }}
                    </a>
                  </td>
                  <td>
                    <span class="badge bg-body-secondary text-body border">
                      <i class="bi bi-building me-1"></i> {{ $faculty->campus->name }}
                    </span>
                  </td>
                  <td>
                    @if ($faculty->dean)
                      <i class="bi bi-person-badge text-primary me-1"></i> {{ $faculty->dean->name }}
                    @else
                      <span class="text-muted fst-italic">Not Assigned</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge text-bg-info">
                      {{ $faculty->departments_count }} {{ Str::plural('Department', $faculty->departments_count) }}
                    </span>
                  </td>
                  <td>
                    @if ($faculty->status === 'active')
                      <span class="badge text-bg-success">Active</span>
                    @else
                      <span class="badge text-bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('academic.faculties.show', $faculty) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('academic.faculties.edit', $faculty) }}" class="btn btn-sm btn-outline-warning" title="Edit Faculty">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('academic.faculties.destroy', $faculty) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this faculty?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Faculty" {{ $faculty->departments_count > 0 ? 'disabled' : '' }}>
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
      initAdminLteDataTable('#faculties-table', {
        filterInput: '#faculties-filter',
        btnCsv: '#faculties-export-csv',
        btnJson: '#faculties-export-json',
        btnPrint: '#faculties-print',
        filename: 'faculties_export',
      });
    });
  </script>
@endpush
