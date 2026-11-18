
//////////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    const contenedor = document.getElementById("users-table");
    const filtro = document.getElementById("table-filter");
    const paginacion = document.getElementById("table-pagination");
    const btnGenerar = document.getElementById("btnGenerar");

    const contador = document.getElementById("total-registros");

    if (!contenedor) return;
    const tabla = new Tabulator(contenedor, {
        locale: "es-es",
        langs: {
            "es-es": {
                "pagination": {
                    "page_size": "Registros por página",
                    "first": "Primera",
                    "first_title": "Primera página",
                    "last": "Última",
                    "last_title": "Última página",
                    "prev": "Anterior",
                    "prev_title": "Página anterior",
                    "next": "Siguiente",
                    "next_title": "Página siguiente",
                    "all": "Todos",
                    "counter": {
                        "showing": "Mostrando",
                        "of": "de",
                        "rows": "registros",
                        "pages": "páginas"
                    }
                },
                "data": {
                    "loading": "Cargando...",
                    "error": "Error al cargar los datos"
                },
                "headerFilters": {
                    "default": "Filtrar columna..."
                }
            }
        },
        data: [],
        layout: "fitColumns",
        pagination: true,
        paginationElement: paginacion,
        paginationSize: 10,
        paginationSizeSelector: [10, 25, 50, 100],
        movableColumns: true,
        columns: [
            { title: "ID", field: "idrecibos", visible: false },
            { title: "N° Recibo", field: "nrecibo" },
            { title: "N° Medidor", field: "nmedidor" },
            { title: "DUI", field: "dui" },
            { title: "Socio", field: "nombre" },
            { title: "Periodo", field: "periodo" },
            { title: "Consumo m3", field: "metros", hozAlign: "center" },
            { title: "Monto ($)", field: "monto", hozAlign: "right" },
            {
                title: "Acciones", field: "acciones", hozAlign: "center", headerSort: false,
                formatter: function () {
                    return `
                    <button type="button" class="btn btn-outline-secondary btn-pagar">
                        <i class="bi bi-currency-dollar" aria-hidden="true"> </i>
                    </button>
                    `;
                },
                cellClick: function (e, cell) {
                    const registro = cell.getRow().getData();

                    if (e.target.closest(".btn-pagar")) {
                        location.href = "pago.php?id=" + registro.idrecibos;
                    }


                }
            }
        ]
    });



    function actualizarContador() {
        const total = tabla.getDataCount("active");
        contador.textContent = `Total de Registros: ${total}`;
    }

    tabla.on("dataLoaded", actualizarContador);
    tabla.on("dataFiltered", actualizarContador);
    tabla.on("dataChanged", actualizarContador);
    tabla.on("dataFiltered", function (filters, filas) {
        contador.textContent = `Total de Registros: ${filas.length}`;
    });

    tabla.on("tableBuilt", function () {
        document.addEventListener("change", function (e) {
            if (e.target.id === "seleccionar-todos") {
                const filas = tabla.getRows("active");
                if (e.target.checked) {
                    filas.forEach(fila => fila.select());
                } else {
                    filas.forEach(fila => fila.deselect());
                }
            }
        });
    });

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

        const respuesta = await fetch("DAOBuscar.php", {
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
        console.error("Error al cargar registros:", error);
        contenedor.textContent = "No se pudieron cargar los registros.";
    }

    if (filtro) {
        filtro.addEventListener("input", () => {

            // Limpiar registros seleccionados
            tabla.deselectRow();

            // Desmarcar seleccionar todos
            const seleccionarTodos = document.getElementById("seleccionar-todos");

            if (seleccionarTodos) {
                seleccionarTodos.checked = false;
            }

            const valor = filtro.value.trim();

            if (!valor) {
                tabla.clearFilter();
                return;
            }

            tabla.setFilter([
                [
                    { field: "nmedidor", type: "like", value: valor },
                    { field: "dui", type: "like", value: valor },
                    { field: "nombre", type: "like", value: valor },
                    { field: "periodo", type: "like", value: valor },
                    { field: "metros", type: "like", value: valor },
                    { field: "monto", type: "like", value: valor },
                    { field: "nrecibo", type: "like", value: valor }
                ]
            ]);

        });
    }

});
