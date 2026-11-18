function validarCampo(campo) {
    const vacio = campo.value.trim() === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");
const dui = document.getElementById("dui");
const nombre = document.getElementById("nombre");

const campos = [dui, nombre];

if (formulario) {
    ///////////////////VALIDACION DE CAMPOS VACIOS

    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
    });

    ///////////////////FORMATO DE DUI///////////////////

    dui.addEventListener("input", function () {
        let valor = dui.value.replace(/\D/g, "");
        valor = valor.substring(0, 9);
        if (valor.length > 8) {
            valor = valor.substring(0, 8) + "-" + valor.substring(8);
        }
        dui.value = valor;
    });
}

///////////////////BOTON CANCELAR//////

btnCancelar.addEventListener("click", function () {
    window.location.href = "lista-empleados.php";
});

btnGuardar.addEventListener("click", async function () {
    const duiValido = validarCampo(dui);
    const nombreValido = validarCampo(nombre);

    if (btnGuardar.textContent.trim() == "Guardar") {

        if (duiValido && nombreValido) {
            const roles = document.querySelectorAll('input[name="roles[]"]:checked');

            const seleccionados = Array.from(roles).map(c => c.value);

            if (seleccionados.length === 0) {
                Swal.fire({
                    position: "top-end",
                    icon: "warning",
                    title: "Poseidón",
                    text: "Selecciona al menos un ROL",
                    showConfirmButton: false,
                    timer: 1500
                });

                //return;
            } else {
                try {
                    const datos = new FormData(formulario);
                    datos.append("opcion", "guardar");
                    datos.append("idempleado", btnGuardar.dataset.id);
                    datos.append("dui", dui.value);

                    btnGuardar.disabled = true;
                    btnGuardar.textContent = "Procesando...";

                    // Enviar datos
                    // await hace que JavaScript ESPERE
                    const respuesta = await fetch("DAONivel-acceso.php", {
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
                            position: "top-end",
                            icon: "success",
                            title: "Poseidón",
                            text: resultado.mensaje,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = "lista-empleados.php";
                        });
                    } else {
                        Swal.fire({
                            position: "top-end",
                            icon: "error",
                            title: "Poseidón",
                            text: resultado.mensaje,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        btnGuardar.disabled = false;
                        btnGuardar.textContent = "Guardar";
                    }
                } catch (error) {
                    console.error("Error:", error);
                    Swal.fire({
                        position: "top-end",
                        icon: "error",
                        title: "Poseidón",
                        text: "Ocurrió un error al procesar la solicitud.",
                        showConfirmButton: false,
                        timer: 2000
                    });
                    btnGuardar.disabled = false;
                    btnGuardar.textContent = "Guardar";
                }
            }
        }
    }
});

///////////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {

    ///////////////////////
    // Obtener el ID de la URL
    const parametros = new URLSearchParams(window.location.search);
    const idempleado = parametros.get("id");

    if (idempleado) {//SI EXISTE EL ID, SE HACE LA BUSQUEDA
        try {
            const datos = new FormData();
            datos.append("opcion", "consultar");
            datos.append("idempleado", idempleado);

            Swal.fire({
                title: "Consultando...",
                text: "Espere",
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            const respuesta = await fetch("DAONivel-acceso.php", {
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
                nombre.value = resultado.datos.nombre + " " + resultado.datos.apellido;
                btnGuardar.dataset.id = resultado.datos.idempleado;
                btnGuardar.disabled = false;

                // Marcar los roles seleccionados
                const roles = resultado.datos.rol ? resultado.datos.rol.split(",").map(r => r.trim()) : [];

                document.querySelectorAll('input[name="roles[]"]').forEach(chk => {
                    chk.checked = roles.includes(chk.value);
                });
            }

        } catch (error) {
            Swal.close();
        }
    }

});

const btnCerrarSesion = document.getElementById("cerrarSesion");
if (btnCerrarSesion) {
    btnCerrarSesion.addEventListener("click", function (e) {
        e.preventDefault();
        Swal.fire({
            title: "¿Cerrar sesión?",
            text: "Se cerrará tu sesión actual.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, salir",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "salir.php";
            }
        });
    });
}