<!doctype html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sistema de Agua - POSEIDÓN</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/styles/overlayscrollbars.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<!--end::Head-->
<!--begin::Body-->

<body class="login-page bg-body-secondary">
  <main class="login-box">
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <h1 class="mb-0"><b>Poseidón</b></h1>
      </div>
      <div class="card-body login-card-body">
        <p class="login-box-msg">Ingresa tus credenciales para iniciar sesión</p>

        <form action="#" method="post" id="formulario" autocomplete="off">
          <div class="input-group mb-1">
            <div class="form-floating">
              <input id="loginUsuario" name="loginUsuario" type="text" class="form-control" value="" placeholder="" />
              <label for="loginUsuario">Usuario</label>
            </div>
            <div class="input-group-text">
              <span class="bi bi-envelope"></span>
            </div>
          </div>
          <div class="input-group mb-1">
            <div class="form-floating">
              <input id="loginClave" name="loginClave" type="password" class="form-control" placeholder="" />
              <label for="loginClave">Contraseña</label>
            </div>
            <div class="input-group-text">
              <span class="bi bi-lock-fill"></span>
            </div>
          </div>
          <!--begin::Row-->
          <div class="row">

            <!-- /.col -->
            <div class="col-4">
              <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary" id="btnEntrar">Entrar</button>
              </div>
            </div>
            <!-- /.col -->
          </div>
          <!--end::Row-->
        </form>
      </div>
      <!-- /.login-card-body -->

    </div>
  </main>
  <!-- /.login-box -->

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script
    src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js"
    crossorigin="anonymous"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)--><!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script
    src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)--><!--begin::Required Plugin(Bootstrap 5)-->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
    crossorigin="anonymous"></script>
  <!--end::Required Plugin(Bootstrap 5)--><!--begin::Required Plugin(AdminLTE)-->
  <script src="js/adminlte.js"></script>
  <!--end::Required Plugin(AdminLTE)-->
  <!--begin::OverlayScrollbars Configure-->
  <script src="js/para-el-menu.js"></script>
  <script src="js/JSLogin.js?v=<?php echo filemtime('js/JSLogin.js'); ?>"></script>
</body>
<!--end::Body-->

</html>
