function validarCampo(campo) {
    if (!campo) return true;
    const valor = campo.value.trim();
    const vacio = valor === "" || valor === "[SELECCIONE]" || valor === "[SELECCIONE UN CARGO]" || valor === "[SELECCIONE UN CANTON]";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");

const dui = document.getElementById("dui");
const nombre = document.getElementById("nombre");
const empleado1 = document.getElementById("empleado1");
const cargo1 = document.getElementById("cargo1");
const empleado2 = document.getElementById("empleado2");
const cargo2 = document.getElementById("cargo2");
const fecha = document.getElementById("fecha");
const monto = document.getElementById("monto");
const numero = document.getElementById("numero");


const campos = [dui, nombre, empleado1, cargo1, fecha, monto];

if (formulario) {
    ////////////////////VALIDACION DE CAMPOS VACIOS

    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
    });

    ////////////////////PASAR TODO A MAYUSCULAS
    const campos1 = [cargo1, cargo2];

    campos1.forEach(function (campo) {
        if (campo) {
            campo.addEventListener("input", function () {
                campo.value = campo.value.toUpperCase();
                validarCampo(campo);
            });
        }
    });

    //////////////////////////FORMATO DE DUI//////////////

    dui.addEventListener("input", function () {
        let valor = dui.value.replace(/\D/g, "");
        valor = valor.substring(0, 9);
        if (valor.length > 8) {
            valor = valor.substring(0, 8) + "-" + valor.substring(8);
        }
        dui.value = valor;
    });


    monto.addEventListener("input", function () {
        let valor = this.value.replace(/[^0-9.]/g, "");

        const partes = valor.split(".");
        if (partes.length > 2) {
            valor = partes[0] + "." + partes.slice(1).join("");
        }

        const posicion = valor.indexOf(".");
        if (posicion !== -1) {
            valor = valor.substring(0, posicion + 3);
        }

        this.value = valor;
    });

    ////////////////////BUSQUEDA DE DUI- PARA REPERIDOS/////////
    dui.addEventListener("keyup", async function () {
        if (dui.value.length === 10) {
            try {
                const datos = new FormData();
                datos.append("opcion", "consultarDUISocio");
                datos.append("dui", dui.value);

                Swal.fire({
                    title: "Espere...",
                    text: "Consultando DUI",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                const respuesta = await fetch("DAOContrato.php", {
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
                    nombre.value = resultado.datos.nombreCompleto;
                    btnGuardar.disabled = false;
                    btnGuardar.dataset.id = resultado.datos.idsocio;
                } else {
                    nombre.value = "";
                    btnGuardar.disabled = true;
                    Swal.fire({
                        icon: "warning",
                        title: "Poseidón",
                        text: "No se encontró un socio con ese DUI",
                        showConfirmButton: false,
                        timer: 1500
                    });
                }

            } catch (error) {
                Swal.close();
            }
        }
    });

    btnGuardar.addEventListener("click", async function () {
        const duiValido = validarCampo(dui);
        const nombreValido = validarCampo(nombre);
        const empleado1Valido = validarCampo(empleado1);
        const cargo1Valido = validarCampo(cargo1);
        const fechaValido = validarCampo(fecha);
        const montoValido = validarCampo(monto);

        const empleado1Select = empleado1.value !== "0" && empleado1.value !== "";
        empleado1.classList.toggle("is-invalid", !empleado1Select);
        empleado1.classList.toggle("is-valid", empleado1Select);

        if (!(duiValido && nombreValido && empleado1Valido && cargo1Valido && fechaValido && montoValido && empleado1Select)) {
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
            datos.append("idsocio", btnGuardar.dataset.id);

            btnGuardar.disabled = true;
            btnGuardar.textContent = "Procesando...";

            // Enviar datos
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
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.message || "Error al procesar la solicitud"
            });
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = modoModificar ? "Modificar" : "Guardar";
        }
    });

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
async function cargarNumeroContrato() {
    try {
        const datos = new FormData();
        datos.append("opcion", "generarNumero");

        const respuesta = await fetch("DAOContrato.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const resultado = await respuesta.json();
        if (resultado.estado) {
            const numero = document.getElementById("numero");
            if (numero) {
                numero.value = resultado.numero;
            }
        }
    } catch (error) {
        console.error(error);
    }
}

///////////////////LIMPIAR TODOS LOS CAMPOS///////////
function limpiarCampos() {
    [dui, nombre, cargo1, cargo2, fecha, monto, numero].forEach(function (campo) {
        if (campo) {
            campo.value = "";
            campo.classList.remove("is-invalid");
            campo.classList.remove("is-valid");
        }
    });
    if (empleado1) {
        empleado1.value = "0";
        empleado1.classList.remove("is-invalid");
        empleado1.classList.remove("is-valid");
    }
    if (empleado2) {
        empleado2.value = "0";
        empleado2.classList.remove("is-invalid");
        empleado2.classList.remove("is-valid");
    }
    if (btnGuardar) {
        btnGuardar.disabled = true;
        delete btnGuardar.dataset.id;
    }
    cargarNumeroContrato();
}

//////////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    try {
        const datos = new FormData();
        datos.append("opcion", "consultarEmpleados");

        const respuesta = await fetch("DAOContrato.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            console.error(respuesta.status);
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const resultado = await respuesta.json();

        const select1 = document.getElementById("empleado1");
        const select2 = document.getElementById("empleado2");

        select1.innerHTML = '<option value="0">[SELECCIONE]</option>';
        select2.innerHTML = '<option value="0">[SELECCIONE]</option>';

        resultado.forEach(empleado => {
            const opcion1 = document.createElement("option");
            opcion1.value = empleado.idempleado;
            opcion1.textContent = empleado.nombre;
            select1.appendChild(opcion1);

            const opcion2 = document.createElement("option");
            opcion2.value = empleado.idempleado;
            opcion2.textContent = empleado.nombre;
            select2.appendChild(opcion2);
        });

    } catch (error) {
        Swal.close();
    }

    // Generar número de contrato automático
    cargarNumeroContrato();
});
