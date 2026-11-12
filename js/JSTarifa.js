function validarCampo(campo) {
    const vacio = campo.value.trim() === "";
    campo.classList.toggle("is-invalid", vacio);
    campo.classList.toggle("is-valid", !vacio);

    return !vacio;
}

const formulario = document.getElementById("formulario");
const btnGuardar = document.getElementById("btnGuardar");
const btnCancelar = document.getElementById("btnCancelar");
const desde = document.getElementById("desde");
const hasta = document.getElementById("hasta");
const precio = document.getElementById("precio");
const tipo = document.getElementById("tipo");

const campos = [desde, hasta, precio, tipo];

if (formulario) {
    /////////////////VALIDACION DE CAMPOS VACIOS
    campos.forEach(function (campo) {
        campo.addEventListener("blur", function () {
            validarCampo(campo);
        });
    });

    ////////////////VALIDAR QUE EL SUELDO SEAN SOLO NUMEROS////////////////
    precio.addEventListener("input", function () {
        // Permitir solo números y un punto decimal
        let valor = this.value.replace(/[^0-9.]/g, "");

        // Evitar más de un punto decimal
        const partes = valor.split(".");
        if (partes.length > 2) {
            valor = partes[0] + "." + partes.slice(1).join("");
        }

        // Permitir solo un decimal
        const posicion = valor.indexOf(".");
        if (posicion !== -1) {
            valor = valor.substring(0, posicion + 3);
        }

        this.value = valor;
    });

    /////////////////DESDE Y HASTA SOLO NUMERO
    desde.addEventListener("input", function () {
        // Permitir solo números y un punto decimal
        let valor = this.value.replace(/[^0-9]/g, "");
        this.value = valor;
    });

    hasta.addEventListener("input", function () {
        // Permitir solo números y un punto decimal
        let valor = this.value.replace(/[^0-9]/g, "");
        this.value = valor;
    });

    ///////////////////////BOTON CANCELAR/////
    btnCancelar.addEventListener("click", function () {
        window.location.href = "tarifa.php";
    });

    btnGuardar.addEventListener("click", async function () {
        const desdeValido = validarCampo(desde);
        const hastaValido = validarCampo(desde);
        const tipoValido = validarCampo(tipo);
        const precioValido = validarCampo(precio);

        if (btnGuardar.textContent.trim() == "Guardar") {
            if (desdeValido && hastaValido && tipoValido && precioValido) {
                if (Number(hasta.value) < Number(desde.value)) {
                    Swal.fire({
                        position: "top-end",
                        icon: "warning",
                        title: "Poseidón",
                        text: "El rango no es correcto",
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    try {
                        const datos = new FormData(formulario);
                        datos.append("opcion", "guardar");
                        //datos.append("idsocio", btnGuardar.dataset.id);

                        btnGuardar.disabled = true;
                        btnGuardar.textContent = "Procesando...";

                        // Enviar datos
                        // await hace que JavaScript ESPERE
                        const respuesta = await fetch("DAOTarifa.php", {
                            method: "POST",
                            body: datos
                        });

                        // Verificar respuesta HTTP
                        if (!respuesta.ok) {
                            throw new Error("Error HTTP: " + respuesta.status);
                        }

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
                            formulario.reset();
                            campos.forEach(c => {
                                c.classList.remove("is-valid");
                                c.classList.remove("is-invalid");
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
                    } finally {
                        btnGuardar.disabled = false;
                        btnGuardar.textContent = "Guardar";
                    }
                }
            }
        }
    });
}
