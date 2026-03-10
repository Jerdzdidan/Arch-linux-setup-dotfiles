@extends('layout.base')

@section('title')
Email Announcements
@endsection

@section('nav_title')
Email Announcements
@endsection

@section('body')
<div class="container-fluid">
    <div class="content-container">
        <!-- Page Header -->
        <x-table.page-header title="" subtitle="Compose and send email announcements">
            <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#compose-modal">
                <i class="fa-solid fa-pen-to-square me-2"></i>
                Compose Announcement
            </button>
        </x-table.page-header>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <x-table.stats-card
                id="totalAnnouncements"
                title="Total Sent"
                icon="fa-solid fa-bullhorn fa-2x"
                bgColor="bg-primary"
                class="col-md-4"/>

            <x-table.stats-card
                id="totalRecipients"
                title="Total Recipients"
                icon="fa-solid fa-users fa-2x"
                bgColor="bg-info"
                class="col-md-4"/>

            <x-table.stats-card
                id="latestAnnouncement"
                title="Latest Sent"
                icon="fa-solid fa-clock fa-2x"
                bgColor="bg-success"
                class="col-md-4"/>
        </div>

        <!-- DataTable -->
        <x-table.table id="announcementsTable">
            <th>ID</th>
            <th>Subject</th>
            <th>Sent To</th>
            <th>Recipients</th>
            <th>Sent By</th>
            <th>Date</th>
        </x-table.table>

        {{-- Compose Announcement Modal --}}
        <x-modals.creation-and-update-modal
            id="compose-modal"
            title="Compose Announcement"
            action=""
            formId="announcement-form"
            submitButtonName="Send Announcement"
        >
            {{-- Subject --}}
            <div class="mb-3">
                <x-input.input-field
                    id="subject"
                    name="subject"
                    label="Subject"
                    icon="fa-solid fa-heading"
                    placeholder="e.g. Grades are now posted!"
                />
            </div>

            {{-- Recipient Type --}}
            <div class="mb-3">
                <x-input.select-field
                    id="recipient_type"
                    label="Send To"
                    icon=""
                    :options="[
                        ['value' => 'all', 'text' => 'All Users'],
                        ['value' => 'students', 'text' => 'Students Only'],
                        ['value' => 'officers', 'text' => 'Officers Only'],
                        ['value' => 'admins', 'text' => 'Admins Only'],
                    ]"
                    placeholder="Select recipient type"
                />
            </div>

            {{-- Program Filter --}}
            <div class="mb-3" id="filter-program-container">
                <label class="form-label w-100" for="program_id">Program (optional filter)</label>
                <select id="program_id" name="program_id" class="form-control w-100">
                    <option value=""></option>
                </select>
            </div>

            {{-- Year Level Filter --}}
            <div class="mb-3" id="filter-year-container">
                <label class="form-label w-100" for="year_level">Year Level (optional filter)</label>
                <select id="year_level" name="year_level" class="form-control w-100">
                    <option value=""></option>
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                    <option value="4">Year 4</option>
                    <option value="5">Year 5</option>
                </select>
            </div>

            {{-- Recipient Count Preview --}}
            <div class="mb-3">
                <span class="badge bg-label-info">
                    <i class="fa-solid fa-users me-1"></i>
                    Estimated Recipients: <strong id="recipient-count">0</strong>
                </span>
            </div>

            {{-- Body --}}
            <div class="mb-3">
                <x-input.text-area-field
                    id="body"
                    label="Message Body"
                    icon="fa-solid fa-message"
                    rows="6"
                    placeholder="Write your announcement message here..."
                />
            </div>
        </x-modals.creation-and-update-modal>

    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/shared/generic-datatable.js') }}"></script>
