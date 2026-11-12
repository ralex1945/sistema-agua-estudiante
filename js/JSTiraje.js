function validarCampo(campo) {
    const vacio = campo.value.trim() === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");
const prefijo = document.getElementById("prefijo");
const ultimo = document.getElementById("ultimo");
const estado = document.getElementById("estado");

const campos = [prefijo, ultimo, estado];

function limpiarCampos() {
    campos.forEach(function (campo) {
        campo.value = "";
        campo.classList.remove("is-invalid");
        campo.classList.remove("is-valid");
    });
}

async function cargarPrefijos() {
    try {
        const datos = new FormData();
        datos.append("opcion", "consultar");

        Swal.fire({
            title: "Consultando...",
            text: "Espere",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        const respuesta = await fetch("DAOTiraje.php", {
            method: "POST",
            body: datos
        });

        const resultado = await respuesta.json();

        // Cerrar el mensaje de espera
        Swal.close();

        const select = document.getElementById("prefijo");

        // Limpiar el select
        select.innerHTML = '<option value="">[Seleccione]</option>';

        let estado = 0;
        // Recorrer el arreglo
        resultado.forEach(prefijo => {
            const opcion = document.createElement("option");
            opcion.value = prefijo.prefijo;
            opcion.textContent = prefijo.prefijo;

            select.appendChild(opcion);
            estado = 1;
        });

    } catch (error) {
        Swal.close();
    }
}

if (formulario) {
    /////////////////VALIDACION DE CAMPOS VACIOS
    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
    });

    /////////////////BOTON CANCELAR/////
    btnCancelar.addEventListener("click", function () {
        limpiarCampos();
    });

    prefijo.addEventListener("change", async function () {
        if (!prefijo.value) {
            ultimo.value = "";
            estado.value = "";
            return;
        }

        try {
            const datos = new FormData();
            datos.append("opcion", "consultarPrefijo");
            datos.append("prefijo", prefijo.value);

            Swal.fire({
                title: "Consultando...",
                text: "Espere",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const respuesta = await fetch("DAOTiraje.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) {
                console.error(respuesta.status);
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();

            // Cerrar el mensaje de espera
            Swal.close();

            if (resultado.estado) {
                ultimo.value = resultado.datos.ultimo;
                estado.value = resultado.datos.estado;
            } else {
                ultimo.value = resultado.datos.ultimo ?? 0;
                estado.value = resultado.datos.estado ?? "No Existe";
            }
        } catch (error) {
            Swal.close();
        }
    });

    btnGuardar.addEventListener("click", async function () {
        const prefijoValido = validarCampo(prefijo);
        const ultimoValido = validarCampo(ultimo);
        const estadoValido = validarCampo(estado);

        if (btnGuardar.textContent.trim() == "Nuevo Tiraje de números de factura") {
            if (prefijoValido && ultimoValido && estadoValido) {
                Swal.fire({
                    title: "Poseidon",
                    text: "Al Guardar el numero PERIODO se iniciará en contador a 0.\nESTA ACCIÓN NO SE PUEDE REVERTIR",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Si, Confirmo"
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const datos = new FormData();
                            datos.append("opcion", "guardar");

                            const respuesta = await fetch("DAOTiraje.php", {
                                method: "POST",
                                body: datos
                            });

                            //verificar respuesta
                            if (!respuesta.ok) {
                                throw new Error("Error de Comunicacion" + respuesta.status);
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
                                await cargarPrefijos();
                            } else {
                                Swal.fire({
                                    position: "top-end",
                                    icon: "warning",
                                    title: "Poseidón",
                                    text: resultado.mensaje,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            }
                        } catch (error) {
                            console.error(error);
                        }
                    }
                });
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", async () => {
    await cargarPrefijos();
});

