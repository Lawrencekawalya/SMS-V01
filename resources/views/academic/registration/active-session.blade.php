@extends('layouts.app')

@section('title', 'Active Calendar Session & Multi-Cohort Progression - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Active Calendar Session & Multi-Cohort Roster')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.registrations.index') }}">Course Registrations</a></li>
  <li class="breadcrumb-item active" aria-current="page">Active Session Cohorts</li>
@endsection

@section('content')
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Top Hero Card: Active University Calendar Session -->
  <div class="card card-outline card-success shadow-sm mb-4">
    <div class="card-header bg-success-subtle border-bottom border-success-subtle py-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
          @if ($currentSemester && $currentSemester->is_active)
            <span class="badge text-bg-success text-uppercase px-3 py-1 fs-7">
              <i class="bi bi-calendar2-check-fill me-1"></i> Active University Calendar Session
            </span>
          @else
            <span class="badge text-bg-secondary text-uppercase px-3 py-1 fs-7">
              <i class="bi bi-calendar2-check me-1"></i> Archived University Calendar Session
            </span>
          @endif
        </div>
        <div class="d-flex align-items-center gap-2">
          <form method="GET" action="{{ route('academic.registrations.active-session') }}" class="d-flex align-items-center gap-2">
            <label for="semester_id_select" class="small fw-semibold text-muted text-nowrap mb-0 d-none d-sm-inline">Switch Session:</label>
            <select name="semester_id" id="semester_id_select" class="form-select form-select-sm" onchange="this.form.submit()">
              @foreach ($allSemesters as $sem)
                <option value="{{ $sem->id }}" {{ $currentSemester && $currentSemester->id === $sem->id ? 'selected' : '' }}>
                  {{ $sem->academicYear->name }} — {{ $sem->name }} {{ $sem->is_active ? '(Active)' : '' }}
                </option>
              @endforeach
            </select>
          </form>
          <a href="{{ route('academic.registrations.index') }}" class="btn btn-outline-secondary btn-sm text-nowrap">
            <i class="bi bi-arrow-left me-1"></i> All Registration Slips
          </a>
        </div>
      </div>
    </div>

    <div class="card-body">
      <!-- Main Session Title & Timeline -->
      <div class="mb-3 pb-2 border-bottom">
        <h2 class="h3 fw-bold mb-1 text-success-emphasis">
          Academic Year {{ $currentSemester ? $currentSemester->academicYear->name : 'N/A' }} — {{ $currentSemester ? $currentSemester->name : 'N/A' }}
        </h2>
        <p class="text-body-secondary mb-0">
          <i class="bi bi-clock-history me-1 text-success"></i>
          Operational Timeline:
          <span class="fw-semibold text-body">
            {{ $currentSemester && $currentSemester->start_date ? $currentSemester->start_date->format('d M Y') : '15 Aug 2026' }}
            –
            {{ $currentSemester && $currentSemester->end_date ? $currentSemester->end_date->format('d M Y') : '20 Dec 2026' }}
          </span>
          <span class="badge bg-body-secondary text-body border ms-2">
            <i class="bi bi-tag-fill me-1 text-primary"></i> Term: Semester {{ $currentSemester ? $currentSemester->semester_number : 1 }}
          </span>
        </p>
      </div>

      <!-- Cohort Breakdown & Counter Cards in the SAME row / lining -->
      <div class="row g-3 align-items-stretch">
        <!-- Session Cohort Breakdown Banner (Left) -->
        <div class="col-12 col-lg-7">
          <div class="p-3 bg-body-secondary rounded-3 border h-100 d-flex flex-column justify-content-between">
            <div class="small fw-bold text-uppercase text-muted tracking-wide mb-2">
              <i class="bi bi-diagram-3-fill me-1 text-primary"></i> Session Cohort Breakdown (All Concurrent in This Session):
            </div>
            <div class="d-flex flex-wrap gap-2">
              <a href="{{ route('academic.registrations.active-session', array_merge(request()->query(), ['study_year' => 1])) }}"
                 class="badge {{ (string) $studyYear === '1' ? 'text-bg-primary' : 'bg-body text-body border' }} text-decoration-none px-3 py-2 fs-7 shadow-xs">
                <i class="bi bi-mortarboard-fill me-1 text-primary"></i>
                <span class="fw-semibold">Year 1 (Freshers):</span> {{ $stats['year_1'] }} Students
              </a>
              <a href="{{ route('academic.registrations.active-session', array_merge(request()->query(), ['study_year' => 2])) }}"
                 class="badge {{ (string) $studyYear === '2' ? 'text-bg-info text-dark' : 'bg-body text-body border' }} text-decoration-none px-3 py-2 fs-7 shadow-xs">
                <i class="bi bi-mortarboard-fill me-1 text-info"></i>
                <span class="fw-semibold">Year 2 (Continuing):</span> {{ $stats['year_2'] }} Students
              </a>
              <a href="{{ route('academic.registrations.active-session', array_merge(request()->query(), ['study_year' => 3])) }}"
                 class="badge {{ (string) $studyYear === '3' ? 'text-bg-dark' : 'bg-body text-body border' }} text-decoration-none px-3 py-2 fs-7 shadow-xs">
                <i class="bi bi-mortarboard-fill me-1 text-warning"></i>
                <span class="fw-semibold">Year 3 (Finalists):</span> {{ $stats['year_3'] }} Students
              </a>
              @if ($stats['year_4'] > 0)
                <a href="{{ route('academic.registrations.active-session', array_merge(request()->query(), ['study_year' => 4])) }}"
                   class="badge {{ (string) $studyYear === '4' ? 'text-bg-secondary' : 'bg-body text-body border' }} text-decoration-none px-3 py-2 fs-7 shadow-xs">
                  <i class="bi bi-mortarboard-fill me-1"></i>
                  <span class="fw-semibold">Year 4:</span> {{ $stats['year_4'] }} Students
                </a>
              @endif
              <a href="{{ route('academic.registrations.active-session', array_diff_key(request()->query(), ['study_year' => ''])) }}"
                 class="badge {{ empty($studyYear) ? 'text-bg-success' : 'bg-body text-body border' }} text-decoration-none px-3 py-2 fs-7 shadow-xs">
                <i class="bi bi-people-fill me-1"></i>
                <span class="fw-semibold">All Cohorts:</span> {{ $stats['total'] }} Students
              </a>
            </div>
          </div>
        </div>

        <!-- Metric Summary Counters (Right, aligned in the same row) -->
        <div class="col-12 col-lg-5">
          <div class="row g-2 h-100">
            <div class="col-6">
              <div class="border rounded-3 p-3 bg-body text-center shadow-xs h-100 d-flex flex-column justify-content-center">
                <div class="text-muted small fw-semibold text-uppercase">Total Registered</div>
                <div class="fs-2 fw-bold text-primary">{{ $stats['total'] }}</div>
                <div class="small text-muted">{{ $stats['total_credits'] }} Total Credit Units</div>
              </div>
            </div>
            <div class="col-6">
              <div class="border rounded-3 p-3 bg-body text-center shadow-xs h-100 d-flex flex-column justify-content-center">
                <div class="text-muted small fw-semibold text-uppercase">Approved Slips</div>
                <div class="fs-2 fw-bold text-success">{{ $stats['approved'] }}</div>
                <div class="small text-muted">{{ $stats['pending'] }} Pending / {{ $stats['draft'] }} Draft</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Architectural Principle Callout: Demonstration Explainer for Team Lead -->
      <div class="mt-4 pt-3 border-top">
        <div class="callout callout-info bg-body-tertiary border-start border-4 border-info p-3 rounded-2 shadow-xs mb-0">
          <div class="d-flex align-items-start gap-3">
            <div class="fs-3 text-info">
              <i class="bi bi-lightbulb-fill"></i>
            </div>
            <div>
              <h5 class="fw-bold text-info mb-1">
                Architectural Principle: Why a Single Calendar Session Drives All Study Levels
              </h5>
              <p class="mb-2 text-body">
                The university operates on <strong>ONE synchronized academic calendar timeline</strong> (e.g. <em>{{ $currentSemester ? $currentSemester->academicYear->name : '2026/2027' }} — Semester 1</em>).
                Freshers, continuing students, and finalists all attend classes, register, and sit exams during this exact same operational window.
              </p>
              <div class="row g-3 small">
                <div class="col-12 col-md-4">
                  <div class="p-2 bg-body rounded border h-100">
                    <span class="fw-bold text-success d-block mb-1"><i class="bi bi-calendar2-event me-1"></i> 1. Calendar Session (Timeline)</span>
                    Universal timeline window across the entire university. Controls start/end dates, add/drop deadlines, and financial billing.
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="p-2 bg-body rounded border h-100">
                    <span class="fw-bold text-primary d-block mb-1"><i class="bi bi-person-badge me-1"></i> 2. Student Progression Stage (Level)</span>
                    Where the student is in their academic journey (e.g., <strong>Y1S1</strong>, <strong>Y2S1</strong>, <strong>Y3S1</strong>). Tracks individual cohort seniority.
                  </div>
                </div>
                <div class="col-12 col-md-4">
                  <div class="p-2 bg-body rounded border h-100">
                    <span class="fw-bold text-warning-emphasis d-block mb-1"><i class="bi bi-book me-1"></i> 3. Curriculum Matrix (Catalog)</span>
                    Dynamically filters available course units and stage credit limits (Min/Max CU) matching each student's current progression stage.
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter & Actions Card -->
  <div class="card card-outline card-secondary mb-4">
    <div class="card-header py-2">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title fs-6 mb-0">
          <i class="bi bi-funnel me-1"></i> Filter Active Session Cohorts
        </h3>
        @if ($studyYear || $programmeId || $status)
          <a href="{{ route('academic.registrations.active-session', ['semester_id' => $currentSemester?->id]) }}" class="badge text-bg-warning text-decoration-none">
            <i class="bi bi-x-circle me-1"></i> Reset Active Filters
          </a>
        @endif
      </div>
    </div>
    <div class="card-body py-2">
      <form action="{{ route('academic.registrations.active-session') }}" method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="semester_id" value="{{ $currentSemester?->id }}">

        <div class="col-12 col-sm-6 col-md-3">
          <label for="study_year" class="form-label small fw-semibold mb-1">Student Progression Cohort</label>
          <select name="study_year" id="study_year" class="form-select form-select-sm">
            <option value="">-- All Study Years (Cohorts) --</option>
            <option value="1" {{ (string) $studyYear === '1' ? 'selected' : '' }}>Year 1 (Freshers)</option>
            <option value="2" {{ (string) $studyYear === '2' ? 'selected' : '' }}>Year 2 (Continuing)</option>
            <option value="3" {{ (string) $studyYear === '3' ? 'selected' : '' }}>Year 3 (Finalists)</option>
            <option value="4" {{ (string) $studyYear === '4' ? 'selected' : '' }}>Year 4</option>
          </select>
        </div>

        <div class="col-12 col-sm-6 col-md-4">
          <label for="programme_id" class="form-label small fw-semibold mb-1">Academic Programme</label>
          <select name="programme_id" id="programme_id" class="form-select form-select-sm">
            <option value="">-- All Programmes --</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ (string) $programmeId === (string) $prog->id ? 'selected' : '' }}>
                {{ $prog->code }} — {{ $prog->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
          <label for="status" class="form-label small fw-semibold mb-1">Registration Status</label>
          <select name="status" id="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Submitted (Pending)</option>
            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="add_drop_pending" {{ $status === 'add_drop_pending' ? 'selected' : '' }}>Add/Drop Review</option>
            <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
          </select>
        </div>

        <div class="col-12 col-sm-6 col-md-2 d-flex gap-1">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1" title="Filter Roster">
            <i class="bi bi-filter me-1"></i> Filter
          </button>
          <a href="{{ route('academic.registrations.active-session', ['semester_id' => $currentSemester?->id]) }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Live Demonstration Table Card -->
  <div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header py-3">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <h3 class="card-title fw-bold mb-0">
            <i class="bi bi-table text-primary me-2"></i>
            Live Multi-Cohort Demonstration Roster
            <span class="badge text-bg-primary ms-2">{{ $registrations->count() }} Students</span>
          </h3>
          <div class="text-muted small mt-1">
            Displaying students from distinct progression cohorts registered within the single active calendar session.
          </div>
        </div>
        <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0">
          <a href="{{ route('academic.registrations.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Register Student
          </a>
          <div class="input-group input-group-sm" style="width: 14rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="roster-filter" class="form-control" placeholder="Search student, stage..." autocomplete="off">
          </div>
        </div>
      </div>
    </div>

    <div class="card-body">
      <!-- Export & Print Actions -->
      <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="roster-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="roster-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="roster-print">
          <i class="bi bi-printer me-1"></i> Print Roster
        </button>
      </div>

      <!-- Comparison Table -->
      <div class="table-responsive">
        <table id="roster-table" class="table table-hover table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th tabulator-formatter="plaintext" width="45">#</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Student Details</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Student Progression Stage</th>
              <th tabulator-formatter="html">Active Calendar Session</th>
              <th tabulator-formatter="html">Courses Enrolled (Stage Catalog)</th>
              <th tabulator-formatter="html" hozAlign="center">Total Load</th>
              <th tabulator-formatter="html">Status</th>
              <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="90" hozAlign="right">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($registrations as $reg)
              <tr>
                <td>{{ $loop->iteration }}</td>

                <!-- Student Details -->
                <td>
                  <div class="fw-bold text-body">
                    {{ $reg->student->full_name }}
                    @if ($reg->student->id === 11)
                      <span class="badge text-bg-warning text-dark ms-1" title="Demo Freshmen Account"><i class="bi bi-star-fill"></i> Freshers Demo</span>
                    @endif
                  </div>
                  <div class="small font-monospace text-primary fw-semibold">{{ $reg->student->registration_number }}</div>
                  <div class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $reg->student->programme->name }}">
                    <span class="badge bg-secondary-subtle text-secondary border me-1">{{ $reg->student->programme->code }}</span>
                    {{ $reg->student->programme->name }}
                  </div>
                </td>

                <!-- Student Progression Stage (Distinct Badge by Study Year) -->
                <td>
                  @php
                    $stageCode = 'Y' . $reg->study_year . 'S' . $reg->semester_number;
                    $badgeClass = match ($reg->study_year) {
                        1 => 'bg-primary-subtle text-primary border border-primary-subtle',
                        2 => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                        3 => 'bg-dark-subtle text-dark-emphasis border border-dark-subtle',
                        default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                    };
                    $cohortLabel = match ($reg->study_year) {
                        1 => 'Freshers',
                        2 => 'Continuing',
                        3 => 'Finalists',
                        default => 'Senior',
                    };
                  @endphp
                  <div>
                    <span class="badge {{ $badgeClass }} px-2 py-1 fs-7 fw-semibold">
                      <i class="bi bi-mortarboard-fill me-1"></i> Year {{ $reg->study_year }}, Semester {{ $reg->semester_number }} ({{ $stageCode }})
                    </span>
                  </div>
                  <small class="text-muted d-block mt-1">
                    Cohort: <span class="fw-semibold text-body">{{ $cohortLabel }}</span>
                  </small>
                </td>

                <!-- Active Calendar Session (Constant Across All Cohorts) -->
                <td>
                  <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="bi bi-calendar2-check-fill me-1 text-success"></i>
                    {{ $reg->semester->academicYear->name }} — {{ $reg->semester->name }}
                  </span>
                  <small class="d-block text-muted mt-1">
                    {{ $reg->semester->start_date ? $reg->semester->start_date->format('M Y') : 'Aug 2026' }} – {{ $reg->semester->end_date ? $reg->semester->end_date->format('M Y') : 'Dec 2026' }}
                  </small>
                </td>

                <!-- Courses Enrolled (Stage Catalog) -->
                <td>
                  <div class="d-flex flex-wrap gap-1 align-items-center" style="max-width: 360px;">
                    @foreach ($reg->items as $item)
                      <span class="badge bg-body-secondary text-body border font-monospace" title="{{ $item->courseUnit->name }} ({{ number_format($item->credit_units, 1) }} CU)">
                        {{ $item->courseUnit->code }}
                      </span>
                    @endforeach
                    <span class="badge text-bg-light border small text-muted">
                      {{ $reg->items->count() }} units
                    </span>
                  </div>
                </td>

                <!-- Total Load -->
                <td class="text-center">
                  <span class="fw-bold fs-6 {{ $reg->total_credits >= 12.0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($reg->total_credits, 1) }} CU
                  </span>
                  @php
                    $curriculum = $reg->student->curriculum;
                    $bounds = $curriculum ? $curriculum->getStageCreditBounds($reg->study_year, $reg->semester_number) : null;
                  @endphp
                  @if ($bounds)
                    <small class="d-block text-muted" style="font-size: 0.75rem;">
                      [Min {{ $bounds['min'] }} | Max {{ $bounds['max'] }}]
                    </small>
                  @endif
                </td>

                <!-- Status -->
                <td>
                  <span class="badge {{ $reg->status_badge_class }}">
                    {{ $reg->status_label }}
                  </span>
                  <div class="small text-muted mt-1 font-monospace">
                    #REG-{{ str_pad($reg->id, 5, '0', STR_PAD_LEFT) }}
                  </div>
                </td>

                <!-- Actions -->
                <td class="text-end text-nowrap">
                  <a href="{{ route('academic.registrations.show', $reg) }}" class="btn btn-sm btn-outline-primary" title="View Official Registration Slip">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="{{ route('academic.registrations.print', $reg) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Official Slip">
                    <i class="bi bi-printer"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                  No student registrations found matching the selected cohort or filter criteria in this calendar session.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      initAdminLteDataTable('#roster-table', {
        filterInput: '#roster-filter',
        btnCsv: '#roster-export-csv',
        btnJson: '#roster-export-json',
        btnPrint: '#roster-print',
        filename: 'active_session_cohort_demonstration_roster',
      });
    });
  </script>
@endpush
