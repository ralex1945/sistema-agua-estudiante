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
    ////////////////////VALIDACION DE CAMPOS OBLIGATORIOS
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

    ////////////////////PASAR TODO A MAYUSCULAS
    const campos1 = [nombre, apellido];

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

    telefono.addEventListener("input", function () {
        let valor = telefono.value.replace(/\D/g, "");
        valor = valor.substring(0, 8);
        if (valor.length > 5) {
            valor = valor.substring(0, 4) + "-" + valor.substring(4);
        }
        telefono.value = valor;
    });

    ////////////////////BUSQUEDA DE DUI- PARA REPERIDOS/////////

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
                    console.error(respuesta.status);
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();

                // Cerrar el mensaje de espera
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

    btnGuardar.addEventListener("click", async function () {
        // Validar únicamente los campos obligatorios
        let formularioValido = true;
        camposObligatorios.forEach(function (campo) {
            if (campo && !validarCampo(campo)) {
                formularioValido = false;
            }
        });

        // Si falta algún campo obligatorio, mostramos alerta y no continúa
        if (!formularioValido) {
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
            if (modoModificar && btnGuardar.dataset.id) {
                datos.append("idempleado", btnGuardar.dataset.id);
            }

            btnGuardar.disabled = true;
            btnGuardar.textContent = "Procesando...";

            // Enviar datos
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
///////////////////LIMPIAR TODOS LOS CAMPOS///////////
function limpiarCampos() {
    todosLosCampos.forEach(function (campo) {
        if (campo) {
            campo.value = "";
            campo.classList.remove("is-invalid");
            campo.classList.remove("is-valid");
        }
    });
}

//////////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    // Obtener el ID de la URL
    const parametros = new URLSearchParams(window.location.search);
    const idempleado = parametros.get("id");

    if (idempleado) {//SI EXISTE EL ID, SE HACE LA BUSQUEDA
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarIndividual");
            datos.append("idempleado", idempleado);

            Swal.fire({
                title: "Consultando...",
                text: "Espere",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const respuesta = await fetch("DAOEmpleado.php", {
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
                dui.value = resultado.datos.dui;
                nombre.value = resultado.datos.nombre;
                apellido.value = resultado.datos.apellido;
                telefono.value = resultado.datos.telefono;

                btnGuardar.textContent = "Modificar";
                btnGuardar.dataset.id = resultado.datos.idempleado;
            }

        } catch (error) {
            Swal.close();
        }
    }


});

