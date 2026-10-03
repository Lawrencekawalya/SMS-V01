<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Official Course Registration Slip - {{ $registration->student->registration_number }}</title>
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 12mm 15mm 12mm;
    }
    body {
      background-color: #f8f9fa;
      color: #212529;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .print-container {
      max-width: 820px;
      margin: 20px auto;
      background: #ffffff;
      padding: 30px;
      border: 1px solid #dee2e6;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .header-border {
      border-bottom: 3px double #0d6efd;
      padding-bottom: 12px;
      margin-bottom: 20px;
    }
    .table-bordered th, .table-bordered td {
      border: 1px solid #495057 !important;
      padding: 6px 10px;
      font-size: 0.9rem;
    }
    .signature-box {
      border-top: 1px dashed #212529;
      margin-top: 50px;
      padding-top: 6px;
      text-align: center;
      font-size: 0.85rem;
    }
    @media print {
      body {
        background-color: #ffffff;
        color: #000000;
      }
      .no-print {
        display: none !important;
      }
      .print-container {
        border: none;
        box-shadow: none;
        margin: 0;
        padding: 0;
        max-width: 100%;
      }
    }
  </style>
</head>
<body>

  <!-- Screen Toolbar (Hidden When Printing) -->
  <div class="no-print bg-dark text-white py-2 mb-3 shadow">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 820px;">
      <div>
        <i class="bi bi-printer me-1"></i>
        <span>Official Printable Slip Preview (#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }})</span>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('registration.show', $registration) }}" class="btn btn-outline-light btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Return to Portal
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="window.print()">
          <i class="bi bi-printer-fill me-1"></i> Print / Save as PDF
        </button>
      </div>
    </div>
  </div>

  <div class="print-container">
    <!-- University Crest & Official Header -->
    <div class="header-border text-center">
      <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
        <img src="{{ asset('vendor/adminlte/assets/img/AdminLTELogo.png') }}" alt="Logo" style="height: 60px; width: 60px; object-fit: contain;">
        <div>
          <h3 class="fw-bold mb-0 text-uppercase tracking-wide" style="color: #0d47a1; letter-spacing: 1px;">
            Bishop Stuart University
          </h3>
          <h6 class="text-uppercase fw-semibold mb-0 text-muted">
            Office of the Academic Registrar &bull; Department of Academic Affairs
          </h6>
        </div>
      </div>
      <div class="badge bg-primary text-white text-uppercase px-3 py-1 fs-6 mt-1">
        Official Semester Course Registration Slip
      </div>
      <div class="small text-muted mt-1">
        Academic Term: <strong>{{ $registration->semester->name }} &bull; {{ $registration->academicYear->name }}</strong>
      </div>
    </div>

    <!-- Student Academic Profile Metadata Grid -->
    <div class="row g-2 mb-3 small">
      <div class="col-6">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <th class="ps-0 text-muted" style="width: 140px;">Student Name:</th>
            <td class="fw-bold fs-6">{{ $registration->student->full_name }}</td>
          </tr>
          <tr>
            <th class="ps-0 text-muted">Registration No:</th>
            <td class="fw-bold font-monospace text-primary">{{ $registration->student->registration_number }}</td>
          </tr>
          <tr>
            <th class="ps-0 text-muted">Student Number:</th>
            <td class="font-monospace">{{ $registration->student->student_number }}</td>
          </tr>
          <tr>
            <th class="ps-0 text-muted">Study Mode / Gender:</th>
            <td>{{ $registration->student->study_mode }} &bull; {{ ucfirst($registration->student->gender) }}</td>
          </tr>
        </table>
      </div>

      <div class="col-6">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <th class="ps-0 text-muted" style="width: 140px;">Academic Programme:</th>
            <td class="fw-bold">{{ $registration->student->programme->name }} ({{ $registration->student->programme->code }})</td>
          </tr>
          <tr>
            <th class="ps-0 text-muted">Faculty & Dept:</th>
            <td>{{ $registration->student->programme->department->name }} &bull; {{ $registration->student->campus->name }}</td>
          </tr>
          <tr>
            <th class="ps-0 text-muted">Current Stage:</th>
            <td class="fw-semibold">Study Year {{ $registration->study_year }}, Semester {{ $registration->semester_number }}</td>
          </tr>
          <tr>
            <th class="ps-0 text-muted">Slip Reference ID:</th>
            <td class="font-monospace">#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }} &bull; Status: {{ strtoupper($registration->status) }}</td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Registered Course Units Table -->
    <div class="mb-4">
      <h6 class="fw-bold text-uppercase border-bottom pb-1 mb-2">Registered Courses & Credit Units Breakdown</h6>
      <table class="table table-bordered table-sm align-middle mb-0">
        <thead style="background-color: #e9ecef;">
          <tr>
            <th style="width: 35px;" class="text-center">#</th>
            <th style="width: 100px;">Course Code</th>
            <th>Course Title</th>
            <th style="width: 90px;" class="text-center">Type</th>
            <th style="width: 180px;">Teaching Department</th>
            <th style="width: 90px;" class="text-center">Credit Units</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($registration->items as $item)
            <tr class="{{ $item->isDropped() ? 'text-decoration-line-through text-muted' : '' }}">
              <td class="text-center">{{ $loop->iteration }}</td>
              <td class="font-monospace fw-bold">{{ $item->courseUnit->code }}</td>
              <td>{{ $item->courseUnit->name }}</td>
              <td class="text-center">{{ $item->course_type }}</td>
              <td class="small">{{ $item->courseUnit->department->name ?? 'Academic Core' }}</td>
              <td class="text-center fw-bold">{{ number_format($item->credit_units, 1) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-3">No courses registered on this slip.</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot style="background-color: #f8f9fa;">
          <tr>
            <th colspan="5" class="text-end fw-bold">TOTAL REGISTERED SEMESTER LOAD:</th>
            <th class="text-center fw-bold fs-6">{{ number_format($registration->total_credits, 1) }} CU</th>
          </tr>
        </tfoot>
      </table>
      <div class="small text-muted mt-1 d-flex justify-content-between">
        <span>* Institutional Standard Range: Min 12.0 CU &mdash; Max {{ number_format(max(24.0, (float) $registration->total_credits), 1) }} CU</span>
        <span>Date Generated: {{ now()->format('d M Y, H:i:s') }}</span>
      </div>
    </div>

    @if ($registration->advisor_remarks)
      <div class="p-2 border rounded mb-4 small bg-light">
        <strong>Academic Advisor Remarks:</strong> {{ $registration->advisor_remarks }}
      </div>
    @endif

    <!-- Official Authorization Signatures Block -->
    <div class="row pt-4" style="margin-top: 40px;">
      <div class="col-4">
        <div class="signature-box">
          <strong>{{ $registration->student->full_name }}</strong><br>
          <span class="text-muted">Student's Signature & Date</span>
        </div>
      </div>
      <div class="col-4">
        <div class="signature-box">
          <strong>{{ $registration->approvedBy->name ?? 'Academic Advisor' }}</strong><br>
          <span class="text-muted">Advisor's Signature & Stamp</span>
        </div>
      </div>
      <div class="col-4">
        <div class="signature-box">
          <strong>Academic Registrar's Office</strong><br>
          <span class="text-muted">Official Seal & Endorsement</span>
        </div>
      </div>
    </div>

    <!-- Official Document Notice -->
    <div class="text-center text-muted small mt-4 pt-3 border-top">
      This is an official document generated by the University Student Management System. Alterations render this slip void.
    </div>
  </div>

</body>
</html>
