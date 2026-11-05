@extends('layouts.app')

@section('title', 'Graduation Candidates & Clearance - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Graduation Clearance & Honors Roll')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item active" aria-current="page">Graduation Clearance</li>
@endsection

@section('content')
  <!-- Metrics KPI Row -->
  <div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-people-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total Candidates</span>
          <span class="info-box-number fs-4">{{ $stats['total_candidates'] }}</span>
          <span class="progress-description text-muted small">Registered in system</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-check-circle-fill"></i></span>
        <div class="info-box-content">
          <div class="d-flex align-items-center justify-content-between">
            <span class="info-box-text mb-0">Cleared for Graduation</span>
            <span
              class="text-success cursor-pointer ms-1"
              tabindex="0"
              role="button"
              data-bs-toggle="tooltip"
              data-bs-html="true"
              data-bs-placement="top"
              data-bs-custom-class="clearance-tooltip"
              title="&lt;div class=&#039;text-start&#039;&gt;&lt;div class=&#039;fw-bold border-bottom pb-1 mb-2 text-body&#039;&gt;&lt;i class=&#039;bi bi-shield-check text-success me-1&#039;&gt;&lt;/i&gt;Clearance Requirements (100% Required):&lt;/div&gt;&lt;ul class=&#039;list-unstyled mb-0&#039; style=&#039;font-size: 0.82rem; line-height: 1.6;&#039;&gt;&lt;li class=&#039;mb-1&#039;&gt;&lt;i class=&#039;bi bi-check2-circle text-success me-1&#039;&gt;&lt;/i&gt; Academic Core Courses&lt;/li&gt;&lt;li class=&#039;mb-1&#039;&gt;&lt;i class=&#039;bi bi-check2-circle text-success me-1&#039;&gt;&lt;/i&gt; Retakes &amp;amp; Missed Papers (MP) Cleared&lt;/li&gt;&lt;li class=&#039;mb-1&#039;&gt;&lt;i class=&#039;bi bi-check2-circle text-success me-1&#039;&gt;&lt;/i&gt; Minimum Credit Units &amp;amp; CGPA Met&lt;/li&gt;&lt;li class=&#039;mb-1&#039;&gt;&lt;i class=&#039;bi bi-check2-circle text-success me-1&#039;&gt;&lt;/i&gt; Degree Honours Classification Assigned&lt;/li&gt;&lt;li class=&#039;mb-1&#039;&gt;&lt;i class=&#039;bi bi-check2-circle text-success me-1&#039;&gt;&lt;/i&gt; Official Senate Gazette Approved&lt;/li&gt;&lt;li class=&#039;text-body-secondary mt-2 pt-1 border-top border-secondary-subtle&#039;&gt;&lt;i class=&#039;bi bi-clock-history me-1&#039;&gt;&lt;/i&gt; Finance / Fees Clearance &lt;em class=&#039;small text-body-tertiary&#039;&gt;[coming soon]&lt;/em&gt;&lt;/li&gt;&lt;li class=&#039;text-body-secondary&#039;&gt;&lt;i class=&#039;bi bi-clock-history me-1&#039;&gt;&lt;/i&gt; Library Clearance &lt;em class=&#039;small text-body-tertiary&#039;&gt;[coming soon]&lt;/em&gt;&lt;/li&gt;&lt;/ul&gt;&lt;/div&gt;"
              aria-label="Graduation clearance criteria details"
            >
              <i class="bi bi-info-circle fs-6"></i>
            </span>
          </div>
          <span class="info-box-number fs-4">{{ $stats['total_cleared'] }}</span>
          <span class="progress-description text-muted small">All requirements satisfied</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-danger shadow-sm"><i class="bi bi-exclamation-octagon-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Pending Deficiencies</span>
          <span class="info-box-number fs-4">{{ $stats['total_deficient'] }}</span>
          <span class="progress-description text-muted small">Retakes / credit shortfalls</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="info-box shadow-sm">
        <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-award-fill"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Clearance Rate</span>
          <span class="info-box-number fs-4">{{ $stats['clearance_rate'] }}%</span>
          <span class="progress-description text-muted small">Graduation readiness</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Search & Action Card -->
  <div class="card card-outline card-secondary mb-4 shadow-sm no-print">
    <div class="card-header py-2">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title fs-6 fw-bold mb-0">
          <i class="bi bi-funnel me-1"></i> Filter Graduation Candidates
        </h3>
        <div class="card-tools d-flex gap-2 me-0 ms-auto">
          <a href="{{ route('graduation.honors-roll', request()->query()) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-journal-bookmark me-1"></i> View Honors Roll Gazette
          </a>
        </div>
      </div>
    </div>
    <div class="card-body">
      <form action="{{ route('graduation.list') }}" method="GET" class="row g-3">
        <div class="col-12 col-md-4">
          <label for="search" class="form-label small fw-semibold">Search Candidate</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="search" id="search" value="{{ $search }}" class="form-control" placeholder="Reg No, student ID, name...">
          </div>
        </div>

        <div class="col-12 col-md-4">
          <label for="programme_id" class="form-label small fw-semibold">Programme</label>
          <select name="programme_id" id="programme_id" class="form-select form-select-sm">
            <option value="">-- All Academic Programmes --</option>
            @foreach ($programmes as $prog)
              <option value="{{ $prog->id }}" {{ (string) $selectedProgrammeId === (string) $prog->id ? 'selected' : '' }}>
                {{ $prog->name }} ({{ $prog->code }})
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-12 col-md-2">
          <label for="status" class="form-label small fw-semibold">Clearance Status</label>
          <select name="status" id="status" class="form-select form-select-sm">
            <option value="">-- All Statuses --</option>
            <option value="cleared" {{ $filterStatus === 'cleared' ? 'selected' : '' }}>Cleared Only</option>
            <option value="deficiencies" {{ $filterStatus === 'deficiencies' ? 'selected' : '' }}>Deficiencies Only</option>
          </select>
        </div>

        <div class="col-12 col-md-2 d-flex align-items-end gap-2">
          <button type="submit" class="btn btn-sm btn-primary w-100">
            <i class="bi bi-filter me-1"></i> Filter
          </button>
          <a href="{{ route('graduation.list') }}" class="btn btn-sm btn-outline-secondary">
            Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Candidates Table Card -->
  <div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header py-2">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title fs-6 fw-bold mb-0">
          <i class="bi bi-mortarboard-fill me-1 text-primary"></i> Graduation Candidates Directory
        </h3>
        <span class="badge bg-secondary ms-auto">{{ count($candidates) }} Candidate(s) Displayed</span>
      </div>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 40px;">#</th>
              <th>Student Details</th>
              <th>Programme</th>
              <th>Credits Progress</th>
              <th class="text-center">CGPA</th>
              <th>Award Classification</th>
              <th class="text-center">Clearance Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($candidates as $idx => $candidate)
              @php
                $st = $candidate['student'];
                $pct = $candidate['required_credits'] > 0
                  ? min(100, round(($candidate['earned_credits'] / $candidate['required_credits']) * 100, 1))
                  : 100;
              @endphp
              <tr>
                <td class="text-muted fw-semibold">{{ $idx + 1 }}</td>
                <td>
                  <div class="fw-bold">{{ $st->user->name ?? $st->full_name }}</div>
                  <div class="small font-monospace text-muted">{{ $st->registration_number }} &bull; {{ $st->student_number }}</div>
                </td>
                <td>
                  <div class="small fw-semibold">{{ $st->programme->name ?? 'N/A' }}</div>
                  <div class="text-muted" style="font-size: 0.75rem;">{{ $st->programme->code ?? '' }}</div>
                </td>
                <td style="min-width: 150px;">
                  <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-bold {{ $candidate['earned_credits'] >= $candidate['required_credits'] ? 'text-success' : 'text-danger' }}">
                      {{ number_format($candidate['earned_credits'], 1) }} CU
                    </span>
                    <span class="text-muted">of {{ number_format($candidate['required_credits'], 1) }} CU</span>
                  </div>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : 'bg-warning' }}" role="progressbar" style="width: {{ $pct }}%"></div>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge {{ $candidate['cgpa'] >= 3.6 ? 'bg-success' : ($candidate['cgpa'] >= ($candidate['min_grad_cgpa'] ?? 2.0) ? 'bg-info' : 'bg-danger') }} fs-6">
                    {{ number_format($candidate['cgpa'], 2) }}
                  </span>
                </td>
                <td>
                  <span class="badge {{ $candidate['badge_class'] }}">
                    {{ $candidate['award_classification'] }}
                  </span>
                </td>
                <td class="text-center">
                  @if ($candidate['is_cleared'])
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                      <i class="bi bi-check-circle-fill me-1"></i> Cleared
                    </span>
                  @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                      <i class="bi bi-exclamation-triangle-fill me-1"></i> Deficiencies
                    </span>
                    <div class="text-muted" style="font-size: 0.72rem;">{{ count($candidate['deficiencies']) }} Issue(s)</div>
                  @endif
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="{{ route('graduation.audit', $st->id) }}" class="btn btn-outline-primary" title="Audit Clearance">
                      <i class="bi bi-shield-check me-1"></i> Audit
                    </a>
                    <a href="{{ route('result.transcript', $st->id) }}" class="btn btn-outline-secondary" title="View Transcript">
                      <i class="bi bi-file-earmark-text"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                  <i class="bi bi-mortarboard fs-2 d-block mb-2"></i>
                  No graduation candidate records found matching the filter criteria.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .clearance-tooltip .tooltip-inner {
      max-width: 380px;
      text-align: left;
      padding: 14px 16px;
      font-size: 0.84rem;
      line-height: 1.55;
      background-color: var(--bs-body-bg) !important;
      color: var(--bs-body-color) !important;
      border: 1px solid var(--bs-border-color) !important;
      box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.25);
      border-radius: 0.5rem;
    }
    .clearance-tooltip.bs-tooltip-top .tooltip-arrow::before {
      border-top-color: var(--bs-border-color) !important;
    }
    .clearance-tooltip.bs-tooltip-bottom .tooltip-arrow::before {
      border-bottom-color: var(--bs-border-color) !important;
    }
    .clearance-tooltip.bs-tooltip-start .tooltip-arrow::before {
      border-left-color: var(--bs-border-color) !important;
    }
    .clearance-tooltip.bs-tooltip-end .tooltip-arrow::before {
      border-right-color: var(--bs-border-color) !important;
    }
    .cursor-pointer {
      cursor: pointer;
    }
  </style>
@endpush

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    });
  </script>
@endpush
