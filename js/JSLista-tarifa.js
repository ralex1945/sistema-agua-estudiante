document.addEventListener("DOMContentLoaded", async () => {
    const contenedor = document.getElementById("users-table");
    const filtro = document.getElementById("table-filter");
    const paginacion = document.getElementById("table-pagination");
    const btnImprimir = document.getElementById("print-table");
    const contador = document.getElementById("total-registros");

    if (!contenedor) return;

    const tabla = new Tabulator(contenedor, {
        data: [],
        layout: "fitColumns",
        pagination: true,
        paginationElement: paginacion,
        paginationSize: 10,
        paginationSizeSelector: [10, 25, 50, 100],
        movableColumns: true,
        columns: [
            { title: "ID", field: "idtarifa", visible: false },
            { title: "Desde", field: "desde", hozAlign: "center" },
            { title: "Hasta", field: "hasta", hozAlign: "center" },
            { title: "Precio ($)", field: "precio", hozAlign: "right" },
            { title: "Tipo", field: "tipo" }
        ]
    });

    function actualizarContador() {
        const total = tabla.getDataCount("active");
        contador.textContent = `Total de Registros: ${total}`;
    }

    tabla.on("dataLoaded", actualizarContador);
    tabla.on("dataFiltered", actualizarContador);

    if (filtro) {
        filtro.addEventListener("input", function () {
            const valor = this.value.trim().toLowerCase();
            tabla.setFilter(function (fila) {
                return (
                    String(fila.desde ?? "").toLowerCase().includes(valor) ||
                    String(fila.hasta ?? "").toLowerCase().includes(valor) ||
                    String(fila.precio ?? "").toLowerCase().includes(valor) ||
                    String(fila.tipo ?? "").toLowerCase().includes(valor)
                );
            });
        });
    }

    if (btnImprimir) {
        btnImprimir.addEventListener("click", function () {
            tabla.print(false, true);
        });
    }

    try {
        Swal.fire({
            title: "Cargando...",
            text: "Espere",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        const datos = new FormData();
        datos.append("opcion", "consultar");

        const respuesta = await fetch("DAOTarifa.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const registros = await respuesta.json();
        Swal.close();

        if (!Array.isArray(registros)) {
            throw new Error(registros.error || "Respuesta no válida");
        }

        await tabla.replaceData(registros);
        actualizarContador();
    } catch (error) {
        console.error("Error al cargar registros: ", error);
    }
});

