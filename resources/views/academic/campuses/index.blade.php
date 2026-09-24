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
          <div class="card-tools me-0">
            <a href="{{ route('academic.campuses.create') }}" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-circle me-1"></i> Add Campus
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th style="width: 60px">#</th>
                  <th>Campus Name</th>
                  <th>Code</th>
                  <th>Location</th>
                  <th>Type</th>
                  <th>Faculties</th>
                  <th>Status</th>
                  <th style="width: 160px" class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($campuses as $campus)
                  <tr>
                    <td>{{ $loop->iteration + ($campuses->currentPage() - 1) * $campuses->perPage() }}</td>
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
                @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                      <i class="bi bi-building-exclamation fs-3 d-block mb-2"></i>
                      No campuses found. Click "Add Campus" to create the first one.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        @if ($campuses->hasPages())
          <div class="card-footer clearfix">
            <div class="float-end">
              {{ $campuses->links() }}
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
