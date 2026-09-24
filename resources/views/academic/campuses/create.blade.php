@extends('layouts.app')

@section('title', 'Add New Campus - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Add Campus')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.campuses.index') }}">Campuses</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add New</li>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Campus Information</h3>
        </div>
        <form action="{{ route('academic.campuses.store') }}" method="POST">
          @csrf
          <div class="card-body">
            @if ($universities->count() > 1)
              <div class="mb-3">
                <label for="university_id" class="form-label">University <span class="text-danger">*</span></label>
                <select name="university_id" id="university_id" class="form-select @error('university_id') is-invalid @enderror" required>
                  @foreach ($universities as $university)
                    <option value="{{ $university->id }}" {{ old('university_id') == $university->id ? 'selected' : '' }}>
                      {{ $university->name }} ({{ $university->code }})
                    </option>
                  @endforeach
                </select>
                @error('university_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            @else
              <input type="hidden" name="university_id" value="{{ $universities->first()?->id }}">
            @endif

            <div class="mb-3">
              <label for="name" class="form-label">Campus Name <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-building"></i></span>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="e.g. Main Campus, Western Branch"
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
                <label for="code" class="form-label">Campus Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-tag"></i></span>
                  <input
                    type="text"
                    name="code"
                    id="code"
                    class="form-control text-uppercase @error('code') is-invalid @enderror"
                    placeholder="e.g. MAIN, WEST"
                    value="{{ old('code') }}"
                    required
                  />
                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <small class="form-text text-muted">A short unique identifier.</small>
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
              <label for="location" class="form-label">Physical Location / Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <input
                  type="text"
                  name="location"
                  id="location"
                  class="form-control @error('location') is-invalid @enderror"
                  placeholder="e.g. Plot 12 Academic Road, City"
                  value="{{ old('location') }}"
                />
                @error('location')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3 form-check form-switch">
              <input
                type="checkbox"
                name="is_main_campus"
                id="is_main_campus"
                class="form-check-input"
                value="1"
                {{ old('is_main_campus') ? 'checked' : '' }}
              />
              <label class="form-check-label fw-bold" for="is_main_campus">
                Designate as Primary / Main Campus
              </label>
              <div class="form-text">If checked, any existing primary campus flag will automatically transfer to this campus.</div>
            </div>
          </div>
          <div class="card-footer">
            <a href="{{ route('academic.campuses.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary float-end">
              <i class="bi bi-check2-circle me-1"></i> Save Campus
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
