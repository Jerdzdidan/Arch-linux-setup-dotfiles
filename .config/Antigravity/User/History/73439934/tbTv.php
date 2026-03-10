@extends('layout.base')

@section('title')
Email Announcements
@endsection

@section('head')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <style>
        .compose-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .compose-card .card-header {
            background: linear-gradient(135deg, #696cff, #5f61e6);
            color: #fff;
            border-radius: 10px 10px 0 0;
            padding: 16px 24px;
        }
        .compose-card .card-header h5 {
            margin: 0;
            font-weight: 600;
        }
        #quill-editor {
            min-height: 200px;
            background: #fff;
        }
        .ql-toolbar.ql-snow {
            border-radius: 6px 6px 0 0;
            border-color: #d9dee3;
        }
        .ql-container.ql-snow {
            border-radius: 0 0 6px 6px;
            border-color: #d9dee3;
        }
        .recipient-count-badge {
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 6px;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            margin-top: 12px;
        }
    </style>
@endsection

@section('nav_title')
Email Announcements
@endsection

@section('body')
<div class="container-fluid">
    <div class="content-container">

        {{-- Compose Announcement Card --}}
        <div class="card compose-card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5><i class="fa-solid fa-pen-to-square me-2"></i>Compose Announcement</h5>
            </div>
            <div class="card-body p-4">
                <form id="announcement-form">
                    @csrf
                    {{-- Subject --}}
                    <div class="mb-3">
                        <label for="subject" class="form-label fw-semibold">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject"
                               placeholder="e.g. Grades are now posted!" required>
                        <div class="invalid-feedback" id="subject-error"></div>
                    </div>

                    {{-- Recipient Info (locked to students) --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Send To</label>
                        <input type="text" class="form-control" value="Students Only" disabled>
                        <small class="text-muted">As an officer, announcements are sent to students.</small>
                    </div>

                    {{-- Filters --}}
                    <div class="filter-section" id="student-filters">
                        <label class="form-label fw-semibold mb-2">
                            <i class="fa-solid fa-filter me-1"></i>Optional Filters
                        </label>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="program_id" class="form-label">Program</label>
                                <select class="form-select" id="program_id" name="program_id">
                                    <option value="">All Programs</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="year_level" class="form-label">Year Level</label>
                                <select class="form-select" id="year_level" name="year_level">
                                    <option value="">All Year Levels</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Recipient Count Preview --}}
                    <div class="mt-3 mb-3">
                        <span class="recipient-count-badge bg-label-info">
                            <i class="fa-solid fa-users me-1"></i>
                            Estimated Recipients: <strong id="recipient-count">0</strong>
                        </span>
                    </div>

                    {{-- Body (Rich Text) --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Message Body</label>
                        <div id="quill-editor"></div>
                        <input type="hidden" name="body" id="body-input">
                        <div class="invalid-feedback d-block" id="body-error"></div>
                    </div>

                    {{-- Submit --}}
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4" id="send-btn">
                            <i class="fa-solid fa-paper-plane me-2"></i>Send Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Announcement History --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 fw-semibold"><i class="fa-solid fa-clock-rotate-left me-2"></i>My Announcement History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="announcementsTable" style="width:100%">
                        <thead>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Sent To</th>
                            <th>Recipients</th>
                            <th>Date</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
$(document).ready(function() {

    // ─── Quill Editor ───
    var quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your announcement message here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    // ─── Load Filter Options ───
    $.get("{{ route('officer.announcements.filters') }}", function(data) {
        data.programs.forEach(function(p) {
            $('#program_id').append(`<option value="${p.id}">${p.text}</option>`);
        });
        data.year_levels.forEach(function(y) {
            $('#year_level').append(`<option value="${y.id}">${y.text}</option>`);
        });

        updateRecipientCount();
    });

    $('#program_id, #year_level').on('change', function() {
        updateRecipientCount();
    });

    // ─── Recipient Count ───
    var countTimer = null;
    function updateRecipientCount() {
        clearTimeout(countTimer);
        countTimer = setTimeout(function() {
            $.get("{{ route('officer.announcements.count') }}", {
                program_id: $('#program_id').val(),
                year_level: $('#year_level').val()
            }, function(data) {
                $('#recipient-count').text(data.count);
            });
        }, 300);
    }

    // ─── DataTable ───
    $('#announcementsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('officer.announcements.data') }}",
        order: [[0, 'desc']],
        columns: [
            { data: 'id', visible: false },
            { data: 'subject' },
            { data: 'recipient_label', orderable: false },
            { data: 'recipients_count' },
            { data: 'formatted_date', orderable: false }
        ]
    });

    // ─── Form Submit ───
    $('#announcement-form').on('submit', function(e) {
        e.preventDefault();

        // Sync Quill content
        var htmlContent = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) {
            $('#body-error').text('The message body is required.');
            return;
        }
        $('#body-input').val(htmlContent);
        $('#body-error').text('');

        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('#subject-error').text('');

        var btn = $('#send-btn');
        var originalHtml = btn.html();
        btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Sending...');

        Swal.fire({
            title: 'Send Announcement?',
            html: `This will send an email to <strong>${$('#recipient-count').text()}</strong> student(s).<br>This action cannot be undone.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i> Yes, Send',
            cancelButtonText: 'Cancel'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('officer.announcements.store') }}",
                    method: 'POST',
                    data: $('#announcement-form').serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        $('#announcement-form')[0].reset();
                        quill.setContents([]);
                        $('#announcementsTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            if (errors) {
                                if (errors.subject) {
                                    $('#subject').addClass('is-invalid');
                                    $('#subject-error').text(errors.subject[0]);
                                }
                                if (errors.body) {
                                    $('#body-error').text(errors.body[0]);
                                }
                            }
                            if (xhr.responseJSON.message) {
                                toastr.error(xhr.responseJSON.message);
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
});
</script>
@endsection
