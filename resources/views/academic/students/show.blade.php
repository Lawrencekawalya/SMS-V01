@extends('layouts.app')

@section('title', $student->full_name . ' (' . $student->registration_number . ') - ' . config('app.name', 'SMS-V01'))
@section('page-title', $student->full_name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Structure</li>
  <li class="breadcrumb-item"><a href="{{ route('academic.students.index') }}">Students</a></li>
  <li class="breadcrumb-item active" aria-current="page">{{ $student->registration_number }}</li>
@endsection

@section('content')
  <div class="row">
    <!-- Student Profile Card -->
    <div class="col-12 col-lg-5">
      <div class="card card-primary card-outline mb-4">
        <div class="card-body box-profile">
          <div class="text-center mb-3">
            <span class="rounded-circle bg-primary-subtle p-3 d-inline-block text-primary">
              <i class="bi bi-person-fill fs-1"></i>
            </span>
          </div>
          <h3 class="profile-username text-center mb-1">{{ $student->full_name }}</h3>
          <p class="text-muted text-center mb-2">
            Reg No: <strong class="font-monospace text-primary">{{ $student->registration_number }}</strong>
            <span class="mx-1">&bull;</span>
            ID: <span class="font-monospace">{{ $student->student_number }}</span>
          </p>
          <div class="text-center mb-3">
            <span class="badge {{ $student->status_badge_class }} py-1 px-2">
              <i class="bi bi-check-circle me-1"></i> {{ ucfirst($student->status) }}
            </span>
            <span class="badge text-bg-secondary py-1 px-2 ms-1">
              {{ $student->academic_stage }}
            </span>
          </div>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">Gender</span>
              <span class="fw-semibold text-capitalize">{{ $student->gender }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">Date of Birth</span>
              <span>{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'N/A' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">Email</span>
              <span class="text-primary">{{ $student->email }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">Phone Number</span>
              <span>{{ $student->phone ?? 'N/A' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">National ID / NIN</span>
              <span class="font-monospace">{{ $student->national_id_nin ?? 'N/A' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">Campus</span>
              <span class="fw-semibold">{{ $student->campus->name ?? 'Main Campus' }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <span class="text-muted">Study Mode &amp; Intake</span>
              <span>
                <span class="badge bg-body-secondary text-body border">{{ $student->study_mode }}</span>
                <span class="badge bg-body-secondary text-body border ms-1">{{ $student->intake }} Intake</span>
              </span>
            </li>
          </ul>

          <div class="d-grid gap-2">
            <a href="{{ route('academic.students.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Back to Students Directory
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Academic Placement & Curriculum Card -->
    <div class="col-12 col-lg-7">
      <div class="card card-outline card-success mb-4">
        <div class="card-header">
          <h3 class="card-title mb-0">
            <i class="bi bi-mortarboard-fill me-1 text-success"></i> Academic Placement &amp; Curriculum
          </h3>
        </div>
        <div class="card-body">
          <div class="row g-3 mb-4">
            <div class="col-12">
              <label class="form-label text-muted small mb-0">Enrolled Programme</label>
              <div class="d-flex align-items-center justify-content-between p-2 rounded bg-body-tertiary border">
                <div>
                  <h6 class="fw-bold mb-0 text-primary">{{ $student->programme->name }}</h6>
                  <small class="text-muted">{{ $student->programme->department->name }} &bull; {{ $student->programme->department->faculty->name ?? '' }}</small>
                </div>
                <a href="{{ route('academic.programmes.show', $student->programme) }}" class="btn btn-sm btn-outline-primary" title="View Programme">
                  <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small mb-0">Assigned Curriculum Version</label>
              <div class="p-2 rounded bg-body-tertiary border">
                <span class="fw-bold text-body">{{ $student->curriculum->version_name }}</span>
                <small class="d-block text-muted">Range: {{ $student->curriculum->start_academic_year }} &ndash; {{ $student->curriculum->end_academic_year ?? 'Present' }}</small>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small mb-0">Admission Cohort</label>
              <div class="p-2 rounded bg-body-tertiary border">
                <span class="fw-bold text-body">{{ $student->admissionAcademicYear->name }}</span>
                <small class="d-block text-muted">Admitted Session</small>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small mb-0">Academic Standing &amp; CGPA</label>
              <div class="p-2 rounded bg-body-tertiary border">
                <span class="fs-5 fw-bold text-success">{{ number_format($student->cumulative_gpa, 2) }}</span>
                <small class="d-block text-muted">{{ $student->cumulative_gpa > 0 ? 'Cumulative GPA' : 'Fresher (No grades recorded yet)' }}</small>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label text-muted small mb-0">Academic Stage</label>
              <div class="p-2 rounded bg-body-tertiary border">
                <span class="fw-bold text-body">Year {{ $student->current_study_year }}, Semester {{ $student->current_semester }}</span>
                <small class="d-block text-muted">Active Term</small>
              </div>
            </div>
          </div>

          <div class="callout callout-success mb-3">
            <h6 class="fw-bold mb-1"><i class="bi bi-check2-circle text-success me-1"></i> Week 2 Registration Engine Integration</h6>
            <p class="mb-2 text-muted small">
              This student is configured for the <strong>Week 2 Course Registration Module</strong>. The registration system will dynamically fetch courses allocated to <strong>Year {{ $student->current_study_year }} Semester {{ $student->current_semester }}</strong> in the <strong>{{ $student->curriculum->version_name }}</strong> progression matrix.
            </p>
            <a href="{{ route('academic.curriculums.show', $student->curriculum) }}" class="btn btn-sm btn-success">
              <i class="bi bi-diagram-3-fill me-1"></i> Inspect Curriculum Progression Matrix
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
