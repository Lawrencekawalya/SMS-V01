@extends('layouts.app')

@section('title', 'Add Academic Programme - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Add Programme')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.programmes.index') }}">Programmes</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add New</li>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-7">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Programme Information</h3>
        </div>
        <form action="{{ route('academic.programmes.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="mb-3">
              <label for="department_id" class="form-label">Offering Department <span class="text-danger">*</span></label>
              <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                <option value="">-- Select Department --</option>
                @foreach ($departments as $dept)
                  <option value="{{ $dept->id }}" {{ (string) old('department_id', $selectedDepartmentId) === (string) $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }} ({{ $dept->code }}) &bull; {{ $dept->faculty->name ?? '' }}
                  </option>
                @endforeach
              </select>
              @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="name" class="form-label">Programme Name <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-mortarboard"></i></span>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="e.g. Bachelor of Science in Software Engineering"
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
                <label for="code" class="form-label">Programme Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-tag"></i></span>
                  <input
                    type="text"
                    name="code"
                    id="code"
                    class="form-control text-uppercase @error('code') is-invalid @enderror"
                    placeholder="e.g. BSSE, BSCS, BBA"
                    value="{{ old('code') }}"
                    required
                  />
                  @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <small class="form-text text-muted">A unique institutional identifier.</small>
              </div>

              <div class="col-md-6 mb-3">
                <label for="award_type" class="form-label">Award Level <span class="text-danger">*</span></label>
                <select name="award_type" id="award_type" class="form-select @error('award_type') is-invalid @enderror" required>
                  <option value="">-- Select Award Level --</option>
                  @foreach ($awardTypes as $type)
                    <option value="{{ $type }}" {{ old('award_type', 'Bachelors') === $type ? 'selected' : '' }}>
                      {{ $type }}
                    </option>
                  @endforeach
                </select>
                @error('award_type')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="duration_years" class="form-label">Duration (Years) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                  <input
                    type="number"
                    name="duration_years"
                    id="duration_years"
                    min="1"
                    max="7"
                    class="form-control @error('duration_years') is-invalid @enderror"
                    value="{{ old('duration_years', 3) }}"
                    required
                  />
                  <span class="input-group-text">Years</span>
                  @error('duration_years')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="required_credits_to_graduate" class="form-label">Graduation Credits (CU) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-award"></i></span>
                  <input
                    type="number"
                    name="required_credits_to_graduate"
                    id="required_credits_to_graduate"
                    min="10"
                    max="500"
                    class="form-control @error('required_credits_to_graduate') is-invalid @enderror"
                    value="{{ old('required_credits_to_graduate', 110) }}"
                    required
                  />
                  <span class="input-group-text">CU</span>
                  @error('required_credits_to_graduate')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="status" class="form-label">Operational Status <span class="text-danger">*</span></label>
              <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Programme Overview / Description</label>
              <textarea
                name="description"
                id="description"
                rows="3"
                class="form-control @error('description') is-invalid @enderror"
                placeholder="Overview, career pathways, and academic focus of this programme..."
              >{{ old('description') }}</textarea>
              @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <div class="card-footer">
            <a href="{{ route('academic.programmes.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary float-end">
              <i class="bi bi-check2-circle me-1"></i> Save Programme
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
