@extends('layouts.app')

@section('title', 'Academic Calendar - ' . config('app.name', 'SMS-V01'))
@section('page-title', 'Academic Calendar & Sessions')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
  <li class="breadcrumb-item">Academic Core</li>
  <li class="breadcrumb-item active" aria-current="page">Academic Calendar</li>
@endsection

@section('content')
  <!-- Active Academic Session Banner -->
  @if ($currentYear && $activeSemester)
    <div class="callout callout-success mb-4 shadow-sm bg-body">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h5 class="fw-bold mb-1 text-success">
            <i class="bi bi-calendar-check-fill me-2"></i> Current University Session: {{ $currentYear->name }} &mdash; {{ $activeSemester->name }}
          </h5>
          <div class="text-body-secondary small d-flex flex-wrap gap-3 mt-1">
            <span><i class="bi bi-clock me-1"></i> Term: <strong>{{ $activeSemester->start_date->format('M d, Y') }}</strong> &ndash; <strong>{{ $activeSemester->end_date->format('M d, Y') }}</strong></span>
            @if ($activeSemester->registration_start_date && $activeSemester->registration_end_date)
              <span>
                <i class="bi bi-pencil-square me-1"></i> Registration: 
                <strong>{{ $activeSemester->registration_start_date->format('M d') }}</strong> &ndash; <strong>{{ $activeSemester->registration_end_date->format('M d, Y') }}</strong>
                @if ($activeSemester->isRegistrationOpen())
                  <span class="badge text-bg-success ms-1"><i class="bi bi-unlock-fill me-1"></i>Open</span>
                @else
                  <span class="badge text-bg-secondary ms-1"><i class="bi bi-lock-fill me-1"></i>Closed</span>
                @endif
              </span>
            @endif
            @if ($activeSemester->add_drop_deadline)
              <span>
                <i class="bi bi-calendar-x me-1"></i> Add/Drop Deadline: 
                <strong>{{ $activeSemester->add_drop_deadline->format('M d, Y') }}</strong>
                @if ($activeSemester->isAddDropOpen())
                  <span class="badge text-bg-info ms-1">Active</span>
                @else
                  <span class="badge text-bg-danger ms-1">Passed</span>
                @endif
              </span>
            @endif
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('academic.academic-years.show', $currentYear) }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-eye me-1"></i> View Year Details
          </a>
          <a href="{{ route('academic.semesters.create', ['academic_year_id' => $currentYear->id]) }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add Semester
          </a>
        </div>
      </div>
    </div>
  @else
    <div class="callout callout-warning mb-4 shadow-sm bg-body">
      <div class="d-flex align-items-center justify-content-between">
        <div>
          <h5 class="fw-bold mb-1 text-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> No Active Academic Session
          </h5>
          <p class="mb-0 text-muted small">Please activate an Academic Year and Semester to start the university session engine.</p>
        </div>
        <a href="{{ route('academic.academic-years.create') }}" class="btn btn-warning btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Setup Academic Year
        </a>
      </div>
    </div>
  @endif

  <!-- Academic Years Card with Tabulator DataTable -->
  <div class="card card-primary card-outline mb-4">
    <div class="card-header">
      <h3 class="card-title">Academic Years ({{ $academicYears->count() }})</h3>
      <div class="card-tools d-flex align-items-center gap-2 me-0">
        <div class="input-group input-group-sm" style="width: 16rem;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" id="years-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
        </div>
        <a href="{{ route('academic.academic-years.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Add Academic Year
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="years-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="years-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="years-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <table id="years-table" class="table table-hover table-striped align-middle mb-0">
        <thead>
          <tr>
            <th tabulator-formatter="plaintext" width="60">#</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Academic Year</th>
            <th tabulator-formatter="plaintext" tabulator-headerFilter="input">Start Date</th>
            <th tabulator-formatter="plaintext" tabulator-headerFilter="input">End Date</th>
            <th tabulator-formatter="html">Status</th>
            <th tabulator-formatter="html">Semesters</th>
            <th tabulator-formatter="plaintext">Description</th>
            <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="220" hozAlign="right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($academicYears as $year)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td class="fw-bold">
                <a href="{{ route('academic.academic-years.show', $year) }}" class="text-decoration-none">
                  {{ $year->name }}
                </a>
              </td>
              <td>{{ $year->start_date->format('Y-m-d') }}</td>
              <td>{{ $year->end_date->format('Y-m-d') }}</td>
              <td>
                @if ($year->is_current)
                  <span class="badge text-bg-success"><i class="bi bi-check-circle-fill me-1"></i> Current Year</span>
                @else
                  <span class="badge text-bg-secondary">Archived / Past</span>
                @endif
              </td>
              <td>
                <span class="badge text-bg-info">
                  {{ $year->semesters->count() }} {{ Str::plural('Semester', $year->semesters->count()) }}
                </span>
              </td>
              <td>{{ Str::limit($year->description ?? 'N/A', 40) }}</td>
              <td class="text-end">
                @if (! $year->is_current)
                  <form action="{{ route('academic.academic-years.make-current', $year) }}" method="POST" class="d-inline" onsubmit="return confirm('Set {{ $year->name }} as the current university academic year?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Current Year">
                      <i class="bi bi-check-lg"></i> Set Current
                    </button>
                  </form>
                @endif
                <a href="{{ route('academic.academic-years.show', $year) }}" class="btn btn-sm btn-outline-info" title="View Details">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('academic.academic-years.edit', $year) }}" class="btn btn-sm btn-outline-warning" title="Edit Year">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('academic.academic-years.destroy', $year) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete academic year {{ $year->name }}?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Year" {{ $year->is_current ? 'disabled' : '' }}>
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Semesters Across Academic Years with Tabulator DataTable -->
  <div class="card card-outline card-secondary mb-4">
    <div class="card-header">
      <h3 class="card-title">All University Semesters & Study Sessions</h3>
      <div class="card-tools d-flex align-items-center gap-2 me-0">
        <div class="input-group input-group-sm" style="width: 16rem;">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="search" id="semesters-filter" class="form-control" placeholder="Filter rows..." autocomplete="off">
        </div>
        <a href="{{ route('academic.semesters.create') }}" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Add Semester
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="semesters-export-csv">
          <i class="bi bi-filetype-csv me-1"></i> Export CSV
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="semesters-export-json">
          <i class="bi bi-filetype-json me-1"></i> Export JSON
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="semesters-print">
          <i class="bi bi-printer me-1"></i> Print
        </button>
      </div>

      <table id="semesters-table" class="table table-hover table-striped align-middle mb-0">
        <thead>
          <tr>
            <th tabulator-formatter="plaintext" width="50">#</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Academic Year</th>
            <th tabulator-formatter="html" tabulator-headerFilter="input">Semester</th>
            <th tabulator-formatter="plaintext">Term Dates</th>
            <th tabulator-formatter="html">Registration Window</th>
            <th tabulator-formatter="html">Add/Drop Deadline</th>
            <th tabulator-formatter="html">Session Status</th>
            <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="200" hozAlign="right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @php $semIndex = 1; @endphp
          @foreach ($academicYears as $year)
            @foreach ($year->semesters as $semester)
              <tr>
                <td>{{ $semIndex++ }}</td>
                <td>
                  <span class="badge text-bg-secondary">{{ $year->name }}</span>
                </td>
                <td class="fw-bold">{{ $semester->name }} (Sem {{ $semester->semester_number }})</td>
                <td>{{ $semester->start_date->format('M d, Y') }} &ndash; {{ $semester->end_date->format('M d, Y') }}</td>
                <td>
                  @if ($semester->registration_start_date && $semester->registration_end_date)
                    <small class="d-block">{{ $semester->registration_start_date->format('M d') }} &ndash; {{ $semester->registration_end_date->format('M d, Y') }}</small>
                    @if ($semester->isRegistrationOpen())
                      <span class="badge text-bg-success">Open</span>
                    @else
                      <span class="badge text-bg-secondary">Closed</span>
                    @endif
                  @else
                    <span class="text-muted fst-italic">Not Set</span>
                  @endif
                </td>
                <td>
                  @if ($semester->add_drop_deadline)
                    <small class="d-block">{{ $semester->add_drop_deadline->format('M d, Y') }}</small>
                    @if ($semester->isAddDropOpen())
                      <span class="badge text-bg-info">Active</span>
                    @else
                      <span class="badge text-bg-danger">Passed</span>
                    @endif
                  @else
                    <span class="text-muted fst-italic">Not Set</span>
                  @endif
                </td>
                <td>
                  @if ($semester->is_active)
                    <span class="badge text-bg-success"><i class="bi bi-broadcast me-1"></i> Active Session</span>
                  @else
                    <span class="badge text-bg-secondary">Inactive</span>
                  @endif
                </td>
                <td class="text-end">
                  @if (! $semester->is_active)
                    <form action="{{ route('academic.semesters.activate', $semester) }}" method="POST" class="d-inline" onsubmit="return confirm('Activate {{ $semester->name }} ({{ $year->name }}) as the current live session?');">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Activate Semester">
                        <i class="bi bi-play-circle me-1"></i> Activate
                      </button>
                    </form>
                  @endif
                  <a href="{{ route('academic.semesters.edit', $semester) }}" class="btn btn-sm btn-outline-warning" title="Edit Semester">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ route('academic.semesters.destroy', $semester) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete {{ $semester->name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Semester" {{ $semester->is_active ? 'disabled' : '' }}>
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- Scheduled Academic Events & University Almanac Card -->
  <div class="card card-outline card-success mb-4">
    <div class="card-header">
      <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0">
          <i class="bi bi-calendar-event me-1 text-success"></i> Scheduled Academic Events & University Almanac ({{ $academicEvents->count() }})
        </h3>
        <div class="card-tools d-flex flex-wrap align-items-center gap-2 me-0">
          <div class="btn-group btn-group-sm" role="group" aria-label="View toggle">
            <button type="button" class="btn btn-outline-success active" id="btn-view-table">
              <i class="bi bi-table me-1"></i> Agenda Table
            </button>
            <button type="button" class="btn btn-outline-success" id="btn-view-calendar">
              <i class="bi bi-calendar3 me-1"></i> Interactive Calendar
            </button>
          </div>
          <div class="input-group input-group-sm table-search-box" style="width: 15rem;">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="events-filter" class="form-control" placeholder="Filter events..." autocomplete="off">
          </div>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createEventModal">
            <i class="bi bi-plus-circle me-1"></i> Schedule Academic Event
          </button>
        </div>
      </div>
    </div>

    <div class="card-body">
      <!-- 1. Agenda Table View (Default) -->
      <div id="events-table-container">
        <div class="d-flex gap-2 mb-3">
          <button type="button" class="btn btn-sm btn-outline-secondary" id="events-export-csv">
            <i class="bi bi-filetype-csv me-1"></i> Export CSV
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="events-export-json">
            <i class="bi bi-filetype-json me-1"></i> Export JSON
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="events-print">
            <i class="bi bi-printer me-1"></i> Print
          </button>
        </div>

        <table id="events-table" class="table table-hover table-striped align-middle mb-0">
          <thead>
            <tr>
              <th tabulator-formatter="plaintext" width="50">#</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Date(s)</th>
              <th tabulator-formatter="html" tabulator-headerFilter="input">Event Title & Scope</th>
              <th tabulator-formatter="html">Academic Session</th>
              <th tabulator-formatter="html">Event Category</th>
              <th tabulator-formatter="html">Audience</th>
              <th tabulator-formatter="html">Holiday?</th>
              <th tabulator-formatter="html" tabulator-headerSort="false" tabulator-download="false" tabulator-print="false" width="140" hozAlign="right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($academicEvents as $event)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <span class="badge text-bg-light border fw-bold">
                    <i class="bi bi-calendar3 me-1"></i> {{ $event->start_date->format('M d, Y') }}
                    @if ($event->end_date && $event->end_date->ne($event->start_date))
                      &ndash; {{ $event->end_date->format('M d, Y') }}
                    @endif
                  </span>
                </td>
                <td>
                  <div class="fw-bold">{{ $event->title }}</div>
                  @if ($event->description)
                    <small class="text-muted d-block text-truncate" style="max-width: 320px;">
                      {{ $event->description }}
                    </small>
                  @endif
                </td>
                <td>
                  <span class="badge text-bg-secondary">{{ $event->academicYear->name }}</span>
                  @if ($event->semester)
                    <span class="badge bg-body-secondary text-body border ms-1">{{ $event->semester->name }}</span>
                  @else
                    <span class="badge bg-body-secondary text-body border ms-1">Annual Event</span>
                  @endif
                </td>
                <td>
                  <span class="badge {{ $event->badge_class }}">
                    {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-body-secondary text-body border">
                    <i class="bi bi-people me-1"></i> {{ ucfirst($event->target_audience) }}
                  </span>
                </td>
                <td>
                  @if ($event->is_holiday)
                    <span class="badge text-bg-success"><i class="bi bi-check-circle me-1"></i> Yes</span>
                  @else
                    <span class="text-muted">&mdash;</span>
                  @endif
                </td>
                <td class="text-end">
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-warning btn-edit-event"
                    title="Edit Event"
                    data-event="{{ json_encode([
                      'id' => $event->id,
                      'title' => $event->title,
                      'academic_year_id' => $event->academic_year_id,
                      'semester_id' => $event->semester_id,
                      'event_type' => $event->event_type,
                      'start_date' => $event->start_date->format('Y-m-d'),
                      'end_date' => $event->end_date ? $event->end_date->format('Y-m-d') : '',
                      'target_audience' => $event->target_audience,
                      'is_holiday' => $event->is_holiday,
                      'description' => $event->description ?? '',
                    ]) }}"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>
                  <form action="{{ route('academic.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete scheduled event {{ $event->title }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Event">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- 2. Interactive FullCalendar View (Initially Hidden) -->
      <div id="events-calendar-container" style="display: none;">
        <!-- Calendar Legend & Filter Toolbar -->
        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 mb-3 rounded bg-body-tertiary border gap-2">
          <div class="d-flex flex-wrap align-items-center gap-2 small">
            <span class="fw-bold me-1">Legend:</span>
            <span class="badge text-bg-primary">Lectures / Teaching</span>
            <span class="badge text-bg-warning">Academic Deadlines</span>
            <span class="badge text-bg-danger">Examinations</span>
            <span class="badge text-bg-success">Official Holidays</span>
            <span class="badge text-bg-secondary">Ceremonies & Awards</span>
            <span class="badge text-bg-info">Governance & Senate</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="calendar.today()">
              Today
            </button>
          </div>
        </div>

        <div id="academic-fullcalendar" class="p-2"></div>
      </div>
    </div>
  </div>

  <!-- Create Academic Event Modal -->
  <div class="modal fade" id="createEventModal" tabindex="-1" aria-labelledby="createEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form action="{{ route('academic.events.store') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="createEventModalLabel">
              <i class="bi bi-calendar-plus me-1 text-success"></i> Schedule New Academic Event / Deadline
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label for="event_title" class="form-label fw-bold">Event Title / Milestone Name <span class="text-danger">*</span></label>
                <input type="text" name="title" id="event_title" class="form-control" placeholder="e.g. End of Semester 1 Final Examinations" required>
              </div>

              <div class="col-md-6">
                <label for="event_academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                <select name="academic_year_id" id="event_academic_year_id" class="form-select" required>
                  @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}" {{ $year->is_current ? 'selected' : '' }}>
                      {{ $year->name }} {{ $year->is_current ? '(Current Session)' : '' }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label for="event_semester_id" class="form-label fw-bold">Semester (Optional)</label>
                <select name="semester_id" id="event_semester_id" class="form-select">
                  <option value="">-- Full Year / Annual Event --</option>
                  @foreach ($academicYears as $year)
                    <optgroup label="{{ $year->name }}">
                      @foreach ($year->semesters as $sem)
                        <option value="{{ $sem->id }}" {{ $sem->is_active ? 'selected' : '' }}>
                          {{ $year->name }} &bull; {{ $sem->name }}
                        </option>
                      @endforeach
                    </optgroup>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label for="event_type" class="form-label fw-bold">Event Category <span class="text-danger">*</span></label>
                <select name="event_type" id="event_type" class="form-select" required>
                  <option value="academic_deadline">Academic Deadline (e.g. Registration, Add/Drop)</option>
                  <option value="examination">Examination Period / Tests</option>
                  <option value="lecture_period">Lectures & Teaching Session</option>
                  <option value="holiday">Public / University Holiday</option>
                  <option value="governance">Governance / Senate / Board Meeting</option>
                  <option value="ceremony">Ceremony / Graduation / Orientation</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="event_target_audience" class="form-label fw-bold">Target Audience <span class="text-danger">*</span></label>
                <select name="target_audience" id="event_target_audience" class="form-select" required>
                  <option value="all">All University Community</option>
                  <option value="students">Students Only</option>
                  <option value="freshers">Freshers / First-Years</option>
                  <option value="lecturers">Academic Staff / Lecturers</option>
                  <option value="staff">Administrative Staff</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="event_start_date" class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="event_start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
              </div>

              <div class="col-md-6">
                <label for="event_end_date" class="form-label fw-bold">End Date (If Multi-Day Event)</label>
                <input type="date" name="end_date" id="event_end_date" class="form-control">
                <div class="form-text">Leave blank if this is a single-day event or milestone.</div>
              </div>

              <div class="col-12">
                <div class="form-check form-switch">
                  <input type="checkbox" name="is_holiday" id="event_is_holiday" class="form-check-input" value="1">
                  <label for="event_is_holiday" class="form-check-label fw-semibold">
                    Official Holiday (Pauses lectures, attendance tracking, and office operations)
                  </label>
                </div>
              </div>

              <div class="col-12">
                <label for="event_description" class="form-label fw-bold">Description / Guidelines / Venue Notes</label>
                <textarea name="description" id="event_description" rows="3" class="form-control" placeholder="Optional background details or student instructions..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
              <i class="bi bi-check-circle me-1"></i> Schedule Event
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Academic Event Modal -->
  <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="editEventForm" action="" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-header">
            <h5 class="modal-title" id="editEventModalLabel">
              <i class="bi bi-pencil-square me-1 text-warning"></i> Edit Scheduled Academic Event
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label for="edit_title" class="form-label fw-bold">Event Title / Milestone Name <span class="text-danger">*</span></label>
                <input type="text" name="title" id="edit_title" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label for="edit_academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                <select name="academic_year_id" id="edit_academic_year_id" class="form-select" required>
                  @foreach ($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label for="edit_semester_id" class="form-label fw-bold">Semester (Optional)</label>
                <select name="semester_id" id="edit_semester_id" class="form-select">
                  <option value="">-- Full Year / Annual Event --</option>
                  @foreach ($academicYears as $year)
                    <optgroup label="{{ $year->name }}">
                      @foreach ($year->semesters as $sem)
                        <option value="{{ $sem->id }}">{{ $year->name }} &bull; {{ $sem->name }}</option>
                      @endforeach
                    </optgroup>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label for="edit_event_type" class="form-label fw-bold">Event Category <span class="text-danger">*</span></label>
                <select name="event_type" id="edit_event_type" class="form-select" required>
                  <option value="academic_deadline">Academic Deadline</option>
                  <option value="examination">Examination Period / Tests</option>
                  <option value="lecture_period">Lectures & Teaching Session</option>
                  <option value="holiday">Public / University Holiday</option>
                  <option value="governance">Governance / Senate / Board Meeting</option>
                  <option value="ceremony">Ceremony / Graduation / Orientation</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="edit_target_audience" class="form-label fw-bold">Target Audience <span class="text-danger">*</span></label>
                <select name="target_audience" id="edit_target_audience" class="form-select" required>
                  <option value="all">All University Community</option>
                  <option value="students">Students Only</option>
                  <option value="freshers">Freshers / First-Years</option>
                  <option value="lecturers">Academic Staff / Lecturers</option>
                  <option value="staff">Administrative Staff</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="edit_start_date" class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                <input type="date" name="start_date" id="edit_start_date" class="form-control" required>
              </div>

              <div class="col-md-6">
                <label for="edit_end_date" class="form-label fw-bold">End Date</label>
                <input type="date" name="end_date" id="edit_end_date" class="form-control">
              </div>

              <div class="col-12">
                <div class="form-check form-switch">
                  <input type="checkbox" name="is_holiday" id="edit_is_holiday" class="form-check-input" value="1">
                  <label for="edit_is_holiday" class="form-check-label fw-semibold">
                    Official Holiday (Pauses lectures, attendance tracking, and office operations)
                  </label>
                </div>
              </div>

              <div class="col-12">
                <label for="edit_description" class="form-label fw-bold">Description / Guidelines</label>
                <textarea name="description" id="edit_description" rows="3" class="form-control"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning">
              <i class="bi bi-check-circle me-1"></i> Update Event
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Event Details Modal (Triggered on FullCalendar Click) -->
  <div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="viewEventModalLabel">
            <i class="bi bi-info-circle me-1 text-primary"></i> <span id="view-event-title"></span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <span id="view-event-badge" class="badge"></span>
            <span id="view-event-audience" class="badge bg-body-secondary text-body border ms-1"></span>
            <span id="view-event-holiday" class="badge text-bg-success ms-1 d-none"><i class="bi bi-check-circle me-1"></i> Holiday</span>
          </div>

          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted"><i class="bi bi-calendar3 me-2"></i> Date Period</span>
              <span id="view-event-dates" class="fw-bold"></span>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0">
              <span class="text-muted"><i class="bi bi-clock me-2"></i> Academic Session</span>
              <span id="view-event-session" class="fw-bold"></span>
            </li>
          </ul>

          <div id="view-event-description-container" class="p-3 bg-body-tertiary rounded small d-none">
            <h6 class="fw-bold mb-1">Notes & Scope:</h6>
            <div id="view-event-description" style="white-space: pre-line;"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('styles')
  <style>
    .fc .fc-toolbar-title { font-size: 1.2rem; font-weight: 700; }
    .fc .fc-button-primary { background-color: var(--bs-success); border-color: var(--bs-success); }
    .fc .fc-button-primary:hover { background-color: #157347; border-color: #146c43; }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active { background-color: #146c43; border-color: #13653f; }
    .fc-event { cursor: pointer; border-radius: 4px; padding: 2px 4px; font-size: 0.85rem; }
    [data-bs-theme="dark"] .fc-theme-standard td,
    [data-bs-theme="dark"] .fc-theme-standard th { border-color: rgba(255,255,255,0.12); }
    [data-bs-theme="dark"] .fc-theme-standard .fc-scrollgrid { border-color: rgba(255,255,255,0.12); }
    [data-bs-theme="dark"] .fc .fc-daygrid-day-number { color: var(--bs-body-color); }
    [data-bs-theme="dark"] .fc .fc-col-header-cell-cushion { color: var(--bs-body-color); }
    [data-bs-theme="dark"] .fc .fc-list-event:hover td { background-color: rgba(255,255,255,0.05); }
    [data-bs-theme="dark"] .fc .fc-list-day-cushion { background-color: var(--bs-body-secondary); color: var(--bs-body-color); }
  </style>
@endpush

@push('scripts')
  <script src="{{ asset('vendor/fullcalendar/index.global.min.js') }}"></script>
  <script>
    let calendar = null;

    document.addEventListener('DOMContentLoaded', function () {
      // 1. Initialize Academic Years DataTable
      initAdminLteDataTable('#years-table', {
        filterInput: '#years-filter',
        btnCsv: '#years-export-csv',
        btnJson: '#years-export-json',
        btnPrint: '#years-print',
        filename: 'academic_years_export',
      });

      // 2. Initialize Semesters DataTable
      initAdminLteDataTable('#semesters-table', {
        filterInput: '#semesters-filter',
        btnCsv: '#semesters-export-csv',
        btnJson: '#semesters-export-json',
        btnPrint: '#semesters-print',
        filename: 'semesters_export',
      });

      // 3. Initialize Scheduled Events DataTable
      initAdminLteDataTable('#events-table', {
        filterInput: '#events-filter',
        btnCsv: '#events-export-csv',
        btnJson: '#events-export-json',
        btnPrint: '#events-print',
        filename: 'academic_events_export',
      });

      // 4. View Switching: Agenda Table vs FullCalendar
      const btnViewTable = document.getElementById('btn-view-table');
      const btnViewCalendar = document.getElementById('btn-view-calendar');
      const tableContainer = document.getElementById('events-table-container');
      const calendarContainer = document.getElementById('events-calendar-container');
      const tableSearchBox = document.querySelector('.table-search-box');

      btnViewTable.addEventListener('click', function () {
        btnViewTable.classList.add('active');
        btnViewCalendar.classList.remove('active');
        tableContainer.style.display = 'block';
        calendarContainer.style.display = 'none';
        if (tableSearchBox) tableSearchBox.style.display = 'flex';
      });

      btnViewCalendar.addEventListener('click', function () {
        btnViewCalendar.classList.add('active');
        btnViewTable.classList.remove('active');
        tableContainer.style.display = 'none';
        calendarContainer.style.display = 'block';
        if (tableSearchBox) tableSearchBox.style.display = 'none';

        if (!calendar) {
          initFullCalendar();
        } else {
          calendar.updateSize();
        }
      });

      // 5. Initialize FullCalendar
      function initFullCalendar() {
        const calendarEl = document.getElementById('academic-fullcalendar');
        if (!calendarEl || typeof FullCalendar === 'undefined') return;

        calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          initialDate: '{{ $activeSemester ? $activeSemester->start_date->toDateString() : date('Y-m-d') }}',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
          },
          events: '{{ route('academic.events.feed') }}',
          eventClick: function(info) {
            info.jsEvent.preventDefault();
            const event = info.event;
            const props = event.extendedProps;

            document.getElementById('view-event-title').textContent = event.title;
            const badgeEl = document.getElementById('view-event-badge');
            badgeEl.className = 'badge ' + (props.badgeClass || 'text-bg-primary');
            badgeEl.textContent = props.eventType;

            document.getElementById('view-event-audience').textContent = 'Audience: ' + props.audience;
            const holidayEl = document.getElementById('view-event-holiday');
            if (props.isHoliday) {
              holidayEl.classList.remove('d-none');
            } else {
              holidayEl.classList.add('d-none');
            }

            let dateText = props.rawStartDate;
            if (props.rawEndDate && props.rawEndDate !== props.rawStartDate) {
              dateText += ' – ' + props.rawEndDate;
            }
            document.getElementById('view-event-dates').textContent = dateText;
            document.getElementById('view-event-session').textContent = props.academicYear + (props.semester ? ' • ' + props.semester : '');

            const descContainer = document.getElementById('view-event-description-container');
            const descEl = document.getElementById('view-event-description');
            if (props.description) {
              descEl.textContent = props.description;
              descContainer.classList.remove('d-none');
            } else {
              descContainer.classList.add('d-none');
            }

            const modal = new bootstrap.Modal(document.getElementById('viewEventModal'));
            modal.show();
          },
          themeSystem: 'bootstrap5'
        });

        calendar.render();
      }

      // 6. Handle Edit Event Modal Population
      document.querySelectorAll('.btn-edit-event').forEach(btn => {
        btn.addEventListener('click', function () {
          const data = JSON.parse(this.getAttribute('data-event'));
          const form = document.getElementById('editEventForm');
          form.action = '{{ url('academic/events') }}/' + data.id;

          document.getElementById('edit_title').value = data.title;
          document.getElementById('edit_academic_year_id').value = data.academic_year_id;
          document.getElementById('edit_semester_id').value = data.semester_id || '';
          document.getElementById('edit_event_type').value = data.event_type;
          document.getElementById('edit_target_audience').value = data.target_audience;
          document.getElementById('edit_start_date').value = data.start_date;
          document.getElementById('edit_end_date').value = data.end_date || '';
          document.getElementById('edit_is_holiday').checked = Boolean(data.is_holiday);
          document.getElementById('edit_description').value = data.description || '';

          const modal = new bootstrap.Modal(document.getElementById('editEventModal'));
          modal.show();
        });
      });
    });
  </script>
@endpush
