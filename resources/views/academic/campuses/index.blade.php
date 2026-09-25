@extends('layouts.app')

@section('title', 'Campuses - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Campuses')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item active" aria-current="page">Campuses</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">University Campuses</h3>
          <div class="card-tools d-flex align-items-center gap-2 me-0">
            <div class="input-group input-group-sm" style="width: 16rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="campuses-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
            </div>
            <a href="{{ route('academic.campuses.create') }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Campus
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="campuses-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="campuses-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="campuses-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="campuses-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="60">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Campus Name</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Code</th>
                <th tabulator-formatter="plaintext" tabulator-headerFilter="input">Location</th>
                <th tabulator-formatter="html">Type</th>
                <th tabulator-formatter="html">Faculties</th>
                <th tabulator-formatter="html">Status</th>
                <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="160" hozAlign="right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($campuses as $campus)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td class="fw-bold">
                    <a href="{{ route('academic.campuses.show', $campus) }}" class="text-decoration-none">
                      {{ $campus->name }}
                    </a>
                  </td>
                  <td><span class="badge text-bg-secondary">{{ $campus->code }}</span></td>
                  <td>{{ $campus->location ?? 'N/A' }}</td>
                  <td>
                    @if ($campus->is_main_campus)
                      <span class="badge text-bg-success"><i class="bi bi-star-fill me-1"></i> Main Campus</span>
                    @else
                      <span class="badge text-bg-secondary">Branch Campus</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge text-bg-info">
                      {{ $campus->faculties_count }} {{ Str::plural('Faculty', $campus->faculties_count) }}
                    </span>
                  </td>
                  <td>
                    @if ($campus->status === 'active')
                      <span class="badge text-bg-success">Active</span>
                    @else
                      <span class="badge text-bg-danger">Inactive</span>
                    @endif
                  </td>
                  <td class="text-end">
                    <a href="{{ route('academic.campuses.show', $campus) }}" class="btn btn-sm btn-outline-info" title="View Details">
                      <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('academic.campuses.edit', $campus) }}" class="btn btn-sm btn-outline-warning" title="Edit Campus">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('academic.campuses.destroy', $campus) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this campus?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Campus" {{ $campus->faculties_count > 0 ? 'disabled' : '' }}>
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
      initAdminLteDataTable('#campuses-table', {
        filterInput: '#campuses-filter',
        btnCsv: '#campuses-export-csv',
        btnJson: '#campuses-export-json',
        btnPrint: '#campuses-print',
        filename: 'campuses_export',
      });
    });
  </script>
@endpush
