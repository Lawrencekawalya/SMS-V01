@extends('layouts.app')

@section('title', 'Special Examinations: ' . $sheet->courseUnit->code . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Special & Supplementary Examinations')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('assessment.list') }}">Examinations &amp; Grading</a></li>
  <li class="breadcrumb-item"><a href="{{ route('assessment.show', $sheet) }}">{{ $sheet->courseUnit->code }}</a></li>
  <li class="breadcrumb-item active" aria-current="page">Special Exams</li>
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
      <div class="fw-bold mb-1"><i class="bi bi-x-octagon-fill me-2"></i> Submission Errors:</div>
      <ul class="mb-0 ps-3">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <!-- Course Context Header -->
  <div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <span class="badge text-bg-primary fs-6">{{ $sheet->courseUnit->code }}</span>
          <span class="badge {{ $sheet->status_badge_class }} fs-6 ms-1">{{ $sheet->status_label }}</span>
          <h4 class="mb-0 mt-2 fw-bold">{{ $sheet->courseUnit->name }}</h4>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('assessment.show', $sheet) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Main Sheet
          </a>
          <a href="{{ route('assessment.special-exams') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-journal-check me-1"></i> Central Special Exams Desk
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12 col-md-4">
          <div class="text-body-secondary small">Academic Term</div>
          <div class="fw-semibold">{{ $sheet->semester->name }} ({{ $sheet->academicYear->name }})</div>
        </div>
        <div class="col-12 col-md-4">
          <div class="text-body-secondary small">Instructor</div>
          <div class="fw-semibold">{{ $sheet->instructor->name ?? 'Unassigned' }}</div>
        </div>
        <div class="col-12 col-md-4">
          <div class="text-body-secondary small">Assessment Weighting</div>
          <span class="badge text-bg-info">CA: {{ number_format($sheet->ca_weight, 0) }}%</span>
          <span class="badge text-bg-primary ms-1">Exam: {{ number_format($sheet->exam_weight, 0) }}%</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Candidates Table -->
  <div class="card card-outline card-secondary shadow-sm mb-4">
    <div class="card-header">
      <h5 class="card-title mb-0 fw-bold">
        <i class="bi bi-people-fill me-2 text-primary"></i>Candidates Eligible for Special / Supplementary Examinations
      </h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;" class="text-center">#</th>
              <th>Student Details</th>
              <th class="text-center">CA Score</th>
              <th class="text-center">Special Exam Score</th>
              <th class="text-center">Final Score</th>
              <th class="text-center">Grade</th>
              <th class="text-center">Status</th>
              <th>Authority / Remarks</th>
              <th style="width: 140px;" class="text-end">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($sheet->studentMarks as $index => $mark)
              <tr>
                <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                <td>
                  <div class="fw-bold">{{ $mark->student->full_name }}</div>
                  <div class="font-monospace small text-primary">{{ $mark->student->registration_number }}</div>
                </td>
                <td class="text-center font-monospace">
                  {{ $mark->ca_score !== null ? number_format($mark->ca_score, 1) : '-' }} / {{ number_format($sheet->ca_weight, 0) }}
                </td>
                <td class="text-center font-monospace">
                  @if ($mark->special_exam_score !== null)
                    <strong>{{ number_format($mark->special_exam_score, 1) }}</strong> / {{ number_format($sheet->exam_weight, 0) }}
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
                  <small class="d-block">{{ $mark->special_exam_remarks ?? $mark->lecturer_remarks ?? 'Pending sitting' }}</small>
                  @if ($mark->special_exam_date)
                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $mark->special_exam_date->format('d M Y') }}</small>
                  @endif
                </td>
                <td class="text-end">
                  <button
                    type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#specialExamModal{{ $mark->id }}"
                  >
                    <i class="bi bi-pencil-square me-1"></i>
                    {{ $mark->isMissedWithPermission() ? 'Enter Mark' : 'Edit Mark' }}
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                  <i class="bi bi-check-circle fs-2 d-block mb-2 text-success"></i>
                  No students in this course unit have pending missed papers or special examination sittings.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  @include('academic.assessments.partials.special-exam-modal')
@endsection
