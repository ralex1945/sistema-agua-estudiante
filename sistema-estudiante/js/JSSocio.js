function validarCampo(campo) {
    const valor = campo.value.trim();
    const vacio = valor === "";

    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const dui = document.getElementById("dui");
const nombre = document.getElementById("nombre");
const apellido = document.getElementById("apellido");
const telefono = document.getElementById("telefono");
const profesion = document.getElementById("profesion");
const nit = document.getElementById("nit");
const direccion = document.getElementById("direccion");
const canton = document.getElementById("canton");
const fecha = document.getElementById("fecha");

const campos = [dui, nombre, apellido, telefono, profesion, nit, direccion, fecha, canton];

if (formulario) {
    ////////////////VALIDACION DE CAMPOS VACIOS
    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
    });

    ////////////////PASAR A MAYUSCULA NOMBRE Y APELLIDO
    [nombre, apellido, direccion, profesion].forEach(function (campo) {
        campo.addEventListener("input", function () {
            this.value = this.value.toUpperCase();
        });
    });

    ////////////////FORMATO DEL DUI 00000000-0
    dui.addEventListener("input", function () {
        let valor = this.value.replace(/\D/g, ""); // Solo números
        valor = valor.substring(0, 9); // Máximo 9 dígitos

        if (valor.length > 8) {
            this.value = valor.slice(0, 8) + "-" + valor.slice(8);
        } else {
            this.value = valor;
        }
    });

    ////////////////FORMATO DE TELEFONO 0000-0000
    telefono.addEventListener("input", function () {
        let valor = this.value.replace(/\D/g, ""); // Solo números
        valor = valor.substring(0, 8); // Máximo 8 dígitos

        if (valor.length > 4) {
            this.value = valor.slice(0, 4) + "-" + valor.slice(4);
        } else {
            this.value = valor;
        }
    });

    ////////////////FORMATO DE NIT 0000-000000-000-0
    nit.addEventListener("input", function () {
        let valor = this.value.replace(/\D/g, ""); // Solo números
        valor = valor.substring(0, 14); // Máximo 14 dígitos

        let formato = "";

        if (valor.length > 0) {
            formato = valor.substring(0, 4);
        }
        if (valor.length > 4) {
            formato += "-" + valor.substring(4, 10);
        }
        if (valor.length > 10) {
            formato += "-" + valor.substring(10, 13);
        }
        if (valor.length > 13) {
            formato += "-" + valor.substring(13, 14);
        }

        this.value = formato;
    });

    ////////////////FORMATO DE FECHA
    // Evitar fechas futuras
    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = String(hoy.getMonth() + 1).padStart(2, '0');
    const dd = String(hoy.getDate()).padStart(2, '0');
    fecha.max = `${yyyy}-${mm}-${dd}`;

    // Validar cuando cambie la fecha
    fecha.addEventListener("change", function () {
        validarCampo(this);
    });

    ////////////////BUSCAR EL DUI DEL SOCIO SI EXISTE
    dui.addEventListener("keydown", async function (e) {
        if (e.key === "Enter" && this.value.length === 10) {
            e.preventDefault(); // evita que se envíe el formulario

            try {
                const datos = new FormData();
                datos.append("opcion", "consultarDui");
                datos.append("dui", dui.value);

                const respuesta = await fetch("DAOSocio.php", {
                    method: "POST",
                    body: datos
                });

                if (!respuesta.ok) {
                    console.error(respuesta.status);
                    throw new Error("Error HTTP: " + respuesta.status);
                }

                const resultado = await respuesta.json();

                if (resultado.estado) {
                    limpiarCampos();
                    dui.value = resultado.datos.dui;
                    nombre.value = resultado.datos.nombre;
                    apellido.value = resultado.datos.apellido;
                    telefono.value = resultado.datos.telefono;
                    fecha.value = resultado.datos.fecha_nacimiento || resultado.datos.fechanacimiento;
                    nit.value = resultado.datos.nit;
                    profesion.value = resultado.datos.profesion;
                    direccion.value = resultado.datos.direccion;
                    canton.value = resultado.datos.idcanton;

                    btnGuardar.textContent = "Modificar";
                    btnGuardar.dataset.id = resultado.datos.idsocio; // guardamos el id en el botón
                }
            } catch (error) {
                console.error(error);
            }
        }
    });

    /////////////////BOTON GUARDAR/////
    btnGuardar.addEventListener("click", async function () {
        const duiValido = validarCampo(dui);
        const nombreValido = validarCampo(nombre);
        const apellidoValido = validarCampo(apellido);
        const telefonoValido = validarCampo(telefono);
        const profesionValido = validarCampo(profesion);
        const nitValido = validarCampo(nit);
        const direccionValida = validarCampo(direccion);
        const fechaValida = validarCampo(fecha);
        const cantonValido = validarCampo(canton);

        if (btnGuardar.textContent.trim() == "Guardar") {
            if (duiValido && nombreValido && apellidoValido && telefonoValido && profesionValido && nitValido && direccionValida && fechaValida && cantonValido) {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "guardar");

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Procesando...";

                    // Enviar datos
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
                        });
                        limpiarCampos();
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
                } finally {
                    btnGuardar.disabled = false;
                    btnGuardar.textContent = "Guardar";
                }
            }
        } else if (btnGuardar.textContent.trim() == "Modificar") {
            if (duiValido && nombreValido && apellidoValido && telefonoValido && fechaValida && profesionValido && nitValido && direccionValida && cantonValido) {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "modificar");
                    datos.append("idsocio", btnGuardar.dataset.id);

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Procesando...";

                    // Enviar datos
                    // await hace que JavaScript ESPERE
                    const respuesta = await fetch("DAOSocio.php", {
                        method: "POST",
                        body: datos
                    });

                    // Verificar respuesta HTTP
                    if (!respuesta.ok) {
                        throw new Error("Error HTTP: " + respuesta.status);
                    }

                    // Esperar a que DAO termine
                    const resultado = await respuesta.json();
                    if (resultado.estado) {
                        //todo salio bien
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
                            icon: "warning",
                            title: "Poseidón",
                            text: resultado.mensaje,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                } catch (error) {
                    console.error(error);
                } finally {
                    btnGuardar.disabled = false;
                    btnGuardar.textContent = "Guardar";
                    delete btnGuardar.dataset.id;
                }
            }
        }
    });
}

