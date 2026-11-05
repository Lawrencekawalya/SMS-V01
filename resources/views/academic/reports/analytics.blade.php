@extends('layouts.app')

@section('title', 'Academic Performance Analytics - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Academic Performance Analytics')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('assessment.list') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item active" aria-current="page">Senate Analytics &amp; Reports</li>
@endsection

@section('content')
  <!-- Filter & Senate Action Bar -->
  <div class="card card-outline card-primary mb-4 shadow-sm no-print">
    <div class="card-header">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0">
          <i class="bi bi-graph-up-arrow me-1 text-primary"></i> Senate Academic Performance &amp; Analytics Filter
        </h3>
        @if ($selectedSemester)
          <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0 ms-auto">
            <a href="{{ route('analytics.export.program-standing', ['semester_id' => $selectedSemester->id]) }}" class="btn btn-sm btn-success">
              <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Standing CSV
            </a>
            <a href="{{ route('analytics.export.honours-performance', ['semester_id' => $selectedSemester->id]) }}" class="btn btn-sm btn-primary">
              <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Honours CSV
            </a>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-printer me-1"></i> Print Senate Report (PDF)
            </button>
          </div>
        @endif
      </div>
    </div>
    <div class="card-body">
      <form action="{{ route('analytics.list') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-12 col-md-5">
          <label for="academic_year_id" class="form-label small fw-semibold">Academic Year</label>
          <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm">
            @foreach ($academicYears as $ay)
              <option value="{{ $ay->id }}" {{ (string) ($selectedAcademicYear?->id ?? '') === (string) $ay->id ? 'selected' : '' }}>
                {{ $ay->name }} {{ $ay->is_current ? '(Current Session)' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-5">
          <label for="semester_id" class="form-label small fw-semibold">Target Semester</label>
          <select name="semester_id" id="semester_id" class="form-select form-select-sm">
            @foreach ($semesters as $sem)
              <option value="{{ $sem->id }}"
                      data-academic-year-id="{{ $sem->academic_year_id }}"
                      data-is-active="{{ $sem->is_active ? '1' : '0' }}"
                      {{ (string) ($selectedSemester?->id ?? '') === (string) $sem->id ? 'selected' : '' }}>
                {{ $sem->name }} ({{ $sem->academicYear->name ?? '' }}) {{ $sem->is_active ? '— (Active Session)' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-2">
          <button type="submit" class="btn btn-sm btn-primary w-100">
            <i class="bi bi-filter me-1"></i> Apply Filter
          </button>
        </div>
      </form>
    </div>
  </div>

  @if ($selectedSemester)
    <!-- Official Institutional Header for Senate Board Presentation (Printed PDF Only) -->
    <div class="d-none d-print-block text-center mb-4">
      <h4 class="fw-bold mb-1 text-uppercase">{{ $university->name ?? 'BISHOP STUART UNIVERSITY' }}</h4>
      <h6 class="text-secondary mb-1">OFFICE OF THE ACADEMIC REGISTRAR &bull; SENATE EXAMINATIONS COMMITTEE</h6>
      <h5 class="fw-bold text-primary mb-1">FACULTY &amp; DEPARTMENTAL PERFORMANCE ANALYTICS REPORT</h5>
      <p class="small text-muted mb-0">
        <strong>Session:</strong> {{ $selectedSemester->name }} ({{ $selectedSemester->academicYear->name ?? '' }}) &bull;
        <strong>Statutory Authority:</strong> Uganda National Council for Higher Education (NCHE) Standards &bull;
        <strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}
      </p>
    </div>

    <!-- Senate Report Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2 no-print" id="analyticsTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold text-nowrap" id="standing-tab" data-bs-toggle="pill" data-bs-target="#standing-pane" type="button" role="tab">
          <i class="bi bi-mortarboard-fill me-1"></i> 1. Programme Standing
          <i class="bi bi-info-circle ms-1 opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Normal Progress, Retakes, Missed Papers"></i>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-nowrap" id="honours-tab" data-bs-toggle="pill" data-bs-target="#honours-pane" type="button" role="tab">
          <i class="bi bi-award-fill me-1"></i> 2. Honours Classification
          <i class="bi bi-info-circle ms-1 opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Performance by NCHE Tiers (First Class, 2nd Upper, 2nd Lower, Pass, Probation)"></i>
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold text-nowrap" id="departmental-tab" data-bs-toggle="pill" data-bs-target="#departmental-pane" type="button" role="tab">
          <i class="bi bi-building me-1"></i> 3. Faculty &amp; Departments
          <i class="bi bi-info-circle ms-1 opacity-75" data-bs-toggle="tooltip" data-bs-placement="top" title="Faculty Overview, Department Breakdown &amp; Anomaly Watchlist"></i>
        </button>
      </li>
    </ul>

    <div class="tab-content" id="analyticsTabsContent">
      <!-- TAB 1: SENATE PROGRAMME STANDING (NP, Retakes, Missed Papers) -->
      <div class="tab-pane fade show active" id="standing-pane" role="tabpanel" tabindex="0">
        <!-- Standing KPI Overview Cards -->
        @php
          $totStudents = $standingData['totals']['total_students'];
          $totNormal = $standingData['totals']['normal_progress_count'];
          $totRetakes = $standingData['totals']['retakes_count'];
          $totMp = $standingData['totals']['missed_papers_count'];
          $totProb = $standingData['totals']['probation_count'];

          $pctNormal = $totStudents > 0 ? round(($totNormal / $totStudents) * 100, 1) : 0;
          $pctRetakes = $totStudents > 0 ? round(($totRetakes / $totStudents) * 100, 1) : 0;
          $pctMp = $totStudents > 0 ? round(($totMp / $totStudents) * 100, 1) : 0;
          $pctProb = $totStudents > 0 ? round(($totProb / $totStudents) * 100, 1) : 0;
        @endphp

        <div class="row g-3 mb-4">
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check2-circle"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-body-secondary">Normal Progress (Clear)</span>
                <span class="info-box-number fs-4 text-success">{{ $totNormal }}</span>
                <span class="progress-description small text-muted">{{ $pctNormal }}% of enrolled cohort</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-warning text-dark shadow-sm"><i class="bi bi-arrow-repeat"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-body-secondary">Active Retakes (RTK)</span>
                <span class="info-box-number fs-4 text-warning-emphasis">{{ $totRetakes }}</span>
                <span class="progress-description small text-muted">{{ $pctRetakes }}% requires re-sitting</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-hospital"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-body-secondary">Missed Papers (MP)</span>
                <span class="info-box-number fs-4 text-info">{{ $totMp }}</span>
                <span class="progress-description small text-muted">{{ $pctMp }}% approved medical/official</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i></span>
              <div class="info-box-content">
                <span class="info-box-text text-body-secondary">Probation (CGPA &lt; 2.0)</span>
                <span class="info-box-number fs-4 text-danger">{{ $totProb }}</span>
                <span class="progress-description small text-muted">{{ $pctProb }}% academic distress</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Programme Standing Breakdown Table -->
        <div class="card card-outline card-primary shadow-sm mb-4">
          <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title fs-6 fw-bold m-0 text-primary">
              <i class="bi bi-table me-1"></i> Programme Academic Standing Ledger &bull; Senate Gazette Breakdown
            </h3>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
              {{ count($standingData['rows']) }} Active Programme(s)
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 28%;">Academic Programme</th>
                    <th style="width: 18%;">Faculty &amp; Department</th>
                    <th style="width: 8%;" class="text-center">Enrolled</th>
                    <th style="width: 10%;" class="text-center text-success">Normal Progress</th>
                    <th style="width: 9%;" class="text-center text-warning-emphasis">Retakes (RTK)</th>
                    <th style="width: 9%;" class="text-center text-info">Missed Papers (MP)</th>
                    <th style="width: 8%;" class="text-center text-danger">Probation</th>
                    <th style="width: 6%;" class="text-center">Mean GPA</th>
                    <th style="width: 8%;" class="text-center no-print">Drilldown</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($standingData['rows'] as $index => $row)
                    <tr>
                      <td class="text-muted text-center">{{ $index + 1 }}</td>
                      <td>
                        <strong class="d-block">{{ $row['programme']->name }}</strong>
                        <span class="badge bg-light text-secondary border font-monospace">{{ $row['programme']->code }}</span>
                      </td>
                      <td>
                        <div class="small fw-semibold">{{ $row['department']->name ?? 'General' }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $row['faculty']->name ?? '' }}</div>
                      </td>
                      <td class="text-center fw-bold fs-6">{{ $row['total_students'] }}</td>
                      <td class="text-center">
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">
                          {{ $row['normal_progress_count'] }}
                        </span>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['normal_progress_pct'] }}%</div>
                      </td>
                      <td class="text-center">
                        @if ($row['retakes_count'] > 0)
                          <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fs-6">
                            {{ $row['retakes_count'] }}
                          </span>
                          <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['retakes_pct'] }}%</div>
                        @else
                          <span class="text-muted small">0</span>
                        @endif
                      </td>
                      <td class="text-center">
                        @if ($row['missed_papers_count'] > 0)
                          <span class="badge bg-info-subtle text-info border border-info-subtle fs-6">
                            {{ $row['missed_papers_count'] }}
                          </span>
                          <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['missed_papers_pct'] }}%</div>
                        @else
                          <span class="text-muted small">0</span>
                        @endif
                      </td>
                      <td class="text-center">
                        @if ($row['probation_count'] > 0)
                          <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6">
                            {{ $row['probation_count'] }}
                          </span>
                          <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['probation_pct'] }}%</div>
                        @else
                          <span class="text-muted small">0</span>
                        @endif
                      </td>
                      <td class="text-center">
                        <span class="badge {{ $row['average_gpa'] >= 4.0 ? 'bg-success' : ($row['average_gpa'] >= 2.0 ? 'bg-primary' : 'bg-danger') }} font-monospace fs-6">
                          {{ number_format($row['average_gpa'], 2) }}
                        </span>
                      </td>
                      <td class="text-center no-print">
                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" data-bs-toggle="modal" data-bs-target="#studentsModal{{ $row['programme']->id }}">
                          <i class="bi bi-people me-1"></i> Roster
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center py-4 text-muted">
                        No student performance records registered for this semester session.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
                @if (! empty($standingData['rows']))
                  <tfoot class="table-light fw-bold border-top border-2">
                    <tr>
                      <td colspan="3" class="text-uppercase text-secondary">Institutional Cohort Aggregate</td>
                      <td class="text-center text-primary fs-6">{{ $totStudents }}</td>
                      <td class="text-center text-success fs-6">{{ $totNormal }} ({{ $pctNormal }}%)</td>
                      <td class="text-center text-warning-emphasis fs-6">{{ $totRetakes }} ({{ $pctRetakes }}%)</td>
                      <td class="text-center text-info fs-6">{{ $totMp }} ({{ $pctMp }}%)</td>
                      <td class="text-center text-danger fs-6">{{ $totProb }} ({{ $pctProb }}%)</td>
                      <td class="text-center font-monospace fs-6">{{ number_format($institutionKpis['average_gpa'], 2) }}</td>
                      <td class="no-print"></td>
                    </tr>
                  </tfoot>
                @endif
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: HONOURS CLASSIFICATION PERFORMANCE (NCHE TIERS) -->
      <div class="tab-pane fade" id="honours-pane" role="tabpanel" tabindex="0">
        @php
          $hTotals = $honoursData['totals'];
          $hCohort = $hTotals['total_students'];
          $hFirst = $hTotals['first_class_count'];
          $hUpper = $hTotals['second_upper_count'];
          $hLower = $hTotals['second_lower_count'];
          $hPass = $hTotals['pass_count'];
          $hProb = $hTotals['probation_count'];

          $pFirst = $hCohort > 0 ? round(($hFirst / $hCohort) * 100, 1) : 0;
          $pUpper = $hCohort > 0 ? round(($hUpper / $hCohort) * 100, 1) : 0;
          $pLower = $hCohort > 0 ? round(($hLower / $hCohort) * 100, 1) : 0;
          $pPass = $hCohort > 0 ? round(($hPass / $hCohort) * 100, 1) : 0;
          $pProb = $hCohort > 0 ? round(($hProb / $hCohort) * 100, 1) : 0;
        @endphp

        <!-- Institutional Stacked Honours Distribution Bar -->
        <div class="card card-outline card-success shadow-sm mb-4">
          <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title fs-6 fw-bold m-0 text-success">
              <i class="bi bi-award me-1"></i> University-Wide Honours Classification Performance Distribution
            </h3>
            <span class="badge bg-success-subtle text-success border border-success-subtle">
              Total Evaluated: {{ $hCohort }} Candidates
            </span>
          </div>
          <div class="card-body">
            <div class="progress mb-3 shadow-sm" style="height: 28px;">
              <div class="progress-bar bg-success fw-bold" role="progressbar" style="width: {{ $pFirst }}%" title="First Class: {{ $hFirst }} ({{ $pFirst }}%)">
                @if ($pFirst >= 5) 1st Class ({{ $pFirst }}%) @endif
              </div>
              <div class="progress-bar bg-primary fw-bold" role="progressbar" style="width: {{ $pUpper }}%" title="2nd Class Upper: {{ $hUpper }} ({{ $pUpper }}%)">
                @if ($pUpper >= 5) 2nd Upper ({{ $pUpper }}%) @endif
              </div>
              <div class="progress-bar bg-info text-dark fw-bold" role="progressbar" style="width: {{ $pLower }}%" title="2nd Class Lower: {{ $hLower }} ({{ $pLower }}%)">
                @if ($pLower >= 5) 2nd Lower ({{ $pLower }}%) @endif
              </div>
              <div class="progress-bar bg-warning text-dark fw-bold" role="progressbar" style="width: {{ $pPass }}%" title="Pass Degree: {{ $hPass }} ({{ $pPass }}%)">
                @if ($pPass >= 5) Pass ({{ $pPass }}%) @endif
              </div>
              <div class="progress-bar bg-danger fw-bold" role="progressbar" style="width: {{ $pProb }}%" title="Probation: {{ $hProb }} ({{ $pProb }}%)">
                @if ($pProb >= 5) Prob ({{ $pProb }}%) @endif
              </div>
            </div>

            <!-- Honours Metric Cards -->
            <div class="row g-2 text-center">
              <div class="col-6 col-md">
                <div class="p-2 border rounded bg-success-subtle text-success-emphasis">
                  <span class="d-block small text-uppercase fw-semibold">First Class</span>
                  <strong class="fs-4">{{ $hFirst }}</strong>
                  <span class="d-block small text-muted">4.40 - 5.00 ({{ $pFirst }}%)</span>
                </div>
              </div>
              <div class="col-6 col-md">
                <div class="p-2 border rounded bg-primary-subtle text-primary-emphasis">
                  <span class="d-block small text-uppercase fw-semibold">2nd Upper</span>
                  <strong class="fs-4">{{ $hUpper }}</strong>
                  <span class="d-block small text-muted">3.60 - 4.39 ({{ $pUpper }}%)</span>
                </div>
              </div>
              <div class="col-6 col-md">
                <div class="p-2 border rounded bg-info-subtle text-info-emphasis">
                  <span class="d-block small text-uppercase fw-semibold">2nd Lower</span>
                  <strong class="fs-4">{{ $hLower }}</strong>
                  <span class="d-block small text-muted">2.80 - 3.59 ({{ $pLower }}%)</span>
                </div>
              </div>
              <div class="col-6 col-md">
                <div class="p-2 border rounded bg-warning-subtle text-warning-emphasis">
                  <span class="d-block small text-uppercase fw-semibold">Pass Degree</span>
                  <strong class="fs-4">{{ $hPass }}</strong>
                  <span class="d-block small text-muted">2.00 - 2.79 ({{ $pPass }}%)</span>
                </div>
              </div>
              <div class="col-6 col-md">
                <div class="p-2 border rounded bg-danger-subtle text-danger-emphasis">
                  <span class="d-block small text-uppercase fw-semibold">Probation</span>
                  <strong class="fs-4">{{ $hProb }}</strong>
                  <span class="d-block small text-muted">&lt; 2.00 ({{ $pProb }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Programme-Level Honours Classification Matrix Table -->
        <div class="card card-outline card-secondary shadow-sm mb-4">
          <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h3 class="card-title fs-6 fw-bold m-0">
              <i class="bi bi-mortarboard me-1 text-primary"></i> Degree Honours Classification Performance by Programme
            </h3>
            <span class="badge bg-secondary-subtle text-secondary border">
              Statutory NCHE 5.0 Scale
            </span>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-bordered table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 26%;">Academic Programme</th>
                    <th style="width: 8%;" class="text-center">Cohort Size</th>
                    <th style="width: 10%;" class="text-center text-success">First Class<br><small class="text-muted fw-normal">4.40 - 5.00</small></th>
                    <th style="width: 10%;" class="text-center text-primary">2nd Upper<br><small class="text-muted fw-normal">3.60 - 4.39</small></th>
                    <th style="width: 10%;" class="text-center text-info">2nd Lower<br><small class="text-muted fw-normal">2.80 - 3.59</small></th>
                    <th style="width: 10%;" class="text-center text-warning-emphasis">Pass Degree<br><small class="text-muted fw-normal">2.00 - 2.79</small></th>
                    <th style="width: 8%;" class="text-center text-danger">Probation<br><small class="text-muted fw-normal">&lt; 2.00</small></th>
                    <th style="width: 7%;" class="text-center">Mean CGPA</th>
                    <th style="width: 7%;" class="text-center">Top CGPA</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($honoursData['rows'] as $index => $row)
                    <tr>
                      <td class="text-muted text-center">{{ $index + 1 }}</td>
                      <td>
                        <strong class="d-block">{{ $row['programme']->name }}</strong>
                        <span class="badge bg-light text-secondary border font-monospace">{{ $row['programme']->code }}</span>
                      </td>
                      <td class="text-center fw-bold fs-6">{{ $row['total_students'] }}</td>
                      <td class="text-center">
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">
                          {{ $row['first_class_count'] }}
                        </span>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['first_class_pct'] }}%</div>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                          {{ $row['second_upper_count'] }}
                        </span>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['second_upper_pct'] }}%</div>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-info-subtle text-info border border-info-subtle fs-6">
                          {{ $row['second_lower_count'] }}
                        </span>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['second_lower_pct'] }}%</div>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle fs-6">
                          {{ $row['pass_count'] }}
                        </span>
                        <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['pass_pct'] }}%</div>
                      </td>
                      <td class="text-center">
                        @if ($row['probation_count'] > 0)
                          <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6">
                            {{ $row['probation_count'] }}
                          </span>
                          <div class="small text-muted" style="font-size: 0.72rem;">{{ $row['probation_pct'] }}%</div>
                        @else
                          <span class="text-muted small">0</span>
                        @endif
                      </td>
                      <td class="text-center font-monospace fw-bold fs-6">
                        {{ number_format($row['average_cgpa'], 2) }}
                      </td>
                      <td class="text-center font-monospace text-success fw-bold fs-6">
                        {{ number_format($row['top_cgpa'], 2) }}
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center py-4 text-muted">
                        No student performance records available for honours classification in this session.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
                @if (! empty($honoursData['rows']))
                  <tfoot class="table-light fw-bold border-top border-2">
                    <tr>
                      <td colspan="2" class="text-uppercase text-secondary">Institutional Cohort Aggregate</td>
                      <td class="text-center text-primary fs-6">{{ $hCohort }}</td>
                      <td class="text-center text-success fs-6">{{ $hFirst }} ({{ $pFirst }}%)</td>
                      <td class="text-center text-primary fs-6">{{ $hUpper }} ({{ $pUpper }}%)</td>
                      <td class="text-center text-info fs-6">{{ $hLower }} ({{ $pLower }}%)</td>
                      <td class="text-center text-warning-emphasis fs-6">{{ $hPass }} ({{ $pPass }}%)</td>
                      <td class="text-center text-danger fs-6">{{ $hProb }} ({{ $pProb }}%)</td>
                      <td class="text-center font-monospace fs-6" colspan="2">&bull;</td>
                    </tr>
                  </tfoot>
                @endif
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: FACULTY & DEPARTMENT OVERVIEW & ANOMALY WATCHLIST -->
      <div class="tab-pane fade" id="departmental-pane" role="tabpanel" tabindex="0">
        <!-- Institutional KPI Overview -->
        <div class="row g-3 mb-4">
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-mortarboard-fill"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Mean Institutional GPA</span>
                <span class="info-box-number fs-4">{{ number_format($institutionKpis['average_gpa'], 2) }}</span>
                <span class="progress-description text-muted small">{{ $institutionKpis['total_students'] }} active candidate(s)</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Overall Pass Rate</span>
                <span class="info-box-number fs-4">{{ number_format($institutionKpis['pass_rate'], 1) }}%</span>
                <span class="progress-description text-muted small">Passing final marks</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Probation Alerts</span>
                <span class="info-box-number fs-4">{{ $institutionKpis['probation_count'] }}</span>
                <span class="progress-description text-muted small">Students with CGPA &lt; 2.00</span>
              </div>
            </div>
          </div>
          <div class="col-12 col-sm-6 col-xl-3">
            <div class="info-box shadow-sm mb-0">
              <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-file-earmark-check-fill"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Total Evaluated Marks</span>
                <span class="info-box-number fs-4">{{ $totalEvaluatedMarksCount }}</span>
                <span class="progress-description text-muted small">Across all published units</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Anomaly Watchlist Alert (Course Units with >30% Failure Rate) -->
        @if (! empty($courseAnomalies))
          <div class="card card-outline card-danger mb-4 shadow-sm">
            <div class="card-header bg-danger-subtle py-2">
              <h3 class="card-title fs-6 fw-bold text-danger m-0">
                <i class="bi bi-shield-exclamation me-1"></i> Academic Anomaly &amp; High Failure Watchlist
              </h3>
              <div class="card-tools">
                <span class="badge bg-danger">{{ count($courseAnomalies) }} Course(s) Flagged</span>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Course Unit</th>
                      <th>Department / Faculty</th>
                      <th class="text-center">Candidates Evaluated</th>
                      <th class="text-center">Passed</th>
                      <th class="text-center">Failed</th>
                      <th class="text-center">Failure Rate</th>
                      <th>Academic Board Action / Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($courseAnomalies as $anomaly)
                      <tr>
                        <td>
                          <span class="fw-bold font-monospace">{{ $anomaly['course']->code }}</span>
                          <div class="small text-muted">{{ $anomaly['course']->name }}</div>
                        </td>
                        <td>
                          <div class="small fw-semibold">{{ $anomaly['course']->department->name ?? 'General' }}</div>
                          <div class="text-muted" style="font-size: 0.75rem;">{{ $anomaly['course']->department->faculty->name ?? '' }}</div>
                        </td>
                        <td class="text-center fw-semibold">{{ $anomaly['evaluated_count'] }}</td>
                        <td class="text-center text-success fw-semibold">{{ $anomaly['passed_count'] }}</td>
                        <td class="text-center text-danger fw-bold">{{ $anomaly['failed_count'] }}</td>
                        <td class="text-center">
                          <span class="badge bg-danger fs-6">{{ number_format($anomaly['failure_rate'], 1) }}%</span>
                        </td>
                        <td>
                          <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                            <i class="bi bi-exclamation-octagon me-1"></i> {{ $anomaly['type'] }}
                          </span>
                          <div class="small text-muted mt-1">{{ $anomaly['message'] }}</div>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        @endif

        <div class="row g-4 mb-4">
          <!-- Departmental Comparative Performance Table -->
          <div class="col-12 col-xl-8">
            <div class="card card-outline card-secondary shadow-sm h-100">
              <div class="card-header py-2">
                <h3 class="card-title fs-6 fw-bold m-0">
                  <i class="bi bi-building me-1 text-primary"></i> Faculty &amp; Department Performance Breakdown
                </h3>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-bordered table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Department / Unit</th>
                        <th class="text-center">Enrolled</th>
                        <th class="text-center">Normal Progress</th>
                        <th class="text-center">Probation</th>
                        <th class="text-center">Mean GPA</th>
                        <th style="min-width: 140px;">Pass Rate (%)</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($facultiesData as $fData)
                        <tr class="table-light fw-bold">
                          <td colspan="4" class="text-primary">
                            <i class="bi bi-mortarboard me-1"></i> {{ $fData['faculty']->name }}
                          </td>
                          <td class="text-center text-primary">
                            {{ number_format($fData['average_gpa'], 2) }}
                          </td>
                          <td>
                            <span class="fw-bold">{{ number_format($fData['pass_rate'], 1) }}%</span>
                          </td>
                        </tr>
                        @foreach ($fData['departments'] as $dData)
                          <tr>
                            <td class="ps-4">
                              <span class="fw-semibold">{{ $dData['department']->name }}</span>
                              <span class="badge bg-light text-secondary border ms-1">{{ $dData['department']->code }}</span>
                            </td>
                            <td class="text-center">{{ $dData['total_students'] }}</td>
                            <td class="text-center text-success fw-semibold">{{ $dData['normal_progress'] }}</td>
                            <td class="text-center {{ $dData['probation'] > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                              {{ $dData['probation'] }}
                            </td>
                            <td class="text-center">
                              <span class="badge {{ $dData['average_gpa'] >= 3.6 ? 'bg-success' : ($dData['average_gpa'] >= 2.0 ? 'bg-info' : 'bg-danger') }}">
                                {{ number_format($dData['average_gpa'], 2) }}
                              </span>
                            </td>
                            <td>
                              <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                  <div class="progress-bar {{ $dData['pass_rate'] >= 75 ? 'bg-success' : ($dData['pass_rate'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                       role="progressbar"
                                       style="width: {{ min(100, max(0, $dData['pass_rate'])) }}%">
                                  </div>
                                </div>
                                <span class="small fw-semibold" style="width: 45px;">{{ number_format($dData['pass_rate'], 1) }}%</span>
                              </div>
                            </td>
                          </tr>
                        @endforeach
                      @empty
                        <tr>
                          <td colspan="6" class="text-center py-4 text-muted">
                            No faculty or department enrollment records found for this academic session.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- Institutional Grade Distribution Card -->
          <div class="col-12 col-xl-4">
            <div class="card card-outline card-info shadow-sm h-100">
              <div class="card-header py-2">
                <h3 class="card-title fs-6 fw-bold m-0">
                  <i class="bi bi-pie-chart-fill me-1 text-info"></i> Course Grade Distribution
                </h3>
              </div>
              <div class="card-body">
                <p class="small text-muted mb-3">Overall breakdown of letter grades awarded across all published courses this semester:</p>
                @php
                  $gradeColors = [
                    'A' => 'success',
                    'B+' => 'info',
                    'B' => 'primary',
                    'C+' => 'secondary',
                    'C' => 'dark',
                    'D+' => 'warning',
                    'D' => 'warning',
                    'F' => 'danger',
                  ];
                @endphp
                <div class="d-flex flex-column gap-3">
                  @foreach ($gradeDistribution as $grade => $count)
                    @php
                      $pct = $totalEvaluatedMarksCount > 0 ? round(($count / $totalEvaluatedMarksCount) * 100, 1) : 0;
                      $color = $gradeColors[$grade] ?? 'secondary';
                    @endphp
                    <div>
                      <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="fw-bold">Grade {{ $grade }}</span>
                        <span class="text-muted">{{ $count }} student(s) ({{ $pct }}%)</span>
                      </div>
                      <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $pct }}%"></div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Roster Drilldown Modals for Each Programme -->
    @foreach ($standingData['rows'] as $row)
      <div class="modal fade" id="studentsModal{{ $row['programme']->id }}" tabindex="-1" aria-labelledby="studentsModalLabel{{ $row['programme']->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
              <h5 class="modal-title fs-6" id="studentsModalLabel{{ $row['programme']->id }}">
                <i class="bi bi-people-fill me-1"></i> {{ $row['programme']->name }} ({{ $row['programme']->code }}) &bull; Candidate Roster
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th>
                      <th>Registration No</th>
                      <th>Candidate Name</th>
                      <th class="text-center">Term GPA</th>
                      <th class="text-center">Cum. CGPA</th>
                      <th class="text-center">Academic Standing</th>
                      <th class="text-center">Transcript</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse ($row['students'] as $idx => $sItem)
                      @php
                        $st = $sItem['student'];
                        $p = $sItem['performance'];
                      @endphp
                      <tr>
                        <td class="text-muted text-center">{{ $idx + 1 }}</td>
                        <td class="font-monospace fw-bold">{{ $st->registration_number }}</td>
                        <td>{{ $st->user->name ?? $st->full_name }}</td>
                        <td class="text-center font-monospace">{{ number_format($p->gpa, 2) }}</td>
                        <td class="text-center font-monospace fw-bold">{{ number_format($p->cgpa, 2) }}</td>
                        <td class="text-center">
                          @if ($sItem['is_normal'])
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                              <i class="bi bi-check-circle me-1"></i> Normal Progress
                            </span>
                          @elseif ($sItem['has_retake'])
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                              <i class="bi bi-arrow-repeat me-1"></i> Retake (RTK)
                            </span>
                          @elseif ($sItem['has_mp'])
                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                              <i class="bi bi-hospital me-1"></i> Missed Papers (MP)
                            </span>
                          @elseif ($sItem['is_probation'])
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                              <i class="bi bi-exclamation-triangle me-1"></i> Probation
                            </span>
                          @endif
                        </td>
                        <td class="text-center">
                          <a href="{{ route('result.transcript', $st) }}" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-2" title="View Full Academic Transcript">
                            <i class="bi bi-file-earmark-text"></i>
                          </a>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="7" class="text-center py-3 text-muted">No students found.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer py-2">
              <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  @else
    <div class="alert alert-info shadow-sm text-center py-5">
      <i class="bi bi-funnel fs-1 d-block mb-2 text-primary"></i>
      <h5 class="fw-bold">Select Academic Session</h5>
      <p class="text-muted mb-0">Please choose an Academic Year and Semester from the filter above to view Senate performance analytics.</p>
    </div>
  @endif
