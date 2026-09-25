@extends('layouts.app')

@section('title', 'Add Semester - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Create Semester')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.academic-years.index') }}">Academic Calendar</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add Semester</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">New Semester & Session Details</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.academic-years.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Back to Calendar
            </a>
          </div>
        </div>

        <form action="{{ route('academic.semesters.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="academic_year_id" class="form-label fw-bold">Parent Academic Year <span class="text-danger">*</span></label>
                <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                  <option value="">-- Select Academic Year --</option>
                  @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" {{ (string) old('academic_year_id', $academicYearId) === (string) $year->id ? 'selected' : '' }}>
                      {{ $year->name }} {{ $year->is_current ? '(Current)' : '' }}
                    </option>
                  @endforeach
                </select>
                @error('academic_year_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="semester_number" class="form-label fw-bold">Semester Number <span class="text-danger">*</span></label>
                <select name="semester_number" id="semester_number" class="form-select @error('semester_number') is-invalid @enderror" required>
                  <option value="1" {{ old('semester_number') == 1 ? 'selected' : '' }}>Semester 1</option>
                  <option value="2" {{ old('semester_number') == 2 ? 'selected' : '' }}>Semester 2</option>
                  <option value="3" {{ old('semester_number') == 3 ? 'selected' : '' }}>Semester 3 / Recess Term</option>
                </select>
                @error('semester_number')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-12">
                <label for="name" class="form-label fw-bold">Semester Title / Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="e.g. Semester 1, Semester 2, or Special Term"
                  value="{{ old('name', 'Semester 1') }}"
                  required
                />
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="start_date" class="form-label fw-bold">Term Start Date <span class="text-danger">*</span></label>
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
                <label for="end_date" class="form-label fw-bold">Term End Date <span class="text-danger">*</span></label>
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

              <div class="col-12"><hr class="my-2"></div>
              <div class="col-12"><h6 class="fw-bold text-primary"><i class="bi bi-clock-history me-1"></i> Registration & Add/Drop Windows</h6></div>

              <div class="col-md-6">
                <label for="registration_start_date" class="form-label fw-bold">Registration Start Date</label>
                <input
                  type="date"
                  name="registration_start_date"
                  id="registration_start_date"
                  class="form-control @error('registration_start_date') is-invalid @enderror"
                  value="{{ old('registration_start_date') }}"
                />
                @error('registration_start_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="registration_end_date" class="form-label fw-bold">Registration End Date</label>
                <input
                  type="date"
                  name="registration_end_date"
                  id="registration_end_date"
                  class="form-control @error('registration_end_date') is-invalid @enderror"
                  value="{{ old('registration_end_date') }}"
                />
                @error('registration_end_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="add_drop_deadline" class="form-label fw-bold">Add/Drop Courses Deadline</label>
                <input
                  type="date"
                  name="add_drop_deadline"
                  id="add_drop_deadline"
                  class="form-control @error('add_drop_deadline') is-invalid @enderror"
                  value="{{ old('add_drop_deadline') }}"
                />
                <div class="form-text">Final cutoff for students to amend course enrollments.</div>
                @error('add_drop_deadline')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch mt-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    id="is_active"
                    name="is_active"
                    value="1"
                    {{ old('is_active') ? 'checked' : '' }}
                  />
                  <label class="form-check-label fw-bold" for="is_active">
                    Set as Live / Active Semester
                  </label>
                  <div class="form-text">If checked, this semester and its academic year become the live operational session.</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card-footer clearfix">
            <div class="float-end">
              <a href="{{ route('academic.academic-years.index') }}" class="btn btn-secondary me-2">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Save Semester
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
