@foreach ($sheet->studentMarks as $mark)
  <div class="modal fade" id="specialExamModal{{ $mark->id }}" tabindex="-1" aria-labelledby="specialExamModalLabel{{ $mark->id }}" aria-hidden="true">
    <div class="modal-dialog">
      <form action="{{ route('assessment.mark-special-exam', $mark) }}" method="POST">
        @csrf
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="specialExamModalLabel{{ $mark->id }}">
              <i class="bi bi-journal-check me-2"></i>Record Special / Supplementary Mark
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="bg-body-secondary p-3 rounded mb-3 border">
              <div class="d-flex justify-content-between mb-1">
                <span class="text-body-secondary small">Student:</span>
                <strong>{{ $mark->student->full_name }}</strong>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-body-secondary small">Reg Number:</span>
                <span class="font-monospace fw-bold text-primary">{{ $mark->student->registration_number }}</span>
              </div>
              <div class="d-flex justify-content-between mb-1">
                <span class="text-body-secondary small">Course Unit:</span>
                <span>{{ $sheet->courseUnit->code }} &bull; {{ $sheet->courseUnit->name }}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="text-body-secondary small">Continuous Assessment (CA):</span>
                <span class="fw-bold">{{ $mark->ca_score !== null ? number_format($mark->ca_score, 1) : '0.0' }} / {{ number_format($sheet->ca_weight, 0) }} Marks</span>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Examination Sitting Type <span class="text-danger">*</span></label>
              <select name="sitting_type" class="form-select" required>
                <option value="special" {{ $mark->sitting_type === 'special' || $mark->isMissedWithPermission() ? 'selected' : '' }}>
                  Special Examination (Uncapped / Full Marks)
                </option>
                <option value="supplementary" {{ $mark->sitting_type === 'supplementary' ? 'selected' : '' }}>
                  Supplementary Examination (Capped at Pass / Grade C)
                </option>
              </select>
              <div class="form-text small">
                Special Exams are for approved medical/compassionate reasons. Supplementary exams are capped at pass mark.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Examination Score (Max: {{ number_format($sheet->exam_weight, 0) }}) <span class="text-danger">*</span></label>
              <div class="input-group">
                <input
                  type="number"
                  step="0.1"
                  min="0"
                  max="{{ $sheet->exam_weight }}"
                  name="special_exam_score"
                  class="form-control"
                  value="{{ old('special_exam_score', $mark->special_exam_score ?? $mark->exam_score) }}"
                  placeholder="e.g. 45.0"
                  required
                >
                <span class="input-group-text">/ {{ number_format($sheet->exam_weight, 0) }} Marks</span>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Examination Date</label>
              <input
                type="date"
                name="special_exam_date"
                class="form-control"
                value="{{ old('special_exam_date', $mark->special_exam_date?->format('Y-m-d') ?? date('Y-m-d')) }}"
              >
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Approval Authority &amp; Remarks</label>
              <textarea
                name="special_exam_remarks"
                class="form-control"
                rows="2"
                placeholder="e.g. Faculty Board Minute 14/2026 - Medical clearance approved"
              >{{ old('special_exam_remarks', $mark->special_exam_remarks ?? $mark->lecturer_remarks) }}</textarea>
              <div class="form-text small">Include approval minute, medical certificate reference, or examiner remarks.</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary fw-semibold">
              <i class="bi bi-save me-1"></i> Save &amp; Recalculate Performance
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
@endforeach
