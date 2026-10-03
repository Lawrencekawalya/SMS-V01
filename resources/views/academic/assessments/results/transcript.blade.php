<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Official Academic Transcript - {{ $student->registration_number }}</title>
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
    .transcript-container {
      max-width: 860px;
      margin: 20px auto;
      background: #ffffff;
      padding: 35px 30px;
      border: 2px solid #0d47a1;
      box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
      position: relative;
    }
    .header-border {
      border-bottom: 3px double #0d47a1;
      padding-bottom: 12px;
      margin-bottom: 15px;
    }
    .table-bordered th, .table-bordered td {
      border: 1px solid #495057 !important;
      padding: 4px 6px;
      font-size: 0.8rem;
    }
    .table-transcript thead th {
      background-color: #e9ecef !important;
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.72rem;
    }
    .semester-header {
      background-color: #0d47a1 !important;
      color: #ffffff !important;
      font-weight: bold;
      padding: 4px 8px;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
    }
    .signature-box {
      border-top: 1px dashed #212529;
      margin-top: 45px;
      padding-top: 5px;
      text-align: center;
      font-size: 0.8rem;
    }
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-35deg);
      font-size: 5rem;
      color: rgba(13, 71, 161, 0.04);
      font-weight: 900;
      text-transform: uppercase;
      pointer-events: none;
      user-select: none;
      z-index: 0;
      white-space: nowrap;
    }
    @media print {
      body {
        background-color: #ffffff;
        color: #000000;
      }
      .no-print {
        display: none !important;
      }
      .transcript-container {
        border: 2px solid #000000;
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
    <div class="container d-flex justify-content-between align-items-center" style="max-width: 860px;">
      <div>
        <i class="bi bi-mortarboard-fill me-1"></i>
        <span>Official Academic Transcript &bull; {{ $student->registration_number }}</span>
      </div>
      <div class="d-flex gap-2">
        <a href="{{ route('result.list') }}" class="btn btn-outline-light btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Results Hub
        </a>
        <button type="button" class="btn btn-primary btn-sm fw-semibold" onclick="window.print()">
          <i class="bi bi-printer-fill me-1"></i> Print / Save as PDF
        </button>
      </div>
    </div>
  </div>

  <div class="transcript-container">
    <div class="watermark">OFFICIAL TRANSCRIPT</div>

    <!-- Official Institutional Banner -->
    <div class="header-border text-center">
      <div class="d-flex align-items-center justify-content-center gap-3 mb-2">
        <img src="{{ asset('vendor/adminlte/assets/img/AdminLTELogo.png') }}" alt="Logo" style="height: 65px; width: 65px; object-fit: contain;">
        <div>
          <h2 class="fw-bold mb-0 text-uppercase tracking-wide" style="color: #0d47a1; letter-spacing: 1.5px;">
            {{ $university->name ?? 'Bishop Stuart University' }}
          </h2>
          <h6 class="text-uppercase fw-semibold mb-0 text-muted small">
            Office of the Academic Registrar &bull; Directorate of Academic Examinations
          </h6>
          <div class="small text-muted">
            {{ $university->address ?? 'P.O. Box 09, Mbarara, Uganda' }} &bull; Web: {{ $university->website ?? 'www.bsu.ac.ug' }}
          </div>
        </div>
      </div>
      <div class="badge bg-primary text-white text-uppercase px-4 py-1 fs-6 mt-1">
        Official Cumulative Academic Transcript
      </div>
    </div>

    <!-- Student Bio-Data & Academic Placement Card -->
    <div class="border rounded p-2 mb-3 bg-light" style="font-size: 0.8rem;">
      <div class="row g-2">
        <div class="col-8 col-md-5">
          <span class="text-muted d-block small">Candidate Name:</span>
          <strong class="fs-6">{{ strtoupper($student->user->name ?? $student->full_name) }}</strong>
        </div>
        <div class="col-4 col-md-3">
          <span class="text-muted d-block small">Registration No:</span>
          <strong class="font-monospace text-primary fs-6">{{ $student->registration_number }}</strong>
        </div>
        <div class="col-4 col-md-2">
          <span class="text-muted d-block small">Student Number:</span>
          <strong class="font-monospace">{{ $student->student_number }}</strong>
        </div>
        <div class="col-4 col-md-2">
          <span class="text-muted d-block small">Gender / Sex:</span>
          <strong class="text-capitalize">{{ $student->gender ?? 'N/A' }}</strong>
        </div>

        <div class="col-12 col-md-6">
          <span class="text-muted d-block small">Programme of Study:</span>
          <strong>{{ strtoupper($student->programme->name) }} ({{ $student->programme->code }})</strong>
        </div>
        <div class="col-6 col-md-3">
          <span class="text-muted d-block small">Faculty / Department:</span>
          <span>{{ $student->programme->department->name ?? 'Department' }}</span>
        </div>
        <div class="col-6 col-md-3">
          <span class="text-muted d-block small">Date of Admission:</span>
          <span>{{ $student->admissionAcademicYear->name ?? '2026/2027' }}</span>
        </div>
      </div>
    </div>

    <!-- Multi-Semester Chronological Academic Performance Modules -->
    @forelse ($transcriptData as $data)
      @php
        $semester = $data['semester'];
        $perf = $data['performance'];
        $marks = $data['marks'];
      @endphp
      <div class="mb-3">
        <div class="semester-header d-flex justify-content-between align-items-center">
          <span>{{ strtoupper($semester->name) }} &bull; {{ strtoupper($semester->academicYear->name ?? '') }}</span>
          <span class="badge text-bg-light text-dark small py-0 font-monospace">
            GPA: {{ number_format($perf->gpa, 2) }} | CGPA: {{ number_format($perf->cgpa, 2) }}
          </span>
        </div>
        <table class="table table-bordered table-sm table-transcript align-middle mb-1">
          <thead>
            <tr>
              <th style="width: 14%;">Course Code</th>
              <th style="width: 44%;">Course Unit Title</th>
              <th style="width: 7%;" class="text-center">CU</th>
              <th style="width: 7%;" class="text-center">Score</th>
              <th style="width: 7%;" class="text-center">Grade</th>
              <th style="width: 7%;" class="text-center">GP</th>
              <th style="width: 7%;" class="text-center">WGP</th>
              <th style="width: 7%;" class="text-center">Result</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($marks as $mark)
              @php
                $cu = (float) ($mark->registrationItem?->credit_units ?? $mark->courseAssessmentSheet?->courseUnit?->credit_units ?? 0);
                $gp = (float) ($mark->grade_point ?? 0);
                $wgp = round($cu * $gp, 1);
                $isPass = (bool) $mark->is_passed;
              @endphp
              <tr>
                <td class="font-monospace fw-semibold">{{ $mark->courseAssessmentSheet?->courseUnit?->code ?? 'N/A' }}</td>
                <td>{{ $mark->courseAssessmentSheet?->courseUnit?->name ?? 'Course Unit' }}</td>
                <td class="text-center font-monospace">{{ number_format($cu, 1) }}</td>
                <td class="text-center font-monospace">{{ $mark->final_score !== null ? number_format($mark->final_score, 0) : '-' }}</td>
                <td class="text-center font-monospace fw-bold">{{ $mark->grade_letter ?? '-' }}</td>
                <td class="text-center font-monospace">{{ $mark->grade_point !== null ? number_format($gp, 1) : '-' }}</td>
                <td class="text-center font-monospace">{{ number_format($wgp, 1) }}</td>
                <td class="text-center">
                  @if ($mark->final_score !== null)
                    <span class="badge {{ $isPass ? 'text-bg-success' : 'text-bg-danger' }}">
                      {{ $isPass ? 'P' : 'F' }}
                    </span>
                  @else
                    <span class="badge text-bg-secondary">INC</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-1 text-muted small">
                  No courses graded for this semester session.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
        <!-- Semester Sub-Summary Strip -->
        <div class="d-flex justify-content-between align-items-center bg-light border p-1 small" style="font-size: 0.75rem;">
          <div>
            Registered CU: <strong>{{ number_format($perf->credit_units_registered, 1) }}</strong> &bull;
            Earned CU: <strong>{{ number_format($perf->credit_units_earned, 1) }}</strong>
          </div>
          <div>
            Semester GPA: <strong class="text-primary font-monospace">{{ number_format($perf->gpa, 2) }}</strong> &bull;
            Cumulative CGPA: <strong class="text-dark font-monospace">{{ number_format($perf->cgpa, 2) }}</strong> &bull;
            Standing: <strong class="{{ $perf->academic_standing === 'Probation' ? 'text-danger' : 'text-success' }}">{{ $perf->academic_standing }}</strong>
          </div>
        </div>
      </div>
    @empty
      <div class="alert alert-info small my-4 text-center">
        No completed academic semesters recorded for this candidate yet.
      </div>
    @endforelse

    <!-- Cumulative Degree Award & Final Standing Summary -->
    <div class="card border-primary mb-3">
      <div class="card-header bg-primary text-white py-1 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-uppercase small"><i class="bi bi-award-fill me-1"></i> Cumulative Degree Graduation &amp; Award Clearance</span>
        <span class="badge bg-white text-primary text-uppercase">{{ $summary['academic_standing'] }}</span>
      </div>
      <div class="card-body p-2" style="font-size: 0.85rem;">
        <div class="row g-2 align-items-center">
          <div class="col-6 col-md-3 border-end text-center">
            <span class="text-muted d-block small">Total Registered CU:</span>
            <strong class="fs-6 font-monospace">{{ number_format($summary['total_registered_credits'], 1) }}</strong>
          </div>
          <div class="col-6 col-md-3 border-end text-center">
            <span class="text-muted d-block small">Total Earned CU:</span>
            <strong class="fs-6 font-monospace text-success">{{ number_format($summary['total_earned_credits'], 1) }}</strong>
          </div>
          <div class="col-6 col-md-3 border-end text-center">
            <span class="text-muted d-block small">Cumulative CGPA:</span>
            <strong class="fs-5 font-monospace text-primary">{{ number_format($summary['cumulative_cgpa'], 2) }}</strong>
          </div>
          <div class="col-6 col-md-3 text-center">
            <span class="text-muted d-block small">Award Honours Classification:</span>
            <strong class="text-success text-uppercase">
              {{ strtoupper($summary['award_classification']->name ?? 'Normal Completion') }}
            </strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Statutory NCHE 5.0 Grading Key (Compact) -->
    <div class="border rounded p-2 mb-3 bg-light small" style="font-size: 0.72rem;">
      <div class="fw-bold mb-1 text-secondary text-uppercase">Statutory National Council for Higher Education (NCHE) Grading Scale:</div>
      <div class="d-flex flex-wrap gap-2">
        @foreach ($gradingScaleTiers as $tier)
          <div>
            <strong>{{ $tier->grade_letter }}</strong>:
            {{ number_format($tier->min_score, 0) }}-{{ number_format($tier->max_score, 0) }}%
            ({{ number_format($tier->grade_point, 1) }} GP)
          </div>
        @endforeach
      </div>
      <div class="text-muted mt-1 fst-italic">
        Pass Mark: 50.0%. Discontinuation or Probation applies when Cumulative CGPA falls below 2.00 in two consecutive terms.
      </div>
    </div>

    <!-- Institutional Sign-off & Registrar Certification -->
    <div class="row text-center mt-3">
      <div class="col-4">
        <div class="signature-box">
          <strong>Dean of Faculty</strong>
          <div class="small text-muted">{{ $student->programme->department->faculty->name ?? 'Faculty of Science' }}</div>
        </div>
      </div>
      <div class="col-4">
        <div class="d-flex flex-column align-items-center justify-content-center" style="height: 60px;">
          <div class="border border-primary rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 60px; height: 60px; font-size: 0.6rem; border-style: double !important; border-width: 3px !important;">
            OFFICIAL<br>UNIVERSITY<br>SEAL
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
    <div class="text-center text-muted mt-3 pt-1 border-top small" style="font-size: 0.68rem;">
      This transcript is issued under the authority of the Senate of {{ $university->name ?? 'Bishop Stuart University' }}. An official transcript must bear the embossed institutional seal and signature of the Academic Registrar.
      Verification Reference: #TRX-{{ str_pad($student->id, 5, '0', STR_PAD_LEFT) }}-{{ now()->format('Ymd') }}
    </div>
  </div>

</body>
</html>
