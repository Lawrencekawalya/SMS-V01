@extends('layouts.app')

@section('title', 'Official Graduation Gazette & Honors Roll - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Graduation Gazette & Honors Roll')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.graduation.index') }}">Graduation Clearance</a></li>
  <li class="breadcrumb-item active" aria-current="page">Honors Roll Gazette</li>
@endsection

@section('content')
  <!-- Filter & Action Bar (Screen Only) -->
  <div class="card card-outline card-primary mb-4 shadow-sm no-print">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
      <h3 class="card-title fs-6 fw-bold m-0">
        <i class="bi bi-funnel me-1"></i> Programme Filter
      </h3>
      <div class="d-flex gap-2">
        <a href="{{ route('academic.graduation.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Candidates Directory
        </a>
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-dark">
          <i class="bi bi-printer me-1"></i> Print Gazette Booklet
        </button>
      </div>
    </div>
    <div class="card-body">
      <form action="{{ route('academic.graduation.honors-roll') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-8">
          <label for="programme_id" class="form-label small fw-semibold">Academic Programme</label>
          <select name="programme_id" id="programme_id" class="form-select form-select-sm">
            <option value="">-- All Academic Programmes --</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ (string) $selectedProgrammeId === (string) $prog->id ? 'selected' : '' }}>
                {{ $prog->name }} ({{ $prog->code }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-4">
          <button type="submit" class="btn btn-sm btn-primary w-100">
            <i class="bi bi-filter me-1"></i> Filter Gazette
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Graduation Gazette Booklet Document -->
  <div class="card shadow-sm border-0 mb-4 gazette-card">
    <div class="card-body p-4 p-md-5">
      <!-- Formal University Gazette Title Header -->
      <div class="text-center pb-4 border-bottom mb-4">
        <h3 class="fw-bold mb-1 text-uppercase text-dark">{{ $university->name ?? 'BISHOP STUART UNIVERSITY' }}</h3>
        <h6 class="text-secondary text-uppercase mb-2">OFFICE OF THE ACADEMIC REGISTRAR &bull; 20TH CONGREGATION</h6>
        <div class="badge bg-primary fs-6 px-3 py-2 text-uppercase mb-2">
          OFFICIAL GRADUATION GAZETTE &bull; HONORS ROLL
        </div>
        <p class="small text-muted mb-0">
          The following candidates have completed all institutional and Senate curriculum requirements and are hereby cleared for the conferment of degrees and award of diplomas.
        </p>
        <div class="mt-2 fw-semibold text-primary small">
          Total Qualified Graduands: {{ $totalGraduands }}
        </div>
      </div>

      @forelse ($clearedByProgramme as $programmeName => $awards)
        <div class="mb-5 page-break-inside-avoid">
          <!-- Programme Heading -->
          <div class="p-2 px-3 bg-dark text-white rounded-top fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-mortarboard-fill me-2"></i> {{ $programmeName }}</span>
            <span class="badge bg-light text-dark">
              {{ array_sum(array_map('count', $awards)) }} Graduand(s)
            </span>
          </div>

          <div class="border border-top-0 rounded-bottom p-3 bg-white">
            @foreach ($awards as $awardName => $candidates)
              <div class="mb-4">
                <!-- Award Tier Subheader -->
                <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                  <i class="bi bi-star-fill text-warning"></i>
                  <h6 class="fw-bold m-0 text-uppercase text-primary">{{ $awardName }}</h6>
                  <span class="badge bg-secondary-subtle text-secondary small">({{ count($candidates) }})</span>
                </div>

                <!-- Graduands Table -->
                <div class="table-responsive">
                  <table class="table table-sm table-striped align-middle mb-0 gazette-table">
                    <thead class="table-light small">
                      <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 180px;">Registration Number</th>
                        <th>Graduand Name</th>
                        <th class="text-center" style="width: 100px;">CGPA</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($candidates as $i => $item)
                        @php $st = $item['student']; $audit = $item['audit']; @endphp
                        <tr>
                          <td class="text-muted small">{{ $i + 1 }}</td>
                          <td class="font-monospace fw-bold small">{{ $st->registration_number }}</td>
                          <td class="fw-semibold text-uppercase">{{ $st->user->name ?? $st->full_name }}</td>
                          <td class="text-center fw-bold text-primary">{{ number_format($audit['cgpa'], 2) }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <div class="text-center py-5 text-muted">
          <i class="bi bi-journal-x fs-1 d-block mb-3 text-secondary"></i>
          <h5 class="fw-bold">No Cleared Graduands Found</h5>
          <p class="small text-muted mb-0">No students currently meet all the academic clearance criteria for this selection.</p>
        </div>
      @endforelse

      <!-- Formal Senate & Congregation Signoff Block -->
      <div class="mt-5 pt-4 border-top page-break-inside-avoid">
        <div class="row text-center g-4">
          <div class="col-4">
            <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
            <div class="fw-bold small text-uppercase">Academic Registrar</div>
            <div class="text-muted" style="font-size: 0.72rem;">Signature &amp; Official Seal</div>
          </div>
          <div class="col-4">
            <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
            <div class="fw-bold small text-uppercase">Vice-Chancellor</div>
            <div class="text-muted" style="font-size: 0.72rem;">Signature &amp; Date</div>
          </div>
          <div class="col-4">
            <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
            <div class="fw-bold small text-uppercase">Chancellor</div>
            <div class="text-muted" style="font-size: 0.72rem;">Conferment Confirmation</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
<style>
  @media print {
    @page {
      size: portrait;
      margin: 15mm;
    }
    .no-print, .main-header, .main-sidebar, .app-header, .app-sidebar, .app-footer, .breadcrumb {
      display: none !important;
    }
    .app-main, .content-wrapper, body {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }
    .gazette-card {
      border: none !important;
      box-shadow: none !important;
    }
    .page-break-inside-avoid {
      page-break-inside: avoid;
    }
    .gazette-table {
      font-size: 0.85rem !important;
    }
  }
</style>
@endpush