/////////LIMPIAR TODOS LOS CAMPOS/////////
function limpiarCampos() {
    formulario.reset();
    campos.forEach(function (campo) {
        campo.value = "";
        campo.classList.remove("is-invalid");
        campo.classList.remove("is-valid");
    });
    btnGuardar.textContent = "Guardar";
    delete btnGuardar.dataset.id;
}

///////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    /////LLENAMOS EL SELECT DE CANTONES
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
        const select = document.getElementById("canton");

        // Limpiar el select
        select.innerHTML = '<option value="">[SELECCIONE UN CANTON]</option>';

        // Recorrer el arreglo de cantones
        resultado.forEach(cantonItem => {
            const opcion = document.createElement("option");
            opcion.value = cantonItem.idcanton;
            opcion.textContent = cantonItem.nombre;

            select.appendChild(opcion);
        });
    } catch (error) {
        console.error(error);
    }

    ///////CUANDO RECIBE PARAMETROS
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");

    if (id) {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarIndividual");
            datos.append("idsocio", id);

            const respuesta = await fetch("DAOSocio.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) {
                console.error(respuesta.status);
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();

            if (resultado.estado) {
                limpiarCampos();
                dui.value = resultado.datos.dui;
                nombre.value = resultado.datos.nombre;
                apellido.value = resultado.datos.apellido;
                telefono.value = resultado.datos.telefono;
                fecha.value = resultado.datos.fecha_nacimiento || resultado.datos.fechanacimiento;
                nit.value = resultado.datos.nit;
                profesion.value = resultado.datos.profesion;
                direccion.value = resultado.datos.direccion;
                canton.value = resultado.datos.idcanton;

                btnGuardar.textContent = "Modificar";
                btnGuardar.dataset.id = resultado.datos.idsocio; // guardamos el id en el botón
            }
        } catch (error) {
            console.error(error);
        }
    }
});
