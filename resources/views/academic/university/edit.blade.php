@extends('layouts.app')

@section('title', 'Institution Profile - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Institution Profile')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item active" aria-current="page">University Profile</li>
@endsection

@section('content')
  <div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h3 class="card-title">Root Institution Governance Profile</h3>
        </div>
        <form action="{{ route('academic.university.update', $university) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="card-body">
            <div class="callout callout-info mb-4">
              <h5 class="mb-1"><i class="bi bi-bank me-1"></i> Root Organization Settings</h5>
              <p class="mb-0 small text-muted">
                This stands as the top-level entity in the academic hierarchy defined by the team lead. Campuses and faculties descend from this institution profile.
              </p>
            </div>

            <div class="row">
              <div class="col-md-8 mb-3">
                <label for="name" class="form-label">University Full Name <span class="text-danger">*</span></label>
                <input
                  type="text"
                  name="name"
                  id="name"
                  class="form-control @error('name') is-invalid @enderror"
                  value="{{ old('name', $university->name) }}"
                  required
                />
                @error('name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4 mb-3">
                <label for="code" class="form-label">Institution Code <span class="text-danger">*</span></label>
                <input
                  type="text"
                  name="code"
                  id="code"
                  class="form-control text-uppercase @error('code') is-invalid @enderror"
                  value="{{ old('code', $university->code) }}"
                  required
                />
                @error('code')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Official Email</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                  <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $university->email) }}"
                  />
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Official Telephone</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                  <input
                    type="text"
                    name="phone"
                    id="phone"
                    class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $university->phone) }}"
                  />
                  @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>

            <div class="mb-3">
              <label for="address" class="form-label">Headquarters Address</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                <input
                  type="text"
                  name="address"
                  id="address"
                  class="form-control @error('address') is-invalid @enderror"
                  value="{{ old('address', $university->address) }}"
                />
                @error('address')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="mb-3">
              <label for="website" class="form-label">Official Portal / Website</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-globe"></i></span>
                <input
                  type="url"
                  name="website"
                  id="website"
                  class="form-control @error('website') is-invalid @enderror"
                  value="{{ old('website', $university->website) }}"
                  placeholder="https://..."
                />
                @error('website')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
          <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-check2-circle me-1"></i> Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
