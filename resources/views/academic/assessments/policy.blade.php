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

  @php
    $activeTab = ($errors->any() || session('active_tab') === 'configuration') ? 'configuration' : 'overview';
  @endphp

  <!-- Primary Navigation Tabs (Reduces Info Overload) -->
  <ul class="nav nav-pills mb-4 p-2 bg-body-secondary rounded border shadow-sm gap-2" id="policyMainTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button
        class="nav-link {{ $activeTab === 'overview' ? 'active' : '' }} fw-bold px-4 py-2 fs-6"
        id="tab-overview"
        data-bs-toggle="pill"
        data-bs-target="#pane-overview"
        type="button"
        role="tab"
        aria-controls="pane-overview"
        aria-selected="{{ $activeTab === 'overview' ? 'true' : 'false' }}"
      >
        <i class="bi bi-table me-2"></i>Grading Scale &amp; Policy Standards
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button
        class="nav-link {{ $activeTab === 'configuration' ? 'active' : '' }} fw-bold px-4 py-2 fs-6"
        id="tab-configuration"
        data-bs-toggle="pill"
        data-bs-target="#pane-configuration"
        type="button"
        role="tab"
        aria-controls="pane-configuration"
        aria-selected="{{ $activeTab === 'configuration' ? 'true' : 'false' }}"
      >
        <i class="bi bi-sliders me-2"></i>Policy Configuration &amp; Award Classifications
      </button>
    </li>
  </ul>

  <!-- Main Tabs Content Container -->
  <div class="tab-content" id="policyMainTabsContent">

    <!-- ============================================================= -->
    <!-- TAB 1: GRADING SCALE & POLICY STANDARDS (Attached Screenshots) -->
    <!-- ============================================================= -->
    <div class="tab-pane fade {{ $activeTab === 'overview' ? 'show active' : '' }}" id="pane-overview" role="tabpanel" aria-labelledby="tab-overview">

      <!-- Screenshot 2 Top: Hero & Navigation Bar -->
      <div class="card card-outline card-primary mb-4 shadow-sm">
        <div class="card-header py-3">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <div class="mb-1">
                <span class="badge text-bg-primary fs-6">NCHE Statutory Standard</span>
                <span class="badge text-bg-success fs-6 ms-1">Active University Policy</span>
              </div>
              <h4 class="mb-0 mt-2 fw-bold d-inline-flex align-items-center flex-wrap">
                <span>Academic Assessment, Examination &amp; Grading Policy</span>
                <span
                  class="d-inline-flex align-items-center text-primary ms-2 cursor-pointer"
                  tabindex="0"
                  role="button"
                  data-bs-toggle="tooltip"
                  data-bs-placement="right"
                  data-bs-custom-class="policy-tooltip"
                  title="In accordance with the National Council for Higher Education (NCHE) statutory framework and university Senate regulations, student academic achievement is evaluated using a continuous assessment and final examination weighting model combined with an institutional 5.0 Maximum Grade Point (GP) scale. All academic programmes (Bachelor's Degrees, Postgraduate, Diplomas, and Certificates) adhere strictly to these institutional thresholds."
                  aria-label="Statutory assessment policy framework details"
                >
                  <i class="bi bi-info-circle fs-5"></i>
                </span>
              </h4>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('assessment.list') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-card-checklist me-1"></i> Course Mark Sheets Directory
                <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Screenshot 2 Bottom: Policy Key Metrics KPI Row -->
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
              <span class="info-box-text">Minimum Pass Marks</span>
              <span class="info-box-number fs-4">{{ number_format($passMark, 0) }}% <span class="fs-6 fw-normal text-muted">UG</span> / {{ number_format($postgradPassMark, 0) }}% <span class="fs-6 fw-normal text-muted">PG</span></span>
              <div class="progress">
                <div class="progress-bar bg-success" style="width: {{ $passMark }}%"></div>
              </div>
              <span class="progress-description text-muted small">UG: 50.0% &bull; Postgrad: 60.0%</span>
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

      <!-- Screenshot 1 Top: Official NCHE 5.0 Grading Scale Table (Editable) -->
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

      <!-- Screenshot 1 Bottom: Assessment Calculation Formulas & Governance -->
      <div class="card card-outline card-warning shadow-sm mb-3">
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

    <!-- ========================================================================= -->
    <!-- TAB 2: POLICY CONFIGURATION & AWARDS (Unified per Honours/Award Level)    -->
    <!-- ========================================================================= -->
    <div class="tab-pane fade {{ $activeTab === 'configuration' ? 'show active' : '' }}" id="pane-configuration" role="tabpanel" aria-labelledby="tab-configuration">
      
      <!-- Academic Award Classifications & Level Assessment Policies Card -->
      <div class="card card-outline card-info shadow-sm mb-4">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <h3 class="card-title mb-0 fw-bold">
                <i class="bi bi-mortarboard me-1"></i> Award &amp; Honours Classifications &amp; Assessment Policies
              </h3>
              <span class="text-body-secondary small ms-2 d-none d-md-inline">
                Each tertiary tier enforces its own assessment weighting, pass mark, and CGPA Honours classification
              </span>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editAwardsModal">
                <i class="bi bi-pencil-square me-1"></i> Edit Policies &amp; Awards
              </button>
              <form action="{{ route('assessment.policy.awards.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Reset all award classifications and level assessment policies to standard NCHE collegiate defaults?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Reset All to Defaults">
                  <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Defaults
                </button>
              </form>
            </div>
          </div>
        </div>

        <div class="card-body p-0">
          <!-- Level Tabs: Undergraduate, Postgraduate, Diploma, Certificate -->
          <ul class="nav nav-tabs px-3 pt-2 bg-body-tertiary" id="awardsTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active py-2" id="undergraduate-tab" data-bs-toggle="tab" data-bs-target="#undergraduate-tab-pane" type="button" role="tab">
                <i class="bi bi-mortarboard me-1"></i> Undergraduate (Bachelor's)
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link py-2" id="postgraduate-tab" data-bs-toggle="tab" data-bs-target="#postgraduate-tab-pane" type="button" role="tab">
                <i class="bi bi-mortarboard-fill me-1"></i> Postgraduate (Master's / PGD)
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
            
            <!-- Tab 1: Undergraduate Degrees & Honours -->
            <div class="tab-pane fade show active" id="undergraduate-tab-pane" role="tabpanel">
              <!-- Assessment Policy Bar for Undergraduate -->
              <div class="p-3 bg-body-secondary border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                  <div class="fw-bold text-primary small">
                    <i class="bi bi-sliders me-1"></i> Assessment Policy for Undergraduate Programmes (Bachelor's)
                  </div>
                  <span class="badge text-bg-light border ">Statutory NCHE Model</span>
                </div>
                <div class="row g-2 text-center">
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">CA Weight</small>
                      <span class="fs-5 fw-bold text-info">{{ number_format($degreePolicy->ca_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Final Exam</small>
                      <span class="fs-5 fw-bold text-primary">{{ number_format($degreePolicy->exam_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Pass Mark Threshold</small>
                      <span class="fs-5 fw-bold text-success">{{ number_format($degreePolicy->pass_mark, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Retake Policy</small>
                      <span class="fw-semibold text-truncate d-block small mt-1">
                        {{ $degreePolicy->retake_cgpa_policy === 'replace_fail' ? 'Grade Replacement' : 'Cumulative Sum' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Undergraduate Classifications Table -->
              <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                  <thead>
                    <tr class="table-light">
                      <th class="ps-3">Cumulative CGPA</th>
                      <th>Undergraduate Honours Award</th>
                      <th class="text-end pe-3">Academic Standing</th>
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
                <i class="bi bi-info-circle me-1"></i> Standard Uganda NCHE Honours classification for Bachelor's Degrees (First Class, Second Upper, Second Lower, Pass Degree).
              </div>
            </div>

            <!-- Tab 2: Postgraduate Standards (Master's, PGD, PhD Scale) -->
            <div class="tab-pane fade" id="postgraduate-tab-pane" role="tabpanel">
              <!-- Assessment Policy Bar for Postgraduate -->
              <div class="p-3 bg-body-secondary border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                  <div class="fw-bold text-primary small">
                    <i class="bi bi-sliders me-1"></i> Assessment Policy for Postgraduate Programmes (Master's, PGD, PhD)
                  </div>
                  <span class="badge text-bg-warning text-dark border">Higher Threshold</span>
                </div>
                <div class="row g-2 text-center">
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">CA Weight</small>
                      <span class="fs-5 fw-bold text-info">{{ number_format($postgradPolicy->ca_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Final Exam</small>
                      <span class="fs-5 fw-bold text-primary">{{ number_format($postgradPolicy->exam_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Pass Mark Threshold</small>
                      <span class="fs-5 fw-bold text-warning text-dark">{{ number_format($postgradPolicy->pass_mark, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Retake Policy</small>
                      <span class="fw-semibold text-truncate d-block small mt-1">
                        {{ $postgradPolicy->retake_cgpa_policy === 'replace_fail' ? 'Grade Replacement' : 'Cumulative Sum' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Postgraduate Classifications Table -->
              <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                  <thead>
                    <tr class="table-light">
                      <th class="ps-3">Cumulative CGPA</th>
                      <th>Postgraduate Award</th>
                      <th class="text-end pe-3">Academic Standing</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($postgraduateClassifications as $class)
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
                <i class="bi bi-info-circle me-1"></i> Postgraduate regulations: Distinction (4.40+), Credit (3.60+), Pass (3.00+). No Second Class Lower is awarded.
              </div>
            </div>

            <!-- Tab 3: Ordinary Diploma -->
            <div class="tab-pane fade" id="diploma-tab-pane" role="tabpanel">
              <!-- Assessment Policy Bar for Diploma -->
              <div class="p-3 bg-body-secondary border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                  <div class="fw-bold text-primary small">
                    <i class="bi bi-sliders me-1"></i> Assessment Policy for Undergraduate Diplomas
                  </div>
                  <span class="badge text-bg-light border text-muted">Class I, II, III Scale</span>
                </div>
                <div class="row g-2 text-center">
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">CA Weight</small>
                      <span class="fs-5 fw-bold text-info">{{ number_format($diplomaPolicy->ca_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Final Exam</small>
                      <span class="fs-5 fw-bold text-primary">{{ number_format($diplomaPolicy->exam_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Pass Mark Threshold</small>
                      <span class="fs-5 fw-bold text-success">{{ number_format($diplomaPolicy->pass_mark, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Retake Policy</small>
                      <span class="fw-semibold text-truncate d-block small mt-1">
                        {{ $diplomaPolicy->retake_cgpa_policy === 'replace_fail' ? 'Grade Replacement' : 'Cumulative Sum' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Diploma Classifications Table -->
              <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                  <thead>
                    <tr class="table-light">
                      <th class="ps-3">Cumulative CGPA</th>
                      <th>Diploma Award</th>
                      <th class="text-end pe-3">Academic Standing</th>
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

            <!-- Tab 4: Ordinary Certificate -->
            <div class="tab-pane fade" id="certificate-tab-pane" role="tabpanel">
              <!-- Assessment Policy Bar for Certificate -->
              <div class="p-3 bg-body-secondary border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                  <div class="fw-bold text-primary small">
                    <i class="bi bi-sliders me-1"></i> Assessment Policy for Certificate Programmes
                  </div>
                  <span class="badge text-bg-light border text-muted">Cert Scale</span>
                </div>
                <div class="row g-2 text-center">
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">CA Weight</small>
                      <span class="fs-5 fw-bold text-info">{{ number_format($certificatePolicy->ca_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Final Exam</small>
                      <span class="fs-5 fw-bold text-primary">{{ number_format($certificatePolicy->exam_weight, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Pass Mark Threshold</small>
                      <span class="fs-5 fw-bold text-success">{{ number_format($certificatePolicy->pass_mark, 1) }}%</span>
                    </div>
                  </div>
                  <div class="col-6 col-md-3">
                    <div class="p-2 rounded bg-body border shadow-sm">
                      <small class="text-body-secondary d-block">Retake Policy</small>
                      <span class="fw-semibold text-truncate d-block small mt-1">
                        {{ $certificatePolicy->retake_cgpa_policy === 'replace_fail' ? 'Grade Replacement' : 'Cumulative Sum' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Certificate Classifications Table -->
              <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                  <thead>
                    <tr class="table-light">
                      <th class="ps-3">Cumulative CGPA</th>
                      <th>Certificate Award</th>
                      <th class="text-end pe-3">Academic Standing</th>
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

  <!-- Modal 2: Edit Award Classifications & Level Assessment Policies -->
  <div class="modal fade" id="editAwardsModal" tabindex="-1" aria-labelledby="editAwardsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <form action="{{ route('assessment.policy.awards.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="editAwardsModalLabel">
              <i class="bi bi-mortarboard me-2 text-info"></i>Edit Award Classifications &amp; Assessment Policies
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body p-0">
            <div class="p-3 bg-body-tertiary border-bottom small text-body-secondary">
              <i class="bi bi-info-circle me-1"></i> Configure assessment weightings (CA &amp; Exam), pass mark threshold, and CGPA award classifications independently for each tertiary tier.
            </div>

            <!-- Modal Sub-Tabs -->
            <ul class="nav nav-pills p-3 border-bottom bg-body" id="modalAwardsTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active btn-sm" id="modal-undergraduate-tab" data-bs-toggle="pill" data-bs-target="#modal-undergraduate-pane" type="button" role="tab">
                  🎓 Undergraduate (Bachelor's)
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm ms-2" id="modal-postgraduate-tab" data-bs-toggle="pill" data-bs-target="#modal-postgraduate-pane" type="button" role="tab">
                  🏛️ Postgraduate (Master's / PGD)
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

            <div class="tab-content p-3" id="modalAwardsTabContent">
              
              <!-- Modal Tab 1: Undergraduate (Bachelor's) -->
              <div class="tab-pane fade show active" id="modal-undergraduate-pane" role="tabpanel">
                <!-- Undergraduate Assessment Policy Editor -->
                <div class="card card-body bg-body-tertiary border mb-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold small text-primary">
                      <i class="bi bi-sliders me-1"></i>Undergraduate Assessment Policy &amp; Exam Weightings
                    </span>
                    <span class="badge text-bg-light border text-muted">Sum: 100% Total Rule</span>
                  </div>
                  <div class="row g-2">
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">CA Weight (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[degree][ca_weight]" value="{{ $degreePolicy->ca_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Final Exam (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[degree][exam_weight]" value="{{ $degreePolicy->exam_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Pass Mark (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[degree][pass_mark]" value="{{ $degreePolicy->pass_mark }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Retake CGPA Policy</label>
                      <select name="policies[degree][retake_cgpa_policy]" class="form-select form-select-sm">
                        <option value="replace_fail" {{ $degreePolicy->retake_cgpa_policy === 'replace_fail' ? 'selected' : '' }}>Grade Replacement</option>
                        <option value="accumulate_all" {{ $degreePolicy->retake_cgpa_policy === 'accumulate_all' ? 'selected' : '' }}>Cumulative Sum</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 140px;">Min CGPA</th>
                        <th style="width: 140px;">Max CGPA</th>
                        <th>Undergraduate Award Title</th>
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

              <!-- Modal Tab 2: Postgraduate (Master's / PGD) -->
              <div class="tab-pane fade" id="modal-postgraduate-pane" role="tabpanel">
                <!-- Postgraduate Assessment Policy Editor -->
                <div class="card card-body bg-body-tertiary border mb-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold small text-primary">
                      <i class="bi bi-sliders me-1"></i>Postgraduate Assessment Policy &amp; Exam Weightings
                    </span>
                    <span class="badge text-bg-warning text-dark border">Higher Threshold</span>
                  </div>
                  <div class="row g-2">
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">CA Weight (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[postgraduate][ca_weight]" value="{{ $postgradPolicy->ca_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Final Exam (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[postgraduate][exam_weight]" value="{{ $postgradPolicy->exam_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Pass Mark (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[postgraduate][pass_mark]" value="{{ $postgradPolicy->pass_mark }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Retake CGPA Policy</label>
                      <select name="policies[postgraduate][retake_cgpa_policy]" class="form-select form-select-sm">
                        <option value="replace_fail" {{ $postgradPolicy->retake_cgpa_policy === 'replace_fail' ? 'selected' : '' }}>Grade Replacement</option>
                        <option value="accumulate_all" {{ $postgradPolicy->retake_cgpa_policy === 'accumulate_all' ? 'selected' : '' }}>Cumulative Sum</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="table-responsive">
                  <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width: 140px;">Min CGPA</th>
                        <th style="width: 140px;">Max CGPA</th>
                        <th>Postgraduate Award Title</th>
                        <th style="width: 180px;">Standing</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($postgraduateClassifications as $class)
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

              <!-- Modal Tab 3: Diploma -->
              <div class="tab-pane fade" id="modal-diploma-pane" role="tabpanel">
                <!-- Diploma Assessment Policy Editor -->
                <div class="card card-body bg-body-tertiary border mb-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold small text-primary">
                      <i class="bi bi-sliders me-1"></i>Diploma Assessment Policy &amp; Exam Weightings
                    </span>
                    <span class="badge text-bg-light border text-muted">100% Total Rule</span>
                  </div>
                  <div class="row g-2">
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">CA Weight (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[diploma][ca_weight]" value="{{ $diplomaPolicy->ca_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Final Exam (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[diploma][exam_weight]" value="{{ $diplomaPolicy->exam_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Pass Mark (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[diploma][pass_mark]" value="{{ $diplomaPolicy->pass_mark }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Retake CGPA Policy</label>
                      <select name="policies[diploma][retake_cgpa_policy]" class="form-select form-select-sm">
                        <option value="replace_fail" {{ $diplomaPolicy->retake_cgpa_policy === 'replace_fail' ? 'selected' : '' }}>Grade Replacement</option>
                        <option value="accumulate_all" {{ $diplomaPolicy->retake_cgpa_policy === 'accumulate_all' ? 'selected' : '' }}>Cumulative Sum</option>
                      </select>
                    </div>
                  </div>
                </div>

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

              <!-- Modal Tab 4: Certificate -->
              <div class="tab-pane fade" id="modal-certificate-pane" role="tabpanel">
                <!-- Certificate Assessment Policy Editor -->
                <div class="card card-body bg-body-tertiary border mb-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold small text-primary">
                      <i class="bi bi-sliders me-1"></i>Certificate Assessment Policy &amp; Exam Weightings
                    </span>
                    <span class="badge text-bg-light border text-muted">100% Total Rule</span>
                  </div>
                  <div class="row g-2">
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">CA Weight (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[certificate][ca_weight]" value="{{ $certificatePolicy->ca_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Final Exam (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[certificate][exam_weight]" value="{{ $certificatePolicy->exam_weight }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Pass Mark (%)</label>
                      <div class="input-group input-group-sm">
                        <input type="number" step="0.5" min="0" max="100" name="policies[certificate][pass_mark]" value="{{ $certificatePolicy->pass_mark }}" class="form-control font-monospace text-center" required>
                        <span class="input-group-text">%</span>
                      </div>
                    </div>
                    <div class="col-6 col-md-3">
                      <label class="form-label small fw-semibold mb-1">Retake CGPA Policy</label>
                      <select name="policies[certificate][retake_cgpa_policy]" class="form-select form-select-sm">
                        <option value="replace_fail" {{ $certificatePolicy->retake_cgpa_policy === 'replace_fail' ? 'selected' : '' }}>Grade Replacement</option>
                        <option value="accumulate_all" {{ $certificatePolicy->retake_cgpa_policy === 'accumulate_all' ? 'selected' : '' }}>Cumulative Sum</option>
                      </select>
                    </div>
                  </div>
                </div>

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
              <i class="bi bi-save me-1"></i> Save Policies &amp; Awards
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <style>
    .policy-tooltip .tooltip-inner {
      max-width: 440px;
      text-align: left;
      padding: 10px 14px;
      font-size: 0.875rem;
      line-height: 1.5;
    }
    .cursor-pointer {
      cursor: pointer;
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Initialize Bootstrap 5 Tooltips
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

      // Handle tab switching via URL hash (#overview or #configuration)
      function handleUrlHash() {
        const hash = window.location.hash;
        if (hash === '#configuration' || hash === '#settings' || hash === '#awards') {
          const configTab = document.getElementById('tab-configuration');
          if (configTab) {
            bootstrap.Tab.getOrCreateInstance(configTab).show();
          }
        } else if (hash === '#overview' || hash === '#grading-scale') {
          const overviewTab = document.getElementById('tab-overview');
          if (overviewTab) {
            bootstrap.Tab.getOrCreateInstance(overviewTab).show();
          }
        }
      }

      handleUrlHash();
      window.addEventListener('hashchange', handleUrlHash);

      document.getElementById('tab-overview')?.addEventListener('shown.bs.tab', function () {
        history.replaceState(null, null, '#overview');
      });
      document.getElementById('tab-configuration')?.addEventListener('shown.bs.tab', function () {
        history.replaceState(null, null, '#configuration');
      });
    });
  </script>
@endpush
