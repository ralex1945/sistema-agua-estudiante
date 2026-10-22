function validarCampo(campo) {
    if (!campo) return true;
    const vacio = campo.value.trim() === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);
    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");

const numero_contrato = document.getElementById("numero_contrato");
const idsocio = document.getElementById("idsocio");
const idruta = document.getElementById("idruta");
const fecha_contrato = document.getElementById("fecha_contrato");
const medidor = document.getElementById("medidor");
const tipo_servicio = document.getElementById("tipo_servicio");
const direccion_servicio = document.getElementById("direccion_servicio");
const estado = document.getElementById("estado");

const camposObligatorios = [
    numero_contrato,
    idsocio,
    idruta,
    fecha_contrato,
    medidor,
    tipo_servicio,
    direccion_servicio,
    estado
];

const todosLosCampos = [
    numero_contrato,
    idsocio,
    idruta,
    fecha_contrato,
    medidor,
    tipo_servicio,
    direccion_servicio,
    estado
];

async function cargarSelects() {
    try {
        // Cargar Socios
        const fdSocios = new FormData();
        fdSocios.append("opcion", "listarSocios");
        const rSocios = await fetch("DAOContrato.php", { method: "POST", body: fdSocios });
        if (rSocios.ok) {
            const listSocios = await rSocios.json();
            idsocio.innerHTML = '<option value="" selected>[SELECCIONE UN SOCIO]</option>';
            listSocios.forEach(s => {
                const opt = document.createElement("option");
                opt.value = s.idsocio;
                opt.textContent = s.socioNombre;
                idsocio.appendChild(opt);
            });
        }

        // Cargar Rutas
        const fdRutas = new FormData();
        fdRutas.append("opcion", "listarRutas");
        const rRutas = await fetch("DAOContrato.php", { method: "POST", body: fdRutas });
        if (rRutas.ok) {
            const listRutas = await rRutas.json();
            idruta.innerHTML = '<option value="" selected>[SELECCIONE UNA RUTA]</option>';
            listRutas.forEach(r => {
                const opt = document.createElement("option");
                opt.value = r.idruta;
                opt.textContent = r.nombre;
                idruta.appendChild(opt);
            });
        }
    } catch (e) {
        console.error("Error al cargar desplegables:", e);
    }
}

if (formulario) {
    if (fecha_contrato && !fecha_contrato.value) {
        fecha_contrato.value = new Date().toISOString().split("T")[0];
    }

    camposObligatorios.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("blur", () => validarCampo(campo));
            campo.addEventListener("change", () => validarCampo(campo));
        }
    });

    if (btnGuardar) {
        btnGuardar.addEventListener("click", async function () {
            let valido = true;
            camposObligatorios.forEach(function (campo) {
                if (campo && !validarCampo(campo)) {
                    valido = false;
                }
            });

            if (!valido) {
                Swal.fire({
                    icon: "error",
                    title: "Campos incompletos",
                    text: "Por favor complete todos los campos obligatorios (*)"
                });
                return;
            }

            const modoModificar = btnGuardar.textContent.trim() === "Modificar";
            const opcion = modoModificar ? "modificar" : "guardar";

            try {
                const datos = new FormData(formulario);
                datos.append("opcion", opcion);
                if (modoModificar && btnGuardar.dataset.id) {
                    datos.append("idcontrato", btnGuardar.dataset.id);
                }

                btnGuardar.disabled = true;
                btnGuardar.textContent = "Procesando...";

                const respuesta = await fetch("DAOContrato.php", {
                    method: "POST",
                    body: datos
                });

                if (!respuesta.ok) {
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();

                if (resultado.estado) {
                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Poseidón",
                        text: resultado.mensaje,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        if (modoModificar) {
                            window.location.href = "contrato.php";
                        } else {
                            limpiarCampos();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: resultado.mensaje
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error de servidor",
                    text: error.message
                });
            } finally {
                btnGuardar.disabled = false;
                btnGuardar.textContent = modoModificar ? "Modificar" : "Guardar";
            }
        });
    }

    if (btnCancelar) {
        btnCancelar.addEventListener("click", function () {
            limpiarCampos();
            if (btnGuardar) {
                btnGuardar.textContent = "Guardar";
                delete btnGuardar.dataset.id;
            }
            Swal.fire({
                position: "top-end",
                icon: "info",
                title: "Formulario limpiado",
                showConfirmButton: false,
                timer: 1000
            });
        });
    }
}

function limpiarCampos() {
    todosLosCampos.forEach(function (campo) {
        if (campo) {
            campo.value = "";
            campo.classList.remove("is-invalid");
            campo.classList.remove("is-valid");
        }
    });
    if (fecha_contrato) {
        fecha_contrato.value = new Date().toISOString().split("T")[0];
    }
    if (estado) {
        estado.value = "ACTIVO";
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    await cargarSelects();

    const parametros = new URLSearchParams(window.location.search);
    const idcontrato = parametros.get("id");

    if (idcontrato) {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarIndividual");
            datos.append("idcontrato", idcontrato);

            const respuesta = await fetch("DAOContrato.php", {
                method: "POST",
                body: datos
            });

            if (respuesta.ok) {
                const resultado = await respuesta.json();
                if (resultado.estado && resultado.datos) {
                    const d = resultado.datos;
                    if (numero_contrato) numero_contrato.value = d.numero_contrato || "";
                    if (idsocio) idsocio.value = d.idsocio || "";
                    if (idruta) idruta.value = d.idruta || "";
                    if (fecha_contrato) fecha_contrato.value = d.fecha_contrato || "";
                    if (medidor) medidor.value = d.medidor || "";
                    if (tipo_servicio) tipo_servicio.value = d.tipo_servicio || "";
                    if (direccion_servicio) direccion_servicio.value = d.direccion_servicio || "";
                    if (estado) estado.value = d.estado || "ACTIVO";

                    if (btnGuardar) {
                        btnGuardar.textContent = "Modificar";
                        btnGuardar.dataset.id = d.idcontrato;
                    }
                }
            }
        } catch (e) {
            console.error(e);
        }
    }
});
