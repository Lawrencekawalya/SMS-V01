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
        <form action="{{ route('university.update', $university) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')
          <div class="card-body">
            <div class="callout callout-info mb-4">
              <h5 class="mb-1"><i class="bi bi-bank me-1"></i> Root Organization Settings</h5>
              <p class="mb-0 small text-muted">
                This stands as the top-level entity in the academic hierarchy defined by the team lead. Campuses and faculties descend from this institution profile.
              </p>
            </div>

            <!-- Institution Logo & Crest Upload Section -->
            <div class="card card-outline card-secondary mb-4 bg-body-tertiary">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col-auto text-center">
                    <div class="p-2 border rounded bg-white shadow-sm d-inline-block" style="width: 100px; height: 100px;">
                      <img
                        id="logoPreview"
                        src="{{ $university->logo_url }}"
                        alt="Institution Crest"
                        class="img-fluid h-100 w-100"
                        style="object-fit: contain;"
                      />
                    </div>
                    <div class="small text-muted mt-1">
                      @if ($university->hasCustomLogo())
                        <span class="badge text-bg-success">Custom Logo</span>
                      @else
                        <span class="badge text-bg-secondary">Default Brand</span>
                      @endif
                    </div>
                  </div>
                  <div class="col">
                    <label for="logo" class="form-label fw-bold mb-1">
                      <i class="bi bi-image me-1 text-primary"></i> Institution Crest / System Logo
                    </label>
                    <p class="small text-muted mb-2">
                      Upload your official university crest or logo. This will update the sidebar brand, browser favicon, and all official academic transcripts and printouts across the entire system.
                    </p>
                    <input
                      type="file"
                      name="logo"
                      id="logo"
                      accept="image/png,image/jpeg,image/jpg,image/svg+xml,image/webp,image/x-icon"
                      class="form-control @error('logo') is-invalid @enderror"
                    />
                    <div class="form-text small text-muted">
                      Accepted formats: PNG, JPG, SVG, WebP, ICO (Max: 2MB). Transparent square PNG recommended.
                    </div>
                    @error('logo')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    @if ($university->hasCustomLogo())
                      <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                        <label class="form-check-label small text-danger fw-semibold" for="remove_logo">
                          <i class="bi bi-trash me-1"></i> Revert to default system logo
                        </label>
                      </div>
                    @endif
                  </div>
                </div>
              </div>
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

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logoPreview');
    const removeLogoCheckbox = document.getElementById('remove_logo');
    const defaultLogoUrl = "{{ asset('vendor/adminlte/assets/img/AdminLTELogo.png') }}";
    const currentLogoUrl = "{{ $university->logo_url }}";

    if (logoInput && logoPreview) {
      logoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
          logoPreview.src = URL.createObjectURL(file);
          if (removeLogoCheckbox) {
            removeLogoCheckbox.checked = false;
          }
        }
      });
    }

    if (removeLogoCheckbox && logoPreview) {
      removeLogoCheckbox.addEventListener('change', function () {
        if (this.checked) {
          logoPreview.src = defaultLogoUrl;
          if (logoInput) {
            logoInput.value = '';
          }
        } else {
          logoPreview.src = currentLogoUrl;
        }
      });
    }
  });
</script>
@endpush
