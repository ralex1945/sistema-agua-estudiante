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
            { title: "ID", field: "idcontrato", visible: false },
            { title: "N° Contrato", field: "ncontrato", hozAlign: "center", width: 130 },
            { title: "Fecha", field: "fecha", hozAlign: "center" },
            { title: "Monto ($)", field: "monto", hozAlign: "right" },
            { title: "Prima ($)", field: "prima", hozAlign: "right", visible: false },
            { title: "ID Socio", field: "idsocio", visible: false },
            { title: "Socio", field: "socio" },
            { title: "Empleado 1", field: "empleado1" },
            { title: "Cargo 1", field: "cargo1" },
            { title: "Empleado 2", field: "empleado2" },
            { title: "Cargo 2", field: "cargo2" },
            {
                title: "Acciones", field: "acciones", hozAlign: "center", headerSort: false,
                formatter: function () {
                    return `
                    <button type="button" class="btn btn-outline-secondary btn-editar" title="Editar">
                        <i class="bi bi-pencil" aria-hidden="true"> </i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-eliminar" title="Eliminar">
                        <i class="bi bi-trash" aria-hidden="true"> </i>
                    </button>
                    `;
                },
                cellClick: function (e, cell) {
                    const contrato = cell.getRow().getData();

                    if (e.target.closest(".btn-editar")) {
                        location.href = "contrato.php?id=" + contrato.idcontrato;
                    }

                    if (e.target.closest(".btn-eliminar")) {
                        Swal.fire({
                            title: "Poseidon",
                            text: "Confirmar Eliminacion de Registro?",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Si, Confirmo"
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                try {
                                    const datos = new FormData();
                                    datos.append("idcontrato", contrato.idcontrato);
                                    datos.append("opcion", "eliminar");

                                    const respuesta = await fetch("DAOContrato.php", {
                                        method: "POST",
                                        body: datos
                                    });

                                    if (!respuesta.ok) {
                                        throw new Error("Error de Comunicacion " + respuesta.status);
                                    }

                                    const resultado = await respuesta.json();

                                    if (resultado.estado) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "success",
                                            title: "Poseidon",
                                            text: resultado.mensaje,
                                            showConfirmButton: false,
                                            timer: 1500
                                        }).then(() => {
                                            window.location.href = "lista-contratos.php";
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: "error",
                                            title: "Poseidon",
                                            text: resultado.mensaje || "Error al eliminar"
                                        });
                                    }
                                } catch (error) {
                                    console.error(error);
                                }
                            }
                        });
                    }
                }
            }
        ]
    });

    function actualizarContador() {
        const total = tabla.getDataCount("active");
        if (contador) {
            contador.textContent = `Total de Contratos: ${total}`;
        }
    }

    tabla.on("dataLoaded", actualizarContador);
    tabla.on("dataFiltered", function (filters, filas) {
        if (contador) {
            contador.textContent = `Total de Contratos: ${filas.length}`;
        }
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

        const respuesta = await fetch("DAOContrato.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const contratos = await respuesta.json();
        Swal.close();

        if (!Array.isArray(contratos)) {
            throw new Error(contratos.mensaje || "Respuesta no válida");
        }

        await tabla.replaceData(contratos);
        actualizarContador();

    } catch (error) {
        Swal.close();
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
                    { field: "ncontrato", type: "like", value: valor },
                    { field: "fecha", type: "like", value: valor },
                    { field: "monto", type: "like", value: valor },
                    { field: "socio", type: "like", value: valor },
                    { field: "empleado1", type: "like", value: valor },
                    { field: "cargo1", type: "like", value: valor },
                    { field: "empleado2", type: "like", value: valor },
                    { field: "cargo2", type: "like", value: valor }
                ]
            ]);
        });
    }

    btnImprimir?.addEventListener("click", () => {
        window.print();
    });
});

