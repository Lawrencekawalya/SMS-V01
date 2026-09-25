@extends('layouts.app')

@section('title', 'Add Curriculum - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Create Curriculum Version')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.curriculums.index') }}">Curriculums</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add Curriculum</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">New Programme Curriculum Specification</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.curriculums.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Back to Curriculums
            </a>
          </div>
        </div>

        <form action="{{ route('academic.curriculums.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-12">
                <label for="programme_id" class="form-label fw-bold">Academic Degree Programme <span class="text-danger">*</span></label>
                <select name="programme_id" id="programme_id" class="form-select @error('programme_id') is-invalid @enderror" required>
                  <option value="">-- Select Degree Programme --</option>
                  @foreach ($programmes as $prog)
                    <option value="{{ $prog->id }}" {{ (string) old('programme_id', $programmeId) === (string) $prog->id ? 'selected' : '' }}>
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
                  placeholder="e.g. 2024-2028 Revised Curriculum Structure"
                  value="{{ old('version_name') }}"
                  required
                />
                <div class="form-text">A descriptive label identifying this curriculum edition for accreditation and student catalogs.</div>
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
                  placeholder="e.g. 2024"
                  min="2000"
                  max="2100"
                  value="{{ old('start_academic_year', date('Y')) }}"
                  required
                />
                @error('start_academic_year')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="end_academic_year" class="form-label fw-bold">End Academic Year (Optional)</label>
                <input
                  type="number"
                  name="end_academic_year"
                  id="end_academic_year"
                  class="form-control @error('end_academic_year') is-invalid @enderror"
                  placeholder="e.g. 2028"
                  min="2000"
                  max="2100"
                  value="{{ old('end_academic_year', date('Y') + 4) }}"
                />
                <div class="form-text">Leave blank if this curriculum is open-ended until next review.</div>
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
                  placeholder="e.g. 110"
                  min="1"
                  max="500"
                  value="{{ old('min_graduation_credits', '110') }}"
                  required
                />
                <div class="form-text">Total required credit load across all completed study years.</div>
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
                    {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                  />
                  <label for="is_active" class="form-check-label fw-semibold">
                    Active Curriculum (Available for Student Enrolment)
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer clearfix">
            <div class="float-end">
              <a href="{{ route('academic.curriculums.index') }}" class="btn btn-secondary me-2">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Save & Continue to Course Mapping
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
