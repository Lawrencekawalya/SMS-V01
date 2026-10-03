@extends('layouts.app')

@section('title', 'Institutional Assessment & Grading Standards - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Institutional Assessment & Grading Standards')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item">Examinations &amp; Grading</li>
  <li class="breadcrumb-item active" aria-current="page">Grading Policy &amp; Standards</li>
@endsection

@section('content')
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Configuration Error:</div>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Top Hero & Navigation Bar -->
  <div class="card card-outline card-primary mb-4 shadow-sm">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <span class="badge text-bg-primary fs-6">NCHE Statutory Standard</span>
          <span class="badge text-bg-success fs-6 ms-1">Active University Policy</span>
          <h4 class="mb-0 mt-2 fw-bold">Academic Assessment, Examination &amp; Grading Policy</h4>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('assessment.list') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-card-checklist me-1"></i> Course Mark Sheets Directory
            <i class="bi bi-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <p class="text-body-secondary mb-0">
        In accordance with the <strong>National Council for Higher Education (NCHE)</strong> statutory framework and university Senate regulations, student academic achievement is evaluated using a continuous assessment and final examination weighting model combined with an institutional <strong>5.0 Maximum Grade Point (GP)</strong> scale. All academic programmes (Bachelor's Degrees, Postgraduate, Diplomas, and Certificates) adhere strictly to these institutional thresholds.
      </p>
    </div>
  </div>

  <!-- Policy Key Metrics KPI Row -->
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-journal-text"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Continuous Assessment (CA)</span>
          <span class="info-box-number fs-3">{{ number_format($caWeight, 1) }}%</span>
          <div class="progress">
            <div class="progress-bar bg-info" style="width: {{ $caWeight }}%"></div>
          </div>
          <span class="progress-description text-muted small">Coursework, tests &amp; practicals</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-pencil-square"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Final Examination</span>
          <span class="info-box-number fs-3">{{ number_format($examWeight, 1) }}%</span>
          <div class="progress">
            <div class="progress-bar bg-primary" style="width: {{ $examWeight }}%"></div>
          </div>
          <span class="progress-description text-muted small">End of semester examination</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Minimum Pass Mark</span>
          <span class="info-box-number fs-3">{{ number_format($passMark, 1) }}%</span>
          <div class="progress">
            <div class="progress-bar bg-success" style="width: {{ $passMark }}%"></div>
          </div>
          <span class="progress-description text-muted small">NCHE collegiate standard pass</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-dark shadow-sm"><i class="bi bi-award-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Grading Scale Engine</span>
          <span class="info-box-number fs-3">5.0 Scale</span>
          <div class="progress">
            <div class="progress-bar bg-dark" style="width: 100%"></div>
          </div>
          <span class="progress-description text-muted small">8 letter grade tiers (A to F)</span>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: Assessment Weights Form & Degree / Diploma / Certificate Classifications -->
    <div class="col-12 col-lg-5">
      <!-- Policy Weights Editor Form Card -->
      <div class="card card-outline card-secondary shadow-sm mb-4">
        <div class="card-header">
          <h3 class="card-title mb-0">
            <i class="bi bi-sliders me-1"></i> Configure Assessment Policy
          </h3>
        </div>
        <div class="card-body">
          <form action="{{ route('assessment.policy.update') }}" method="POST" id="policy-form">
            @csrf

            <div class="mb-3">
              <label for="ca_weight" class="form-label fw-semibold">
                Continuous Assessment (CA) Weight (%)
              </label>
              <div class="input-group">
                <input
                  type="number"
                  step="0.5"
                  min="0"
                  max="100"
                  name="ca_weight"
                  id="ca_weight"
                  class="form-control @error('ca_weight') is-invalid @enderror"
                  value="{{ old('ca_weight', $caWeight) }}"
                  required
                >
                <span class="input-group-text">%</span>
              </div>
              <small class="text-body-secondary">Weight allocated to assignments, coursework, tests, and lab practicals.</small>
              @error('ca_weight')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="exam_weight" class="form-label fw-semibold">
                Final Examination Weight (%)
              </label>
              <div class="input-group">
                <input
                  type="number"
                  step="0.5"
                  min="0"
                  max="100"
                  name="exam_weight"
                  id="exam_weight"
                  class="form-control @error('exam_weight') is-invalid @enderror"
                  value="{{ old('exam_weight', $examWeight) }}"
                  required
                >
                <span class="input-group-text">%</span>
              </div>
              <small class="text-body-secondary">Weight allocated to the formal invigilated end-of-semester examination paper.</small>
              @error('exam_weight')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <!-- Zero-Sum Verification Box -->
            <div class="p-3 rounded bg-body-secondary mb-3 border">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Total Composite Weight:</span>
                <span id="total-weight-display" class="fw-bold fs-5 text-success">100.0%</span>
              </div>
              <div id="weight-alert" class="small mt-1 text-success">
                <i class="bi bi-check-circle-fill me-1"></i> Weights satisfy the 100% total balance rule.
              </div>
            </div>

            <div class="mb-4">
              <label for="pass_mark" class="form-label fw-semibold">
                Minimum Pass Mark Threshold (%)
              </label>
              <div class="input-group">
                <input
                  type="number"
                  step="0.5"
                  min="0"
                  max="100"
                  name="pass_mark"
                  id="pass_mark"
                  class="form-control @error('pass_mark') is-invalid @enderror"
                  value="{{ old('pass_mark', $passMark) }}"
                  required
                >
                <span class="input-group-text">%</span>
              </div>
              <small class="text-body-secondary">Scores below this mark are classified as 'F' (Fail) requiring course retake.</small>
              @error('pass_mark')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="d-grid">
              <button type="submit" id="save-policy-btn" class="btn btn-primary">
                <i class="bi bi-save me-1"></i> Save Institutional Policy
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Academic Award & Honours Classifications Card (Degree, Diploma, Certificate) -->
      <div class="card card-outline card-info shadow-sm">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h3 class="card-title mb-0">
              <i class="bi bi-mortarboard me-1"></i> Award &amp; Honours Classifications
            </h3>
            <div class="d-flex gap-1">
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAwardsModal">
                <i class="bi bi-pencil-square me-1"></i> Edit
              </button>
              <form action="{{ route('assessment.policy.awards.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Reset all award classifications to standard NCHE collegiate defaults?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Reset to Defaults">
                  <i class="bi bi-arrow-counterclockwise"></i>
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="card-body p-0">
          <!-- Level Tabs: Degree/Postgrad, Diploma, Certificate -->
          <ul class="nav nav-tabs px-3 pt-2 bg-body-tertiary" id="awardsTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active py-2" id="degree-tab" data-bs-toggle="tab" data-bs-target="#degree-tab-pane" type="button" role="tab">
                <i class="bi bi-award me-1"></i> Degree / Postgrad
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link py-2" id="diploma-tab" data-bs-toggle="tab" data-bs-target="#diploma-tab-pane" type="button" role="tab">
                <i class="bi bi-journal-check me-1"></i> Diploma
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link py-2" id="certificate-tab" data-bs-toggle="tab" data-bs-target="#certificate-tab-pane" type="button" role="tab">
                <i class="bi bi-file-earmark-text me-1"></i> Certificate
              </button>
            </li>
          </ul>

          <div class="tab-content" id="awardsTabContent">
            <!-- Tab 1: Degree & Postgraduate (Honours Scale) -->
            <div class="tab-pane fade show active" id="degree-tab-pane" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                  <thead>
                    <tr class="table-light">
                      <th class="ps-3">Cumulative CGPA</th>
                      <th>Degree Award</th>
                      <th class="text-end pe-3">Standing</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($degreeClassifications as $class)
                      <tr>
                        <td class="ps-3 font-monospace fw-bold">
                          {{ number_format($class->min_cgpa, 2) }} – {{ number_format($class->max_cgpa, 2) }}
                        </td>
                        <td>
                          <span class="badge {{ $class->badge_class }} me-1">
                            {{ $class->name }}
                          </span>
                        </td>
                        <td class="text-end pe-3">
                          <span class="badge {{ $class->academic_standing === 'Normal Progress' ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $class->academic_standing }}
                          </span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="p-2 px-3 bg-body-tertiary small text-body-secondary border-top">
                <i class="bi bi-info-circle me-1"></i> Standard Uganda NCHE Honours classification for Bachelor's Degrees, Postgraduate Diplomas &amp; Masters.
              </div>
            </div>

            <!-- Tab 2: Ordinary Diploma (Class I, II, III Scale) -->
            <div class="tab-pane fade" id="diploma-tab-pane" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                  <thead>
                    <tr class="table-light">
                      <th class="ps-3">Cumulative CGPA</th>
                      <th>Diploma Award</th>
                      <th class="text-end pe-3">Standing</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($diplomaClassifications as $class)
                      <tr>
                        <td class="ps-3 font-monospace fw-bold">
                          {{ number_format($class->min_cgpa, 2) }} – {{ number_format($class->max_cgpa, 2) }}
                        </td>
                        <td>
                          <span class="badge {{ $class->badge_class }} me-1">
                            {{ $class->name }}
                          </span>
                        </td>
                        <td class="text-end pe-3">
                          <span class="badge {{ $class->academic_standing === 'Normal Progress' ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $class->academic_standing }}
                          </span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="p-2 px-3 bg-body-tertiary small text-body-secondary border-top">
                <i class="bi bi-info-circle me-1"></i> Collegiate classification for Undergraduate Diplomas (Class I Distinction, Class II Credit, Class III Pass).
              </div>
            </div>

            <!-- Tab 3: Ordinary Certificate -->
            <div class="tab-pane fade" id="certificate-tab-pane" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                  <thead>
                    <tr class="table-light">
                      <th class="ps-3">Cumulative CGPA</th>
                      <th>Certificate Award</th>
                      <th class="text-end pe-3">Standing</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($certificateClassifications as $class)
                      <tr>
                        <td class="ps-3 font-monospace fw-bold">
                          {{ number_format($class->min_cgpa, 2) }} – {{ number_format($class->max_cgpa, 2) }}
                        </td>
                        <td>
                          <span class="badge {{ $class->badge_class }} me-1">
                            {{ $class->name }}
                          </span>
                        </td>
                        <td class="text-end pe-3">
                          <span class="badge {{ $class->academic_standing === 'Normal Progress' ? 'text-bg-success' : 'text-bg-danger' }}">
                            {{ $class->academic_standing }}
                          </span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="p-2 px-3 bg-body-tertiary small text-body-secondary border-top">
                <i class="bi bi-info-circle me-1"></i> Standard classification for Certificate programmes (Distinction, Credit, Pass).
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Official NCHE 5.0 Grading Scale Table (Editable) -->
    <div class="col-12 col-lg-7">
      <div class="card card-outline card-success shadow-sm mb-4">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <h3 class="card-title mb-0">
                <i class="bi bi-table me-1"></i> Grading Scale (NCHE 5.0 Standard)
              </h3>
              <span class="badge text-bg-success ms-2">{{ $gradingScale->count() }} Tiers (A to F)</span>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editGradingScaleModal">
                <i class="bi bi-pencil-square me-1"></i> Edit Scale Tiers
              </button>
              <form action="{{ route('assessment.policy.scale.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Reset all grading scale tiers to standard NCHE 5.0 defaults?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Reset to Defaults">
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </button>
              </form>
            </div>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr class="table-light text-uppercase fs-7">
                  <th class="ps-3">Mark Range</th>
                  <th class="text-center">Grade</th>
                  <th class="text-center">Grade Point (GP)</th>
                  <th>Classification</th>
                  <th class="text-end pe-3">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($gradingScale as $tier)
                  @php
                    $isPassed = $tier->is_pass;
                  @endphp
                  <tr class="{{ ! $isPassed ? 'table-danger-subtle' : '' }}">
                    <td class="ps-3 font-monospace fw-bold fs-6">
                      {{ number_format($tier->min_score, 1) }}% – {{ number_format($tier->max_score, 1) }}%
                    </td>
                    <td class="text-center">
                      <span class="badge {{ $tier->badge_class }} fs-6 px-3">
                        {{ $tier->grade_letter }}
                      </span>
                    </td>
                    <td class="text-center font-monospace fw-bold fs-6">
                      {{ number_format($tier->grade_point, 1) }}
                    </td>
                    <td>
                      <div class="fw-semibold">{{ $tier->classification }}</div>
                    </td>
                    <td class="text-end pe-3">
                      @if ($isPassed)
                        <span class="badge text-bg-success">
                          <i class="bi bi-check-circle me-1"></i> Pass
                        </span>
                      @else
                        <span class="badge text-bg-danger">
                          <i class="bi bi-x-circle me-1"></i> Fail (Retake)
                        </span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-body-tertiary">
          <div class="row text-center g-2">
            <div class="col-4 border-end">
              <span class="text-muted small d-block">Distinction Grade</span>
              <strong class="text-success fs-6">
                {{ number_format($gradingScale->first()->min_score ?? 80.0, 1) }}% – 100.0% (A)
              </strong>
            </div>
            <div class="col-4 border-end">
              <span class="text-muted small d-block">Minimum Pass Mark</span>
              <strong class="text-primary fs-6">{{ number_format($passMark, 1) }}%</strong>
            </div>
            <div class="col-4">
              <span class="text-muted small d-block">Retake Threshold</span>
              <strong class="text-danger fs-6">&lt; {{ number_format($passMark, 1) }}% (F)</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Assessment Calculation Formulas & Governance -->
      <div class="card card-outline card-warning shadow-sm">
        <div class="card-header">
          <h3 class="card-title mb-0">
            <i class="bi bi-calculator me-1"></i> Automated Score &amp; GPA Calculation Formulas
          </h3>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="p-3 border rounded bg-body-tertiary h-100">
                <h6 class="fw-bold text-primary mb-2">
                  <i class="bi bi-1-circle me-1"></i> Course Unit Final Score
                </h6>
                <p class="font-monospace small mb-1 bg-body p-2 rounded border">
                  Final Score [/100] = CA [/{{ number_format($caWeight, 0) }}] + Exam [/{{ number_format($examWeight, 0) }}]
                </p>
                <small class="text-muted">
                  Continuous assessment (Coursework, assignments, tests) is added directly to the final exam score to produce total score out of 100%.
                </small>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <div class="p-3 border rounded bg-body-tertiary h-100">
                <h6 class="fw-bold text-success mb-2">
                  <i class="bi bi-2-circle me-1"></i> Semester GPA Formula
                </h6>
                <p class="font-monospace small mb-1 bg-body p-2 rounded border">
                  GPA = &Sigma; (Credit Units &times; GP) / &Sigma; Registered CU
                </p>
                <small class="text-muted">
                  Each course unit's credit weight is multiplied by the earned Grade Point, summed, and divided by total registered credits.
                </small>
              </div>
            </div>
          </div>

          <div class="alert alert-light border mt-3 mb-0">
            <div class="d-flex">
              <i class="bi bi-shield-lock-fill text-warning fs-4 me-2"></i>
              <div>
                <strong>Academic Governance &amp; Audit Trail:</strong>
                All grade entries and score adjustments made after initial submission are permanently logged in the <code>grade_audit_logs</code> table with the user ID, previous score, new score, and mandatory academic justification.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal 1: Edit Grading Scale Tiers -->
  <div class="modal fade" id="editGradingScaleModal" tabindex="-1" aria-labelledby="editGradingScaleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <form action="{{ route('assessment.policy.scale.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="editGradingScaleModalLabel">
              <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Grading Scale Tiers (NCHE 5.0 Standard)
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-0">
            <div class="p-3 bg-body-tertiary border-bottom small text-body-secondary">
              <i class="bi bi-info-circle me-1"></i> Adjust the mark ranges, letter grades, grade points (GP), and classifications to match your university's academic charter.
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 90px;" class="text-center">Grade</th>
                    <th style="width: 140px;">Min Score (%)</th>
                    <th style="width: 140px;">Max Score (%)</th>
                    <th style="width: 130px;" class="text-center">Grade Point</th>
                    <th>Classification Title</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($gradingScale as $tier)
                    <tr>
                      <td class="text-center">
                        <input type="hidden" name="tiers[{{ $loop->index }}][id]" value="{{ $tier->id }}">
                        <input
                          type="text"
                          name="tiers[{{ $loop->index }}][grade_letter]"
                          value="{{ $tier->grade_letter }}"
                          class="form-control form-control-sm text-center font-monospace fw-bold"
                          maxlength="5"
                          required
                        >
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="tiers[{{ $loop->index }}][min_score]"
                            value="{{ $tier->min_score }}"
                            class="form-control form-control-sm font-monospace"
                            required
                          >
                          <span class="input-group-text">%</span>
                        </div>
                      </td>
                      <td>
                        <div class="input-group input-group-sm">
                          <input
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            name="tiers[{{ $loop->index }}][max_score]"
                            value="{{ $tier->max_score }}"
                            class="form-control form-control-sm font-monospace"
                            required
                          >
                          <span class="input-group-text">%</span>
                        </div>
                      </td>
                      <td>
                        <input
                          type="number"
                          step="0.1"
                          min="0"
                          max="5"
                          name="tiers[{{ $loop->index }}][grade_point]"
                          value="{{ $tier->grade_point }}"
                          class="form-control form-control-sm text-center font-monospace fw-bold"
                          required
                        >
                      </td>
                      <td>
                        <input
                          type="text"
                          name="tiers[{{ $loop->index }}][classification]"
                          value="{{ $tier->classification }}"
                          class="form-control form-control-sm"
                          required
                        >
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="modal-footer bg-body-tertiary">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i> Save Grading Scale Tiers
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal 2: Edit Award Classifications -->
  <div class="modal fade" id="editAwardsModal" tabindex="-1" aria-labelledby="editAwardsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <form action="{{ route('assessment.policy.awards.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="editAwardsModalLabel">
              <i class="bi bi-mortarboard me-2 text-info"></i>Edit Academic Award Classifications
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-0">
            <div class="p-3 bg-body-tertiary border-bottom small text-body-secondary">
              <i class="bi bi-info-circle me-1"></i> Configure CGPA ranges, award titles, and academic standing across Bachelor's/Postgraduate, Diploma, and Certificate programmes.
            </div>

            <!-- Modal Sub-Tabs -->
            <ul class="nav nav-pills p-3 border-bottom bg-body" id="modalAwardsTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active btn-sm" id="modal-degree-tab" data-bs-toggle="pill" data-bs-target="#modal-degree-pane" type="button" role="tab">
                  🎓 Bachelor's &amp; Postgraduate
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm ms-2" id="modal-diploma-tab" data-bs-toggle="pill" data-bs-target="#modal-diploma-pane" type="button" role="tab">
                  📜 Undergraduate Diploma
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm ms-2" id="modal-certificate-tab" data-bs-toggle="pill" data-bs-target="#modal-certificate-pane" type="button" role="tab">
                  📑 Certificate
                </button>
              </li>
            </ul>

            @php
              $allAwards = $degreeClassifications->concat($diplomaClassifications)->concat($certificateClassifications);
            @endphp

            <div class="tab-content p-3" id="modalAwardsTabContent">
              <!-- Modal Tab 1: Degree & Postgraduate -->
              <div class="tab-pane fade show active" id="modal-degree-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 140px;">Min CGPA</th>
                        <th style="width: 140px;">Max CGPA</th>
                        <th>Degree Award Title</th>
                        <th style="width: 180px;">Standing</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($degreeClassifications as $class)
                        <tr>
                          <td>
                            <input type="hidden" name="awards[{{ $class->id }}][id]" value="{{ $class->id }}">
                            <input
                              type="number"
                              step="0.01"
                              min="0"
                              max="5"
                              name="awards[{{ $class->id }}][min_cgpa]"
                              value="{{ $class->min_cgpa }}"
                              class="form-control form-control-sm font-monospace text-center"
                              required
                            >
                          </td>
                          <td>
                            <input
                              type="number"
                              step="0.01"
                              min="0"
                              max="5"
                              name="awards[{{ $class->id }}][max_cgpa]"
                              value="{{ $class->max_cgpa }}"
                              class="form-control form-control-sm font-monospace text-center"
                              required
                            >
                          </td>
                          <td>
                            <input
                              type="text"
                              name="awards[{{ $class->id }}][name]"
                              value="{{ $class->name }}"
                              class="form-control form-control-sm fw-semibold"
                              required
                            >
                          </td>
                          <td>
                            <select name="awards[{{ $class->id }}][academic_standing]" class="form-select form-select-sm">
                              <option value="Normal Progress" {{ $class->academic_standing === 'Normal Progress' ? 'selected' : '' }}>Normal Progress</option>
                              <option value="Probation" {{ $class->academic_standing === 'Probation' ? 'selected' : '' }}>Probation</option>
                              <option value="Discontinued" {{ $class->academic_standing === 'Discontinued' ? 'selected' : '' }}>Discontinued</option>
                            </select>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Modal Tab 2: Diploma -->
              <div class="tab-pane fade" id="modal-diploma-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 140px;">Min CGPA</th>
                        <th style="width: 140px;">Max CGPA</th>
                        <th>Diploma Award Title</th>
                        <th style="width: 180px;">Standing</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($diplomaClassifications as $class)
                        <tr>
                          <td>
                            <input type="hidden" name="awards[{{ $class->id }}][id]" value="{{ $class->id }}">
                            <input
                              type="number"
                              step="0.01"
                              min="0"
                              max="5"
                              name="awards[{{ $class->id }}][min_cgpa]"
                              value="{{ $class->min_cgpa }}"
                              class="form-control form-control-sm font-monospace text-center"
                              required
                            >
                          </td>
                          <td>
                            <input
                              type="number"
                              step="0.01"
                              min="0"
                              max="5"
                              name="awards[{{ $class->id }}][max_cgpa]"
                              value="{{ $class->max_cgpa }}"
                              class="form-control form-control-sm font-monospace text-center"
                              required
                            >
                          </td>
                          <td>
                            <input
                              type="text"
                              name="awards[{{ $class->id }}][name]"
                              value="{{ $class->name }}"
                              class="form-control form-control-sm fw-semibold"
                              required
                            >
                          </td>
                          <td>
                            <select name="awards[{{ $class->id }}][academic_standing]" class="form-select form-select-sm">
                              <option value="Normal Progress" {{ $class->academic_standing === 'Normal Progress' ? 'selected' : '' }}>Normal Progress</option>
                              <option value="Probation" {{ $class->academic_standing === 'Probation' ? 'selected' : '' }}>Probation</option>
                              <option value="Discontinued" {{ $class->academic_standing === 'Discontinued' ? 'selected' : '' }}>Discontinued</option>
                            </select>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Modal Tab 3: Certificate -->
              <div class="tab-pane fade" id="modal-certificate-pane" role="tabpanel">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 140px;">Min CGPA</th>
                        <th style="width: 140px;">Max CGPA</th>
                        <th>Certificate Award Title</th>
                        <th style="width: 180px;">Standing</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($certificateClassifications as $class)
                        <tr>
                          <td>
                            <input type="hidden" name="awards[{{ $class->id }}][id]" value="{{ $class->id }}">
                            <input
                              type="number"
                              step="0.01"
                              min="0"
                              max="5"
                              name="awards[{{ $class->id }}][min_cgpa]"
                              value="{{ $class->min_cgpa }}"
                              class="form-control form-control-sm font-monospace text-center"
                              required
                            >
                          </td>
                          <td>
                            <input
                              type="number"
                              step="0.01"
                              min="0"
                              max="5"
                              name="awards[{{ $class->id }}][max_cgpa]"
                              value="{{ $class->max_cgpa }}"
                              class="form-control form-control-sm font-monospace text-center"
                              required
                            >
                          </td>
                          <td>
                            <input
                              type="text"
                              name="awards[{{ $class->id }}][name]"
                              value="{{ $class->name }}"
                              class="form-control form-control-sm fw-semibold"
                              required
                            >
                          </td>
                          <td>
                            <select name="awards[{{ $class->id }}][academic_standing]" class="form-select form-select-sm">
                              <option value="Normal Progress" {{ $class->academic_standing === 'Normal Progress' ? 'selected' : '' }}>Normal Progress</option>
                              <option value="Probation" {{ $class->academic_standing === 'Probation' ? 'selected' : '' }}>Probation</option>
                              <option value="Discontinued" {{ $class->academic_standing === 'Discontinued' ? 'selected' : '' }}>Discontinued</option>
                            </select>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-body-tertiary">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-info text-white">
              <i class="bi bi-save me-1"></i> Save Award Classifications
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
      const caInput = document.getElementById('ca_weight');
      const examInput = document.getElementById('exam_weight');
      const totalDisplay = document.getElementById('total-weight-display');
      const weightAlert = document.getElementById('weight-alert');
      const saveBtn = document.getElementById('save-policy-btn');

      function validateWeights() {
        const ca = parseFloat(caInput.value) || 0;
        const exam = parseFloat(examInput.value) || 0;
        const total = (ca + exam).toFixed(1);

        totalDisplay.textContent = total + '%';

        if (Math.abs(parseFloat(total) - 100.0) < 0.01) {
          totalDisplay.className = 'fw-bold fs-5 text-success';
          weightAlert.className = 'small mt-1 text-success';
          weightAlert.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Weights satisfy the 100% total balance rule.';
          saveBtn.disabled = false;
        } else {
          totalDisplay.className = 'fw-bold fs-5 text-danger';
          weightAlert.className = 'small mt-1 text-danger';
          weightAlert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Total must equal 100.0% (Current: ' + total + '%). Adjust CA or Exam weight.';
          saveBtn.disabled = true;
        }
      }

      caInput.addEventListener('input', validateWeights);
      examInput.addEventListener('input', validateWeights);
      validateWeights();
    });
  </script>
@endpush
