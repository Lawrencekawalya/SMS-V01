@extends('layouts.app')

@section('title', 'Student Results & Transcripts - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Student Results & Academic Transcripts')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.assessments.index') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item active" aria-current="page">Results &amp; Transcripts</li>
@endsection

@section('content')
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Metrics KPI Row -->
  <div class="row mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-people-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Students</span>
          <span class="info-box-number fs-4">{{ $stats['total_students'] }}</span>
          <span class="progress-description text-muted small">Registered in system</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-award-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Normal Progress</span>
          <span class="info-box-number fs-4">{{ $stats['normal_progress'] }}</span>
          <span class="progress-description text-muted small">CGPA &ge; 2.00 threshold</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">On Probation</span>
          <span class="info-box-number fs-4">{{ $stats['probation'] }}</span>
          <span class="progress-description text-muted small">CGPA &lt; 2.00 alert</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-graph-up"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Mean CGPA</span>
          <span class="info-box-number fs-4">{{ number_format($stats['average_cgpa'], 2) }}</span>
          <span class="progress-description text-muted small">Evaluated student average</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Search & Filter Card -->
  <div class="card card-outline card-secondary mb-4 shadow-sm">
    <div class="card-header">
      <h3 class="card-title">
        <i class="bi bi-funnel me-1"></i> Filter Student Academic Records
      </h3>
    </div>
    <div class="card-body">
      <form action="{{ route('academic.results.index') }}" method="GET" class="row g-3">
        <div class="col-12 col-md-4">
          <label for="search" class="form-label small fw-semibold">Search Student</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Registration number, student number, name...">
          </div>
        </div>

        <div class="col-12 col-md-4">
          <label for="programme_id" class="form-label small fw-semibold">Programme</label>
          <select name="programme_id" id="programme_id" class="form-select form-select-sm">
            <option value="">-- All Academic Programmes --</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ (string) $programmeId === (string) $prog->id ? 'selected' : '' }}>
                {{ $prog->name }} ({{ $prog->code }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-4">
          <label for="academic_standing" class="form-label small fw-semibold">Academic Standing</label>
          <select name="academic_standing" id="academic_standing" class="form-select form-select-sm">
            <option value="">-- All Standings --</option>
            <option value="Normal Progress" {{ $standing === 'Normal Progress' ? 'selected' : '' }}>Normal Progress (CGPA &ge; 2.0)</option>
            <option value="Probation" {{ $standing === 'Probation' ? 'selected' : '' }}>Probation (CGPA &lt; 2.0)</option>
          </select>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2">
          <a href="{{ route('academic.results.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
          </a>
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-funnel-fill me-1"></i> Apply Filter
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Student Results Directory Card -->
  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h3 class="card-title mb-0">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Student Assessment Records &amp; Transcripts
      </h3>
      <div class="d-flex align-items-center gap-2">
        <div class="input-group input-group-sm" style="width: 15rem;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" id="results-filter" class="form-control" placeholder="Quick search..." autocomplete="off">
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="results-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="results-print">
          <i class="bi bi-printer me-1"></i> Print Directory
        </button>
      </div>

      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0" id="results-table">
          <thead class="table-light">
            <tr>
              <th tabulator-formatter="plaintext" width="50">#</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Student Identity</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Programme &amp; Department</th>
              <th tabulator-formatter="html" hozAlign="center">Stage</th>
              <th tabulator-formatter="html" hozAlign="center">Cumulative CGPA</th>
              <th tabulator-formatter="html" hozAlign="center">Standing</th>
              <th tabulator-formatter="html" hozAlign="center">Evaluated Terms</th>
              <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="220" hozAlign="right">Official Documents</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($students as $student)
              @php
                $cgpa = (float) ($student->cumulative_gpa ?? 0.0);
                $performances = $student->semesterPerformances;
                $latestPerf = $performances->sortByDesc('created_at')->first();
                $standingLabel = $latestPerf?->academic_standing ?? ($cgpa >= 2.0 ? 'Normal Progress' : ($cgpa > 0 ? 'Probation' : 'Not Evaluated'));
              @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <div class="d-flex flex-column">
                    <strong class="text-body">{{ $student->user->name ?? $student->full_name }}</strong>
                    <span class="font-monospace small text-primary fw-semibold">{{ $student->registration_number }}</span>
                    <small class="text-muted font-monospace">ID: {{ $student->student_number }}</small>
                  </div>
                </td>
                <td>
                  <div class="fw-semibold text-body">{{ $student->programme->name ?? 'General Programme' }}</div>
                  <small class="text-muted">{{ $student->programme->department->name ?? '' }}</small>
                </td>
                <td class="text-center">
                  <span class="badge bg-body-secondary text-body border">
                    {{ $student->academic_stage }}
                  </span>
                </td>
                <td class="text-center font-monospace">
                  @if ($cgpa > 0)
                    <strong class="fs-6 {{ $cgpa >= 4.4 ? 'text-success' : ($cgpa >= 3.6 ? 'text-primary' : ($cgpa >= 2.0 ? 'text-body' : 'text-danger')) }}">
                      {{ number_format($cgpa, 2) }}
                    </strong>
                  @else
                    <span class="text-muted fst-italic">0.00</span>
                  @endif
                </td>
                <td class="text-center">
                  <span class="badge {{ $standingLabel === 'Normal Progress' ? 'text-bg-success' : ($standingLabel === 'Probation' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                    {{ $standingLabel }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge text-bg-info">
                    {{ $performances->count() }} Term(s)
                  </span>
                </td>
                <td class="text-end text-nowrap">
                  <div class="d-inline-flex gap-1 justify-content-end align-items-center">
                    {{-- Semester Result Slip dropdown or direct link --}}
                    @if ($performances->isNotEmpty())
                      <div class="btn-group btn-group-sm">
                        @if ($performances->count() === 1)
                          <a href="{{ route('academic.results.slip', ['student' => $student, 'semester' => $performances->first()->semester_id]) }}"
                             class="btn btn-outline-primary"
                             title="Print Semester Result Slip">
                            <i class="bi bi-file-earmark-check me-1"></i> Result Slip
                          </a>
                        @else
                          <button type="button"
                                  class="btn btn-outline-primary"
                                  data-bs-toggle="modal"
                                  data-bs-target="#semesterSlipModal-{{ $student->id }}"
                                  title="Select Semester to View Result Slip">
                            <i class="bi bi-file-earmark-check me-1"></i> Result Slip <i class="bi bi-chevron-down ms-1 small"></i>
                          </button>
                        @endif
                      </div>
                    @elseif ($semesters->isNotEmpty())
                      <a href="{{ route('academic.results.slip', ['student' => $student, 'semester' => $semesters->first()->id]) }}"
                         class="btn btn-sm btn-outline-secondary"
                         title="View Active Semester Results">
                        <i class="bi bi-file-earmark-check me-1"></i> Result Slip
                      </a>
                    @endif

                    {{-- Cumulative Academic Transcript Link --}}
                    <a href="{{ route('academic.results.transcript', $student) }}"
                       class="btn btn-sm btn-primary"
                       title="View Official Cumulative Academic Transcript">
                      <i class="bi bi-mortarboard-fill me-1"></i> Transcript
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                  <div class="fw-semibold">No student records found matching the filter criteria.</div>
                  <div class="small">Try clearing search filters or selecting another academic programme.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">
          Showing {{ $students->firstItem() ?? 0 }} to {{ $students->lastItem() ?? 0 }} of {{ $students->total() }} students
        </small>
        {{ $students->links() }}
      </div>
    </div>
  </div>

  <!-- Per-Student Semester Result Slip Modals -->
  @foreach ($students as $student)
    @php
      $studentPerfs = $student->semesterPerformances;
    @endphp
    @if ($studentPerfs->count() > 1)
      <div class="modal fade" id="semesterSlipModal-{{ $student->id }}" tabindex="-1" aria-labelledby="semesterSlipModalLabel-{{ $student->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content shadow border-0">
            <div class="modal-header bg-body-secondary border-bottom">
              <div>
                <h5 class="modal-title fw-bold mb-0" id="semesterSlipModalLabel-{{ $student->id }}">
                  <i class="bi bi-file-earmark-text text-primary me-2"></i>Select Semester Result Slip
                </h5>
                <div class="small text-muted mt-1">
                  <strong>{{ $student->user->name ?? $student->full_name }}</strong> &bull;
                  <span class="font-monospace text-primary">{{ $student->registration_number }}</span> &bull;
                  {{ $student->programme->code ?? 'N/A' }}
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
              <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                  This candidate has evaluated results across <strong>{{ $studentPerfs->count() }} semesters</strong>. Select the semester slip you wish to inspect or print:
                </div>
              </div>
              <div class="list-group gap-2 border-0">
                @foreach ($studentPerfs as $perf)
                  <a href="{{ route('academic.results.slip', ['student' => $student, 'semester' => $perf->semester_id]) }}" 
                     class="list-group-item list-group-item-action border rounded p-3 d-flex justify-content-between align-items-center shadow-sm">
                    <div>
                      <div class="fw-bold fs-6 text-primary">
                        {{ $perf->semester->name ?? 'Term' }} 
                        <span class="text-body-secondary fw-normal">({{ $perf->semester->academicYear->name ?? '' }})</span>
                      </div>
                      <small class="text-body-secondary">
                        Registered: <strong>{{ number_format($perf->credit_units_registered, 1) }} CU</strong> &bull; 
                        Earned: <strong>{{ number_format($perf->credit_units_earned, 1) }} CU</strong>
                      </small>
                      <div class="small mt-1">
                        Standing: <span class="badge {{ $perf->academic_standing === 'Normal Progress' ? 'text-bg-success' : 'text-bg-danger' }}">{{ $perf->academic_standing }}</span>
                      </div>
                    </div>
                    <div class="text-end ps-3">
                      <div class="fs-5 fw-bold font-monospace text-dark-emphasis">{{ number_format($perf->gpa, 2) }} <span class="fs-7 text-muted fw-normal">GPA</span></div>
                      <span class="btn btn-sm btn-outline-primary mt-1">
                        <i class="bi bi-printer me-1"></i> View Slip
                      </span>
                    </div>
                  </a>
                @endforeach
              </div>
            </div>
            <div class="modal-footer bg-body-secondary border-top py-2">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endforeach
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      initAdminLteDataTable('#results-table', {
        filterInput: '#results-filter',
        btnCsv: '#results-export-csv',
        btnPrint: '#results-print',
        filename: 'student_results_and_transcripts_export',
      });
    });
  </script>
@endpush
