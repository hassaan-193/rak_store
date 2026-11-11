<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in | FTS</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min2167.css?v=3.2.0') }}">
  <link rel="stylesheet" href="{{ asset('dist/css/style.css') }}">
</head>

<body class="hold-transition login-page" style="background-image: url('{{ asset('dist/img/login-bg.png') }}'); background-repeat: no-repeat; background-size: cover;">
<div class="login-box">
  <div class="login-logo">
    <!-- <a href="#"><img src="{{ asset('dist/img/ftsits-logo-fts.png') }}" width="60px" alt=""></a> -->
  </div>

  <div class="card1">
    <div class="card-body login-card-body1">
      <h3 class="login-box-msg">Login Account</h3>

      <!-- Error Display -->
      @if ($errors->any())
          <div class="alert alert-danger">
              <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required autofocus>
        </div>

        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="icheck-primary">
              <input type="checkbox" name="remember" id="remember">
              <label for="remember">
                Keep me signed in
              </label>
            </div>
          </div>

          <div class="col-12 mt-2">
            <button type="submit" class="btn btn-primary btn-block form-control-lg">Login</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('dist/js/adminlte.min2167.js?v=3.2.0') }}"></script>

</body>
</html>
