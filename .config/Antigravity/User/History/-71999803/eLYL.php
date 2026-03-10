<!DOCTYPE html>

<html
  lang="en"
  class="layout-menu-fixed layout-wide"
  data-assets-path="{{ asset('themes/sneat/assets') }}"
  data-template="vertical-menu-template-free">
  <head>

    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title')</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/logo/arellano_logo.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/fonts/iconify-icons.css') }}" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->

    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- endbuild -->

    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/libs/datatables-rowgroup-bs5/rowgroup.bootstrap5.css') }}" />

    <!-- Flatpicker -->
    <link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/libs/flatpickr/flatpickr.css') }}" />

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Remixicon icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css">

    <!-- Fontawesome icons -->
    <script src="https://kit.fontawesome.com/c5804bd254.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('css/layout/select2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout/layout_custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout/delete_popup_modal.css') }}">

    @yield('head')

    @yield('style')

    <!-- Helpers -->
    <script src="{{ asset('themes/sneat/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="{{ asset('themes/sneat/assets/js/config.js') }}"></script>
    
  </head>

  <body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
        <!-- SIDEBAR -->
        @if(auth()->user()->user_type == 'ADMIN')
          @include('layout.sidebar.admin')
        @elseif(auth()->user()->user_type == 'OFFICER')
          @include('layout.sidebar.officer')
        @elseif(auth()->user()->user_type == 'STUDENT')
          @include('layout.sidebar.student')
        @endif
        <!-- SIDEBAR -->

        <!-- DELETE POPUP MODAL -->
        <!-- DELETE POPUP MODAL -->

        <!-- Layout container -->
        <div class="layout-page">
          <!-- Navbar -->
          @include('layout.navbar')
          <!-- / Navbar -->

          <!-- Content wrapper -->
          <div class="content-wrapper">
            <!-- MAIN Content -->
            <div class="container-fluid flex-grow-1 container-p-y mb-0">
              @yield('body')
            </div>
            <!-- / MAIN Content -->

            <!-- Footer -->
            <footer class="content-footer footer bg-footer-theme">
              <div class="container-fluid">
                <div
                  class="footer-container d-flex align-items-center justify-content-between py-0 flex-md-row flex-column">
                  <div class="mb-2 mb-md-0">
                    ©
                    <script>
                      document.write(new Date().getFullYear());
                    </script>, made by
                    <a href="https://www.facebook.com/jdgonzdayao" target="_blank" class="footer-link">Jerdan Gondayao</a>,
                    <a href="https://www.facebook.com/rhennmarc.mercado" target="_blank" class="footer-link">Rhenn Marc Mercado</a>, and
                    <a href="https://www.facebook.com/Johnmer1115" target="_blank" class="footer-link">Johnmer Tanqui-on</a>
                  </div>
                </div>
              </div>
            </footer>
            <!-- / Footer -->

            <div class="content-backdrop fade"></div>
          </div>
          <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    {{-- Email Prompt Modal (students without email) --}}
    @if(auth()->check() && auth()->user()->user_type === 'STUDENT' && isset($studentHasEmail) && !$studentHasEmail)
    <div class="modal fade" id="emailPromptModal" tabindex="-1" aria-labelledby="emailPromptModalLabel" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="emailPromptModalLabel">
              <i class="ri-mail-line me-2"></i>Email Address Required
            </h5>
          </div>
          <div class="modal-body">
            <p class="mb-3">We noticed you don't have an email address on file. Please provide your email to continue.</p>
            <form id="emailPromptForm">
              @csrf
              <div class="mb-0">
                <label for="studentEmailInput" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="studentEmailInput" name="email"
                       placeholder="Enter your email address" required>
                <div class="invalid-feedback" id="emailError"></div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="submitEmailBtn">
              <span class="spinner-border spinner-border-sm d-none me-1" id="emailSpinner" role="status"></span>
              Save Email
            </button>
          </div>
        </div>
      </div>
    </div>
    @endif


    <!-- Core JS -->

    <script src="{{ asset('themes/sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="{{ asset('themes/sneat/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('themes/sneat/assets/vendor/js/bootstrap.js') }}"></script>

    <script src="{{ asset('themes/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('themes/sneat/assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('themes/sneat/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <!-- Flatpicker -->
    <script src="{{ asset('themes/sneat/assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Main JS -->

    <script src="{{ asset('themes/sneat/assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('themes/sneat/assets/js/tables-datatables-basic.js') }}"></script>

    @yield('scripts')

    {{-- Email Prompt Modal Script --}}
    @if(auth()->check() && auth()->user()->user_type === 'STUDENT' && isset($studentHasEmail) && !$studentHasEmail)
    <script>
      $(document).ready(function() {
        // Show modal on page load
        var emailModal = new bootstrap.Modal(document.getElementById('emailPromptModal'));
        emailModal.show();

        // Handle form submission
        $('#submitEmailBtn').on('click', function() {
          var email = $('#studentEmailInput').val();
          var btn = $(this);
          var spinner = $('#emailSpinner');

          // Clear previous errors
          $('#studentEmailInput').removeClass('is-invalid');
          $('#emailError').text('');

          // Show spinner
          spinner.removeClass('d-none');
          btn.prop('disabled', true);

          $.ajax({
            url: '{{ route("student.update_email") }}',
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              email: email
            },
            success: function(response) {
              emailModal.hide();
              Swal.fire({
                icon: 'success',
                title: 'Email Saved!',
                text: response.message,
                confirmButtonColor: '#696cff'
              }).then(function() {
                location.reload();
              });
            },
            error: function(xhr) {
              spinner.addClass('d-none');
              btn.prop('disabled', false);

              if (xhr.status === 422) {
                var errors = xhr.responseJSON.errors;
                if (errors.email) {
                  $('#studentEmailInput').addClass('is-invalid');
                  $('#emailError').text(errors.email[0]);
                }
              } else {
                toastr.error('Something went wrong. Please try again.');
              }
            }
          });
        });

        // Allow Enter key to submit
        $('#studentEmailInput').on('keypress', function(e) {
          if (e.which === 13) {
            e.preventDefault();
            $('#submitEmailBtn').click();
          }
        });
      });
    </script>
    @endif

    <!-- Place this tag before closing body tag for github widget button. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
  </body>

</html>
