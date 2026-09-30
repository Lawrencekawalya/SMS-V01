<!-- Grading Scale Reference Modal (Reusable across Instructor & HoD Portals) -->
<div class="modal fade" id="gradingScaleReferenceModal" tabindex="-1" aria-labelledby="gradingScaleReferenceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-body-tertiary">
        <h5 class="modal-title fw-bold" id="gradingScaleReferenceModalLabel">
          <i class="bi bi-award-fill text-primary me-2"></i>Institutional Grading Scale Reference (NCHE 5.0 Standard)
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-0">
        <!-- Weights Banner -->
        <div class="p-3 bg-body-secondary border-bottom">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <span class="text-body-secondary small d-block">Active Assessment Weighting:</span>
              <strong>Continuous Assessment (CA): {{ number_format(config('academic.assessment_ca_weight', 40.0), 0) }}%</strong> +
              <strong>Final Examination: {{ number_format(config('academic.assessment_exam_weight', 60.0), 0) }}%</strong>
            </div>
            <span class="badge text-bg-success fs-6">
              Pass Mark: {{ number_format(config('academic.assessment_pass_mark', 50.0), 0) }}%
            </span>
          </div>
        </div>

        @php
          $tiers = class_exists(\App\Models\GradingScaleTier::class) && \App\Models\GradingScaleTier::count() > 0
            ? \App\Models\GradingScaleTier::ordered()->get()
            : collect(config('academic.grading_scale', []));
        @endphp

        <!-- Scale Table -->
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">Mark Range</th>
                <th class="text-center">Grade</th>
                <th class="text-center">Grade Point</th>
                <th>Classification</th>
                <th class="text-end pe-3">Result</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($tiers as $tier)
                @php
                  $isPassed = is_object($tier) ? $tier->is_pass : ($tier['grade_letter'] !== 'F');
                  $letter = is_object($tier) ? $tier->grade_letter : $tier['grade_letter'];
                  $min = is_object($tier) ? $tier->min_score : $tier['min_score'];
                  $max = is_object($tier) ? $tier->max_score : $tier['max_score'];
                  $gp = is_object($tier) ? $tier->grade_point : $tier['grade_point'];
                  $classification = is_object($tier) ? $tier->classification : $tier['classification'];
                  $badge = is_object($tier) ? $tier->badge_class : $tier['badge_class'];
                @endphp
                <tr class="{{ ! $isPassed ? 'table-danger-subtle' : '' }}">
                  <td class="ps-3 font-monospace fw-bold">
                    {{ number_format($min, 1) }}% – {{ number_format($max, 1) }}%
                  </td>
                  <td class="text-center">
                    <span class="badge {{ $badge }} fs-6 px-3">{{ $letter }}</span>
                  </td>
                  <td class="text-center font-monospace fw-bold">
                    {{ number_format($gp, 1) }}
                  </td>
                  <td>{{ $classification }}</td>
                  <td class="text-end pe-3">
                    @if ($isPassed)
                      <span class="badge text-bg-success">Pass</span>
                    @else
                      <span class="badge text-bg-danger">Fail (Retake)</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Formulas Summary Box -->
        <div class="p-3 bg-body-tertiary border-top">
          <div class="row g-2 text-body-secondary small">
            <div class="col-12 col-md-6">
              <strong>Course Final Score:</strong>
              <div class="font-monospace bg-body p-2 rounded border mt-1">
                Final = CA [/{{ number_format(config('academic.assessment_ca_weight', 40.0), 0) }}] + Exam [/{{ number_format(config('academic.assessment_exam_weight', 60.0), 0) }}]
              </div>
            </div>
            <div class="col-12 col-md-6">
              <strong>Semester GPA Formula:</strong>
              <div class="font-monospace bg-body p-2 rounded border mt-1">
                GPA = &Sigma;(Credit Units &times; GP) / &Sigma; Registered CU
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-body-secondary">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
