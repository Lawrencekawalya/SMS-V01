@extends('layouts.app')

@section('title', 'Graduation Clearance Audit - ' . ($student->user->name ?? $student->full_name))
@section('page-title', 'Student Graduation Clearance Audit')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('graduation.list') }}">Graduation Clearance</a></li>
  <li class="breadcrumb-item active" aria-current="page">Clearance Audit</li>
@endsection

@section('content')
  <!-- Action Buttons (No Print) -->
  <div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <a href="{{ route('graduation.list') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i> Back to Candidates Directory
    </a>
    <div class="d-flex gap-2">
      <a href="{{ route('result.transcript', $student->id) }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-file-earmark-text me-1"></i> Official Academic Transcript
      </a>
      <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-printer me-1"></i> Print Clearance Audit
      </button>
    </div>
  </div>

  <!-- Formal Institutional Audit Document -->
  <div class="card card-outline card-primary shadow-sm mb-4 print-card">
    <div class="card-header py-2 no-print">
      <div class="d-flex justify-content-between align-items-center">
        <h3 class="card-title fs-6 fw-bold mb-0">
          <i class="bi bi-file-earmark-ruled me-1 text-primary"></i> Graduation Clearance Audit Sheet
        </h3>
        <span class="badge {{ $audit['badge_class'] }}">{{ $audit['status'] }}</span>
      </div>
    </div>
    <div class="card-body p-4">
      <!-- Official Institutional Header (Printed PDF Only) -->
      <div class="d-none d-print-block text-center pb-3 border-bottom mb-4">
        <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
          <img src="{{ $university?->logo_url ?? $currentUniversity?->logo_url ?? asset('vendor/adminlte/assets/img/AdminLTELogo.png') }}" alt="Logo" style="height: 60px; width: 60px; object-fit: contain;">
          <div>
            <h4 class="fw-bold mb-0 text-uppercase tracking-wide" style="color: #0d47a1; letter-spacing: 1px;">{{ $university->name ?? 'BISHOP STUART UNIVERSITY' }}</h4>
            <h6 class="text-secondary mb-0">OFFICE OF THE ACADEMIC REGISTRAR &bull; SENATE EXAMINATIONS BOARD</h6>
          </div>
        </div>
        <div class="badge bg-primary text-white text-uppercase px-3 py-1 fs-6 mt-1">
          OFFICIAL GRADUATION CLEARANCE AUDIT SHEET
        </div>
      </div>

      <!-- Student Profile & Status Overview -->
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-7">
          <div class="p-3 bg-body-tertiary rounded border">
            <h6 class="fw-bold text-body border-bottom pb-2 mb-2">Student Identity &amp; Programme Details</h6>
            <div class="row g-2 small">
              <div class="col-6">
                <span class="text-muted">Full Name:</span>
                <div class="fw-bold fs-6">{{ $student->user->name ?? $student->full_name }}</div>
              </div>
              <div class="col-6">
                <span class="text-muted">Registration No:</span>
                <div class="fw-bold font-monospace fs-6">{{ $student->registration_number }}</div>
              </div>
              <div class="col-6">
                <span class="text-muted">Student Number:</span>
                <div class="fw-semibold font-monospace">{{ $student->student_number }}</div>
              </div>
              <div class="col-6">
                <span class="text-muted">Academic Programme:</span>
                <div class="fw-semibold">{{ $student->programme->name ?? 'N/A' }} ({{ $student->programme->code ?? '' }})</div>
              </div>
              <div class="col-6">
                <span class="text-muted">Faculty &amp; Dept:</span>
                <div>{{ $student->programme->department->faculty->name ?? 'N/A' }}</div>
              </div>
              <div class="col-6">
                <span class="text-muted">Curriculum Version:</span>
                <div>{{ $audit['curriculum']->version_name ?? 'Standard Active' }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-md-5">
          <!-- Clearance Verdict Box -->
          <div class="p-3 rounded border h-100 text-center d-flex flex-column justify-content-center {{ $audit['is_cleared'] ? 'bg-success-subtle border-success' : 'bg-danger-subtle border-danger' }}">
            <div class="mb-2">
              @if ($audit['is_cleared'])
                <i class="bi bi-patch-check-fill text-success" style="font-size: 2.8rem;"></i>
              @else
                <i class="bi bi-shield-x text-danger" style="font-size: 2.8rem;"></i>
              @endif
            </div>
            <h5 class="fw-bold {{ $audit['is_cleared'] ? 'text-success' : 'text-danger' }} mb-1">
              {{ $audit['status'] }}
            </h5>
            <div class="small fw-semibold text-secondary mb-2">
              Projected Award: <span class="badge {{ $audit['badge_class'] }} fs-6">{{ $audit['award_classification'] }}</span>
            </div>
            <div class="text-muted small">
              Cumulative CGPA: <strong class="fs-6 text-body">{{ number_format($audit['cgpa'], 2) }}</strong>
              <span class="text-muted small">(Pass Min: {{ number_format($audit['min_grad_cgpa'] ?? 2.0, 2) }})</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Deficiencies Alert List (If any) -->
      @if (! $audit['is_cleared'])
        <div class="callout callout-danger mb-4 shadow-sm">
          <h6 class="fw-bold text-danger mb-2">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> Outstanding Graduation Deficiencies ({{ count($audit['deficiencies']) }})
          </h6>
          <ul class="mb-0 small text-danger ps-3">
            @foreach ($audit['deficiencies'] as $deficiency)
              <li class="mb-1 fw-semibold">{{ $deficiency }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <!-- Academic Metrics Audit Bar -->
      <div class="row g-2 mb-4 text-center">
        <div class="col-3">
          <div class="p-2 border rounded bg-body-tertiary">
            <div class="text-muted small">Required Credits</div>
            <div class="fs-5 fw-bold text-body">{{ number_format($audit['required_credits'], 1) }} CU</div>
          </div>
        </div>
        <div class="col-3">
          <div class="p-2 border rounded bg-body-tertiary">
            <div class="text-muted small">Earned Credits</div>
            <div class="fs-5 fw-bold {{ $audit['earned_credits'] >= $audit['required_credits'] ? 'text-success' : 'text-danger' }}">
              {{ number_format($audit['earned_credits'], 1) }} CU
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="p-2 border rounded bg-body-tertiary">
            <div class="text-muted small">Credit Deficit</div>
            <div class="fs-5 fw-bold {{ $audit['credit_deficit'] > 0 ? 'text-danger' : 'text-success' }}">
              {{ number_format($audit['credit_deficit'], 1) }} CU
            </div>
          </div>
        </div>
        <div class="col-3">
          <div class="p-2 border rounded bg-body-tertiary">
            <div class="text-muted small">Courses Passed</div>
            <div class="fs-5 fw-bold text-primary">{{ $audit['passed_courses_count'] }}</div>
          </div>
        </div>
      </div>

      <!-- Core Courses Curriculum Checklist -->
      <div class="mb-4">
        <h6 class="fw-bold text-body border-bottom pb-2 mb-3">
          <i class="bi bi-check2-square me-1 text-primary"></i> Mandatory Core Course Completion Checklist
        </h6>
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle mb-0 small">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">Stage</th>
                <th style="width: 100px;">Course Code</th>
                <th>Course Unit Title</th>
                <th class="text-center" style="width: 80px;">Credits</th>
                <th class="text-center" style="width: 120px;">Audit Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($audit['core_courses_checklist'] as $core)
                <tr class="{{ $core['is_completed'] ? '' : 'table-danger-subtle' }}">
                  <td class="font-monospace fw-semibold">{{ $core['stage'] }}</td>
                  <td class="font-monospace fw-bold">{{ $core['course_unit']->code }}</td>
                  <td>{{ $core['course_unit']->name }}</td>
                  <td class="text-center">{{ number_format($core['credit_units'], 1) }}</td>
                  <td class="text-center">
                    @if ($core['is_completed'])
                      <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="bi bi-check-circle-fill me-1"></i> Completed
                      </span>
                    @else
                      <span class="badge bg-danger text-white px-2 py-1">
                        <i class="bi bi-x-circle-fill me-1"></i> Missing / Failed
                      </span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-3 text-muted">
                    No curriculum core courses mapped for this programme.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <!-- Unresolved Retakes (If any) -->
      @if (! empty($audit['unresolved_retakes']))
        <div class="mb-4">
          <h6 class="fw-bold text-danger border-bottom pb-2 mb-3">
            <i class="bi bi-arrow-repeat me-1 text-danger"></i> Unresolved Retakes &amp; Failed Course Units
          </h6>
          <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0 small">
              <thead class="table-danger">
                <tr>
                  <th>Course Code</th>
                  <th>Course Title</th>
                  <th class="text-center">Credit Units</th>
                  <th class="text-center">Attempts</th>
                  <th class="text-center">Last Grade</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($audit['unresolved_retakes'] as $retake)
                  <tr>
                    <td class="font-monospace fw-bold text-danger">{{ $retake['course_unit']->code }}</td>
                    <td>{{ $retake['course_unit']->name }}</td>
                    <td class="text-center">{{ number_format($retake['course_unit']->credit_units, 1) }}</td>
                    <td class="text-center">{{ $retake['attempts'] }}</td>
                    <td class="text-center fw-bold text-danger">{{ $retake['last_grade'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif

      <!-- Official Institutional Endorsement Block (Printed PDF Only) -->
      <div class="d-none d-print-block mt-5 pt-3 border-top page-break-inside-avoid">
        <div class="row text-center g-4">
          <div class="col-4">
            <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
            <div class="fw-bold small text-uppercase">Head of Department</div>
            <div class="text-muted" style="font-size: 0.72rem;">Signature &amp; Date</div>
          </div>
          <div class="col-4">
            <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
            <div class="fw-bold small text-uppercase">Dean of Faculty</div>
            <div class="text-muted" style="font-size: 0.72rem;">Signature &amp; Date</div>
          </div>
          <div class="col-4">
            <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
            <div class="fw-bold small text-uppercase">Academic Registrar</div>
            <div class="text-muted" style="font-size: 0.72rem;">Official Senate Seal &amp; Date</div>
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
      margin: 12mm;
    }
    .no-print, .main-header, .main-sidebar, .app-header, .app-sidebar, .app-footer, .breadcrumb {
      display: none !important;
    }
    .app-main, .content-wrapper, body {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }
    .print-card {
      border: none !important;
      box-shadow: none !important;
    }
    .page-break-inside-avoid {
      page-break-inside: avoid;
    }
  }
</style>
@endpush
