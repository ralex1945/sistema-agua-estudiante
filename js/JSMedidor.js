function validarCampo(campo) {
    if (!campo) return true;
    const valor = campo.value.trim();
    const vacio = valor === "" || valor === "0" || valor === "[SELECCIONE]" || valor === "[CANTON]" || valor === "[RUTA]";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

document.addEventListener("DOMContentLoaded", async function () {
    const formulario = document.getElementById("formulario");
    const btnGuardar = document.getElementById("btnGuardar");
    const btnCancelar = document.getElementById("btnCancelar");

    const dui = document.getElementById("dui");
    const nombre = document.getElementById("nombre");
    const numero = document.getElementById("numero");
    const fecha = document.getElementById("fecha");

    const canton = document.getElementById("canton");
    const ruta = document.getElementById("ruta");
    const direccion = document.getElementById("direccion");
    const referencia = document.getElementById("referencia");
    const nmedidor = document.getElementById("nmedidor");
    const inicio = document.getElementById("inicio");
    const estado = document.getElementById("estado");

    const camposObligatorios = [canton, ruta, direccion, referencia, nmedidor, inicio, estado];

    // Validación interactiva al perder foco o cambiar
    camposObligatorios.forEach(function (campo) {
        if (!campo) return;
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
        campo.addEventListener("change", function () {
            validarCampo(campo);
        });
    });

    // Pasar a mayúsculas los campos de texto
    const camposTexto = [direccion, referencia, nmedidor];
    camposTexto.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("input", function () {
                campo.value = campo.value.toUpperCase();
            });
        }
    });

    // Cargar opciones del select Cantón
    async function cargarCantones() {
        try {
            const respuesta = await fetch("DAOCanton.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "opcion=consultar"
            });
            if (respuesta.ok) {
                const cantones = await respuesta.json();
                canton.innerHTML = '<option value="0">[CANTON]</option>';
                if (Array.isArray(cantones)) {
                    cantones.forEach(c => {
                        const opt = document.createElement("option");
                        opt.value = c.idcanton;
                        opt.textContent = c.nombre;
                        canton.appendChild(opt);
                    });
                }
            }
        } catch (e) {
            console.error("Error al cargar cantones:", e);
        }
    }

    // Cargar opciones del select Ruta
    async function cargarRutas() {
        try {
            const respuesta = await fetch("DAORuta.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "opcion=consultar"
            });
            if (respuesta.ok) {
                const rutas = await respuesta.json();
                ruta.innerHTML = '<option value="0">[RUTA]</option>';
                if (Array.isArray(rutas)) {
                    rutas.forEach(r => {
                        const opt = document.createElement("option");
                        opt.value = r.idruta;
                        opt.textContent = r.nombre;
                        ruta.appendChild(opt);
                    });
                }
            }
        } catch (e) {
            console.error("Error al cargar rutas:", e);
        }
    }

    await cargarCantones();
    await cargarRutas();

    // Obtener idcontrato desde la URL (?id=...)
    const params = new URLSearchParams(window.location.search);
    const idContrato = params.get("id");

    if (idContrato) {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarMedidor");
            datos.append("idcontrato", idContrato);

            const respuesta = await fetch("DAOMedidor.php", {
                method: "POST",
                body: datos
            });

            if (respuesta.ok) {
                const resultado = await respuesta.json();
                if (resultado.estado && resultado.datos) {
                    dui.value = resultado.datos.dui || "";
                    nombre.value = resultado.datos.nombreCompleto || "";
                    numero.value = resultado.datos.ncontrato || "";
                    fecha.value = resultado.datos.fecha || "";
                    canton.value = resultado.datos.idcanton || "0";
                    ruta.value = resultado.datos.idruta || "0";
                    direccion.value = resultado.datos.direccion || "";
                    referencia.value = resultado.datos.referencia || "";
                    nmedidor.value = resultado.datos.nmedidor || "";
                    inicio.value = resultado.datos.lectura_inicial !== null ? resultado.datos.lectura_inicial : "";
                    estado.value = resultado.datos.estado || "0";

                    if (resultado.datos.nmedidor == null || resultado.datos.nmedidor === "") {
                        btnGuardar.textContent = "Guardar";
                        btnGuardar.dataset.id = idContrato;
                        btnGuardar.disabled = false;
                    } else {
                        btnGuardar.textContent = "Modificar";
                        btnGuardar.dataset.id = resultado.datos.idmedidor;
                        btnGuardar.disabled = false;
                    }
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Poseidón",
                        text: resultado.mensaje || "No se encontró el contrato especificado."
                    });
                }
            }
        } catch (e) {
            console.error("Error al consultar contrato/medidor:", e);
        }
    } else {
        Swal.fire({
            icon: "info",
            title: "Poseidón",
            text: "Seleccione un contrato desde la lista para asignarle medidor."
        });
    }

    // Botón Guardar / Modificar
    if (btnGuardar) {
        btnGuardar.addEventListener("click", async function () {
            let valido = true;
            camposObligatorios.forEach(campo => {
                if (!validarCampo(campo)) {
                    valido = false;
                }
            });

            if (!valido) {
                Swal.fire({
                    icon: "warning",
                    title: "Campos incompletos",
                    text: "Por favor complete todos los campos obligatorios marcados con (*)."
                });
                return;
            }

            const modoModificar = btnGuardar.textContent.trim() === "Modificar";
            const opcion = modoModificar ? "modificar" : "guardar";

            try {
                const datos = new FormData(formulario);
                datos.append("opcion", opcion);
                if (modoModificar) {
                    datos.append("idmedidor", btnGuardar.dataset.id);
                } else {
                    datos.append("idcontrato", btnGuardar.dataset.id || idContrato);
                }

                Swal.fire({
                    title: "Guardando...",
                    text: "Espere un momento",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                const respuesta = await fetch("DAOMedidor.php", {
                    method: "POST",
                    body: datos
                });

                Swal.close();

                if (!respuesta.ok) {
                    throw new Error("Error en servidor: " + respuesta.status);
                }

                const resultado = await respuesta.json();

                if (resultado.estado) {
                    Swal.fire({
                        icon: "success",
                        title: "Poseidón",
                        text: resultado.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = "lista-contrato.php";
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Poseidón",
                        text: resultado.mensaje
                    });
                }
            } catch (err) {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Error de comunicación",
                    text: err.message
                });
            }
        });
    }

    //////////////////////////////////BOTON CANCELAR/////

    btnCancelar.addEventListener("click", function () {
        window.location.href = "lista-contrato.php";
    });
});

