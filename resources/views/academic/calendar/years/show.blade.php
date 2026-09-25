@extends('layouts.app')

@section('title', 'Academic Year Details - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Academic Year: ' . $academicYear->name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.academic-years.index') }}">Academic Calendar</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $academicYear->name }}</li>
@endsection

@section('content')
  <div class="row">
    <!-- Academic Year Profile Card -->
    <div class="col-12 col-lg-4">
      <div class="card card-primary card-outline mb-4">
        <div class="card-body">
          <div class="text-center mb-3">
            <i class="bi bi-calendar-range text-primary display-4"></i>
            <h4 class="mt-2 mb-0 fw-bold">{{ $academicYear->name }}</h4>
            <div class="mt-2">
              @if ($academicYear->is_current)
                <span class="badge text-bg-success"><i class="bi bi-check-circle-fill me-1"></i> Current Academic Year</span>
              @else
                <span class="badge text-bg-secondary">Archived / Past</span>
              @endif
            </div>
          </div>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted"><i class="bi bi-calendar-event me-2"></i> Start Date</span>
              <span class="fw-bold">{{ $academicYear->start_date->format('M d, Y') }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted"><i class="bi bi-calendar-check me-2"></i> End Date</span>
              <span class="fw-bold">{{ $academicYear->end_date->format('M d, Y') }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted"><i class="bi bi-layers me-2"></i> Total Semesters</span>
              <span class="badge text-bg-info">{{ $academicYear->semesters->count() }}</span>
            </li>
            @if ($academicYear->description)
              <li class="list-group-item px-0">
                <span class="text-muted d-block mb-1"><i class="bi bi-card-text me-2"></i> Description</span>
                <span class="small">{{ $academicYear->description }}</span>
              </li>
            @endif
          </ul>

          <div class="d-grid gap-2">
            @if (! $academicYear->is_current)
              <form action="{{ route('academic.academic-years.make-current', $academicYear) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-success w-100" onclick="return confirm('Set {{ $academicYear->name }} as current year?');">
                  <i class="bi bi-check-lg me-1"></i> Set as Current Year
                </button>
              </form>
            @endif
            <a href="{{ route('academic.academic-years.edit', $academicYear) }}" class="btn btn-warning">
              <i class="bi bi-pencil me-1"></i> Edit Year Details
            </a>
            <a href="{{ route('academic.academic-years.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Calendar
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Semesters Table -->
    <div class="col-12 col-lg-8">
      <div class="card card-outline card-info mb-4">
        <div class="card-header">
          <h3 class="card-title">Semesters in {{ $academicYear->name }}</h3>
          <div class="card-tools d-flex align-items-center gap-2 me-0">
            <div class="input-group input-group-sm" style="width: 14rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="sem-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
            </div>
            <a href="{{ route('academic.semesters.create', ['academic_year_id' => $academicYear->id]) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Semester
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="sem-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="sem-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="sem-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="year-semesters-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="50">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Semester Name</th>
                <th tabulator-formatter="plaintext">Term Dates</th>
                <th tabulator-formatter="html">Registration</th>
                <th tabulator-formatter="html">Add/Drop</th>
                <th tabulator-formatter="html">Status</th>
                <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="180" hozAlign="right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($academicYear->semesters as $semester)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td class="fw-bold">{{ $semester->name }} (Sem {{ $semester->semester_number }})</td>
                  <td>{{ $semester->start_date->format('M d, Y') }} &ndash; {{ $semester->end_date->format('M d, Y') }}</td>
                  <td>
                    @if ($semester->registration_start_date && $semester->registration_end_date)
                      <small class="d-block">{{ $semester->registration_start_date->format('M d') }} &ndash; {{ $semester->registration_end_date->format('M d, Y') }}</small>
                      @if ($semester->isRegistrationOpen())
                        <span class="badge text-bg-success">Open</span>
                      @else
                        <span class="badge text-bg-secondary">Closed</span>
                      @endif
                    @else
                      <span class="text-muted fst-italic">Not Set</span>
                    @endif
                  </td>
                  <td>
                    @if ($semester->add_drop_deadline)
                      <small class="d-block">{{ $semester->add_drop_deadline->format('M d, Y') }}</small>
                      @if ($semester->isAddDropOpen())
                        <span class="badge text-bg-info">Active</span>
                      @else
                        <span class="badge text-bg-danger">Passed</span>
                      @endif
                    @else
                      <span class="text-muted fst-italic">Not Set</span>
                    @endif
                  </td>
                  <td>
                    @if ($semester->is_active)
                      <span class="badge text-bg-success"><i class="bi bi-broadcast me-1"></i> Active</span>
                    @else
                      <span class="badge text-bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td class="text-end">
                    @if (! $semester->is_active)
                      <form action="{{ route('academic.semesters.activate', $semester) }}" method="POST" class="d-inline" onsubmit="return confirm('Activate {{ $semester->name }}?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activate Semester">
                          <i class="bi bi-play-circle"></i>
                        </button>
                      </form>
                    @endif
                    <a href="{{ route('academic.semesters.edit', $semester) }}" class="btn btn-sm btn-outline-warning" title="Edit Semester">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('academic.semesters.destroy', $semester) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $semester->name }}?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Semester" {{ $semester->is_active ? 'disabled' : '' }}>
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
      initAdminLteDataTable('#year-semesters-table', {
        filterInput: '#sem-filter',
        btnCsv: '#sem-export-csv',
        btnJson: '#sem-export-json',
        btnPrint: '#sem-print',
        filename: '{{ Str::slug($academicYear->name) }}_semesters',
      });
    });
  </script>
@endpush