<script src="{{ asset('js/shared/select2-init.js') }}"></script>
<script>
$(document).ready(function() {

    // ─── Select2 Dropdowns ───
    $('#recipient_type').select2({
        minimumResultsForSearch: -1,
        placeholder: 'Select recipient type',
        dropdownParent: $('#compose-modal')
    });

    $('#program_id').select2({
        allowClear: true,
        placeholder: 'All Programs',
        dropdownParent: $('#compose-modal')
    });

    $('#year_level').select2({
        allowClear: true,
        placeholder: 'All Year Levels',
        minimumResultsForSearch: -1,
        dropdownParent: $('#compose-modal')
    });

    // Load filter options
    $.get("{{ route('announcements.filters') }}", function(data) {
        data.programs.forEach(function(p) {
            $('#program_id').append('<option value="' + p.id + '">' + p.text + '</option>');
        });
    });

    // Set default to students
    $('#recipient_type').val('students').trigger('change');

    // ─── Toggle Filters Visibility ───
    function toggleFilters() {
        var type = $('#recipient_type').val();
        if (type === 'students') {
            $('#filter-program-container, #filter-year-container').show();
        } else {
            $('#filter-program-container, #filter-year-container').hide();
            resetSelect2('#program_id');
            resetSelect2('#year_level');
        }
    }

    $('#recipient_type').on('change', function() {
        toggleFilters();
        updateRecipientCount();
    });
    toggleFilters();

    $('#program_id, #year_level').on('change', function() {
        updateRecipientCount();
    });

    // ─── Recipient Count ───
    var countTimer = null;
    function updateRecipientCount() {
        clearTimeout(countTimer);
        countTimer = setTimeout(function() {
            $.get("{{ route('announcements.count') }}", {
                recipient_type: $('#recipient_type').val(),
                program_id: $('#program_id').val(),
                year_level: $('#year_level').val()
            }, function(data) {
                $('#recipient-count').text(data.count);
            });
        }, 300);
    }
    updateRecipientCount();

    // ─── DataTable ───
    const announcementsTable = new GenericDataTable({
        tableId: 'announcementsTable',
        ajaxUrl: "{{ route('announcements.data') }}",
        columns: [
            { data: 'id', visible: false },
            { data: 'subject' },
            { data: 'recipient_label', orderable: false },
            { data: 'recipients_count' },
            { data: 'sender_name', orderable: false },
            { data: 'formatted_date', orderable: false }
        ],
        order: [[0, 'desc']],
        statsCards: {
            callback: function() {
                $.get("{{ route('announcements.data') }}", { length: -1 }, function(data) {
                    var records = data.data || [];
                    $('#totalAnnouncements').text(records.length);
                    var totalRecipients = records.reduce(function(sum, r) { return sum + (r.recipients_count || 0); }, 0);
                    $('#totalRecipients').text(totalRecipients);
                    if (records.length > 0) {
                        $('#latestAnnouncement').text(records[0].formatted_date);
                    } else {
                        $('#latestAnnouncement').text('N/A');
                    }
                });
            }
        }
    }).init();

    // ─── Form Submit ───
    $('#announcement-form').on('submit', function(e) {
        e.preventDefault();

        var body = $('#body').val();
        if (!body || body.trim().length === 0) {
            toastr.error('The message body is required.');
            return;
        }

        var btn = $(this).find('button[type="submit"]');
        var originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Sending...');

        Swal.fire({
            title: 'Send Announcement?',
            html: 'This will send an email to <strong>' + $('#recipient-count').text() + '</strong> recipient(s).<br>This action cannot be undone.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i> Yes, Send',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('announcements.store') }}",
                    method: 'POST',
                    data: $('#announcement-form').serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        // Close offcanvas & reset form
                        var offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('compose-modal'));
                        if (offcanvas) offcanvas.hide();
                        $('#announcement-form')[0].reset();
                        $('#recipient_type').val('students').trigger('change');
                        resetSelect2('#program_id');
                        resetSelect2('#year_level');
                        // Reload table
                        announcementsTable.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            if (xhr.responseJSON.message) {
                                toastr.error(xhr.responseJSON.message);
                            }
                            var errors = xhr.responseJSON.errors;
                            if (errors) {
                                $.each(errors, function(key, value) {
                                    toastr.error(value[0]);
                                });
                            }
                        } else {
                            toastr.error('Something went wrong. Please try again.');
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            } else {
                btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Reset form on modal close
    $('#compose-modal').on('hidden.bs.offcanvas', function() {
        $('#announcement-form')[0].reset();
        $('#recipient_type').val('students').trigger('change');
        resetSelect2('#program_id');
        resetSelect2('#year_level');
        updateRecipientCount();
    });
});
</script>
@endsection
