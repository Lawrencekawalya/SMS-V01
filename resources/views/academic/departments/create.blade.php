@extends('layouts.app')

@section('title', 'Add Department - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Add Department')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.departments.index') }}">Departments</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add New</li>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Department Information</h3>
        </div>
        <form action="{{ route('academic.departments.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="mb-3">
              <label for="faculty_id" class="form-label">Parent Faculty <span class="text-danger">*</span></label>
              <select name="faculty_id" id="faculty_id" class="form-select @error('faculty_id') is-invalid @enderror" required>
                <option value="">-- Select Faculty --</option>
                @foreach ($faculties as $faculty)
                  <option value="{{ $faculty->id }}" {{ (string) old('faculty_id', $selectedFacultyId) === (string) $faculty->id ? 'selected' : '' }}>
                    {{ $faculty->name }} ({{ $faculty->code }}) &bull; {{ $faculty->campus->name ?? '' }}
                  </option>
                @endforeach
              </select>
              @error('faculty_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-diagram-3"></i></span>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="e.g. Department of Computer Science"
                  value="{{ old('name') }}"
                  required
                />
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="code" class="form-label">Department Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-tag"></i></span>
                  <input
                    type="text"
                    name="code"
                    id="code"
                    class="form-control text-uppercase @error('code') is-invalid @enderror"
                    placeholder="e.g. CS, IT, AF"
                    value="{{ old('code') }}"
                    required
                  />
                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <small class="form-text text-muted">Unique within this faculty.</small>
              </div>

              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Operational Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                  <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3">
              <label for="hod_user_id" class="form-label">Head of Department (HOD)</label>
              <select name="hod_user_id" id="hod_user_id" class="form-select @error('hod_user_id') is-invalid @enderror">
                <option value="">-- No HOD Assigned Yet --</option>
                @foreach ($users as $user)
                  <option value="{{ $user->id }}" {{ (string) old('hod_user_id') === (string) $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                  </option>
                @endforeach
              </select>
              @error('hod_user_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">The academic head of this department.</small>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description / Scope</label>
              <textarea
                name="description"
                id="description"
                rows="3"
                class="form-control @error('description') is-invalid @enderror"
                placeholder="Brief description of the department's mandate and disciplines..."
              >{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="card-footer">
            <a href="{{ route('academic.departments.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary float-end">
              <i class="bi bi-check2-circle me-1"></i> Save Department
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
