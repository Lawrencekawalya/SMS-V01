@extends('layouts.app')

@section('title', $department->name . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', $department->name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.departments.index') }}">Departments</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $department->code }}</li>
@endsection

@section('content')
  <div class="row">
    <!-- Department Overview Profile -->
    <div class="col-12 col-lg-4">
      <div class="card card-primary card-outline mb-4">
        <div class="card-body box-profile">
          <div class="text-center mb-3">
            <span class="rounded-circle bg-primary-subtle p-3 d-inline-block text-primary">
              <i class="bi bi-diagram-3-fill fs-1"></i>
            </span>
          </div>
          <h3 class="profile-username text-center mb-1">{{ $department->name }}</h3>
          <p class="text-muted text-center mb-3">Code: <strong>{{ $department->code }}</strong></p>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Parent Faculty
              <span class="badge bg-body-secondary text-body border">
                <i class="bi bi-mortarboard me-1"></i> {{ $department->faculty->name }}
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Campus
              <span class="fw-semibold text-end">{{ $department->faculty->campus->name ?? 'N/A' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Head of Dept (HOD)
              @if ($department->hod)
                <span class="fw-semibold text-primary">
                  <i class="bi bi-person-badge me-1"></i> {{ $department->hod->name }}
                </span>
              @else
                <span class="text-muted fst-italic">Not Assigned</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Operational Status
              @if ($department->status === 'active')
                <span class="badge text-bg-success">Active</span>
              @else
                <span class="badge text-bg-secondary">Inactive</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Registered Date
              <span class="text-muted">{{ $department->created_at->format('M d, Y') }}</span>
            </li>
          </ul>

          <div class="callout callout-info mb-3">
            <h6 class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Mandate & Scope</h6>
            <p class="mb-0 text-muted small">
              {{ $department->description ?? 'No detailed description provided for this department yet.' }}
            </p>
          </div>

          <div class="d-grid gap-2">
            <a href="{{ route('academic.departments.edit', $department) }}" class="btn btn-warning">
              <i class="bi bi-pencil me-1"></i> Edit Department
            </a>
            <a href="{{ route('academic.departments.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Departments
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Programmes List -->
    <div class="col-12 col-lg-8">
      <div class="card card-info card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Academic Programmes ({{ $department->programmes->count() }})</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.programmes.create', ['department_id' => $department->id]) }}" class="btn btn-sm btn-primary">
              <i class="bi bi-plus-circle me-1"></i> Add Programme
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Programme Name</th>
                  <th>Award Level</th>
                  <th>Duration</th>
                  <th>Credits</th>
                  <th>Status</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($department->programmes as $programme)
                  <tr>
                    <td><span class="badge text-bg-secondary">{{ $programme->code }}</span></td>
                    <td class="fw-bold">
                      <a href="{{ route('academic.programmes.show', $programme) }}" class="text-decoration-none">
                        {{ $programme->name }}
                      </a>
                    </td>
                    <td>
                      @if ($programme->award_type === 'Bachelors' || $programme->award_type === 'Degree')
                        <span class="badge text-bg-primary">{{ $programme->award_type }}</span>
                      @elseif ($programme->award_type === 'Masters' || $programme->award_type === 'Doctorate')
                        <span class="badge text-bg-purple bg-indigo text-white">{{ $programme->award_type }}</span>
                      @elseif ($programme->award_type === 'Diploma')
                        <span class="badge text-bg-info">{{ $programme->award_type }}</span>
                      @else
                        <span class="badge text-bg-secondary">{{ $programme->award_type }}</span>
                      @endif
                    </td>
                    <td><span class="badge bg-body-secondary text-body border">{{ $programme->duration_years }} {{ Str::plural('Year', $programme->duration_years) }}</span></td>
                    <td><span class="fw-semibold">{{ $programme->required_credits_to_graduate }} CU</span></td>
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
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                      <i class="bi bi-mortarboard fs-3 d-block mb-2"></i>
                      No degree or diploma programmes registered under this department yet.
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
