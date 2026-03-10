<!Doctype html>

<html
  lang="en"
  class=" layout-wide  customizer-hide"
  data-assets-path="{{ asset('themes/sneat/assets') }}"
  data-template="vertical-menu-template"

  <head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<meta name="robots" content="noindex, nofollow" />

<title>CyberWais - Login</title>

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="{{ asset('img/logo/logo-white-bg.png') }}" />

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

<link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/fonts/iconify-icons.css') }}" />

<!-- Core CSS -->
<!-- build:css assets/vendor/css/theme.css  -->

<link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/css/core.css') }}" />
<link rel="stylesheet" href="{{ asset('themes/sneat/assets/css/demo.css') }}" />


<!-- Vendors CSS -->

<link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

<!-- endbuild -->

<!-- Vendor -->

<!-- Page CSS -->
<!-- Page -->
<link rel="stylesheet" href="{{ asset('themes/sneat/assets/vendor/css/pages/page-auth.css') }}" />

<link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}" />

<!-- Helpers -->
<script src="{{ asset('themes/sneat/assets/vendor/js/helpers.js') }}"></script>

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

<script src="{{ asset('themes/sneat/assets/js/config.js') }}"></script>

</head>

<body>

  <!-- Content -->

  <div class="authentication-wrapper authentication-cover">
    <!-- Logo -->
    <a href="{{ route('landing.index') }}" class="app-brand auth-cover-brand gap-2">
      <span class="app-brand-logo demo">
        <span class="">
          <img src="{{ asset('img/landing/logo.png') }}" alt="" style="width: 40px; height: auto; max-width: 100%;">
        </span>
      </span>
      <span class="app-brand-text demo text-heading fw-bold">CyberWais</span>
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
      <!-- /Left Text -->
      <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
        <div class="w-100 d-flex justify-content-center">
          <img src="{{ asset('img/auth/signup-art.png') }}" class="img-fluid" alt="Login image" width="700" data-app-dark-img="illustrations/boy-with-rocket-dark.png" data-app-light-img="illustrations/boy-with-rocket-light.png" />
        </div>
      </div>
      <!-- /Left Text -->

      <!-- Login -->
      <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
        <div class="w-px-400 mx-auto mt-sm-12 mt-8">
          <div class="d-flex align-items-center">
            <h4 class="mb-1">Learning starts here!</h4>
            <img src="{{ asset('img/auth/rocket.gif') }}" id="hand_gif" class="mb-2" alt="">
          </div>
          <p class="mb-6">Please sign-up to create your account and start learning!</p>
          @foreach(['success', 'error', 'warning', 'info'] as $type)
          @if(session($type))
          <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible" role="alert">
            {{ session($type) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif
          @endforeach

          @if($errors->any())
          <div class="alert alert-danger alert-dismissible" role="alert">
            @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <form id="formAuthentication" method="post" action="{{ route('auth.create-user') }}" class="mb-6">
            @csrf

            <div class="mb-6">
              <label for="first_name">First Name:</label>
              <input type="text" name="first_name" id="first_name" class="form-control" placeholder="Enter your first name" autofocus maxlength="150" required>
            </div>
            <div class="mb-6">
              <label for="last_name">Last Name:</label>
              <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Enter your last name" autofocus maxlength="150" required>
            </div>

            <div class="mb-6">
              <label for="email">Email:</label>
              <input type="text" name="email" id="email" class="form-control" placeholder="Enter your email" maxlength="150" required>
            </div>

            <div class="mb-6 form-password-toggle">
              <label for="password">Password:</label>
              <div class="input-group input-group-merge">
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••••••" aria-describedby="password" required>
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
            </div>

            <div class="mb-6 form-password-toggle">
              <label for="password_confirmation">Confirm password:</label>
              <div class="input-group input-group-merge">
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="••••••••••••" aria-describedby="password_confirmation" required>
                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
              </div>
            </div>

            <div class="mb-8">
              <div class="d-flex justify-content-between">
                <div class="form-check mb-0">
                  <input class="form-check-input" type="checkbox" id="remember-me" />
                  <label class="form-check-label" for="remember-me"> I agree to <span class="text-primary">privacy policy & terms</span> </label>
                </div>
              </div>
            </div>
            <div class="mb-6">
              <button class="btn btn-primary d-grid w-100" type="submit">Sign up</button>
            </div>
          </form>

          <p class="text-center">
            <span>Already have an account?</span>
            <a href="{{ route('auth.sign-in') }}">
              <span>Sign in instead</span>
            </a>
          </p>

          {{-- <div class="divider my-6">
            <div class="divider-text">or</div>
          </div> --}}

          {{-- <div class="d-flex justify-content-center">
            <a href="javascript:;" class="btn btn-sm btn-icon rounded-circle btn-text-facebook me-1_5">
              <i class="icon-base bx bxl-facebook-circle icon-20px"></i>
            </a>

            <a href="javascript:;" class="btn btn-sm btn-icon rounded-circle btn-text-twitter me-1_5">
              <i class="icon-base bx bxl-twitter icon-20px"></i>
            </a>

            <a href="javascript:;" class="btn btn-sm btn-icon rounded-circle btn-text-github me-1_5">
              <i class="icon-base bx bxl-github icon-20px"></i>
            </a>

            <a href="javascript:;" class="btn btn-sm btn-icon rounded-circle btn-text-google-plus">
              <i class="icon-base bx bxl-google icon-20px"></i>
            </a>
          </div> --}}
        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>

  <!-- / Content -->

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/theme.js  -->


  <script src="{{ asset('themes/sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>

  <script src="{{ asset('themes/sneat/assets/vendor/libs/popper/popper.js') }}"></script>
  <script src="{{ asset('themes/sneat/assets/vendor/js/bootstrap.js') }}"></script>


  <script src="{{ asset('themes/sneat/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>



  <script src="{{ asset('themes/sneat/assets/vendor/js/menu.js') }}"></script>

  <!-- endbuild -->

  <!-- Vendors JS -->

  <!-- Main JS -->

  <script src="{{ asset('themes/sneat/assets/js/main.js') }}"></script>


  <!-- Page JS -->
  {{-- <script src="{{ asset('themes/sneat/assets/js/pages-auth.js') }}"></script> --}}
</body>

</html>

<!-- beautify ignore:end -->