function validarCampo(campo) {
    const valor = campo.value.trim();
    const vacio = valor === "";

    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");
const btnEliminar = document.getElementById("btnEliminar");
const empleado = document.getElementById("empleado");
const ruta = document.getElementById("ruta");
const rutas = document.getElementById("rutas");

const campos = [empleado, ruta];

if (formulario) {
    ////////////////VALIDACION DE CAMPOS VACIOS
    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
    });

    ///////////ELIMINAR RUTAS ASIGNADAS
    rutas.addEventListener("change", async function () {
        if (rutas.value.trim() !== "") {
            document.getElementById("btnEliminar").style.display = "inline-block";
        }
    });

    btnEliminar.addEventListener("click", async function () {
        if (rutas.value.trim() !== "") {
            try {
                const datos = new FormData();
                datos.append("opcion", "eliminar");
                datos.append("idempleado_ruta", rutas.value);

                const respuesta = await fetch("DAOAsignarRuta.php", {
                    method: "POST",
                    body: datos
                });

                if (!respuesta.ok) {
                    console.error(respuesta.status);
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
                        window.location.href = "asignarRuta.php";
                    });
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

    ///EVENTO CUANDO SE SELECCIONE UN EMPLEADO - MUESTRA LAS RUTAS
    empleado.addEventListener("change", async function () {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultarRutas");
            datos.append("idempleado", empleado.value);

            const respuesta = await fetch("DAORuta.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) {
                console.error(respuesta.status);
                throw new Error("Error HTTP: " + respuesta.status);
            }

            const resultado = await respuesta.json();
            const select = document.getElementById("rutas");

            // Limpiar el select
            select.innerHTML = '<option value="">[Seleccione Ruta]</option>';

            // Recorrer el arreglo de ruta
            if (resultado.estado) {
                resultado.datos.forEach(ruta => {
                    const opcion = document.createElement("option");
                    opcion.value = ruta.idempleado_ruta;
                    opcion.textContent = ruta.nombre;

                    select.appendChild(opcion);
                });
            }
        } catch (error) {
            console.error(error);
        }
    });

    /////////////////BOTON CANCELAR/////
    btnCancelar.addEventListener("click", function () {
        window.location.href = "asignarRuta.php";
    });

    /////////////////BOTON GUARDAR/////
    btnGuardar.addEventListener("click", async function () {
        const empleadoValido = validarCampo(empleado);
        const rutaValida = validarCampo(ruta);

        if (btnGuardar.textContent.trim() == "Guardar") {
            if (empleadoValido && rutaValida) {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "guardar");

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Procesando...";

                    // Enviar datos
                    const respuesta = await fetch("DAOAsignarRuta.php", {
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
}

///////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    /////LLENAMOS EL SELECT DE EMPLEADOS
    try {
        const datos = new FormData();
        datos.append("opcion", "consultarLectores");

        const respuesta = await fetch("DAOEmpleado.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            console.error(respuesta.status);
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const resultado = await respuesta.json();
        const select = document.getElementById("empleado");

        // Limpiar el select
        select.innerHTML = '<option value="">[Seleccione Empleado]</option>';

        // Recorrer el arreglo de cargos
        resultado.forEach(empleado => {
            const opcion = document.createElement("option");
            opcion.value = empleado.idempleado;
            opcion.textContent = empleado.nombre;

            select.appendChild(opcion);
        });
    } catch (error) {
        console.error(error);
    }

    /////LLENAMOS EL SELECT DE RUTAS
    try {
        const datos = new FormData();
        datos.append("opcion", "consultar");

        const respuesta = await fetch("DAORuta.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            console.error(respuesta.status);
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const resultado = await respuesta.json();
        const select = document.getElementById("ruta");

        // Limpiar el select
        select.innerHTML = '<option value="">[Seleccione Ruta]</option>';

        // Recorrer el arreglo de cargos
        resultado.forEach(ruta => {
            const opcion = document.createElement("option");
            opcion.value = ruta.idruta;
            opcion.textContent = ruta.nombre;

            select.appendChild(opcion);
        });
    } catch (error) {
        console.error(error);
    }

    document.getElementById("btnEliminar").style.display = "none";
});