@endsection

@push('styles')
<style>
  @media print {
    @page {
      size: landscape;
      margin: 10mm;
    }
    .no-print, .main-header, .main-sidebar, .app-header, .app-sidebar, .app-footer, .breadcrumb, .nav-pills {
      display: none !important;
    }
    .tab-content > .tab-pane {
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }
    .app-main, .content-wrapper, body {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }
    .card {
      border: 1px solid #dee2e6 !important;
      break-inside: avoid;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const yearSelect = document.getElementById('academic_year_id');
    const semesterSelect = document.getElementById('semester_id');
    if (!yearSelect || !semesterSelect) return;

    // Snapshot full options list with institutional metadata
    const allOptions = Array.from(semesterSelect.options).map(function (opt) {
      return {
        value: opt.value,
        text: opt.text,
        yearId: String(opt.getAttribute('data-academic-year-id') || ''),
        isActive: opt.getAttribute('data-is-active') === '1',
        isSelected: opt.selected
      };
    });

    function syncSemesterOptions() {
      const selectedYearId = String(yearSelect.value || '');
      const currentSelectedVal = String(semesterSelect.value || '');

      // Filter semesters belonging to the selected academic year
      const matching = allOptions.filter(function (opt) {
        return !selectedYearId || opt.yearId === selectedYearId;
      });

      semesterSelect.innerHTML = '';

      let targetValToSelect = null;

      matching.forEach(function (opt) {
        const optionElem = document.createElement('option');
        optionElem.value = opt.value;
        optionElem.text = opt.text;
        optionElem.setAttribute('data-academic-year-id', opt.yearId);
        if (opt.isActive) {
          optionElem.setAttribute('data-is-active', '1');
        }

        // Retain current selection if it exists in the filtered set
        if (opt.value === currentSelectedVal) {
          targetValToSelect = opt.value;
        } else if (!targetValToSelect && opt.isActive) {
          // If previous selection isn't in this year, prefer the active semester
          targetValToSelect = opt.value;
        }

        semesterSelect.appendChild(optionElem);
      });

      if (targetValToSelect) {
        semesterSelect.value = targetValToSelect;
      } else if (matching.length > 0) {
        semesterSelect.value = matching[0].value;
      }
    }

    yearSelect.addEventListener('change', syncSemesterOptions);
    syncSemesterOptions();

    // Initialize Bootstrap tooltips for info icons
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }
  });
</script>
@endpush
