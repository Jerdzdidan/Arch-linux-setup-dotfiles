@extends('layout.base')

@section('title')
Home — AU-AIS
@endsection

@section('head')
<link rel="stylesheet" href="{{ asset('css/app/home.css') }}">
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@endsection

@section('nav_title')
HOME
@endsection

@section('body')
<div class="container-fluid">
    <div class="row">

        {{-- ═══════════════════════════════════════════ --}}
        {{-- MAIN CONTENT PANE                         --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="col-lg-8 mb-4">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="home-section-title mb-0">
                        <i class="fa-solid fa-bullhorn me-2"></i>Announcements
                    </h5>
                </div>
                @if(auth()->user()->user_type === 'ADMIN')
                <button class="btn btn-primary btn-compose" data-bs-toggle="modal" data-bs-target="#composeAnnouncementModal">
                    <i class="fa-solid fa-plus me-1"></i> Post Announcement
                </button>
                @endif
            </div>

            {{-- Announcements Feed --}}
            @if($announcements->isEmpty())
            <div class="card announcement-card">
                <div class="card-body empty-announcements">
                    <i class="fa-solid fa-clipboard"></i>
                    <h5>No announcements yet</h5>
                    <p class="mb-0">
                        @if(auth()->user()->user_type === 'ADMIN')
                        Click "Post Announcement" to create the first one.
                        @else
                        Check back later for updates from the administration.
                        @endif
                    </p>
                </div>
            </div>
            @else
            @foreach($announcements as $announcement)
            <div class="card announcement-card mb-3" id="announcement-{{ $announcement->id }}">
                <div class="card-body">
                    {{-- Title row --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="announcement-title mb-0">{{ $announcement->title }}</h6>
                            @if($announcement->is_pinned)
                            <span class="badge bg-label-warning pin-badge">
                                <i class="fa-solid fa-thumbtack me-1"></i>Pinned
                            </span>
                            @endif
                        </div>

                        @if(auth()->user()->user_type === 'ADMIN')
                        <div class="announcement-actions d-flex gap-1 ms-2 flex-shrink-0">
                            <button class="btn btn-outline-warning btn-sm toggle-pin-btn"
                                data-id="{{ $announcement->id }}"
                                title="{{ $announcement->is_pinned ? 'Unpin' : 'Pin' }}">
                                <i class="fa-solid fa-thumbtack"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm delete-announcement-btn"
                                data-id="{{ $announcement->id }}"
                                title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                        @endif
                    </div>

                    {{-- Body --}}
                    <div class="announcement-body mb-3">
                        {!! $announcement->body !!}
                    </div>

                    {{-- Meta --}}
                    <div class="announcement-meta d-flex align-items-center gap-3">
                        <span>
                            <i class="fa-solid fa-user me-1"></i>
                            {{ $announcement->poster->name ?? 'Unknown' }}
                        </span>
                        <span>
                            <i class="fa-regular fa-clock me-1"></i>
                            {{ $announcement->created_at->format('M d, Y — h:i A') }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Pagination --}}
            @if($announcements->hasPages())
            <div class="d-flex justify-content-center mt-3 home-pagination">
                {{ $announcements->links() }}
            </div>
            @endif
            @endif
        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- SIDEBAR PANE                              --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="col-lg-4 home-sidebar">

            {{-- User Information Card --}}
            <div class="card user-info-card mb-4">
                <div class="card-body">
                    <h6 class="home-section-title mb-3">
                        <i class="fa-solid fa-id-card me-2"></i>My Profile
                    </h6>
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('img/profile/default.png') }}" alt="Avatar" class="user-avatar">
                        <div>
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            @php
                            $typeBadgeColors = [
                            'ADMIN' => 'bg-label-danger',
                            'OFFICER' => 'bg-label-info',
                            'STUDENT' => 'bg-label-success',
                            ];
                            $badgeColor = $typeBadgeColors[auth()->user()->user_type] ?? 'bg-label-secondary';
                            @endphp
                            <span class="badge {{ $badgeColor }} user-type-badge">{{ auth()->user()->user_type }}</span>
                        </div>
                    </div>
                    @if(auth()->user()->email)
                    <div class="user-email mt-3">
                        <i class="fa-regular fa-envelope me-1"></i>
                        {{ auth()->user()->email }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Links Card --}}
            <div class="card quick-links-card mb-4">
                <div class="card-body">
                    <h6 class="home-section-title mb-3">
                        <i class="fa-solid fa-link me-2"></i>Quick Links
                    </h6>
                    <div class="list-group list-group-flush">
                        @if(auth()->user()->user_type === 'ADMIN')
                        <a href="{{ route('academic_periods.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-school-flag me-2"></i>Academic Periods
                        </a>
                        <a href="{{ route('students.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-user-graduate me-2"></i>Student Accounts
                        </a>
                        <a href="{{ route('grades.import.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-file-import me-2"></i>Grade Imports
                        </a>
                        <a href="{{ route('announcements.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-bullhorn me-2"></i>Email Announcements
                        </a>
                        @elseif(auth()->user()->user_type === 'OFFICER')
                        <a href="{{ route('officer.students') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-user-graduate me-2"></i>Student Progress
                        </a>
                        <a href="{{ route('officer.announcements.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-bullhorn me-2"></i>Email Announcements
                        </a>
                        @elseif(auth()->user()->user_type === 'STUDENT')
                        <a href="{{ route('student.academic_progress.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-chart-line me-2"></i>Academic Progress
                        </a>
                        <a href="{{ route('student.grades.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-star me-2"></i>Grades
                        </a>
                        <a href="{{ route('student.manual.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-book me-2"></i>Student Manual
                        </a>
                        <a href="{{ route('student.faqs.index') }}" class="list-group-item list-group-item-action">
                            <i class="fa-solid fa-question-circle me-2"></i>FAQs
                        </a>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════ --}}
{{-- COMPOSE ANNOUNCEMENT MODAL (Admin Only)   --}}
{{-- ═══════════════════════════════════════════ --}}
@if(auth()->user()->user_type === 'ADMIN')
<div class="modal fade" id="composeAnnouncementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-bullhorn me-2"></i>Post Announcement
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="homepage-announcement-form">
                    @csrf

                    {{-- Title --}}
                    <div class="mb-3">
                        <x-input.input-field
                            id="announcement-title"
                            name="title"
                            label="Title"
                            icon="fa-solid fa-heading"
                            placeholder="e.g. Enrollment for 2nd Semester is now open!" />
                    </div>

                    {{-- Pin toggle --}}
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="announcement-pinned" name="is_pinned" value="1">
                            <label class="form-check-label" for="announcement-pinned">
                                <i class="fa-solid fa-thumbtack me-1"></i>Pin this announcement
                            </label>
                        </div>
                    </div>

                    {{-- Body (Quill Rich‑Text) --}}
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <div id="homepage-quill-editor"></div>
                        <input type="hidden" name="body" id="homepage-body-input">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="post-announcement-btn">
                    <i class="fa-solid fa-paper-plane me-2"></i>Post Announcement
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
@if(auth()->user()->user_type === 'ADMIN')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
    $(document).ready(function() {

        // ─── Quill Editor ───
        var quill = new Quill('#homepage-quill-editor', {
            theme: 'snow',
            placeholder: 'Write your announcement content here...',
            modules: {
                toolbar: [
                    [{
                        'header': [1, 2, 3, false]
                    }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{
                        'list': 'ordered'
                    }, {
                        'list': 'bullet'
                    }],
                    [{
                        'align': []
                    }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // ─── Post Announcement ───
        $('#post-announcement-btn').on('click', function() {
            // Sync Quill content
            var htmlContent = quill.root.innerHTML;
            if (quill.getText().trim().length === 0) {
                toastr.error('The content body is required.');
                return;
            }
            $('#homepage-body-input').val(htmlContent);

            var title = $('#announcement-title').val();
            if (!title || title.trim().length === 0) {
                toastr.error('The title is required.');
                return;
            }

            var btn = $(this);
            var originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i>Posting...');

            $.ajax({
                url: "{{ route('homepage-announcements.store') }}",
                method: 'POST',
                data: $('#homepage-announcement-form').serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    var modal = bootstrap.Modal.getInstance(document.getElementById('composeAnnouncementModal'));
                    if (modal) modal.hide();
                    // Reload the page to see the new announcement
                    location.reload();
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
        });

        // Reset form on modal close
        $('#composeAnnouncementModal').on('hidden.bs.modal', function() {
            $('#homepage-announcement-form')[0].reset();
            quill.setContents([]);
        });

        // ─── Delete Announcement ───
        $(document).on('click', '.delete-announcement-btn', function() {
            var id = $(this).data('id');
            var card = $('#announcement-' + id);

            Swal.fire({
                title: 'Delete Announcement?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3e1d',
                cancelButtonColor: '#8592a3',
                confirmButtonText: '<i class="fa-solid fa-trash me-1"></i> Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/homepage-announcements/' + id,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            card.fadeOut(300, function() {
                                $(this).remove();
                            });
                        },
                        error: function() {
                            toastr.error('Failed to delete announcement.');
                        }
                    });
                }
            });
        });

        // ─── Toggle Pin ───
        $(document).on('click', '.toggle-pin-btn', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '/admin/homepage-announcements/toggle-pin/' + id,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    toastr.success(response.message);
                    location.reload();
                },
                error: function() {
                    toastr.error('Failed to update pin status.');
                }
            });
        });
    });
</script>
@endif
@endsection