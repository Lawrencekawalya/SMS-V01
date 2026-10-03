@extends('layouts.app')

@section('title', 'Course Registration Slip #' . str_pad($registration->id, 5, '0', STR_PAD_LEFT) . ' - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Course Registration Slip')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item"><a href="{{ route('registration.list') }}">Course Registrations</a></li>
  <li class="breadcrumb-item active" aria-current="page">#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</li>
@endsection

@section('content')
  <div class="row">
    <!-- Student & Slip Summary Header Card -->
    <div class="col-12 mb-4">
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 w-100">
            <div class="d-flex align-items-center gap-2">
              <span class="fs-4 fw-bold font-monospace text-primary">#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</span>
              <span class="badge {{ $registration->status_badge_class }} fs-6">
                {{ $registration->status_label }}
              </span>
            </div>
            <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0 ms-auto">
              <a href="{{ route('registration.list') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Registrations
              </a>
              @if ($registration->canBeEdited())
                <a href="{{ route('registration.edit', $registration) }}" class="btn btn-warning btn-sm">
                  <i class="bi bi-pencil-square me-1"></i> Edit Registration
                </a>
              @endif
              @if ($registration->canAddDrop())
                <a href="{{ route('registration.add-drop.edit', $registration) }}" class="btn btn-outline-warning btn-sm">
                  <i class="bi bi-arrow-left-right me-1"></i> Add / Drop Courses
                </a>
              @endif
              <a href="{{ route('registration.print', $registration) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Official Print Slip
              </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="row g-3">
            <div class="col-12 col-md-4 border-end">
              <h6 class="text-uppercase text-body-secondary fw-bold small mb-2">Student Information</h6>
              <div class="fs-5 fw-bold text-body-emphasis">{{ $registration->student->full_name }}</div>
              <div class="font-monospace text-primary fw-semibold">{{ $registration->student->registration_number }}</div>
              <div class="small text-body-secondary mb-2">Student No: {{ $registration->student->student_number }}</div>
              <div class="small text-body-secondary"><i class="bi bi-envelope me-1"></i>{{ $registration->student->email }}</div>
              <div class="small text-body-secondary"><i class="bi bi-telephone me-1"></i>{{ $registration->student->phone }}</div>
            </div>

            <div class="col-12 col-md-4 border-end">
              <h6 class="text-uppercase text-body-secondary fw-bold small mb-2">Academic Programme & Stage</h6>
              <div class="fw-bold text-body-emphasis">{{ $registration->student->programme->name }} ({{ $registration->student->programme->code }})</div>
              <div class="small text-body-secondary">{{ $registration->student->programme->department->name }} &bull; {{ $registration->student->campus->name }}</div>
              <div class="mt-2">
                <span class="badge text-bg-primary">Study Year {{ $registration->study_year }}</span>
                <span class="badge text-bg-secondary">Semester {{ $registration->semester_number }}</span>
                <span class="badge bg-body-secondary text-body border">{{ $registration->student->curriculum->version_name }}</span>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <h6 class="text-uppercase text-body-secondary fw-bold small mb-2">Session & Sign-Off Details</h6>
              <div class="fw-semibold text-body-emphasis">{{ $registration->semester->name }} &mdash; {{ $registration->academicYear->name }}</div>
              <div class="small text-body-secondary">Submitted: {{ $registration->submitted_at ? $registration->submitted_at->format('d M Y, H:i') : 'Not yet submitted' }}</div>
              @if ($registration->approved_at)
                <div class="small text-success mt-1">
                  @if (config('academic.require_registration_approval', false))
                    <i class="bi bi-check2-circle me-1"></i>Approved on {{ $registration->approved_at->format('d M Y, H:i') }}
                    @if ($registration->approvedBy)
                      by {{ $registration->approvedBy->name }}
                    @endif
                  @else
                    <i class="bi bi-check2-circle me-1"></i>Confirmed on {{ $registration->approved_at->format('d M Y, H:i') }}
                  @endif
                </div>
              @endif

              <div class="mt-2 p-3 rounded bg-body-secondary border border-secondary-subtle">
                <div class="d-flex align-items-center justify-content-between">
                  <span class="small fw-semibold text-body-secondary">Total Registered Load:</span>
                  <span class="fs-5 fw-bold {{ $registration->total_credits >= 12.0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($registration->total_credits, 1) }} CU
                  </span>
                </div>
                <small class="text-body-secondary d-block mt-1">(Min: 12.0 CU &bull; Max: {{ number_format(max(24.0, (float) $registration->total_credits), 1) }} CU)</small>
              </div>
            </div>
          </div>

          @if ($registration->advisor_remarks)
            <div class="callout callout-info mt-3 mb-0">
              <h6 class="fw-bold mb-1">
                <i class="bi bi-chat-left-quote me-1"></i> Academic Advisor Remarks:
              </h6>
              <p class="mb-0 small text-body">{{ $registration->advisor_remarks }}</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Registered Course Units Table Card (Standard Week 1 Datatables Structure) -->
    <div class="col-12">
      <div class="card card-outline card-primary mb-4">
        <div class="card-header">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h3 class="card-title mb-0">
              <i class="bi bi-journal-bookmark me-1 text-primary"></i> Registered Course Units ({{ $registration->items->count() }})
            </h3>
            <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0">
              <div class="input-group input-group-sm" style="width: 15rem;">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="search" id="courses-filter" class="form-control" placeholder="Search courses..." autocomplete="off">
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="courses-export-csv">
              <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="courses-export-json">
              <i class="bi bi-filetype-json me-1"></i> Export JSON
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="courses-print">
              <i class="bi bi-printer me-1"></i> Print
            </button>
          </div>

          <table id="registered-courses-table" class="table table-hover table-striped align-middle mb-0">
            <thead>
              <tr>
                <th tabulator-formatter="plaintext" width="50">#</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Course Code</th>
                <th tabulator-formatter="html" tabulator-headerFilter="input">Course Title</th>
                <th tabulator-formatter="html" hozAlign="center">Course Type</th>
                <th tabulator-formatter="html" hozAlign="center">Credit Units</th>
                <th tabulator-formatter="html" hozAlign="center">Status</th>
                <th tabulator-formatter="html">Remarks / Audit</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($registration->items as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <span class="fw-bold font-monospace text-primary">{{ $item->courseUnit->code }}</span>
                  </td>
                  <td>
                    <div class="fw-semibold">{{ $item->courseUnit->name }}</div>
                    <small class="text-muted">{{ $item->courseUnit->department->name ?? '' }}</small>
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $item->type_badge_class }}">
                      {{ $item->course_type }}
                    </span>
                  </td>
                  <td class="text-center fw-bold">
                    {{ number_format($item->credit_units, 1) }} CU
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $item->status_badge_class }}">
                      {{ ucfirst($item->status) }}
                    </span>
                  </td>
                  <td>
                    @if ($item->isDropped())
                      <small class="text-danger">
                        <i class="bi bi-clock-history me-1"></i>Dropped on {{ $item->dropped_at?->format('d M Y') }}: {{ $item->drop_reason }}
                      </small>
                    @else
                      <small class="text-success"><i class="bi bi-check-lg me-1"></i>Enrolled</small>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      initAdminLteDataTable('#registered-courses-table', {
        filterInput: '#courses-filter',
        btnCsv: '#courses-export-csv',
        btnJson: '#courses-export-json',
        btnPrint: '#courses-print',
        filename: 'registration_slip_{{ $registration->id }}_courses',
      });
    });
  </script>
@endpush
