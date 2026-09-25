@extends('layouts.app')

@section('title', 'Add Course Unit - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Create Course Unit')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.courses.index') }}">Course Catalog</a></li>
  <li class="breadcrumb-item active" aria-current="page">Add Course Unit</li>
@endsection

@section('content')
  <div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">New Course Unit Specification</h3>
          <div class="card-tools me-0">
            <a href="{{ route('academic.courses.index') }}" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Back to Catalog
            </a>
          </div>
        </div>

        <form action="{{ route('academic.courses.store') }}" method="POST">
          @csrf
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="code" class="form-label fw-bold">Course Code <span class="text-danger">*</span></label>
                <input
                  type="text"
                  name="code"
                  id="code"
                  class="form-control text-uppercase @error('code') is-invalid @enderror"
                  placeholder="e.g. CSC1101, BIT1201"
                  value="{{ old('code') }}"
                  required
                />
                <div class="form-text">Alphanumeric code, e.g. 3-4 letters followed by 4 digits.</div>
                @error('code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="department_id" class="form-label fw-bold">Teaching Department <span class="text-danger">*</span></label>
                <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                  <option value="">-- Select Department --</option>
                  @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}" {{ (string) old('department_id', $departmentId) === (string) $dept->id ? 'selected' : '' }}>
                      {{ $dept->name }} ({{ $dept->code }}) &bull; {{ $dept->faculty->campus->name ?? '' }}
                    </option>
                  @endforeach
                </select>
                @error('department_id')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-12">
                <label for="name" class="form-label fw-bold">Course Title / Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  placeholder="e.g. Structured Programming in C"
                  value="{{ old('name') }}"
                  required
                />
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="credit_units" class="form-label fw-bold">Credit Units (CU) <span class="text-danger">*</span></label>
                <input
                  type="number"
                  step="0.5"
                  min="1"
                  max="15"
                  name="credit_units"
                  id="credit_units"
                  class="form-control @error('credit_units') is-invalid @enderror"
                  placeholder="e.g. 4.0"
                  value="{{ old('credit_units', '4.0') }}"
                  required
                />
                <div class="form-text">Standard course weight, usually between 3.0 and 5.0 CU.</div>
                @error('credit_units')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label for="status" class="form-label fw-bold">Course Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                  <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active (Available for Curriculum)</option>
                  <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived (Deprecated)</option>
                </select>
                @error('status')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <label for="description" class="form-label fw-bold">Course Description & Syllabus Outline</label>
                <textarea
                  name="description"
                  id="description"
                  rows="4"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Detailed course scope, learning outcomes, and topic outline..."
                >{{ old('description') }}</textarea>
                @error('description')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="card-footer clearfix">
            <div class="float-end">
              <a href="{{ route('academic.courses.index') }}" class="btn btn-secondary me-2">Cancel</a>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i> Save Course Unit
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
