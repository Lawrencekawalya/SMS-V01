@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'SMS-V01 Dashboard')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
  <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
  @php
    $currentYear = \App\Models\AcademicYear::where('is_current', true)->first();
    $activeSemester = \App\Models\Semester::where('is_active', true)->first();
  @endphp

  @if ($currentYear && $activeSemester)
    <div class="alert alert-success d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 shadow-sm" role="alert">
      <div>
        <i class="bi bi-broadcast me-2 fs-5"></i>
        <strong>Active Academic Session:</strong> {{ $currentYear->name }} &mdash; {{ $activeSemester->name }}
        <span class="badge text-bg-light ms-2 border">{{ $activeSemester->start_date->format('M d, Y') }} &ndash; {{ $activeSemester->end_date->format('M d, Y') }}</span>
      </div>
      <a href="{{ route('academic.academic-years.index') }}" class="btn btn-sm btn-success border">
        <i class="bi bi-calendar3 me-1"></i> Academic Calendar
      </a>
    </div>
  @endif

  <!--begin::Row Info Boxes-->
  <div class="row g-3 mb-4">
    <!--begin::Col-->
    <div class="col-6 col-md-4 col-xl-2">
      <div class="info-box mb-0">
        <span class="info-box-icon text-bg-primary shadow-sm">
          <i class="bi bi-buildings"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Campuses</span>
          <span class="info-box-number">{{ \App\Models\Campus::count() }}</span>
        </div>
      </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-6 col-md-4 col-xl-2">
      <div class="info-box mb-0">
        <span class="info-box-icon text-bg-success shadow-sm">
          <i class="bi bi-mortarboard"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Faculties</span>
          <span class="info-box-number">{{ \App\Models\Faculty::count() }}</span>
        </div>
      </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-6 col-md-4 col-xl-2">
      <div class="info-box mb-0">
        <span class="info-box-icon text-bg-warning shadow-sm">
          <i class="bi bi-diagram-3-fill"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Departments</span>
          <span class="info-box-number">{{ \App\Models\Department::count() }}</span>
        </div>
      </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-6 col-md-4 col-xl-2">
      <div class="info-box mb-0">
        <span class="info-box-icon text-bg-info shadow-sm">
          <i class="bi bi-award-fill"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Programmes</span>
          <span class="info-box-number">{{ \App\Models\Programme::count() }}</span>
        </div>
      </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-6 col-md-4 col-xl-2">
      <div class="info-box mb-0">
        <span class="info-box-icon text-bg-secondary shadow-sm">
          <i class="bi bi-journal-bookmark-fill"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Courses</span>
          <span class="info-box-number">{{ \App\Models\CourseUnit::count() }}</span>
        </div>
      </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-6 col-md-4 col-xl-2">
      <div class="info-box mb-0">
        <span class="info-box-icon text-bg-danger shadow-sm">
          <i class="bi bi-mortarboard-fill"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Curriculums</span>
          <span class="info-box-number">{{ \App\Models\Curriculum::count() }}</span>
        </div>
      </div>
    </div>
    <!--end::Col-->
  </div>
  <!--end::Row Info Boxes-->

  <!--begin::Row Main Cards-->
  <div class="row">
    <div class="col-lg-8">
      <div class="card card-primary card-outline mb-4">
        <div class="card-header">
          <h5 class="card-title m-0">Welcome to {{ config('app.name', 'SMS-V01') }}</h5>
          <div class="card-tools">
            <span class="badge text-bg-primary">AdminLTE v4.9.1</span>
          </div>
        </div>
        <div class="card-body">
          <div class="callout callout-info mb-4">
            <h5>AdminLTE v4 Theme Active</h5>
            <p class="mb-0">
              This application is styled and integrated with <strong>AdminLTE v4.9.1</strong> (Bootstrap 5).
              All layouts, components, and design systems follow the AdminLTE v4 structure.
            </p>
          </div>

          <p class="card-text">
            The base admin layout is ready with responsive sidebar navigation, dark/light theme switching,
            fullscreen support, and standard Bootstrap 5 components.
          </p>

          <a href="#" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New SMS Campaign
          </a>
          <a href="#" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-gear me-1"></i> Configure Gateway
          </a>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Recent Activity</h3>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Module</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Project initialized</td>
                  <td>Core</td>
                  <td><span class="badge text-bg-success">Completed</span></td>
                  <td>Just now</td>
                </tr>
                <tr>
                  <td>AdminLTE v4 Theme installed</td>
                  <td>Theme</td>
                  <td><span class="badge text-bg-success">Active</span></td>
                  <td>Just now</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card card-info card-outline mb-4">
        <div class="card-header">
          <h5 class="card-title m-0">Environment & System Info</h5>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Application Name
              <span class="fw-bold">{{ config('app.name') }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Laravel Version
              <span class="badge text-bg-primary">{{ app()->version() }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              PHP Version
              <span class="badge text-bg-secondary">{{ PHP_VERSION }}</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Theme Framework
              <span class="badge text-bg-info">AdminLTE v4 / Bootstrap 5</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              Database
              <span class="badge text-bg-dark">{{ config('database.default') }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <!--end::Row Main Cards-->
@endsection
