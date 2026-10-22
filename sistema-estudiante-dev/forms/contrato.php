<!DOCTYPE html>
<html lang="es">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sistema de Agua - POSEIDÓN - Contratos</title>

  <!--begin::Theme Init-->
  <script src="../js/color-thema.js"></script>
  <!--end::Theme Init-->

  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <link rel="preload" href="../css/adminlte.css" as="style" />

  <!--begin::Fonts-->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.3.0/index.css" crossorigin="anonymous" />
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
          <form id="formulario" name="formulario" method="POST" action="#">
            <div class="row g-4">

              <!-- Columna Izquierda: Datos del Contrato -->
              <div class="col-md-6">
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header">
                    <div class="card-title">Datos del Contrato</div>
                  </div>
                  <div class="card-body">
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="numero_contrato" name="numero_contrato" placeholder="" />
                      <label for="numero_contrato">N° de Contrato (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <select class="form-select" id="idsocio" name="idsocio" aria-label="Socio">
                        <option value="" selected>[SELECCIONE UN SOCIO]</option>
                      </select>
                      <label for="idsocio">Socio (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <select class="form-select" id="idruta" name="idruta" aria-label="Ruta / Sector">
                        <option value="" selected>[SELECCIONE UNA RUTA]</option>
                      </select>
                      <label for="idruta">Ruta / Sector (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="date" class="form-control" id="fecha_contrato" name="fecha_contrato" />
                      <label for="fecha_contrato">Fecha del Contrato (*)</label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Columna Derecha: Servicio y Medidor -->
              <div class="col-md-6">
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header">
                    <div class="card-title">Servicio y Medidor</div>
                  </div>
                  <div class="card-body">
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="medidor" name="medidor" placeholder="" />
                      <label for="medidor">N° de Medidor / Contador (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <select class="form-select" id="tipo_servicio" name="tipo_servicio" aria-label="Tipo de Servicio">
                        <option value="" selected>[SELECCIONE SERVICIO]</option>
                        <option value="RESIDENCIAL">RESIDENCIAL</option>
                        <option value="COMERCIAL">COMERCIAL</option>
                        <option value="INSTITUCIONAL">INSTITUCIONAL</option>
                        <option value="INDUSTRIAL">INDUSTRIAL</option>
                      </select>
                      <label for="tipo_servicio">Tipo de Servicio (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <textarea class="form-control" id="direccion_servicio" name="direccion_servicio" placeholder="Dirección del servicio" style="height: 100px"></textarea>
                      <label for="direccion_servicio">Dirección del Servicio (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <select class="form-select" id="estado" name="estado" aria-label="Estado">
                        <option value="ACTIVO" selected>ACTIVO</option>
                        <option value="SUSPENDIDO">SUSPENDIDO</option>
                        <option value="CANCELADO">CANCELADO</option>
                      </select>
                      <label for="estado">Estado (*)</label>
                    </div>
                  </div>
                  <div class="card-footer">
                    <button type="button" class="btn btn-success" id="btnGuardar">
                      Guardar
                    </button>
                    <button type="button" class="btn btn-primary float-end" id="btnCancelar">
                      Cancelar
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </form>
        </div>
      </div>
    </main>
    <!--begin::Footer-->
    <?php
    include "../pie.php";
    ?>
    <!--end::Footer-->
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="../js/adminlte.js"></script>
  <script src="../js/para-el-menu.js"></script>
  <script src="../js/JScontrato.js?v=<?php echo filemtime('../js/JScontrato.js'); ?>"></script>
</body>

</html>
