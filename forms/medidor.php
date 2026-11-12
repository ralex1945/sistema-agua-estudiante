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
                      <div class="card-title">Asignación de Medidor a Contrato</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-md-6 col-lg-6">
                  <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                      <div class="card-title">Información del Socio</div>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 col-md-5 col-lg-5">
                          <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="dui" name="dui" placeholder="" disabled />
                            <label for="dui">DUI (*)</label>
                          </div>
                        </div>

                        <div class="col-12 col-md-7 col-lg-7">
                          <div class="form-floating mb-3">
                            <input type="input" class="form-control" id="nombre" name="nombre" placeholder="" disabled />
                            <label for="nombre">Nombre del Socio (*)</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>


                <div class="col-12 col-md-6 col-lg-6">
                  <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                      <div class="card-title">Información del Contrato</div>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="numero" name="numero" placeholder="" disabled />
                            <label for="numero">Número Contrato (*)</label>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-floating mb-3">
                            <input type="date" class="form-control" id="fecha" name="fecha" placeholder="" disabled />
                            <label for="fecha">Fecha de Generación de Contrato (*)</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>

              <div class="row">

                <div class="col-12 col-md-6 col-lg-6">

                  <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                      <div class="card-title">Ubicación Física del Medidor</div>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-floating mb-3">
                            <select class="form-select" id="canton" name="canton">
                              <option value="0">[CANTON]</option>
                            </select>
                            <label for="canton">Selección Cantón(*)</label>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-floating mb-3">
                            <select class="form-select" id="ruta" name="ruta">
                              <option value="0">[RUTA]</option>
                            </select>
                            <label for="ruta">Selección Ruta (*)</label>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                          <div class="form-floating mb-3">
                            <textarea class="form-control" id="direccion" name="direccion" placeholder=""></textarea>
                            <label for="direccion">Dirección Exacta del Medidor(*)</label>
                          </div>
                        </div>

                        <div class="col-12 col-md-12 col-lg-12">
                          <div class="form-floating mb-3">
                            <textarea class="form-control" id="referencia" name="referencia" placeholder=""></textarea>
                            <label for="referencia">Punto de Referencia de la Ubicación del Medidor(*)</label>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>


                <div class="col-12 col-md-6 col-lg-6">
                  <div class="card card-secondary card-outline mb-4">
                    <div class="card-header">
                      <div class="card-title">Datos del Medidor</div>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="nmedidor" name="nmedidor" placeholder="" />
                            <label for="nmedidor">N° Medidor (*)</label>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="inicio" name="inicio" placeholder="" />
                            <label for="inicio">Metros Iniciales (*)</label>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-floating mb-3">
                          <select class="form-select" id="estado" name="estado">
                              <option value="0">[SELECCIONE]</option>
                              <option value="ACTIVO">Activo</option>
                              <option value="SUSPENDIDO">Suspendido</option>
                              <option value="INACTIVO">Inactivo</option>
                            </select>
                            <label for="estado">Estado de Medidor (*)</label>
                          </div>
                        </div>

                        
                      </div>
                    </div>
                    <div class="card-footer d-flex w-100">
                      <button type="button" class="btn btn-primary" id="btnGuardar">
                        Guardar
                      </button>

                      <button type="button" class="btn btn-success ms-auto" id="btnCancelar">
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
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous">
  </script>
  <!--end::Required Plugin(popperjs for Bootstrap 5)-->
  <!--begin::Required Plugin(Bootstrap 5)-->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous">
  </script>
  <!--end::Required Plugin(Bootstrap 5)-->
  <!--begin::Required Plugin(AdminLTE)-->
  <script src="../js/adminlte.js"></script>
  <!--end::Required Plugin(AdminLTE)-->
  <!--begin::OverlayScrollbars Configure-->
  <script src="../js/para-el-menu.js"></script>
  <!--end::OverlayScrollbars Configure-->
  <script src="../js/JSMedidor.js?v=<?php echo filemtime('../js/JSMedidor.js'); ?>"></script>
</body>

</html>
