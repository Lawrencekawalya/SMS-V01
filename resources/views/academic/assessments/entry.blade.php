@extends('layouts.app')

@section('title', 'Mark Entry: ' . $sheet->courseUnit->code . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Lecturer Mark Entry Workspace')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.assessments.index') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item"><a href="{{ route('academic.assessments.show', $sheet) }}">{{ $sheet->courseUnit->code }}</a></li>
  <li class="breadcrumb-item active" aria-current="page">Mark Entry</li>
@endsection

@section('content')
  <!-- Flash Messages & Revision Notices -->
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <div class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-2"></i> Mark Submission Validation Errors:</div>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($sheet->status === 'returned_for_revision' && $sheet->moderation_remarks)
    <div class="callout callout-danger shadow-sm mb-4 border-start border-4 border-danger bg-danger-subtle p-3 rounded">
      <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-octagon-fill text-danger fs-3 me-3"></i>
        <div>
          <h5 class="fw-bold text-danger mb-1">Returned for Revision by Head of Department</h5>
          <p class="mb-0 text-dark">{{ $sheet->moderation_remarks }}</p>
          @if ($sheet->moderatedBy)
            <small class="text-muted d-block mt-1">
              Reviewed by <strong>{{ $sheet->moderatedBy->name }}</strong> on {{ $sheet->moderated_at ? $sheet->moderated_at->format('M d, Y H:i') : 'N/A' }}
            </small>
          @endif
        </div>
      </div>
    </div>
  @endif

  <!-- Course & Policy Overview Header -->
  <div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
          <span class="badge text-bg-primary fs-6">{{ $sheet->courseUnit->code }}</span>
          <span class="badge text-bg-secondary fs-6">{{ number_format($sheet->courseUnit->credit_units, 1) }} CU</span>
          <span class="badge {{ $sheet->status_badge_class }} fs-6">{{ $sheet->status_label }}</span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#gradingScaleReferenceModal">
            <i class="bi bi-award me-1"></i> Grading Scale Reference
          </button>
          <a href="{{ route('academic.assessments.show', $sheet) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Mark Sheet Overview
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <div class="text-body-secondary small">Course Unit</div>
          <h5 class="fw-bold mb-1">{{ $sheet->courseUnit->name }}</h5>
          <div class="text-muted small">Department: {{ $sheet->courseUnit->department->name ?? 'General Department' }}</div>
        </div>
        <div class="col-12 col-md-4">
          <div class="text-body-secondary small">Academic Term &amp; Assigned Lecturer</div>
          <div class="fw-semibold">{{ $sheet->semester->name }} ({{ $sheet->academicYear->name }})</div>
          <div class="text-muted small">
            <i class="bi bi-person-badge me-1"></i> {{ $sheet->instructor->name ?? 'Unassigned Lecturer' }}
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="text-body-secondary small">Grading Weights &amp; Pass Threshold</div>
          <div class="d-flex gap-2 align-items-center mt-1">
            <span class="badge text-bg-info fs-7 px-2 py-1">CA: Max {{ number_format($sheet->ca_weight, 0) }} Marks</span>
            <span class="badge text-bg-primary fs-7 px-2 py-1">Exam: Max {{ number_format($sheet->exam_weight, 0) }} Marks</span>
            <span class="badge text-bg-success fs-7 px-2 py-1">Pass: &ge; {{ number_format($sheet->pass_mark, 0) }}%</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Live Statistics Ticker Row -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="info-box shadow-sm mb-0">
        <span class="info-box-icon text-bg-secondary"><i class="bi bi-people-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-muted">Class Enrolled</span>
          <span class="info-box-number fs-4" id="ticker-total">{{ $studentMarks->count() }}</span>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="info-box shadow-sm mb-0">
        <span class="info-box-icon text-bg-info"><i class="bi bi-check2-circle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-muted">Marks Completed</span>
          <span class="info-box-number fs-4" id="ticker-completed">0</span>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="info-box shadow-sm mb-0">
        <span class="info-box-icon text-bg-primary"><i class="bi bi-calculator"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-muted">Class Average</span>
          <span class="info-box-number fs-4" id="ticker-average">--%</span>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="info-box shadow-sm mb-0">
        <span class="info-box-icon text-bg-success"><i class="bi bi-graph-up-arrow"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-muted">Pass Rate</span>
          <span class="info-box-number fs-4" id="ticker-pass-rate">--%</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Interactive Spreadsheet Mark Entry Form -->
  <form id="mark-entry-form" action="{{ route('academic.assessments.update', $sheet) }}" method="POST">
    @csrf
    @method('PUT')

    <input type="hidden" name="action" id="form-action-input" value="save_draft">

    <div class="card card-outline card-primary shadow-sm mb-4">
      <div class="card-header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold">
              <i class="bi bi-table me-2 text-primary"></i>Student Score Sheet
            </h5>
            <span class="badge bg-body-secondary text-body border" id="missing-counter-badge">
              Calculating status...
            </span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 16rem;">
              <span class="input-group-text"><i class="bi bi-search"></i></span>
              <input type="search" id="roster-search" class="form-control" placeholder="Search student name, reg no..." autocomplete="off">
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-clear-filter" title="Clear Search">
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        @if ($studentMarks->isEmpty())
          <div class="p-5 text-center text-muted">
            <i class="bi bi-person-x fs-1 d-block mb-3 text-secondary"></i>
            <h5>No Registered Students Found</h5>
            <p class="mb-0">There are currently no active registered students confirmed for this course unit in {{ $sheet->semester->name }}.</p>
          </div>
        @else
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0" id="mark-entry-table">
              <thead class="table-light sticky-top border-bottom">
                <tr>
                  <th style="width: 40px;" class="text-center">#</th>
                  <th style="min-width: 220px;">Student Details</th>
                  <th style="min-width: 150px;">Reg &amp; Student No</th>
                  <th style="width: 130px;" class="text-end">
                    CA <span class="badge text-bg-info">/{{ number_format($sheet->ca_weight, 0) }}</span>
                  </th>
                  <th style="width: 130px;" class="text-end">
                    Exam <span class="badge text-bg-primary">/{{ number_format($sheet->exam_weight, 0) }}</span>
                  </th>
                  <th style="width: 100px;" class="text-center">Total (/100)</th>
                  <th style="width: 80px;" class="text-center">Grade</th>
                  <th style="width: 90px;" class="text-center">GP</th>
                  <th style="width: 100px;" class="text-center">Status</th>
                  <th style="min-width: 180px;">Lecturer Remarks</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($studentMarks as $index => $mark)
                  @php
                    $student = $mark->student;
                    $studentInitials = strtoupper(substr($student->first_name ?? 'S', 0, 1) . substr($student->last_name ?? 'M', 0, 1));
                  @endphp
                  <tr class="student-mark-row" data-index="{{ $index }}">
                    <td class="text-center text-muted small fw-bold">
                      {{ $index + 1 }}
                      <input type="hidden" name="marks[{{ $index }}][student_mark_id]" value="{{ $mark->id }}">
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2 bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; font-size: 0.8rem;">
                          {{ $studentInitials }}
                        </div>
                        <div>
                          <div class="fw-bold student-name-text">{{ $student->full_name }}</div>
                          <small class="text-muted">{{ $student->programme->code ?? 'N/A' }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="fw-semibold font-monospace small student-reg-text">{{ $student->registration_number }}</div>
                      <small class="text-muted font-monospace">{{ $student->student_number }}</small>
                    </td>
                    <td>
                      <input
                        type="number"
                        name="marks[{{ $index }}][ca_score]"
                        step="0.1"
                        min="0"
                        max="{{ $sheet->ca_weight }}"
                        value="{{ old("marks.{$index}.ca_score", $mark->ca_score !== null ? number_format($mark->ca_score, 1, '.', '') : '') }}"
                        class="form-control form-control-sm text-end score-input ca-input"
                        placeholder="0.0"
                        data-row="{{ $index }}"
                        data-field="ca"
                        autocomplete="off"
                      >
                      <div class="invalid-feedback small">Max {{ number_format($sheet->ca_weight, 0) }}</div>
                    </td>
                    <td>
                      <input
                        type="number"
                        name="marks[{{ $index }}][exam_score]"
                        step="0.1"
                        min="0"
                        max="{{ $sheet->exam_weight }}"
                        value="{{ old("marks.{$index}.exam_score", $mark->exam_score !== null ? number_format($mark->exam_score, 1, '.', '') : '') }}"
                        class="form-control form-control-sm text-end score-input exam-input"
                        placeholder="0.0"
                        data-row="{{ $index }}"
                        data-field="exam"
                        autocomplete="off"
                      >
                      <div class="invalid-feedback small">Max {{ number_format($sheet->exam_weight, 0) }}</div>
                    </td>
                    <td class="text-center font-monospace fw-bold">
                      <span class="total-score-display fs-6">
                        {{ $mark->final_score !== null ? number_format($mark->final_score, 1) : '-' }}
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="badge grade-badge {{ $mark->grade_badge_class ?? 'bg-secondary' }}">
                        {{ $mark->grade_letter ?? '-' }}
                      </span>
                    </td>
                    <td class="text-center font-monospace">
                      <span class="grade-point-display small fw-semibold">
                        {{ $mark->grade_point !== null ? number_format($mark->grade_point, 1) . ' GP' : '-' }}
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="badge status-badge {{ $mark->final_score !== null ? ($mark->is_passed ? 'text-bg-success' : 'text-bg-danger') : 'bg-secondary-subtle text-secondary' }}">
                        {{ $mark->final_score !== null ? ($mark->is_passed ? 'Pass' : 'Retake') : 'Pending' }}
                      </span>
                    </td>
                    <td>
                      <input
                        type="text"
                        name="marks[{{ $index }}][lecturer_remarks]"
                        value="{{ old("marks.{$index}.lecturer_remarks", $mark->lecturer_remarks) }}"
                        class="form-control form-control-sm"
                        placeholder="Optional remarks..."
                        maxlength="255"
                      >
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

      <!-- Action Footer -->
      <div class="card-footer bg-body-tertiary d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
        <div class="d-flex align-items-center gap-2">
          <a href="{{ route('academic.assessments.show', $sheet) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Return to Overview
          </a>
          <span class="text-muted small d-none d-md-inline">
            <i class="bi bi-info-circle me-1"></i> Tip: Use <kbd>&uarr;</kbd> <kbd>&darr;</kbd> arrow keys or <kbd>Enter</kbd> to rapidly navigate student score cells.
          </span>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-outline-primary px-3" id="btn-save-draft">
            <i class="bi bi-save me-1"></i> Save as Draft
          </button>
          <button type="button" class="btn btn-primary px-4" id="btn-submit-hod">
            <i class="bi bi-send-check me-1"></i> Submit to Head of Department
          </button>
        </div>
      </div>
    </div>
  </form>

  <!-- Reusable Grading Scale Reference Modal -->
  @include('academic.assessments.partials.grading-scale-modal')
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Configuration & Grading Scale Data
      const caMax = {{ (float) $sheet->ca_weight }};
      const examMax = {{ (float) $sheet->exam_weight }};
      const passMark = {{ (float) $sheet->pass_mark }};

      const gradingScaleTiers = @json($gradingScaleTiers);

      const rows = document.querySelectorAll('.student-mark-row');
      const searchInput = document.getElementById('roster-search');
      const clearSearchBtn = document.getElementById('btn-clear-filter');
      const form = document.getElementById('mark-entry-form');
      const actionInput = document.getElementById('form-action-input');
      const btnSaveDraft = document.getElementById('btn-save-draft');
      const btnSubmitHod = document.getElementById('btn-submit-hod');

      // Ticker elements
      const tickerTotal = document.getElementById('ticker-total');
      const tickerCompleted = document.getElementById('ticker-completed');
      const tickerAverage = document.getElementById('ticker-average');
      const tickerPassRate = document.getElementById('ticker-pass-rate');
      const missingCounterBadge = document.getElementById('missing-counter-badge');

      /**
       * Resolve grade tier from final percentage score.
       */
      function resolveTier(score) {
        if (score === null || isNaN(score)) return null;

        for (const tier of gradingScaleTiers) {
          if (score >= tier.min_score && score <= tier.max_score) {
            return tier;
          }
        }

        // Fallback default
        if (score >= 80) return { grade_letter: 'A', grade_point: 5.0, badge_class: 'text-bg-success', is_pass: true };
        if (score >= 75) return { grade_letter: 'B+', grade_point: 4.5, badge_class: 'text-bg-success', is_pass: true };
        if (score >= 70) return { grade_letter: 'B', grade_point: 4.0, badge_class: 'text-bg-primary', is_pass: true };
        if (score >= 65) return { grade_letter: 'C+', grade_point: 3.5, badge_class: 'text-bg-info', is_pass: true };
        if (score >= 60) return { grade_letter: 'C', grade_point: 3.0, badge_class: 'text-bg-info', is_pass: true };
        if (score >= 55) return { grade_letter: 'D+', grade_point: 2.5, badge_class: 'text-bg-warning', is_pass: true };
        if (score >= 50) return { grade_letter: 'D', grade_point: 2.0, badge_class: 'text-bg-warning', is_pass: true };
        return { grade_letter: 'F', grade_point: 0.0, badge_class: 'text-bg-danger', is_pass: false };
      }

      /**
       * Recalculate a single row's total score, grade letter, GP, and status.
       */
      function updateRow(row) {
        const caInput = row.querySelector('.ca-input');
        const examInput = row.querySelector('.exam-input');
        const totalSpan = row.querySelector('.total-score-display');
        const gradeBadge = row.querySelector('.grade-badge');
        const gpSpan = row.querySelector('.grade-point-display');
        const statusBadge = row.querySelector('.status-badge');

        const caVal = caInput.value.trim() !== '' ? parseFloat(caInput.value) : null;
        const examVal = examInput.value.trim() !== '' ? parseFloat(examInput.value) : null;

        // Input validation highlighting
        let isCaValid = true;
        if (caVal !== null && (caVal < 0 || caVal > caMax)) {
          caInput.classList.add('is-invalid');
          isCaValid = false;
        } else {
          caInput.classList.remove('is-invalid');
        }

        let isExamValid = true;
        if (examVal !== null && (examVal < 0 || examVal > examMax)) {
          examInput.classList.add('is-invalid');
          isExamValid = false;
        } else {
          examInput.classList.remove('is-invalid');
        }

        if (caVal !== null && examVal !== null && isCaValid && isExamValid) {
          const total = Math.round((caVal + examVal) * 10) / 10;
          const tier = resolveTier(total);

          totalSpan.textContent = total.toFixed(1);
          if (tier) {
            gradeBadge.textContent = tier.grade_letter;
            gradeBadge.className = 'badge grade-badge ' + (tier.badge_class || 'text-bg-primary');
            gpSpan.textContent = tier.grade_point.toFixed(1) + ' GP';

            const isPassed = total >= passMark && tier.grade_point > 0.0;
            statusBadge.textContent = isPassed ? 'Pass' : 'Retake';
            statusBadge.className = 'badge status-badge ' + (isPassed ? 'text-bg-success' : 'text-bg-danger');
          }
        } else {
          totalSpan.textContent = '-';
          gradeBadge.textContent = '-';
          gradeBadge.className = 'badge grade-badge bg-secondary';
          gpSpan.textContent = '-';
          statusBadge.textContent = 'Pending';
          statusBadge.className = 'badge status-badge bg-secondary-subtle text-secondary';
        }
      }

      /**
       * Recalculate class summary statistics ticker.
       */
      function updateSummaryTicker() {
        const totalRows = rows.length;
        let completeCount = 0;
        let totalScoreSum = 0;
        let passCount = 0;

        rows.forEach(row => {
          const caInput = row.querySelector('.ca-input');
          const examInput = row.querySelector('.exam-input');
          const caVal = caInput.value.trim() !== '' ? parseFloat(caInput.value) : null;
          const examVal = examInput.value.trim() !== '' ? parseFloat(examInput.value) : null;

          if (caVal !== null && examVal !== null && caVal >= 0 && caVal <= caMax && examVal >= 0 && examVal <= examMax) {
            completeCount++;
            const total = caVal + examVal;
            totalScoreSum += total;
            if (total >= passMark) {
              passCount++;
            }
          }
        });

        if (tickerTotal) tickerTotal.textContent = totalRows;
        if (tickerCompleted) tickerCompleted.textContent = `${completeCount} / ${totalRows}`;

        if (completeCount > 0) {
          const avg = (totalScoreSum / completeCount).toFixed(1);
          const passRate = ((passCount / completeCount) * 100).toFixed(1);
          if (tickerAverage) tickerAverage.textContent = `${avg}%`;
          if (tickerPassRate) tickerPassRate.textContent = `${passRate}%`;
        } else {
          if (tickerAverage) tickerAverage.textContent = '--%';
          if (tickerPassRate) tickerPassRate.textContent = '--%';
        }

        const missing = totalRows - completeCount;
        if (missingCounterBadge) {
          if (missing === 0) {
            missingCounterBadge.className = 'badge text-bg-success';
            missingCounterBadge.innerHTML = '<i class="bi bi-check-all me-1"></i> All Marks Complete';
          } else {
            missingCounterBadge.className = 'badge text-bg-warning';
            missingCounterBadge.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> ${missing} Incomplete / Pending`;
          }
        }
      }

      // Initialize all rows on page load
      rows.forEach(row => {
        updateRow(row);
      });
      updateSummaryTicker();

      // Listen for score inputs
      document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('input', function () {
          const row = this.closest('.student-mark-row');
          if (row) {
            updateRow(row);
            updateSummaryTicker();
          }
        });

        // Fast Excel-like keyboard navigation
        input.addEventListener('keydown', function (e) {
          const field = this.getAttribute('data-field');
          const rowIndex = parseInt(this.getAttribute('data-row'), 10);

          if (e.key === 'ArrowDown' || e.key === 'Enter') {
            e.preventDefault();
            const nextRowInput = document.querySelector(`.score-input[data-field="${field}"][data-row="${rowIndex + 1}"]`);
            if (nextRowInput) {
              nextRowInput.focus();
              nextRowInput.select();
            }
          } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevRowInput = document.querySelector(`.score-input[data-field="${field}"][data-row="${rowIndex - 1}"]`);
            if (prevRowInput) {
              prevRowInput.focus();
              prevRowInput.select();
            }
          } else if (e.key === 'ArrowRight' && field === 'ca') {
            const examInput = document.querySelector(`.score-input[data-field="exam"][data-row="${rowIndex}"]`);
            if (examInput && this.selectionStart === this.value.length) {
              examInput.focus();
              examInput.select();
            }
          } else if (e.key === 'ArrowLeft' && field === 'exam') {
            const caInput = document.querySelector(`.score-input[data-field="ca"][data-row="${rowIndex}"]`);
            if (caInput && this.selectionStart === 0) {
              caInput.focus();
              caInput.select();
            }
          }
        });
      });

      // Quick filter search
      if (searchInput) {
        searchInput.addEventListener('input', function () {
          const term = this.value.toLowerCase().trim();
          rows.forEach(row => {
            const name = row.querySelector('.student-name-text')?.textContent.toLowerCase() || '';
            const reg = row.querySelector('.student-reg-text')?.textContent.toLowerCase() || '';
            if (term === '' || name.includes(term) || reg.includes(term)) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          });
        });
      }

      if (clearSearchBtn && searchInput) {
        clearSearchBtn.addEventListener('click', function () {
          searchInput.value = '';
          rows.forEach(row => row.style.display = '');
          searchInput.focus();
        });
      }

      // Save as Draft submission
      if (btnSaveDraft && form && actionInput) {
        btnSaveDraft.addEventListener('click', function () {
          actionInput.value = 'save_draft';
          form.submit();
        });
      }

      // Submit to HoD confirmation
      if (btnSubmitHod && form && actionInput) {
        btnSubmitHod.addEventListener('click', function () {
          // Check for any validation errors
          const invalidInputs = form.querySelectorAll('.score-input.is-invalid');
          if (invalidInputs.length > 0) {
            alert('Please correct the highlighted score errors before submitting to the Head of Department.');
            invalidInputs[0].focus();
            return;
          }

          const confirmed = confirm('Are you sure you want to finalize and submit this mark sheet to the Head of Department for moderation?\n\nOnce submitted, marks will be forwarded for departmental review.');
          if (confirmed) {
            actionInput.value = 'submit_hod';
            form.submit();
          }
        });
      }
    });
  </script>
@endpush
