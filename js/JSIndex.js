const cerrarSesion = document.getElementById("cerrarSesion");
const sumaMensual = document.getElementById("sumaMensual");
const pendientesPago = document.getElementById("pendientesPago");
const numeroSocio = document.getElementById("numeroSocio");

cerrarSesion.addEventListener("click", function (e) {
    e.preventDefault();

    Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro que deseas salir del sistema?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'salir.php';
        }
    });
});

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const datos = new FormData();
        datos.append("opcion", "consulta");

        Swal.fire({
            title: "Consultando...",
            text: "Espere",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        const respuesta = await fetch("DAOIndex.php", {
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
            if (sumaMensual && resultado.sumaMensual !== undefined) {
                sumaMensual.textContent = "$" + resultado.sumaMensual;
            }
            if (pendientesPago && resultado.pendientesPago !== undefined) {
                pendientesPago.textContent = resultado.pendientesPago;
            }
            if (numeroSocio && resultado.numeroSocio !== undefined) {
                numeroSocio.textContent = resultado.numeroSocio;
            }
        }
    } catch (error) {
        Swal.close();
        console.error("Error al cargar el dashboard:", error);
    }
});
