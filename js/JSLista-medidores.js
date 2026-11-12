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
            { title: "Corr.", field: "correlativo" },
            { title: "ID", field: "idmedidor", visible: false },
            { title: "DUI", field: "dui" },
            { title: "Nombre", field: "nombre" },
            { title: "Apellido", field: "apellido" },
            { title: "N. Contrato", field: "ncontrato" },
            { title: "N° Medidor", field: "nmedidor" },
            { title: "Ruta", field: "ruta" },
            {
                title: "Estado", field: "estado", hozAlign: "center",
                formatter: function (cell) {
                    const estado = (cell.getValue() || "").toUpperCase();
                    if (estado === "ACTIVO") {
                        return `<span style="color: #198754; font-size: 1.25rem;" title="Activo">
                            <i class="bi bi-check-circle-fill"></i>
                        </span>`;
                    } else if (estado === "SUSPENDIDO") {
                        return `<span style="color: #ffc107; font-size: 1.25rem;" title="Suspendido">
                            <i class="bi bi-x-circle-fill"></i>
                        </span>`;
                    } else {
                        return `<span style="color: #dc3545; font-size: 1.25rem;" title="Inactivo">
                            <i class="bi bi-x-circle-fill"></i>
                        </span>`;
                    }
                }
            },
            {
                title: "Acciones", field: "acciones", hozAlign: "center", headerSort: false,
                formatter: function () {
                    return `
                        <select class="form-select form-select-sm accion-medidor">
                            <option value="">Seleccionar...</option>
                            <option value="historial">Historial</option>
                        </select>
                    `;
                },
                cellClick: function (e, cell) {
                    if (e.target.matches(".accion-medidor")) {
                        e.target.onchange = function () {
                            const accion = this.value;
                            const medidor = cell.getRow().getData();

                            if (accion === "modificar") {
                                location.href = "medidor.php?id=" + medidor.idcontrato;
                            }

                            if (accion === "eliminar") {
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
                                            datos.append("idmedidor", medidor.idmedidor);
                                            datos.append("opcion", "eliminar");

                                            const respuesta = await fetch("DAOMedidor.php", {
                                                method: "POST",
                                                body: datos
                                            });

                                            if (!respuesta.ok) {
                                                throw new Error("Error de Comunicacion: " + respuesta.status);
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
                                                    window.location.href = "lista-medidores.php";
                                                });
                                            } else {
                                                Swal.fire({
                                                    icon: "error",
                                                    title: "Error",
                                                    text: resultado.mensaje || "No se pudo eliminar el medidor"
                                                });
                                            }
                                        } catch (error) {
                                            console.error("Error al eliminar:", error);
                                        }
                                    }
                                });
                            }
                        };
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

        const respuesta = await fetch("DAOMedidor.php", {
            method: "POST",
            body: datos
        });

        if (!respuesta.ok) {
            throw new Error("Error HTTP: " + respuesta.status);
        }

        const registros = await respuesta.json();
        Swal.close();

        if (!Array.isArray(registros)) {
            throw new Error(registros.mensaje || "Respuesta no válida");
        }

        await tabla.replaceData(registros);
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
                    { field: "nmedidor", type: "like", value: valor },
                    { field: "ncontrato", type: "like", value: valor },
                    { field: "dui", type: "like", value: valor },
                    { field: "nombre", type: "like", value: valor },
                    { field: "apellido", type: "like", value: valor },
                    { field: "canton", type: "like", value: valor },
                    { field: "ruta", type: "like", value: valor },
                    { field: "estado", type: "like", value: valor }
                ]
            ]);
        });
    }

    btnImprimir?.addEventListener("click", () => {
        window.print();
    });
});

