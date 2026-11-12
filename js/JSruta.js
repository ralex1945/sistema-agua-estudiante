function validarCampo(campo) {
    const vacio = campo.value.trim() === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const nombre = document.getElementById("nombre");
const descripcion = document.getElementById("descripcion");

const campos = [nombre, descripcion];

if (formulario) {
    ////////////////////VALIDACION DE CAMPOS VACIOS
    campos.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("blur", function () {
                validarCampo(campo);
            });
        }
    });

    ////////////////////PASAR A MAYUSCULAS (nombre)
    if (nombre) {
        nombre.addEventListener("input", function () {
            nombre.value = nombre.value.toUpperCase();
            validarCampo(nombre);
        });
    }

    ////////////////////BOTON GUARDAR / MODIFICAR
    btnGuardar.addEventListener("click", async function () {
        const nombreValido = validarCampo(nombre);
        const descripcionValido = validarCampo(descripcion);

        if (btnGuardar.textContent.trim() === "Guardar") {
            if (nombreValido && descripcionValido) {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "guardar");

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Procesando...";

                    const respuesta = await fetch("DAORuta.php", {
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
                        });

                        limpiarCampos();
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
            }
        } else if (btnGuardar.textContent.trim() === "Modificar") {
            if (nombreValido && descripcionValido) {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "modificar");
                    datos.append("idruta", btnGuardar.dataset.id);

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Procesando...";

                    const respuesta = await fetch("DAORuta.php", {
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
                            window.location.href = "ruta.php";
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
                        text: "Ocurrió un error al modificar.",
                        showConfirmButton: false,
                        timer: 1500
                    });
                } finally {
                    btnGuardar.disabled = false;
                    btnGuardar.textContent = "Modificar";
                }
            }
        }
    });
}

///////////////////LIMPIAR TODOS LOS CAMPOS///////////
function limpiarCampos() {
    campos.forEach(function (campo) {
        if (campo) {
            campo.value = "";
            campo.classList.remove("is-invalid");
            campo.classList.remove("is-valid");
        }
    });
}

//////////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    const parametros = new URLSearchParams(window.location.search);
    const idruta = parametros.get("id");

    if (idruta) {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarIndividual");
            datos.append("idruta", idruta);

            Swal.fire({
                title: "Consultando...",
                text: "Espere",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const respuesta = await fetch("DAORuta.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) {
                console.error(respuesta.status);
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();
            Swal.close();

            if (resultado.estado) {
                nombre.value = resultado.datos.nombre;
                descripcion.value = resultado.datos.descripcion;

                btnGuardar.textContent = "Modificar";
                btnGuardar.dataset.id = resultado.datos.idruta;
            }
        } catch (error) {
            console.error("Error al consultar:", error);
            Swal.close();
        }
    }
});
