@extends('layouts.app')

@section('title', $faculty->name . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', $faculty->name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.faculties.index') }}">Faculties</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $faculty->code }}</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-5">
      <div class="card card-primary card-outline mb-4">
        <div class="card-body box-profile">
          <div class="text-center mb-3">
            <span class="rounded-circle bg-primary-subtle p-3 d-inline-block text-primary">
              <i class="bi bi-mortarboard-fill fs-1"></i>
            </span>
          </div>
          <h3 class="profile-username text-center mb-1">{{ $faculty->name }}</h3>
          <p class="text-muted text-center mb-3">Code: <strong>{{ $faculty->code }}</strong></p>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Campus Location
              <span class="badge bg-body-secondary text-body border">
                <i class="bi bi-building me-1"></i> {{ $faculty->campus->name }} ({{ $faculty->campus->code }})
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Faculty Dean
              @if ($faculty->dean)
                <span class="fw-semibold text-primary">
                  <i class="bi bi-person-badge me-1"></i> {{ $faculty->dean->name }}
                </span>
              @else
                <span class="text-muted fst-italic">Not Assigned</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Operational Status
              @if ($faculty->status === 'active')
                <span class="badge text-bg-success">Active</span>
              @else
                <span class="badge text-bg-secondary">Inactive</span>
              @endif
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Registered Date
              <span class="text-muted">{{ $faculty->created_at->format('M d, Y') }}</span>
            </li>
          </ul>

          <div class="callout callout-info mb-3">
            <h6 class="fw-bold mb-1"><i class="bi bi-info-circle me-1"></i> Overview & Mandate</h6>
            <p class="mb-0 text-muted small">
              {{ $faculty->description ?? 'No detailed description provided for this faculty yet.' }}
            </p>
          </div>

          <div class="d-grid gap-2">
            <a href="{{ route('academic.faculties.edit', $faculty) }}" class="btn btn-warning">
              <i class="bi bi-pencil me-1"></i> Edit Faculty
            </a>
            <a href="{{ route('academic.faculties.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Faculties
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card card-info card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Departments under {{ $faculty->code }} ({{ $faculty->departments->count() }})</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.departments.create', ['faculty_id' => $faculty->id]) }}" class="btn btn-sm btn-primary">
              <i class="bi bi-plus-circle me-1"></i> Add Department
            </a>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Department Name</th>
                  <th>Head of Dept (HOD)</th>
                  <th>Programmes</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($faculty->departments as $department)
                  <tr>
                    <td><span class="badge text-bg-secondary">{{ $department->code }}</span></td>
                    <td class="fw-bold">
                      <a href="{{ route('academic.departments.show', $department) }}" class="text-decoration-none">
                        {{ $department->name }}
                      </a>
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
                        {{ $department->programmes->count() }} {{ Str::plural('Programme', $department->programmes->count()) }}
                      </span>
                    </td>
                    <td class="text-end">
                      <a href="{{ route('academic.departments.show', $department) }}" class="btn btn-sm btn-outline-info" title="View Details">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="{{ route('academic.departments.edit', $department) }}" class="btn btn-sm btn-outline-warning" title="Edit Department">
                        <i class="bi bi-pencil"></i>
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                      <i class="bi bi-diagram-3 fs-1 text-primary opacity-50 d-block mb-2"></i>
                      No departments created under this faculty yet. Click "Add Department" above.
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
