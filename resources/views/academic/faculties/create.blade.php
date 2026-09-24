@extends('layouts.app')

@section('title', 'Add New Faculty - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Add Faculty')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.faculties.index') }}">Faculties</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add New</li>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Faculty Information</h3>
        </div>
        <form action="{{ route('academic.faculties.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="mb-3">
              <label for="campus_id" class="form-label">Campus Branch <span class="text-danger">*</span></label>
              <select name="campus_id" id="campus_id" class="form-select @error('campus_id') is-invalid @enderror" required>
                <option value="">-- Select Campus --</option>
                @foreach ($campuses as $campus)
                  <option value="{{ $campus->id }}" {{ (string) old('campus_id', request('campus_id')) === (string) $campus->id ? 'selected' : '' }}>
                    {{ $campus->name }} ({{ $campus->code }})
                  </option>
                @endforeach
              </select>
              @error('campus_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="name" class="form-label">Faculty Name <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-mortarboard"></i></span>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="e.g. Faculty of Science & Technology"
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
                <label for="code" class="form-label">Faculty Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-tag"></i></span>
                  <input
                    type="text"
                    name="code"
                    id="code"
                    class="form-control text-uppercase @error('code') is-invalid @enderror"
                    placeholder="e.g. FST, FBE"
                    value="{{ old('code') }}"
                    required
                  />
                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
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
              <label for="dean_user_id" class="form-label">Appointed Dean</label>
              <select name="dean_user_id" id="dean_user_id" class="form-select @error('dean_user_id') is-invalid @enderror">
                <option value="">-- No Dean Assigned Yet --</option>
                @foreach ($users as $user)
                  <option value="{{ $user->id }}" {{ (string) old('dean_user_id') === (string) $user->id ? 'selected' : '' }}>
                    {{ $user->name }} ({{ $user->email }})
                  </option>
                @endforeach
              </select>
              @error('dean_user_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">The academic head of the faculty.</small>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description / Scope</label>
              <textarea
                name="description"
                id="description"
                rows="3"
                class="form-control @error('description') is-invalid @enderror"
                placeholder="Brief description of the faculty's mandate and disciplines..."
              >{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="card-footer">
            <a href="{{ route('academic.faculties.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary float-end">
              <i class="bi bi-check2-circle me-1"></i> Save Faculty
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
