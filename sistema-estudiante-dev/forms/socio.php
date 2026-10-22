<!DOCTYPE html>
<html lang="es">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sistema de Agua - POSEIDÓN - Socios</title>

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

              <!-- Columna Izquierda: Datos del Socio -->
              <div class="col-md-6">
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header">
                    <div class="card-title">Datos del Socio</div>
                  </div>
                  <div class="card-body">
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="dui" name="dui" placeholder="" maxlength="10" />
                      <label for="dui">DUI(*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="nombre" name="nombre" placeholder="" />
                      <label for="nombre">Nombre(*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="apellido" name="apellido" placeholder="" />
                      <label for="apellido">Apellido(*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="text" class="form-control" id="telefono" name="telefono" placeholder="" />
                      <label for="telefono">Teléfono(*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="email" class="form-control" id="correo" name="correo" placeholder="" />
                      <label for="correo">Correo Electrónico</label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Columna Derecha: Ubicación y Estado -->
              <div class="col-md-6">
                <div class="card card-secondary card-outline mb-4">
                  <div class="card-header">
                    <div class="card-title">Ubicación y Estado</div>
                  </div>
                  <div class="card-body">
                    <div class="form-floating mb-3">
                      <select class="form-select" id="canton" name="canton" aria-label="Cantón">
                        <option value="" selected>[SELECCIONE UN CANTON]</option>
                        <option value="CANTÓN EL CENTRO">CANTÓN EL CENTRO</option>
                        <option value="CANTÓN SAN JOSÉ">CANTÓN SAN JOSÉ</option>
                        <option value="CANTÓN EL COCO">CANTÓN EL COCO</option>
                        <option value="CANTÓN LA ESPERANZA">CANTÓN LA ESPERANZA</option>
                        <option value="CANTÓN SAN JUAN">CANTÓN SAN JUAN</option>
                      </select>
                      <label for="canton">Cantón (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <textarea class="form-control" id="direccion" name="direccion" placeholder="Dirección exacta" style="height: 100px"></textarea>
                      <label for="direccion">Dirección Exacta (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <input type="date" class="form-control" id="fecha_registro" name="fecha_registro" />
                      <label for="fecha_registro">Fecha de Registro (*)</label>
                    </div>
                    <div class="form-floating mb-3">
                      <select class="form-select" id="estado" name="estado" aria-label="Estado">
                        <option value="ACTIVO" selected>ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
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
  <script src="../js/JSsocio.js?v=<?php echo filemtime('../js/JSsocio.js'); ?>"></script>
</body>

</html>
