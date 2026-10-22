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

const dui = document.getElementById("dui");
const nombre = document.getElementById("nombre");
const apellido = document.getElementById("apellido");
const telefono = document.getElementById("telefono");
const correo = document.getElementById("correo");
const canton = document.getElementById("canton");
const direccion = document.getElementById("direccion");
const fecha_registro = document.getElementById("fecha_registro");
const estado = document.getElementById("estado");

const camposObligatorios = [
    dui,
    nombre,
    apellido,
    telefono,
    canton,
    direccion,
    fecha_registro,
    estado
];

const todosLosCampos = [
    dui,
    nombre,
    apellido,
    telefono,
    correo,
    canton,
    direccion,
    fecha_registro,
    estado
];

if (formulario) {
    // Fecha por defecto hoy
    if (fecha_registro && !fecha_registro.value) {
        fecha_registro.value = new Date().toISOString().split("T")[0];
    }

    camposObligatorios.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("blur", () => validarCampo(campo));
            campo.addEventListener("change", () => validarCampo(campo));
        }
    });

    [nombre, apellido].forEach(function (campo) {
        if (campo) {
            campo.addEventListener("input", function () {
                campo.value = campo.value.toUpperCase();
                validarCampo(campo);
            });
        }
    });

    if (dui) {
        dui.addEventListener("input", function () {
            let valor = dui.value.replace(/\D/g, "");
            valor = valor.substring(0, 9);
            if (valor.length > 8) {
                valor = valor.substring(0, 8) + "-" + valor.substring(8);
            }
            dui.value = valor;
        });

        dui.addEventListener("keyup", async function () {
            if (dui.value.length === 10) {
                try {
                    const datos = new FormData();
                    datos.append("opcion", "consultarDUI");
                    datos.append("dui", dui.value);

                    const respuesta = await fetch("DAOSocio.php", {
                        method: "POST",
                        body: datos
                    });

                    if (respuesta.ok) {
                        const resultado = await respuesta.json();
                        if (resultado.estado) {
                            Swal.fire({
                                position: "top-end",
                                icon: "warning",
                                title: "Poseidón",
                                text: resultado.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            dui.classList.toggle("is-invalid", true);
                        }
                    }
                } catch (error) {
                    console.error(error);
                }
            }
        });
    }

    if (telefono) {
        telefono.addEventListener("input", function () {
            let valor = telefono.value.replace(/\D/g, "");
            valor = valor.substring(0, 8);
            if (valor.length > 4) {
                valor = valor.substring(0, 4) + "-" + valor.substring(4);
            }
            telefono.value = valor;
        });
    }

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
                    datos.append("idsocio", btnGuardar.dataset.id);
                }

                btnGuardar.disabled = true;
                btnGuardar.textContent = "Procesando...";

                const respuesta = await fetch("DAOSocio.php", {
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
                            window.location.href = "socio.php";
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
    if (fecha_registro) {
        fecha_registro.value = new Date().toISOString().split("T")[0];
    }
    if (estado) {
        estado.value = "ACTIVO";
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    const parametros = new URLSearchParams(window.location.search);
    const idsocio = parametros.get("id");

    if (idsocio) {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarIndividual");
            datos.append("idsocio", idsocio);

            const respuesta = await fetch("DAOSocio.php", {
                method: "POST",
                body: datos
            });

            if (respuesta.ok) {
                const resultado = await respuesta.json();
                if (resultado.estado && resultado.datos) {
                    const d = resultado.datos;
                    if (dui) dui.value = d.dui || "";
                    if (nombre) nombre.value = d.nombre || "";
                    if (apellido) apellido.value = d.apellido || "";
                    if (telefono) telefono.value = d.telefono || "";
                    if (correo) correo.value = d.correo || "";
                    if (canton) canton.value = d.canton || "";
                    if (direccion) direccion.value = d.direccion || "";
                    if (fecha_registro) fecha_registro.value = d.fecha_registro || "";
                    if (estado) estado.value = d.estado || "ACTIVO";

                    if (btnGuardar) {
                        btnGuardar.textContent = "Modificar";
                        btnGuardar.dataset.id = d.idsocio;
                    }
                }
            }
        } catch (e) {
            console.error(e);
        }
    }
});
