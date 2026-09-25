@extends('layouts.app')

@section('title', 'Curriculums - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Curriculum Framework & Progression')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item active" aria-current="page">Curriculums</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <!-- Filter Bar -->
      <div class="card mb-3">
        <div class="card-body py-2">
          <form action="{{ route('academic.curriculums.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
              <label for="prog_filter" class="col-form-label fw-bold">
                <i class="bi bi-funnel me-1"></i> Filter by Programme:
              </label>
            </div>
            <div class="col-auto">
              <select name="programme_id" id="prog_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">-- All Programmes --</option>
                @foreach ($programmes as $prog)
                  <option value="{{ $prog->id }}" {{ (string) $programmeId === (string) $prog->id ? 'selected' : '' }}>
                    {{ $prog->name }} ({{ $prog->code }})
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
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>

            @if ($programmeId || $status !== null && $status !== '')
              <div class="col-auto">
                <a href="{{ route('academic.curriculums.index') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x-circle me-1"></i> Clear Filter
                </a>
              </div>
            @endif
          </form>
        </div>
      </div>

      <!-- Main Curriculums Card with Tabulator DataTable -->
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Curriculum Structures ({{ $curriculums->count() }})</h3>
          <div class="card-tools d-flex align-items-center gap-2 me-0">
            <div class="input-group input-group-sm" style="width: 16rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="curriculums-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
            </div>
            <a href="{{ route('academic.curriculums.create', $programmeId ? ['programme_id' => $programmeId] : []) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Curriculum
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="curriculums-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="curriculums-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="curriculums-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="curriculums-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="50">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Programme</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Version Name</th>
                <th tabulator-formatter="html">Effective Span</th>
                <th tabulator-formatter="html">Min Credits</th>
                <th tabulator-formatter="html">Mapped Courses</th>
                <th tabulator-formatter="html">Status</th>
                <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="160" hozAlign="right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($curriculums as $curr)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="fw-bold">{{ $curr->programme->name }}</span>
                    <span class="badge bg-body-secondary text-body border ms-1">{{ $curr->programme->code }}</span>
                    <small class="text-muted d-block">{{ $curr->programme->department->name ?? '' }}</small>
                  </td>
                  <td class="fw-bold">
                    <a href="{{ route('academic.curriculums.show', $curr) }}" class="text-decoration-none">
                      {{ $curr->version_name }}
                    </a>
                  </td>
                  <td>
                    <span class="badge text-bg-light border">
                      <i class="bi bi-calendar-range me-1"></i> {{ $curr->start_academic_year }} &ndash; {{ $curr->end_academic_year ?? 'Present' }}
                    </span>
                  </td>
                  <td>
                    <span class="badge text-bg-light border fw-bold">
                      {{ $curr->min_graduation_credits }} CU
                    </span>
                  </td>
                  <td>
                    <span class="badge text-bg-info">
                      <i class="bi bi-journal-bookmark me-1"></i> {{ $curr->curriculum_courses_count }} Courses
                    </span>
                  </td>
                  <td>
                    @if ($curr->is_active)
                      <span class="badge text-bg-success">Active</span>
                    @else
                      <span class="badge text-bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('academic.curriculums.show', $curr) }}" class="btn btn-sm btn-outline-primary" title="View Curriculum Matrix Dashboard">
                      <i class="bi bi-grid-3x3-gap"></i>
                    </a>
                    <a href="{{ route('academic.curriculums.edit', $curr) }}" class="btn btn-sm btn-outline-warning" title="Edit Curriculum">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('academic.curriculums.destroy', $curr) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete curriculum {{ $curr->version_name }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Curriculum">
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
      initAdminLteDataTable('#curriculums-table', {
        filterInput: '#curriculums-filter',
        btnCsv: '#curriculums-export-csv',
        btnJson: '#curriculums-export-json',
        btnPrint: '#curriculums-print',
        filename: 'curriculums_export',
      });
    });
  </script>
@endpush
