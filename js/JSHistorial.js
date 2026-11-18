
//////////PROCESA AL TERMINAR DE CARGAR LA PAGINA
document.addEventListener("DOMContentLoaded", async () => {
    const contenedor = document.getElementById("users-table");
    const filtro = document.getElementById("table-filter");
    const paginacion = document.getElementById("table-pagination");
    const btnImprimir = document.getElementById("print-table");

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
            { title: "Corr.", field: "correlativo", visible: true },
            { title: "idconsumo", field: "idconsumo", visible: false },
            { title: "Periodo", field: "periodo", hozAlign: "center" },
            { title: "F. Lectura", field: "fecha_lectura", hozAlign: "center" },
            { title: "F. Vencimiento", field: "fecha_vencimiento", hozAlign: "center" },
            { title: "Lectura Actual", field: "lectura_actual", hozAlign: "center" },
            { title: "Lectura Ant.", field: "lectura_anterior", hozAlign: "center" },
            { title: "Consumo m3", field: "metros", hozAlign: "center" },
            { title: "Monto ($)", field: "monto", hozAlign: "right" },
            {
                title: "Estado", field: "estado", hozAlign: "center", headerSort: false,
                formatter: function (cell) {
                    const pagado = cell.getValue();

                    if (pagado === "PAGADO") {
                        return `<span style="color: green; font-size: 20px;" title="PAGADO">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>`;
                    } else {
                        if (pagado === "PENDIENTE") {
                            return `<span style="color: red; font-size: 20px;" title="PENDIENTE">
                                <i class="bi bi-x-circle-fill"></i>
                            </span>`;
                        }
                    }
                }
            },

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


    try {
        Swal.fire({
            title: "Cargando...",
            text: "Espere",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        const parametros = new URLSearchParams(window.location.search);
        const idmedidor = parametros.get("id");

        const datos = new FormData();
        datos.append("opcion", "consultar");
        datos.append("idmedidor", idmedidor);

        const respuesta = await fetch("DAOHistorial.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const consumo = await respuesta.json();
        //console.log(consumo.numero_medidor);
        document.getElementById("numero").textContent = consumo[0]?.numero_medidor ?? "";
        Swal.close();

        if (!Array.isArray(consumo)) {
            throw new Error(consumo.error || "Respuesta no válida");
        }

        tabla.replaceData(consumo);
        actualizarContador();

    } catch (error) {
        console.error("Error al cargar registros:", error);
        contenedor.textContent = "No se pudieron cargar los registros.";
    }

    if (filtro) {
        filtro.addEventListener("input", () => {
            const valor = filtro.value.trim();

            if (!valor) {
                tabla.clearFilter();
                return;
            }

            tabla.setFilter([
                [
                    { field: "periodo", type: "like", value: valor },
                    { field: "fecha_lectura", type: "like", value: valor },
                    { field: "fecha_vencimiento", type: "like", value: valor },
                    { field: "lectura_actual", type: "like", value: valor },
                    { field: "lectura_anterior", type: "like", value: valor },
                    { field: "metros", type: "like", value: valor },
                    { field: "monto", type: "like", value: valor },
                    { field: "estado", type: "like", value: valor }
                ]
            ]);
        });
    }

    btnImprimir?.addEventListener("click", () => {
        alert("Filtro: " + filtro.value);
    });
});
