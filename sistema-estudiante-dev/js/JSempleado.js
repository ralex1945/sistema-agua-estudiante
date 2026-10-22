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

// Campos del formulario
const dui = document.getElementById("dui");
const nombre = document.getElementById("nombre");
const apellido = document.getElementById("apellido");
const telefono = document.getElementById("telefono");
const isss = document.getElementById("isss");
const afp = document.getElementById("afp");
const nup = document.getElementById("nup");
const renta = document.getElementById("renta");
const sueldo = document.getElementById("sueldo");
const cargo = document.getElementById("cargo");
const canton = document.getElementById("canton");
const estado = document.getElementById("estado");
const fecha_ingreso = document.getElementById("fecha_ingreso");

// Campos obligatorios con (*)
const camposObligatorios = [
    dui,
    nombre,
    apellido,
    telefono,
    renta,
    sueldo,
    cargo,
    canton,
    estado,
    fecha_ingreso
];

// Todos los campos para resetear
const todosLosCampos = [
    dui,
    nombre,
    apellido,
    telefono,
    isss,
    afp,
    nup,
    renta,
    sueldo,
    cargo,
    canton,
    estado,
    fecha_ingreso
];

if (formulario) {
    // Validación al salir del campo
    camposObligatorios.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("blur", function () {
                validarCampo(campo);
            });
            campo.addEventListener("change", function () {
                validarCampo(campo);
            });
        }
    });

    // Pasar nombres a mayúsculas
    [nombre, apellido].forEach(function (campo) {
        if (campo) {
            campo.addEventListener("input", function () {
                campo.value = campo.value.toUpperCase();
                validarCampo(campo);
            });
        }
    });

    // Formato de DUI (00000000-0)
    if (dui) {
        dui.addEventListener("input", function () {
            let valor = dui.value.replace(/\D/g, "");
            valor = valor.substring(0, 9);
            if (valor.length > 8) {
                valor = valor.substring(0, 8) + "-" + valor.substring(8);
            }
            dui.value = valor;
        });

        // Búsqueda de DUI repetido
        dui.addEventListener("keyup", async function () {
            if (dui.value.length === 10) {
                try {
                    const datos = new FormData();
                    datos.append("opcion", "consultarDUI");
                    datos.append("dui", dui.value);

                    Swal.fire({
                        title: "Espere...",
                        text: "Consultando DUI",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });

                    const respuesta = await fetch("DAOEmpleado.php", {
                        method: "POST",
                        body: datos
                    });

                    if (!respuesta.ok) {
                        throw new Error("Error HTTP: " + respuesta.status);
                    }

                    const resultado = await respuesta.json();
                    Swal.close();

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
                } catch (error) {
                    Swal.close();
                }
            }
        });
    }

    // Formato de Teléfono (0000-0000)
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
            let formularioValido = true;
            camposObligatorios.forEach(function (campo) {
                if (campo && !validarCampo(campo)) {
                    formularioValido = false;
                }
            });

            if (!formularioValido) {
                Swal.fire({
                    icon: "error",
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
                if (modoModificar && btnGuardar.dataset.id) {
                    datos.append("idempleado", btnGuardar.dataset.id);
                }

                btnGuardar.disabled = true;
                btnGuardar.textContent = "Procesando...";

                const respuesta = await fetch("DAOEmpleado.php", {
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
                            window.location.href = "empleado.php";
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

    // Botón Cancelar
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

// Limpiar todos los campos
function limpiarCampos() {
    todosLosCampos.forEach(function (campo) {
        if (campo) {
            campo.value = "";
            campo.classList.remove("is-invalid");
            campo.classList.remove("is-valid");
        }
    });
}

// Cargar datos al editar (si viene ?id=...)
document.addEventListener("DOMContentLoaded", async () => {
    const parametros = new URLSearchParams(window.location.search);
    const idempleado = parametros.get("id");

    if (idempleado) {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarIndividual");
            datos.append("idempleado", idempleado);

            Swal.fire({
                title: "Consultando...",
                text: "Espere por favor",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const respuesta = await fetch("DAOEmpleado.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) {
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();
            Swal.close();

            if (resultado.estado && resultado.datos) {
                const d = resultado.datos;
                if (dui) dui.value = d.dui || "";
                if (nombre) nombre.value = d.nombre || "";
                if (apellido) apellido.value = d.apellido || "";
                if (telefono) telefono.value = d.telefono || "";
                if (isss) isss.value = d.isss || "";
                if (afp) afp.value = d.afp || "";
                if (nup) nup.value = d.nup || "";
                if (renta) renta.value = d.renta || "";
                if (sueldo) sueldo.value = d.sueldo || "";
                if (cargo) cargo.value = d.cargo || "";
                if (canton) canton.value = d.canton || "";
                if (estado) estado.value = d.estado || "ACTIVO";
                if (fecha_ingreso) fecha_ingreso.value = d.fecha_ingreso || "";

                if (btnGuardar) {
                    btnGuardar.textContent = "Modificar";
                    btnGuardar.dataset.id = d.idempleado;
                }
            }
        } catch (error) {
            Swal.close();
            console.error(error);
        }
    }
});
