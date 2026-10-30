function validarCampo(campo) {
    if (!campo) return false;
    const vacio = campo.value.trim() === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);
    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");
const nombre = document.getElementById("nombre");

const campos = [nombre];

if (formulario) {
    campos.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("blur", function () {
                validarCampo(campo);
            });
        }
    });

    if (nombre) {
        nombre.addEventListener("input", function () {
            nombre.value = nombre.value.toUpperCase();
            validarCampo(nombre);
        });
    }

    if (btnCancelar) {
        btnCancelar.addEventListener("click", function () {
            window.location.href = "lista-cargos.php";
        });
    }

    if (btnGuardar) {
        btnGuardar.addEventListener("click", async function () {
            const nombreValido = validarCampo(nombre);

            if (!nombreValido) {
                Swal.fire({
                    position: "top-end",
                    icon: "warning",
                    title: "Poseidón",
                    text: "Por favor complete el nombre del cargo",
                    showConfirmButton: false,
                    timer: 1500
                });
                return;
            }

            if (btnGuardar.textContent.trim() === "Guardar") {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "guardar");

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Procesando...";

                    const respuesta = await fetch("DAOCargo.php", {
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
                            limpiarCampos();
                        });
                    } else {
                        Swal.fire({
                            position: "top-end",
                            icon: "error",
                            title: "Poseidón",
                            text: resultado.mensaje,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                } catch (error) {
                    console.error("Error al guardar:", error);
                    Swal.fire({
                        position: "top-end",
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error en el servidor o la solicitud.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                } finally {
                    btnGuardar.disabled = false;
                    btnGuardar.textContent = "Guardar";
                }
            } else if (btnGuardar.textContent.trim() === "Modificar") {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "modificar");
                    datos.append("idcargo", btnGuardar.dataset.id);

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Modificando...";

                    const respuesta = await fetch("DAOCargo.php", {
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
                            window.location.href = "lista-cargos.php";
                        });
                    } else {
                        Swal.fire({
                            position: "top-end",
                            icon: "error",
                            title: "Poseidón",
                            text: resultado.mensaje,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                } catch (error) {
                    console.error("Error al modificar:", error);
                    Swal.fire({
                        position: "top-end",
                        icon: "error",
                        title: "Error",
                        text: "Ocurrió un error en el servidor o la solicitud.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                } finally {
                    btnGuardar.disabled = false;
                    btnGuardar.textContent = "Modificar";
                }
            }
        });
    }
}

function limpiarCampos() {
    campos.forEach(function (campo) {
        if (campo) {
            campo.value = "";
            campo.classList.remove("is-invalid");
            campo.classList.remove("is-valid");
        }
    });
}

document.addEventListener("DOMContentLoaded", async () => {
    const parametros = new URLSearchParams(window.location.search);
    const idcargo = parametros.get("id");

    if (idcargo) {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarIndividual");
            datos.append("idcargo", idcargo);

            Swal.fire({
                title: "Consultando...",
                text: "Espere un momento",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const respuesta = await fetch("DAOCargo.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) {
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();
            Swal.close();

            if (resultado.estado) {
                nombre.value = resultado.datos.nombre;
                validarCampo(nombre);

                btnGuardar.textContent = "Modificar";
                btnGuardar.dataset.id = resultado.datos.idcargo;
            } else {
                Swal.fire({
                    position: "top-end",
                    icon: "error",
                    title: "Poseidón",
                    text: "No se encontró el registro solicitado",
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = "lista-cargos.php";
                });
            }
        } catch (error) {
            Swal.close();
            console.error("Error al consultar cargo:", error);
        }
    }
});
