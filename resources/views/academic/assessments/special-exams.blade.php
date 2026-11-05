@extends('layouts.app')

@section('title', 'Special & Supplementary Examinations - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Special & Supplementary Examinations Desk')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('assessment.list') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item active" aria-current="page">Special &amp; Supplementary Exams</li>
@endsection

@section('content')
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
      <div class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-2"></i> Examination Submission Errors:</div>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- KPI Overview Row -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-4">
      <div class="info-box shadow-sm mb-0">
        <span class="info-box-icon text-bg-warning"><i class="bi bi-hospital"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-body-secondary">Pending Special Exams (MP)</span>
          <span class="info-box-number fs-4">{{ $pendingCount }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
      <div class="info-box shadow-sm mb-0">
        <span class="info-box-icon text-bg-success"><i class="bi bi-check2-circle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-body-secondary">Cleared Special &amp; Supp Papers</span>
          <span class="info-box-number fs-4">{{ $clearedCount }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
      <div class="info-box shadow-sm mb-0">
        <span class="info-box-icon text-bg-primary"><i class="bi bi-journal-check"></i></span>
        <div class="info-box-content">
          <span class="info-box-text text-body-secondary">Total Candidates in Scope</span>
          <span class="info-box-number fs-4">{{ $specialExamMarks->count() }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter Strip Card -->
  <div class="card card-outline card-secondary shadow-sm mb-4">
    <div class="card-body py-2">
      <form method="GET" action="{{ route('assessment.special-exams') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary mb-1">Academic Session / Semester</label>
          <select name="semester_id" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">-- All Semesters --</option>
            @foreach ($semesters as $sem)
              <option value="{{ $sem->id }}" {{ (string) $selectedSemesterId === (string) $sem->id ? 'selected' : '' }}>
                {{ $sem->name }} ({{ $sem->academicYear->name ?? 'Session' }}) {{ $sem->is_active ? '• Active' : '' }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label small text-body-secondary mb-1">Sitting Status</label>
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="" {{ empty($statusFilter) ? 'selected' : '' }}>All Statuses (Pending &amp; Cleared)</option>
            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending Special Exams (MP)</option>
            <option value="cleared" {{ $statusFilter === 'cleared' ? 'selected' : '' }}>Cleared (Special / Supplementary)</option>
          </select>
        </div>
        <div class="col-12 col-md-4 d-flex align-items-end gap-2 mt-auto">
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-funnel me-1"></i> Apply Filter
          </button>
          <a href="{{ route('assessment.special-exams') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Special Examinations Roster Card -->
  <div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h5 class="card-title mb-0 fw-bold">
          <i class="bi bi-table me-2 text-primary"></i>Special &amp; Supplementary Examinations Roster
        </h5>
        <div class="d-flex align-items-center gap-2">
          <div class="input-group input-group-sm" style="width: 15rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="special-filter" class="form-control" placeholder="Search candidate..." autocomplete="off">
          </div>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-export-csv">
            <i class="bi bi-filetype-csv me-1"></i> Export
          </button>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0" id="special-exams-table">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;" class="text-center">#</th>
              <th>Student Details</th>
              <th>Course Unit</th>
              <th class="text-center">CA Score</th>
              <th class="text-center">Exam Score</th>
              <th class="text-center">Final Score</th>
              <th class="text-center">Grade</th>
              <th class="text-center">Current Status</th>
              <th>Authority / Remarks</th>
              <th style="width: 140px;" class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($specialExamMarks as $index => $mark)
              @php
                $sheet = $mark->courseAssessmentSheet;
                $student = $mark->student;
              @endphp
              <tr>
                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                <td>
                  <div class="fw-bold">{{ $student->full_name }}</div>
                  <div class="font-monospace small text-primary">{{ $student->registration_number }}</div>
                  <small class="text-muted">{{ $student->programme->code ?? 'N/A' }}</small>
                </td>
                <td>
                  <span class="badge text-bg-primary">{{ $sheet->courseUnit->code ?? 'N/A' }}</span>
                  <div class="small fw-semibold mt-1">{{ $sheet->courseUnit->name ?? 'Course Unit' }}</div>
                  <small class="text-muted">{{ $sheet->semester->name ?? '' }} ({{ $sheet->semester->academicYear->name ?? '' }})</small>
                </td>
                <td class="text-center font-monospace">
                  {{ $mark->ca_score !== null ? number_format($mark->ca_score, 1) : '-' }} / {{ number_format($sheet->ca_weight ?? 40, 0) }}
                </td>
                <td class="text-center font-monospace">
                  @if ($mark->exam_score !== null)
                    <strong>{{ number_format($mark->exam_score, 1) }}</strong> / {{ number_format($sheet->exam_weight ?? 60, 0) }}
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="text-center font-monospace fw-bold">
                  {{ $mark->final_score !== null ? number_format($mark->final_score, 1) : '-' }}
                </td>
                <td class="text-center">
                  @if ($mark->grade_letter)
                    <span class="badge {{ $mark->grade_badge_class }}">{{ $mark->grade_letter }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="text-center">
                  @if ($mark->isMissedWithPermission())
                    <span class="badge text-bg-warning text-dark"><i class="bi bi-hospital me-1"></i>MP (Medical/Permitted)</span>
                  @elseif ($mark->isSpecialExam())
                    <span class="badge text-bg-info text-dark"><i class="bi bi-star-fill me-1"></i>Special Exam</span>
                  @elseif ($mark->isSupplementaryExam())
                    <span class="badge text-bg-primary"><i class="bi bi-arrow-repeat me-1"></i>Supplementary</span>
                  @else
                    <span class="badge text-bg-secondary">{{ ucfirst($mark->exam_status) }}</span>
                  @endif
                </td>
                <td>
                  <small class="d-block text-truncate" style="max-width: 200px;" title="{{ $mark->special_exam_remarks ?? $mark->lecturer_remarks }}">
                    {{ $mark->special_exam_remarks ?? $mark->lecturer_remarks ?? 'Pending sitting' }}
                  </small>
                  @if ($mark->special_exam_date)
                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $mark->special_exam_date->format('d M Y') }}</small>
                  @endif
                </td>
                <td class="text-end">
                  <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#deskSpecialExamModal{{ $mark->id }}"
                  >
                    <i class="bi bi-pencil-square me-1"></i>
                    {{ $mark->isMissedWithPermission() ? 'Enter Mark' : 'Edit Mark' }}
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                  <i class="bi bi-clipboard2-check fs-1 d-block mb-2 text-secondary"></i>
                  <h5>No Special or Supplementary Examination Records Found</h5>
                  <p class="mb-0">There are currently no students marked with pending missed papers or supplementary sittings in the selected criteria.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Special Exam Entry Modals -->
  @foreach ($specialExamMarks as $mark)
    @php
      $sheet = $mark->courseAssessmentSheet;
    @endphp
    <div class="modal fade" id="deskSpecialExamModal{{ $mark->id }}" tabindex="-1" aria-labelledby="deskSpecialExamModalLabel{{ $mark->id }}" aria-hidden="true">
      <div class="modal-dialog">
        <form action="{{ route('assessment.mark-special-exam', $mark) }}" method="POST">
          @csrf
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="deskSpecialExamModalLabel{{ $mark->id }}">
                <i class="bi bi-journal-check me-2"></i>Record Special / Supplementary Mark
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="bg-body-secondary p-3 rounded mb-3 border">
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-body-secondary small">Student:</span>
                  <strong>{{ $mark->student->full_name }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-body-secondary small">Reg Number:</span>
                  <span class="font-monospace fw-bold text-primary">{{ $mark->student->registration_number }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                  <span class="text-body-secondary small">Course Unit:</span>
                  <span>{{ $sheet->courseUnit->code ?? '' }} &bull; {{ $sheet->courseUnit->name ?? '' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="text-body-secondary small">Continuous Assessment (CA):</span>
                  <span class="fw-bold">{{ $mark->ca_score !== null ? number_format($mark->ca_score, 1) : '0.0' }} / {{ number_format($sheet->ca_weight ?? 40, 0) }} Marks</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Examination Sitting Type <span class="text-danger">*</span></label>
                <select name="sitting_type" class="form-select" required>
                  <option value="special" {{ $mark->sitting_type === 'special' || $mark->isMissedWithPermission() ? 'selected' : '' }}>
                    Special Examination (Uncapped / Full Marks)
                  </option>
                  <option value="supplementary" {{ $mark->sitting_type === 'supplementary' ? 'selected' : '' }}>
                    Supplementary Examination (Capped at Pass / Grade C)
                  </option>
                </select>
                <div class="form-text small">
                  Special Examinations apply to students who missed with medical or official permission. Supplementary marks are capped at pass mark.
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Examination Score (Max: {{ number_format($sheet->exam_weight ?? 60, 0) }}) <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input
                    type="number"
                    step="0.1"
                    min="0"
                    max="{{ $sheet->exam_weight ?? 60 }}"
                    name="special_exam_score"
                    class="form-control"
                    value="{{ old('special_exam_score', $mark->special_exam_score ?? $mark->exam_score) }}"
                    placeholder="e.g. 48.0"
                    required
                  >
                  <span class="input-group-text">/ {{ number_format($sheet->exam_weight ?? 60, 0) }} Marks</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Examination Date</label>
                <input
                  type="date"
                  name="special_exam_date"
                  class="form-control"
                  value="{{ old('special_exam_date', $mark->special_exam_date?->format('Y-m-d') ?? date('Y-m-d')) }}"
                >
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Approval Authority &amp; Remarks</label>
                <textarea
                  name="special_exam_remarks"
                  class="form-control"
                  rows="2"
                  placeholder="e.g. Faculty Board Minute 14/2026 - Medical clearance approved"
                >{{ old('special_exam_remarks', $mark->special_exam_remarks ?? $mark->lecturer_remarks) }}</textarea>
                <div class="form-text small">Include approval minute, medical certificate reference, or examiner remarks.</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary fw-semibold">
                <i class="bi bi-save me-1"></i> Save &amp; Recalculate Performance
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  @endforeach
@endsection
