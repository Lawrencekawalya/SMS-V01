@extends('layouts.app')

@section('title', $courseUnit->code . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Unit: ' . $courseUnit->code)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.courses.index') }}">Course Catalog</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $courseUnit->code }}</li>
@endsection

@section('content')
  <div class="row">
    <!-- Course Profile Summary -->
    <div class="col-12 col-lg-4">
      <div class="card card-primary card-outline mb-4">
        <div class="card-body">
          <div class="text-center mb-3">
            <span class="badge text-bg-primary fs-5 px-3 py-2">{{ $courseUnit->code }}</span>
            <h4 class="mt-3 mb-1 fw-bold">{{ $courseUnit->name }}</h4>
            <div class="mt-2">
              @if ($courseUnit->status === 'active')
                <span class="badge text-bg-success">Active Course</span>
              @else
                <span class="badge text-bg-secondary">Archived</span>
              @endif
            </div>
          </div>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted"><i class="bi bi-clock me-2"></i> Credit Weight</span>
              <span class="badge text-bg-light border fs-6 fw-bold">{{ number_format($courseUnit->credit_units, 1) }} CU</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted"><i class="bi bi-diagram-3 me-2"></i> Department</span>
              <a href="{{ route('academic.departments.show', $courseUnit->department) }}" class="fw-bold text-decoration-none">
                {{ $courseUnit->department->name }}
              </a>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted"><i class="bi bi-mortarboard me-2"></i> Faculty</span>
              <span>{{ $courseUnit->department->faculty->name }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted"><i class="bi bi-building me-2"></i> Campus</span>
              <span>{{ $courseUnit->department->faculty->campus->name ?? 'N/A' }}</span>
            </li>
          </ul>

          <div class="d-grid gap-2">
            <a href="{{ route('academic.courses.edit', $courseUnit) }}" class="btn btn-warning">
              <i class="bi bi-pencil me-1"></i> Edit Course Specification
            </a>
            <form action="{{ route('academic.courses.destroy', $courseUnit) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete course {{ $courseUnit->code }}?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger w-100">
                <i class="bi bi-trash me-1"></i> Delete Course Unit
              </button>
            </form>
            <a href="{{ route('academic.courses.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Catalog
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Course Syllabus & Description -->
    <div class="col-12 col-lg-8">
      <div class="card card-outline card-info mb-4">
        <div class="card-header">
          <h3 class="card-title">Course Description & Syllabus Outline</h3>
          <div class="card-tools me-0">
            <span class="badge text-bg-info">{{ number_format($courseUnit->credit_units, 1) }} Credit Units</span>
          </div>
        </div>
        <div class="card-body">
          @if ($courseUnit->description)
            <div class="lead fs-6" style="white-space: pre-line;">
              {{ $courseUnit->description }}
            </div>
          @else
            <div class="text-center text-muted py-5">
              <i class="bi bi-journal-text fs-2 d-block mb-2"></i>
              No syllabus outline or course description provided yet.
            </div>
          @endif

          <hr class="my-4">

          <h5 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i> Curriculum Progression Note</h5>
          <p class="text-body-secondary small mb-0">
            This course unit is managed within the Master Course Catalog. When mapped into Programme Curriculums (Phase 5), its availability for student enrollment will be dynamically determined by the student's <strong>Study Year (1, 2, 3...)</strong>, <strong>Academic Semester (1 or 2)</strong>, and <strong>Completed Course records</strong>.
          </p>
        </div>
      </div>
    </div>
  </div>
@endsection
