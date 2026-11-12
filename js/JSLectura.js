//JSLectura.js
function validarCampo(campo) {
    //const vacio = campo.value.trim() === "";
    const vacio = campo.value === null || campo.value.trim() === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);
    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const nmedidor = document.getElementById("nmedidor");
const nombre = document.getElementById("nombre");
const lecturaAnterior = document.getElementById("lecturaAnterior");
const lecturaActual = document.getElementById("lecturaActual");
const fecha = document.getElementById("fecha");
const empleado = document.getElementById("empleado");
const aviso = document.getElementById("aviso");
const fechaVencimiento = document.getElementById("fechaVencimiento");
const fechaAnterior = document.getElementById("fechaAnterior");
const anio = document.getElementById("anio");
const mes = document.getElementById("mes");
const canton = document.getElementById("canton");
const ruta = document.getElementById("ruta");

const campos = [lecturaActual, fecha, fechaVencimiento];

if (formulario) {
    /////////////////////VALIDACION DE CAMPOS VACIOS

    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
    });

    /////////////////////PASAR TODO A MAYUSCULAS
    const campos1 = [aviso];

    campos1.forEach(function (campo) {
        campo.addEventListener("input", function () {
            campo.value = campo.value.toUpperCase();
            validarCampo(campo);
        });
    });

    ////////////////VALIDAR QUE EL SUELDO SEAN SOLO NUMEROS///////////
    nmedidor.addEventListener("input", function () {
        // Permitir únicamente números
        this.value = this.value.replace(/[^0-9]/g, "");
    });

    //////////////FECHAS, NO PUEDAN SER FUTURAS///////////

    // Obtener la fecha actual en formato YYYY-MM-DD
    const hoy = new Date();
    const anioActual = hoy.getFullYear();
    const mesActual = String(hoy.getMonth() + 1).padStart(2, "0");
    const diaActual = String(hoy.getDate()).padStart(2, "0");

    const fechaActual = `${anioActual}-${mesActual}-${diaActual}`;

    // No permitir fechas futuras
    fecha.max = fechaActual;

    fecha.addEventListener("change", function () {
        validarCampo(this);
    });

    ///////////////////BUSQUEDA DE MEDIDOR - PARA OBTENER DATOS/////////

    nmedidor.addEventListener("keydown", async function (e) {
        if (e.key !== "Enter") return;

        e.preventDefault();
        e.stopPropagation();

        try {
            const datos = new FormData();
            datos.append("opcion", "consultarConsumo");
            datos.append("nmedidor", nmedidor.value);

            Swal.fire({
                title: "Cargando...",
                text: "Espere",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const respuesta = await fetch("DAOLectura.php", {
                method: "POST",
                body: datos
            });

            Swal.close();

            const resultado = await respuesta.json();

            if (resultado.estado) {
                nombre.value = resultado.datos.nombre;
                canton.value = resultado.datos.canton;
                ruta.value = resultado.datos.ruta;
                document.getElementById("direccion").value = resultado.datos.direccion;
                lecturaAnterior.value = resultado.datos.lectura_anterior;
                fechaAnterior.value = resultado.datos.fecha_lectura ?? "";

                btnGuardar.dataset.id = resultado.datos.idmedidor;

                validarCampo(nmedidor);
                validarCampo(nombre);
                validarCampo(canton);
                validarCampo(ruta);
                validarCampo(document.getElementById("direccion"));

                lecturaActual.focus();
            } else {
                nombre.value = "";
                canton.value = "";
                ruta.value = "";
                document.getElementById("direccion").value = "";
                lecturaAnterior.value = "";
                fechaAnterior.value = "";
                btnGuardar.dataset.id = "";

                validarCampo(nmedidor);
                validarCampo(nombre);

                Swal.fire({
                    icon: "warning",
                    title: "Poseidón",
                    text: resultado.datos || "No Existe"
                });
            }
        } catch (error) {
            Swal.close();
            console.error(error);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo conectar con el servidor. Verifique que MySQL esté activo."
            });
        }
    });

    ///////////////////GUARDAR LECTURA - BOTON GUARDAR/////////

    btnGuardar.addEventListener("click", async function () {
        const nmedidorValido = validarCampo(nmedidor);
        const lecturaActualValido = validarCampo(lecturaActual);
        const fechaValido = validarCampo(fecha);
        const empleadoValido = validarCampo(empleado);
        const fechaVencimientoValido = validarCampo(fechaVencimiento);
        const mesValido = validarCampo(mes);
        const anioValido = validarCampo(anio);
        const nombreValido = validarCampo(nombre);

        if (btnGuardar.textContent.trim() == "Guardar") {

            if (nmedidorValido && lecturaActualValido && fechaValido && empleadoValido && fechaVencimientoValido && mesValido && anioValido && nombreValido) {
                if (Number(lecturaActual.value) < Number(lecturaAnterior.value)) {
                    Swal.fire({
                        icon: "warning",
                        title: "Lectura Incorrecta",
                        text: "La lectura actual no puede ser menor que la lectura anterior."
                    });
                } else {
                    try {
                        const datos = new FormData(formulario);
                        datos.append("opcion", "guardar");
                        datos.append("idmedidor", btnGuardar.dataset.id);

                        btnGuardar.disabled = true;
                        btnGuardar.textContent = "Procesando...";

                        // Enviar datos
                        // await hace que JavaScript ESPERE
                        const respuesta = await fetch("DAOLectura.php", {
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
                            Swal.fire({
                                icon: "success",
                                title: "Poseidón",
                                text: resultado.mensaje,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "warning",
                                title: "Poseidón",
                                text: resultado.mensaje
                            });
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire({
                            icon: "error",
                            title: "Poseidón",
                            text: "Error al procesar la solicitud"
                        });
                    } finally {
                        btnGuardar.disabled = false;
                        btnGuardar.textContent = "Guardar";
                    }
                }
            }
        }
    });

    const btnCancelar = document.getElementById("btnCancelar");
    if (btnCancelar) {
        btnCancelar.addEventListener("click", function () {
            formulario.reset();
            window.location.reload();
        });
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    /////LLENAMOS EL SELECT DE EMPLEADOS
    try {
        const datos = new FormData();
        datos.append("opcion", "consultarEmpleados");

        const respuesta = await fetch("DAOEmpleado.php", {
            method: "POST",
            body: datos
        });
        const resultado = await respuesta.json();
        const select = document.getElementById("empleado");

        select.innerHTML = '<option value="">[Seleccione Empleado]</option>';
        resultado.forEach(emp => {
            const opcion = document.createElement("option");
            opcion.value = emp.idempleado;
            opcion.textContent = emp.nombre;
            select.appendChild(opcion);
        });
    } catch (error) {
        console.error(error);
    }

    const hoy = new Date();
    const yyyy = hoy.getFullYear();
    const mm = hoy.getMonth() + 1;

    if (anio) {
        anio.innerHTML = `<option value="">[Seleccion]</option>`;
        for (let a = yyyy - 2; a <= yyyy + 2; a++) {
            const opt = document.createElement("option");
            opt.value = a;
            opt.textContent = a;
            if (a === yyyy) opt.selected = true;
            anio.appendChild(opt);
        }
    }

    if (mes) {
        mes.value = mm;
    }
});
