@extends('layouts.app')

@section('title', $campus->name . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', $campus->name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.campuses.index') }}">Campuses</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $campus->code }}</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-4">
      <div class="card card-primary card-outline mb-4">
        <div class="card-body box-profile">
          <div class="text-center mb-3">
            <span class="rounded-circle bg-primary-subtle p-3 d-inline-block text-primary">
              <i class="bi bi-buildings fs-1"></i>
            </span>
          </div>
          <h3 class="profile-username text-center mb-1">{{ $campus->name }}</h3>
          <p class="text-muted text-center mb-3">Code: <strong>{{ $campus->code }}</strong></p>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Campus Classification
              @if ($campus->is_main_campus)
                <span class="badge text-bg-success"><i class="bi bi-star-fill me-1"></i> Main Campus</span>
              @else
                <span class="badge text-bg-secondary">Branch Campus</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Operational Status
              @if ($campus->status === 'active')
                <span class="badge text-bg-success">Active</span>
              @else
                <span class="badge text-bg-danger">Inactive</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Physical Location
              <span class="fw-semibold text-end">{{ $campus->location ?? 'Not Specified' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Parent University
              <span class="fw-semibold">{{ $campus->university->name }}</span>
            </li>
          </ul>

          <div class="d-grid gap-2">
            <a href="{{ route('academic.campuses.edit', $campus) }}" class="btn btn-warning">
              <i class="bi bi-pencil me-1"></i> Edit Campus Details
            </a>
            <a href="{{ route('academic.campuses.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Campuses
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="card card-info card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Faculties at this Campus ({{ $campus->faculties->count() }})</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.faculties.create', ['campus_id' => $campus->id]) }}" class="btn btn-sm btn-primary">
              <i class="bi bi-plus-circle me-1"></i> Add Faculty
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Faculty Code</th>
                  <th>Faculty Name</th>
                  <th>Dean</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($campus->faculties as $faculty)
                  <tr>
                    <td><span class="badge text-bg-secondary">{{ $faculty->code }}</span></td>
                    <td class="fw-bold">
                      <a href="{{ route('academic.faculties.show', $faculty) }}" class="text-decoration-none">
                        {{ $faculty->name }}
                      </a>
                    </td>
                    <td>
                      @if ($faculty->dean)
                        <i class="bi bi-person-badge me-1 text-primary"></i> {{ $faculty->dean->name }}
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
                      <a href="{{ route('academic.faculties.show', $faculty) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="{{ route('academic.faculties.edit', $faculty) }}" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil"></i>
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      <i class="bi bi-folder-x fs-3 d-block mb-2"></i>
                      No faculties have been created under this campus yet.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
