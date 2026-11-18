<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sistema de Agua - POSEIDÓN</title>

  <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
  <script src="../js/color-thema.js"></script>
  <!--end::Theme Init-->

  <!--begin::Accessibility Meta Tags-->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
  <!--end::Accessibility Meta Tags-->

  <!--begin::Primary Meta Tags-->
  <meta name="title" content="Sistema de Agua - POSEIDÓN" />
  <meta name="description" content="Sistema Administrativo y Financiero de Agua" />
  <meta name="keywords" content="bootstrap 5, poseidon, agua potable, Sistema Administrativo y Financiero de Agua" />
  <!--end::Primary Meta Tags-->

  <!--begin::Accessibility Features-->
  <!-- Skip links will be dynamically added by accessibility.js -->
  <meta name="supported-color-schemes" content="light dark" />
  <link rel="preload" href="../css/adminlte.css" as="style" />
  <!--end::Accessibility Features-->

  <!--begin::Fonts-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.3.0/index.css" integrity="sha256-5UwXtC6TaPHCAbVQ59waEAqOssHyhn/twyuLRRWszzo=" crossorigin="anonymous" media="print" onload="this.media = 'all'" />
  <!--end::Fonts-->

  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/styles/overlayscrollbars.min.css" crossorigin="anonymous" />
  <!--end::Third Party Plugin(OverlayScrollbars)-->

  <!--begin::Third Party Plugin(Bootstrap Icons)-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous" />
  <!--end::Third Party Plugin(Bootstrap Icons)-->

  <!--begin::Required Plugin(AdminLTE)-->
  <link rel="stylesheet" href="../css/adminlte.css" />
  <!--end::Required Plugin(AdminLTE)-->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">
    <!--begin::Header-->
    <?php
    include "../menu-cabecera.php";
    include "../menu-lateral.php";
    ?>
    <!--end::Sidebar-->
    <main class="app-main">
      <div class="app-content-header">
        <div class="container-fluid">
          <div class="row">
          </div>
        </div>
      </div>
      <div class="app-content">
        <div class="container-fluid">
          <div class="row">

            <form id="formulario" name="formulario" method="POST" action="#" autocomplete="off">
              <div class="row">
                <div class="col-12 col-md-12 col-lg-12">
                  <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                      <div class="card-title">Asignación de Rol a Empleado</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                  <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                      <div class="card-title">Información del Empleado</div>
                    </div>
                    <div class="col-12 col-md-12 col-lg-12">
                      <div class="card-body">
                        <div class="form-floating mb-3">
                          <input type="text" class="form-control" id="dui" name="dui" placeholder="" maxlength="10" readonly />
                          <label for="dui">DUI(*)</label>
                        </div>

                        <div class="form-floating mb-3">
                          <input type="text" class="form-control" id="nombre" name="nombre" placeholder="" disabled />
                          <label for="nombre">Nombre(*)</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-md-6 col-lg-6">
                  <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                      <div class="card-title">Permiso...</div>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="admin" name="roles[]" value="admin">
                            <label class="form-check-label" for="admin">
                              Administrador
                            </label>
                          </div>

                          <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="caja" name="roles[]" value="caja">
                            <label class="form-check-label" for="caja">
                              Caja
                            </label>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="lector" name="roles[]" value="lector">
                            <label class="form-check-label" for="lector">
                              Lector
                            </label>
                          </div>

                          <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="cobro" name="roles[]" value="cobro">
                            <label class="form-check-label" for="cobro">
                              Cobro
                            </label>
                          </div>
                        </div>
                      </div>

                      <p class="text-muted small mb-0">
                        Por defecto el Usuario y la Contraseña será su numero de DUI con guiones
                      </p>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                      <button type="button" id="btnGuardar" name="btnGuardar" class="btn btn-primary">
                        Guardar
                      </button>
                      <button type="button" id="btnCancelar" name="btnCancelar" class="btn btn-success">
                        Cancelar
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </main>
    <!--begin::Footer-->
    <?php
    include "../pie.php";
    ?>
    <!--end::Footer-->
  </div>
  <!--begin::Third Party Plugin(OverlayScrollbars)-->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <!--end::Third Party Plugin(OverlayScrollbars)-->
  <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)-->
  <!--begin::Required Plugin(Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <!--end::Required Plugin(Bootstrap 5)-->
  <!--begin::Required Plugin(AdminLTE)-->
  <script src="../js/adminlte.js"></script>
  <!--end::Required Plugin(AdminLTE)-->
  <!--begin::OverlayScrollbars Configure-->
  <script src="../js/para-el-menu.js"></script>
  <!--end::OverlayScrollbars Configure-->
  <script src="../js/JSNivel-acceso.js"></script>
</body>

</html>
