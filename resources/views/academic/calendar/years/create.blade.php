@extends('layouts.app')

@section('title', 'Add Academic Year - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Create Academic Year')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.academic-years.index') }}">Academic Calendar</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add Academic Year</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">New Academic Year Details</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.academic-years.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Back to Calendar
            </a>
          </div>
        </div>

        <form action="{{ route('academic.academic-years.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-12">
                <label for="name" class="form-label fw-bold">Academic Year Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="e.g. 2026/2027"
                  value="{{ old('name') }}"
                  required
                />
                <div class="form-text">Standard naming format: YYYY/YYYY (e.g. 2026/2027).</div>
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="start_date" class="form-label fw-bold">Session Start Date <span class="text-danger">*</span></label>
                <input
                  type="date"
                  name="start_date"
                  id="start_date"
                  class="form-control @error('start_date') is-invalid @enderror"
                  value="{{ old('start_date') }}"
                  required
                />
                @error('start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="end_date" class="form-label fw-bold">Session End Date <span class="text-danger">*</span></label>
                <input
                  type="date"
                  name="end_date"
                  id="end_date"
                  class="form-control @error('end_date') is-invalid @enderror"
                  value="{{ old('end_date') }}"
                  required
                />
                @error('end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <div class="form-check form-switch mt-2">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="is_current"
                    name="is_current"
                    value="1"
                    {{ old('is_current') ? 'checked' : '' }}
                  />
                  <label class="form-check-label fw-bold" for="is_current">
                    Set as Current Academic Year
                  </label>
                  <div class="form-text">If checked, this will automatically archive and unset any currently active academic year.</div>
                </div>
              </div>

              <div class="col-12">
                <label for="description" class="form-label fw-bold">Description / Notes</label>
                <textarea
                  name="description"
                  id="description"
                  rows="3"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Optional institutional notes or presidential decrees for this academic year..."
                >{{ old('description') }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="card-footer clearfix">
            <div class="float-end">
              <a href="{{ route('academic.academic-years.index') }}" class="btn btn-secondary me-2">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Save Academic Year
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
