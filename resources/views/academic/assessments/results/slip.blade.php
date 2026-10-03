<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Official Result Slip - {{ $student->registration_number }} ({{ $semester->name }})</title>
  <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
  <style>
    @page {
      size: A4 portrait;
      margin: 12mm 10mm 12mm 10mm;
    }
    body {
      background-color: #f8f9fa;
      color: #212529;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .print-container {
      max-width: 840px;
      margin: 20px auto;
      background: #ffffff;
      padding: 30px;
      border: 1px solid #dee2e6;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .header-border {
      border-bottom: 3px double #0d6efd;
      padding-bottom: 12px;
      margin-bottom: 18px;
    }
    .table-bordered th, .table-bordered td {
      border: 1px solid #495057 !important;
      padding: 5px 8px;
      font-size: 0.85rem;
    }
    .table-results thead th {
      background-color: #f1f3f5 !important;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
    }
    .signature-box {
      border-top: 1px dashed #212529;
      margin-top: 40px;
      padding-top: 5px;
      text-align: center;
      font-size: 0.8rem;
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
      .table-bordered th, .table-bordered td {
        border: 1px solid #000000 !important;
      }
    }
  </style>
</head>
<body>

  <!-- Screen Action Toolbar (Hidden When Printing) -->
  <div class="no-print bg-dark text-white py-2 mb-3 shadow">
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 840px;">
      <div>
        <i class="bi bi-file-earmark-text me-1"></i>
        <span>Official Printable Result Slip &bull; {{ $student->registration_number }}</span>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('result.list') }}" class="btn btn-outline-light btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Results Hub
        </a>
        <a href="{{ route('result.transcript', $student) }}" class="btn btn-outline-info btn-sm">
          <i class="bi bi-mortarboard me-1"></i> Transcript
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="window.print()">
          <i class="bi bi-printer-fill me-1"></i> Print / Save as PDF
        </button>
      </div>
    </div>
  </div>

  <div class="print-container">
    <!-- Institutional Crest & Official Header -->
    <div class="header-border text-center">
      <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
        <img src="{{ asset('vendor/adminlte/assets/img/AdminLTELogo.png') }}" alt="Logo" style="height: 60px; width: 60px; object-fit: contain;">
        <div>
          <h3 class="fw-bold mb-0 text-uppercase tracking-wide" style="color: #0d47a1; letter-spacing: 1px;">
            {{ $university->name ?? 'Bishop Stuart University' }}
          </h3>
          <h6 class="text-uppercase fw-semibold mb-0 text-muted small">
            Office of the Academic Registrar &bull; Directorate of Examinations &amp; Records
          </h6>
          <div class="small text-muted">
            {{ $university->address ?? 'P.O. Box 09, Mbarara, Uganda' }} &bull; {{ $university->email ?? 'ar@bsu.ac.ug' }}
          </div>
        </div>
      </div>
      <div class="badge bg-primary text-white text-uppercase px-3 py-1 fs-6 mt-1">
        Official Semester Result Slip
      </div>
      <div class="small text-muted mt-1">
        Academic Term: <strong>{{ $semester->name }} &bull; {{ $semester->academicYear->name ?? 'Academic Year' }}</strong>
      </div>
    </div>

    <!-- Student Academic Credentials Matrix -->
    <div class="card bg-light border mb-3">
      <div class="card-body p-2">
        <div class="row g-2 small">
          <div class="col-6 col-md-4">
            <span class="text-muted d-block">Student Name:</span>
            <strong>{{ $student->user->name ?? $student->full_name }}</strong>
          </div>
          <div class="col-6 col-md-4">
            <span class="text-muted d-block">Registration No:</span>
            <strong class="font-monospace text-primary">{{ $student->registration_number }}</strong>
          </div>
          <div class="col-6 col-md-4">
            <span class="text-muted d-block">Student Number:</span>
            <strong class="font-monospace">{{ $student->student_number }}</strong>
          </div>
          <div class="col-12 col-md-6">
            <span class="text-muted d-block">Programme of Study:</span>
            <strong>{{ $student->programme->name }} ({{ $student->programme->code }})</strong>
          </div>
          <div class="col-6 col-md-3">
            <span class="text-muted d-block">Faculty / School:</span>
            <span>{{ $student->programme->department->faculty->code ?? 'FSC' }}</span>
          </div>
          <div class="col-6 col-md-3">
            <span class="text-muted d-block">Academic Stage:</span>
            <span class="badge text-bg-secondary">{{ $student->academic_stage }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Enrolled Courses & Examination Results Table -->
    <div class="mb-3">
      <h6 class="fw-bold mb-2 text-uppercase text-secondary small">
        <i class="bi bi-card-checklist me-1"></i> Course Units Assessment &amp; Marks Breakdown
      </h6>
      <table class="table table-bordered table-sm table-results align-middle mb-0">
        <thead>
          <tr>
            <th style="width: 4%;" class="text-center">#</th>
            <th style="width: 14%;">Course Code</th>
            <th style="width: 32%;">Course Unit Title</th>
            <th style="width: 8%;" class="text-center">CU</th>
            <th style="width: 8%;" class="text-center">CA</th>
            <th style="width: 8%;" class="text-center">Exam</th>
            <th style="width: 8%;" class="text-center">Total</th>
            <th style="width: 6%;" class="text-center">Grade</th>
            <th style="width: 6%;" class="text-center">GP</th>
            <th style="width: 6%;" class="text-center">Result</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($marks as $index => $mark)
            @php
              $cu = (float) ($mark->registrationItem?->credit_units ?? $mark->courseAssessmentSheet?->courseUnit?->credit_units ?? 0);
              $isPassed = (bool) $mark->is_passed;
            @endphp
            <tr>
              <td class="text-center text-muted">{{ $index + 1 }}</td>
              <td class="font-monospace fw-bold">{{ $mark->courseAssessmentSheet?->courseUnit?->code ?? 'N/A' }}</td>
              <td>{{ $mark->courseAssessmentSheet?->courseUnit?->name ?? 'Course Unit' }}</td>
              <td class="text-center font-monospace">{{ number_format($cu, 1) }}</td>
              <td class="text-center font-monospace">{{ $mark->ca_score !== null ? number_format($mark->ca_score, 1) : '-' }}</td>
              <td class="text-center font-monospace">{{ $mark->exam_score !== null ? number_format($mark->exam_score, 1) : '-' }}</td>
              <td class="text-center font-monospace fw-bold">{{ $mark->final_score !== null ? number_format($mark->final_score, 1) : '-' }}</td>
              <td class="text-center fw-bold font-monospace">{{ $mark->grade_letter ?? '-' }}</td>
              <td class="text-center font-monospace">{{ $mark->grade_point !== null ? number_format($mark->grade_point, 1) : '-' }}</td>
              <td class="text-center">
                @if ($mark->final_score !== null)
                  <span class="badge {{ $isPassed ? 'text-bg-success' : 'text-bg-danger' }}">
                    {{ $isPassed ? 'P' : 'F' }}
                  </span>
                @else
                  <span class="badge text-bg-secondary">INC</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="10" class="text-center py-3 text-muted">
                No course assessment marks recorded for this semester session.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Official Semester Academic Performance Summary Box -->
    <div class="row g-2 mb-3">
      <div class="col-12">
        <div class="card border-primary">
          <div class="card-header bg-primary text-white py-1 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-uppercase small"><i class="bi bi-calculator me-1"></i> Term Standing &amp; Credit Summary</span>
            <span class="badge bg-white text-primary text-uppercase">{{ $performance->academic_standing ?? 'Normal Progress' }}</span>
          </div>
          <div class="card-body p-2">
            <div class="row text-center g-2">
              <div class="col-6 col-md-2 border-end">
                <span class="text-muted d-block small">Registered CU:</span>
                <strong class="fs-6 font-monospace">{{ $performance ? number_format($performance->credit_units_registered, 1) : '-' }}</strong>
              </div>
              <div class="col-6 col-md-2 border-end">
                <span class="text-muted d-block small">Earned CU:</span>
                <strong class="fs-6 font-monospace text-success">{{ $performance ? number_format($performance->credit_units_earned, 1) : '-' }}</strong>
              </div>
              <div class="col-6 col-md-3 border-end">
                <span class="text-muted d-block small">Semester GPA:</span>
                <strong class="fs-5 font-monospace text-primary">{{ $performance ? number_format($performance->gpa, 2) : '0.00' }}</strong>
              </div>
              <div class="col-6 col-md-3 border-end">
                <span class="text-muted d-block small">Cumulative CGPA:</span>
                <strong class="fs-5 font-monospace text-dark">{{ $performance ? number_format($performance->cgpa, 2) : number_format($student->cumulative_gpa ?? 0, 2) }}</strong>
              </div>
              <div class="col-12 col-md-2">
                <span class="text-muted d-block small">Academic Standing:</span>
                <strong class="{{ ($performance?->academic_standing ?? '') === 'Probation' ? 'text-danger' : 'text-success' }}">
                  {{ $performance->academic_standing ?? 'Normal Progress' }}
                </strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statutory NCHE 5.0 Grading Key (Compact Footnote) -->
    <div class="border rounded p-2 mb-4 bg-light small" style="font-size: 0.75rem;">
      <div class="fw-bold mb-1 text-secondary text-uppercase">Statutory NCHE 5.0 Grading Key:</div>
      <div class="d-flex flex-wrap gap-3">
        @foreach ($gradingScaleTiers as $tier)
          <div>
            <strong>{{ $tier->grade_letter }}</strong>:
            {{ number_format($tier->min_score, 0) }}-{{ number_format($tier->max_score, 0) }}%
            ({{ number_format($tier->grade_point, 1) }} GP)
          </div>
        @endforeach
      </div>
      <div class="text-muted mt-1 fst-italic">
        Pass Mark: 50.0%. Grades below 50.0% constitute a retake (F). Normal Academic Progress requires a CGPA &ge; 2.00.
      </div>
    </div>

    <!-- Sign-off & Official Clearance Signatures -->
    <div class="row text-center mt-4">
      <div class="col-4">
        <div class="signature-box">
          <strong>Head of Department</strong>
          <div class="small text-muted">{{ $student->programme->department->name ?? 'Academic Department' }}</div>
        </div>
      </div>
      <div class="col-4">
        <div class="d-flex flex-column align-items-center justify-content-center" style="height: 70px;">
          <div class="border border-secondary rounded-circle d-flex align-items-center justify-content-center text-muted" style="width: 65px; height: 65px; font-size: 0.65rem; border-style: dashed !important;">
            OFFICIAL<br>STAMP
          </div>
        </div>
        <div class="small text-muted mt-1">Date: {{ now()->format('d/m/Y') }}</div>
      </div>
      <div class="col-4">
        <div class="signature-box">
          <strong>Academic Registrar</strong>
          <div class="small text-muted">{{ $university->name ?? 'Bishop Stuart University' }}</div>
        </div>
      </div>
    </div>

    <!-- Security Document Verification Notice -->
    <div class="text-center text-muted mt-4 pt-2 border-top small" style="font-size: 0.7rem;">
      This official result slip is issued without alteration or erasure. For authenticity verification, contact the Office of the Academic Registrar.
      Reference: #SLIP-{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}-{{ $semester->id }}-{{ now()->format('Ymd') }}
    </div>
  </div>

</body>
</html>
