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
const nafp = document.getElementById("nafp");
const renta = document.getElementById("renta");
const sueldo = document.getElementById("sueldo");
const cargo = document.getElementById("cargo");
const canton = document.getElementById("canton");
const estado = document.getElementById("estado");
const fecha = document.getElementById("fecha");

const campos = [dui, nombre, apellido, telefono, sueldo, fecha];

if (formulario) {
    ////////////////////VALIDACION DE CAMPOS VACIOS

    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
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

    sueldo.addEventListener("input", function () {
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
        const duiValido = validarCampo(dui);
        const nombreValido = validarCampo(nombre);
        const apellidoValido = validarCampo(apellido);
        const telefonoValido = validarCampo(telefono);
        const sueldoValido = validarCampo(sueldo);
        const fechaValido = validarCampo(fecha);

        const cantonValido = canton.value !== "0" && canton.value !== "";
        canton.classList.toggle("is-invalid", !cantonValido);
        canton.classList.toggle("is-valid", cantonValido);

        const cargoValido = cargo.value !== "0" && cargo.value !== "";
        cargo.classList.toggle("is-invalid", !cargoValido);
        cargo.classList.toggle("is-valid", cargoValido);

        const rentaValido = renta.value !== "0" && renta.value !== "";
        renta.classList.toggle("is-invalid", !rentaValido);
        renta.classList.toggle("is-valid", rentaValido);

        const estadoValido = estado.value !== "0" && estado.value !== "";
        estado.classList.toggle("is-invalid", !estadoValido);
        estado.classList.toggle("is-valid", estadoValido);

        if (!(duiValido && nombreValido && apellidoValido && telefonoValido && sueldoValido && fechaValido && cantonValido && cargoValido && rentaValido && estadoValido)) {
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
    [dui, nombre, apellido, telefono, isss, afp, nafp, renta, sueldo, cargo, canton, estado, fecha].forEach(function (campo) {
        if (campo) {
            campo.value = "";
            campo.classList.remove("is-invalid");
            campo.classList.remove("is-valid");
        }
    });
}

//////////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    try {
        const datos = new FormData();
        datos.append("opcion", "consultar");

        const respuesta = await fetch("DAOCargo.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            console.error(respuesta.status);
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const resultado = await respuesta.json();

        const selectCargo = document.getElementById("cargo");

        selectCargo.innerHTML = '<option value="0">[SELECCIONE UN CARGO]</option>'

        resultado.forEach(cargo => {
            const opcion = document.createElement("option");
            opcion.value = cargo.idcargo;
            opcion.textContent = cargo.nombre

            selectCargo.appendChild(opcion);
        });

    } catch (error) {
        Swal.close();
    }

    try {
        const datos = new FormData();
        datos.append("opcion", "consultar");

        const respuesta = await fetch("DAOCanton.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            console.error(respuesta.status);
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const resultado = await respuesta.json();

        const selectCanton = document.getElementById("canton");

        selectCanton.innerHTML = '<option value="0">[SELECCIONE UN CANTON]</option>'

        resultado.forEach(canton => {
            const opcion = document.createElement("option");
            opcion.value = canton.idcanton;
            opcion.textContent = canton.nombre;

            selectCanton.appendChild(opcion);
        });

    } catch (error) {
        Swal.close();
    }

    // 3. Obtener el ID de la URL (después de que los selects ya están cargados)
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
                fecha.value = resultado.datos.fecha_ingreso;
                isss.value = resultado.datos.n_isss;
                afp.value = resultado.datos.afp;
                nafp.value = resultado.datos.nup;
                sueldo.value = resultado.datos.sueldo;
                cargo.value = resultado.datos.idcargo;
                renta.value = resultado.datos.renta;
                canton.value = resultado.datos.idcanton;
                estado.value = resultado.datos.estado;

                btnGuardar.textContent = "Modificar";
                btnGuardar.dataset.id = resultado.datos.idempleado;
            }

        } catch (error) {
            Swal.close();
        }
    }

});
