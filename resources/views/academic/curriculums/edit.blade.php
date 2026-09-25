@extends('layouts.app')

@section('title', 'Edit Curriculum - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Edit Curriculum Version')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.curriculums.index') }}">Curriculums</a></li>
  <li class="breadcrumb-item active" aria-current="page">Edit {{ $curriculum->version_name }}</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
      <div class="card card-warning card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Edit Curriculum: {{ $curriculum->version_name }}</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.curriculums.show', $curriculum) }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Back to Curriculum Matrix
            </a>
          </div>
        </div>

        <form action="{{ route('academic.curriculums.update', $curriculum) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-12">
                <label for="programme_id" class="form-label fw-bold">Academic Degree Programme <span class="text-danger">*</span></label>
                <select name="programme_id" id="programme_id" class="form-select @error('programme_id') is-invalid @enderror" required>
                  @foreach ($programmes as $prog)
                    <option value="{{ $prog->id }}" {{ (string) old('programme_id', $curriculum->programme_id) === (string) $prog->id ? 'selected' : '' }}>
                      {{ $prog->name }} ({{ $prog->code }}) &bull; {{ $prog->department->name ?? '' }} ({{ $prog->duration_years }} Yrs, {{ $prog->required_credits_to_graduate }} Credits)
                    </option>
                  @endforeach
                </select>
                @error('programme_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-12">
                <label for="version_name" class="form-label fw-bold">Curriculum Version / Structure Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  name="version_name"
                  id="version_name"
                  class="form-control @error('version_name') is-invalid @enderror"
                  value="{{ old('version_name', $curriculum->version_name) }}"
                  required
                />
                @error('version_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="start_academic_year" class="form-label fw-bold">Start Academic Year <span class="text-danger">*</span></label>
                <input
                  type="number"
                  name="start_academic_year"
                  id="start_academic_year"
                  class="form-control @error('start_academic_year') is-invalid @enderror"
                  min="2000"
                  max="2100"
                  value="{{ old('start_academic_year', $curriculum->start_academic_year) }}"
                  required
                />
                @error('start_academic_year')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="end_academic_year" class="form-label fw-bold">End Academic Year</label>
                <input
                  type="number"
                  name="end_academic_year"
                  id="end_academic_year"
                  class="form-control @error('end_academic_year') is-invalid @enderror"
                  min="2000"
                  max="2100"
                  value="{{ old('end_academic_year', $curriculum->end_academic_year) }}"
                />
                @error('end_academic_year')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="min_graduation_credits" class="form-label fw-bold">Minimum Graduation Credits <span class="text-danger">*</span></label>
                <input
                  type="number"
                  name="min_graduation_credits"
                  id="min_graduation_credits"
                  class="form-control @error('min_graduation_credits') is-invalid @enderror"
                  min="1"
                  max="500"
                  value="{{ old('min_graduation_credits', $curriculum->min_graduation_credits) }}"
                  required
                />
                @error('min_graduation_credits')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold d-block">Status</label>
                <div class="form-check form-switch mt-2">
                  <input
                    type="checkbox"
                    name="is_active"
                    id="is_active"
                    class="form-check-input"
                    value="1"
                    {{ old('is_active', $curriculum->is_active) ? 'checked' : '' }}
                  />
                  <label for="is_active" class="form-check-label fw-semibold">
                    Active Curriculum
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer clearfix">
            <div class="float-end">
              <a href="{{ route('academic.curriculums.show', $curriculum) }}" class="btn btn-secondary me-2">Cancel</a>
              <button type="submit" class="btn btn-warning">
                <i class="bi bi-check-circle me-1"></i> Update Curriculum
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
