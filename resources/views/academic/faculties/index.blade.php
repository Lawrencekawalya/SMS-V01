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
          <div class="card-tools me-0">
            <a href="{{ route('academic.faculties.create', $campusId ? ['campus_id' => $campusId] : []) }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Faculty
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 60px">#</th>
                  <th>Faculty Code</th>
                  <th>Faculty Name</th>
                  <th>Campus</th>
                  <th>Dean</th>
                  <th>Status</th>
                  <th style="width: 160px" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($faculties as $faculty)
                  <tr>
                    <td>{{ $loop->iteration + ($faculties->currentPage() - 1) * $faculties->perPage() }}</td>
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
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Faculty">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                      <i class="bi bi-folder-x fs-3 d-block mb-2"></i>
                      No faculties found. Click "Add Faculty" to register a new one.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if ($faculties->hasPages())
          <div class="card-footer clearfix">
            <div class="float-end">
              {{ $faculties->links() }}
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
