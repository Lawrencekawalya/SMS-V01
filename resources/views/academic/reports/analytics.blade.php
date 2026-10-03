@extends('layouts.app')

@section('title', 'Academic Performance Analytics - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Academic Performance Analytics')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('assessment.list') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item active" aria-current="page">Performance Analytics</li>
@endsection

@section('content')
  <!-- Filter & Action Bar -->
  <div class="card card-outline card-primary mb-4 shadow-sm no-print">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h3 class="card-title m-0">
        <i class="bi bi-graph-up-arrow me-1 text-primary"></i> Academic Health &amp; Analytics Filter
      </h3>
      <div class="d-flex gap-2">
        <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-printer me-1"></i> Print Board Report
        </button>
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
          <label for="semester_id" class="form-label small fw-semibold">Semester</label>
          <select name="semester_id" id="semester_id" class="form-select form-select-sm">
            @foreach ($semesters as $sem)
              <option value="{{ $sem->id }}" {{ (string) ($selectedSemester?->id ?? '') === (string) $sem->id ? 'selected' : '' }}>
                {{ $sem->name }} ({{ $sem->academicYear->name ?? '' }})
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
    <!-- Official Institutional Header for Board Presentation -->
    <div class="text-center mb-4">
      <h4 class="fw-bold mb-1 text-uppercase">{{ $university->name ?? 'BISHOP STUART UNIVERSITY' }}</h4>
      <h6 class="text-secondary mb-1">OFFICE OF THE ACADEMIC REGISTRAR &bull; ACADEMIC BOARD</h6>
      <h5 class="fw-bold text-primary mb-1">FACULTY &amp; DEPARTMENTAL PERFORMANCE ANALYTICS REPORT</h5>
      <p class="small text-muted mb-0">
        <strong>Session:</strong> {{ $selectedSemester->name }} ({{ $selectedSemester->academicYear->name ?? '' }}) &bull;
        <strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}
      </p>
    </div>

    <!-- Institutional KPI Overview -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="info-box shadow-sm">
          <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-mortarboard-fill"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Mean Institutional GPA</span>
            <span class="info-box-number fs-4">{{ number_format($institutionKpis['average_gpa'], 2) }}</span>
            <span class="progress-description text-muted small">{{ $institutionKpis['total_students'] }} active candidate(s)</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="info-box shadow-sm">
          <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Overall Pass Rate</span>
            <span class="info-box-number fs-4">{{ number_format($institutionKpis['pass_rate'], 1) }}%</span>
            <span class="progress-description text-muted small">Passing final marks</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="info-box shadow-sm">
          <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i></span>
          <div class="info-box-content">
            <span class="info-box-text">Probation Alerts</span>
            <span class="info-box-number fs-4">{{ $institutionKpis['probation_count'] }}</span>
            <span class="progress-description text-muted small">Students with CGPA &lt; 2.00</span>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="info-box shadow-sm">
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
              <i class="bi bi-pie-chart-fill me-1 text-info"></i> Grade Distribution
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
  @else
    <div class="alert alert-info shadow-sm text-center py-5">
      <i class="bi bi-funnel fs-1 d-block mb-2 text-primary"></i>
      <h5 class="fw-bold">Select Academic Session</h5>
      <p class="text-muted mb-0">Please choose an Academic Year and Semester from the filter above to view institutional performance analytics.</p>
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
    .no-print, .main-header, .main-sidebar, .app-header, .app-sidebar, .app-footer, .breadcrumb {
      display: none !important;
    }
    .app-main, .content-wrapper, body {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }
  }
</style>
@endpush
