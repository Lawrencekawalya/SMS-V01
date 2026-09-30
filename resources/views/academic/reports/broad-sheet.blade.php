@extends('layouts.app')

@section('title', 'Senate Master Broad-Sheet - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Senate Master Broad-Sheet')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.assessments.index') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item active" aria-current="page">Senate Master Broad-Sheet</li>
@endsection

@section('content')
  <!-- Filter & Action Card -->
  <div class="card card-outline card-primary mb-4 shadow-sm no-print">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h3 class="card-title m-0">
        <i class="bi bi-table me-1 text-primary"></i> Cohort &amp; Session Filter
      </h3>
      <div class="d-flex gap-2">
        @if ($selectedProgramme && $selectedSemester)
          <a href="{{ route('academic.reports.broad-sheet.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
          </a>
          <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Print Ledger
          </button>
        @endif
      </div>
    </div>
    <div class="card-body">
      <form action="{{ route('academic.reports.broad-sheet') }}" method="GET" class="row g-3">
        <div class="col-12 col-md-4">
          <label for="programme_id" class="form-label small fw-semibold">Academic Programme <span class="text-danger">*</span></label>
          <select name="programme_id" id="programme_id" class="form-select form-select-sm" required>
            <option value="">-- Select Programme --</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ (string) ($selectedProgramme?->id ?? '') === (string) $prog->id ? 'selected' : '' }}>
                {{ $prog->name }} ({{ $prog->code }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-3">
          <label for="academic_year_id" class="form-label small fw-semibold">Academic Year</label>
          <select name="academic_year_id" id="academic_year_id" class="form-select form-select-sm">
            @foreach ($academicYears as $ay)
              <option value="{{ $ay->id }}" {{ (string) ($selectedAcademicYear?->id ?? '') === (string) $ay->id ? 'selected' : '' }}>
                {{ $ay->name }} {{ $ay->is_current ? '(Current)' : '' }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-3">
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
          <label for="study_year" class="form-label small fw-semibold">Study Year</label>
          <select name="study_year" id="study_year" class="form-select form-select-sm">
            @for ($y = 1; $y <= 5; $y++)
              <option value="{{ $y }}" {{ (int) $selectedStudyYear === $y ? 'selected' : '' }}>
                Year {{ $y }}
              </option>
            @endfor
          </select>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-funnel me-1"></i> Generate Broad-Sheet
          </button>
        </div>
      </form>
    </div>
  </div>

  @if ($selectedProgramme && $selectedSemester)
    <!-- Broad-Sheet Official Header (Visible on print & screen) -->
    <div class="text-center mb-4">
      <h4 class="fw-bold mb-1 text-uppercase">{{ $university->name ?? 'BISHOP STUART UNIVERSITY' }}</h4>
      <h6 class="text-secondary mb-1">OFFICE OF THE ACADEMIC REGISTRAR &bull; SENATE EXAMINATION BOARD</h6>
      <h5 class="fw-bold text-primary mb-1">MASTER SENATE BROAD-SHEET (RESULTS GAZETTE)</h5>
      <p class="small text-muted mb-0">
        <strong>Programme:</strong> {{ $selectedProgramme->name }} ({{ $selectedProgramme->code }}) &bull;
        <strong>Department:</strong> {{ $selectedProgramme->department->name ?? 'General' }} &bull;
        <strong>Faculty:</strong> {{ $selectedProgramme->department->faculty->name ?? 'General' }} &bull;
        <strong>Term:</strong> {{ $selectedSemester->name }} ({{ $selectedSemester->academicYear->name ?? '' }}) &bull;
        <strong>Cohort Stage:</strong> Study Year {{ $selectedStudyYear }}
      </p>
    </div>

    <!-- Cohort KPI Summary Badges -->
    <div class="row g-2 mb-3 no-print">
      <div class="col-6 col-md-3">
        <div class="card bg-body-tertiary border-0 shadow-sm p-2 text-center">
          <div class="small text-muted fw-semibold">Total Students</div>
          <div class="fs-5 fw-bold text-primary">{{ $cohortStats['total_students'] }}</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card bg-body-tertiary border-0 shadow-sm p-2 text-center">
          <div class="small text-muted fw-semibold">Normal Progress</div>
          <div class="fs-5 fw-bold text-success">{{ $cohortStats['normal_progress_count'] }}</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card bg-body-tertiary border-0 shadow-sm p-2 text-center">
          <div class="small text-muted fw-semibold">On Probation</div>
          <div class="fs-5 fw-bold text-danger">{{ $cohortStats['probation_count'] }}</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card bg-body-tertiary border-0 shadow-sm p-2 text-center">
          <div class="small text-muted fw-semibold">Cohort Mean GPA / CGPA</div>
          <div class="fs-5 fw-bold text-info">
            {{ number_format($cohortStats['cohort_average_gpa'], 2) }} / {{ number_format($cohortStats['cohort_average_cgpa'], 2) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Master 2D Ledger Matrix Card -->
    <div class="card card-outline card-secondary shadow-sm mb-4">
      <div class="card-header d-flex justify-content-between align-items-center py-2 no-print">
        <h3 class="card-title fs-6 fw-bold m-0">
          <i class="bi bi-grid-3x3-gap me-1 text-secondary"></i> Examination Broad-Sheet Matrix
        </h3>
        <span class="badge bg-secondary-subtle text-secondary-emphasis">
          {{ count($studentsMatrix) }} Candidate(s) &bull; {{ $courseUnits->count() }} Course Unit(s)
        </span>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 700px; overflow-x: auto;">
          <table class="table table-bordered table-sm table-hover align-middle mb-0 text-center broad-sheet-table" style="font-size: 0.82rem;">
            <thead class="table-light sticky-top shadow-sm">
              <tr class="align-middle">
                <th rowspan="2" class="sticky-col first-col bg-light" style="width: 40px;">#</th>
                <th rowspan="2" class="sticky-col second-col bg-light text-start" style="min-width: 130px;">Reg Number</th>
                <th rowspan="2" class="sticky-col third-col bg-light text-start" style="min-width: 170px;">Student Name</th>
                @if ($courseUnits->isNotEmpty())
                  <th colspan="{{ $courseUnits->count() }}" class="text-center bg-primary-subtle text-primary-emphasis fw-bold py-1">
                    Course Units &amp; Assessment Performance
                  </th>
                @endif
                <th colspan="7" class="text-center bg-dark-subtle text-dark-emphasis fw-bold py-1">
                  Cumulative Academic Standing
                </th>
              </tr>
              <tr class="align-middle">
                @forelse ($courseUnits as $cu)
                  <th style="min-width: 95px;" class="fw-semibold text-wrap px-1">
                    <div>{{ $cu->code }}</div>
                    <div class="text-muted fw-normal" style="font-size: 0.72rem;">{{ number_format($cu->credit_units, 1) }} CU</div>
                  </th>
                @empty
                  <th class="text-muted">No Courses Listed</th>
                @endforelse
                <th style="min-width: 60px;">Reg CU</th>
                <th style="min-width: 60px;">Earn CU</th>
                <th style="min-width: 65px;">WGP</th>
                <th style="min-width: 60px;" class="bg-info-subtle">GPA</th>
                <th style="min-width: 60px;" class="bg-primary-subtle">CGPA</th>
                <th style="min-width: 110px;">Standing</th>
                <th style="min-width: 130px;" class="text-start">Remarks / Retakes</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($studentsMatrix as $idx => $row)
                @php
                  $student = $row['student'];
                  $marksMap = $row['marks'];
                  $isProbation = $row['standing'] === 'Probation';
                @endphp
                <tr class="{{ $isProbation ? 'table-danger-subtle' : '' }}">
                  <td class="sticky-col first-col bg-white text-muted fw-semibold">{{ $idx + 1 }}</td>
                  <td class="sticky-col second-col bg-white text-start font-monospace small fw-bold">{{ $student->registration_number }}</td>
                  <td class="sticky-col third-col bg-white text-start fw-semibold text-truncate" style="max-width: 180px;">
                    {{ $student->user->name ?? $student->full_name }}
                  </td>

                  @foreach ($courseUnits as $cu)
                    @php
                      $mark = $marksMap[$cu->id] ?? null;
                    @endphp
                    <td class="px-1 {{ $mark && ! $mark['is_passed'] ? 'bg-danger-subtle text-danger' : '' }}">
                      @if ($mark && $mark['final_score'] !== null)
                        <div class="fw-bold">{{ number_format($mark['final_score'], 1) }}</div>
                        <div style="font-size: 0.72rem;">
                          <span class="badge {{ $mark['is_passed'] ? 'bg-secondary' : 'bg-danger' }} p-1" style="font-size: 0.65rem;">
                            {{ $mark['grade_letter'] }} ({{ number_format($mark['grade_point'], 1) }})
                          </span>
                        </div>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  @endforeach

                  <td class="fw-semibold">{{ number_format($row['credits_registered'], 1) }}</td>
                  <td class="fw-semibold text-success">{{ number_format($row['credits_earned'], 1) }}</td>
                  <td class="fw-semibold">{{ number_format($row['weighted_points'], 1) }}</td>
                  <td class="fw-bold bg-info-subtle">{{ number_format($row['gpa'], 2) }}</td>
                  <td class="fw-bold bg-primary-subtle text-primary">{{ number_format($row['cgpa'], 2) }}</td>
                  <td>
                    @if ($row['standing'] === 'Normal Progress')
                      <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">Normal Progress</span>
                    @else
                      <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">Probation</span>
                    @endif
                  </td>
                  <td class="text-start small">
                    @if (! empty($row['failed_courses']))
                      <span class="text-danger fw-semibold">
                        <i class="bi bi-arrow-repeat me-1"></i> Retake: {{ implode(', ', $row['failed_courses']) }}
                      </span>
                    @else
                      <span class="text-success"><i class="bi bi-check-circle me-1"></i> Pass</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ 10 + $courseUnits->count() }}" class="text-center py-4 text-muted">
                    <i class="bi bi-info-circle fs-4 d-block mb-1"></i>
                    No student registration or performance records found for this cohort stage.
                  </td>
                </tr>
              @endforelse
            </tbody>
            @if (! empty($courseStats) && $courseUnits->isNotEmpty())
              <tfoot class="table-light sticky-bottom shadow-sm fw-semibold">
                <!-- Course Average Mark Row -->
                <tr>
                  <th colspan="3" class="text-end text-uppercase pe-2 sticky-col first-col bg-light">Course Average:</th>
                  @foreach ($courseUnits as $cu)
                    @php $cs = $courseStats[$cu->id] ?? null; @endphp
                    <th class="text-center text-primary">
                      {{ $cs && $cs['evaluated_count'] > 0 ? number_format($cs['average'], 1) . '%' : '-' }}
                    </th>
                  @endforeach
                  <th colspan="7" class="bg-light"></th>
                </tr>
                <!-- Course Pass Rate Row -->
                <tr>
                  <th colspan="3" class="text-end text-uppercase pe-2 sticky-col first-col bg-light">Pass Rate (%):</th>
                  @foreach ($courseUnits as $cu)
                    @php $cs = $courseStats[$cu->id] ?? null; @endphp
                    <th class="text-center {{ $cs && $cs['pass_rate'] < 50 ? 'text-danger' : 'text-success' }}">
                      {{ $cs && $cs['evaluated_count'] > 0 ? number_format($cs['pass_rate'], 1) . '%' : '-' }}
                    </th>
                  @endforeach
                  <th colspan="7" class="bg-light"></th>
                </tr>
              </tfoot>
            @endif
          </table>
        </div>
      </div>
    </div>

    <!-- Official Senate Signature Endorsement Block (Visible on print & screen bottom) -->
    <div class="mt-4 pt-3 border-top page-break-inside-avoid">
      <div class="row text-center mt-3 g-4">
        <div class="col-4">
          <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
          <div class="fw-bold small text-uppercase">Head of Department</div>
          <div class="text-muted" style="font-size: 0.75rem;">Date: ________________________</div>
        </div>
        <div class="col-4">
          <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
          <div class="fw-bold small text-uppercase">Dean of Faculty</div>
          <div class="text-muted" style="font-size: 0.75rem;">Date: ________________________</div>
        </div>
        <div class="col-4">
          <div class="border-bottom border-dark pb-4 mb-2 mx-auto" style="width: 80%;"></div>
          <div class="fw-bold small text-uppercase">Academic Registrar</div>
          <div class="text-muted" style="font-size: 0.75rem;">Date: ________________________</div>
        </div>
      </div>
    </div>
  @else
    <div class="alert alert-info shadow-sm text-center py-5">
      <i class="bi bi-funnel fs-1 d-block mb-2 text-primary"></i>
      <h5 class="fw-bold">Select Cohort &amp; Examination Session</h5>
      <p class="text-muted mb-0">Please choose an Academic Programme and Session from the filter bar above to generate the Master Senate Broad-Sheet.</p>
    </div>
  @endif
@endsection

@push('styles')
<style>
  @media print {
    @page {
      size: landscape;
      margin: 8mm;
    }
    .no-print, .main-header, .main-sidebar, .app-header, .app-sidebar, .app-footer, .breadcrumb {
      display: none !important;
    }
    .app-main, .content-wrapper, body {
      margin: 0 !important;
      padding: 0 !important;
      background: #fff !important;
    }
    .table-responsive {
      overflow: visible !important;
      max-height: none !important;
    }
    .broad-sheet-table {
      font-size: 0.68rem !important;
      width: 100% !important;
    }
    .page-break-inside-avoid {
      page-break-inside: avoid;
    }
  }

  /* Sticky freeze pane columns for large horizontal matrices */
  @media screen {
    .broad-sheet-table .sticky-col {
      position: sticky;
      z-index: 2;
    }
    .broad-sheet-table .first-col {
      left: 0;
    }
    .broad-sheet-table .second-col {
      left: 40px;
    }
    .broad-sheet-table .third-col {
      left: 170px;
      box-shadow: 2px 0 4px rgba(0,0,0,0.08);
    }
  }
</style>
@endpush
