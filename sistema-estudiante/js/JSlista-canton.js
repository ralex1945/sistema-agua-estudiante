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
        paginationSizeSelector: [5, 10, 25, 50, 100],
        movableColumns: true,
        columns: [
            { title: "ID", field: "idcanton", width: 90, hozAlign: "center" },
            { title: "Nombre del Cantón", field: "nombre" },
            {
                title: "Acciones",
                field: "acciones",
                hozAlign: "center",
                headerSort: false,
                width: 140,
                formatter: function () {
                    return `
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-editar" title="Editar">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar" title="Eliminar">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </button>
                    `;
                },
                cellClick: function (e, cell) {
                    const canton = cell.getRow().getData();

                    if (e.target.closest(".btn-editar")) {
                        location.href = "canton.php?id=" + canton.idcanton;
                    }

                    if (e.target.closest(".btn-eliminar")) {
                        Swal.fire({
                            title: "Poseidón",
                            text: "¿Confirmar eliminación del cantón \"" + canton.nombre + "\"?",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Sí, confirmar",
                            cancelButtonText: "Cancelar"
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                try {
                                    const datos = new FormData();
                                    datos.append("idcanton", canton.idcanton);
                                    datos.append("opcion", "eliminar");

                                    const respuesta = await fetch("DAOCanton.php", {
                                        method: "POST",
                                        body: datos
                                    });

                                    if (!respuesta.ok) {
                                        throw new Error("Error de comunicación: " + respuesta.status);
                                    }

                                    const res = await respuesta.json();
                                    if (res.estado) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "success",
                                            title: "Poseidón",
                                            text: res.mensaje,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        cargarDatos();
                                    } else {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "error",
                                            title: "Poseidón",
                                            text: res.mensaje,
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                    }
                                } catch (error) {
                                    console.error("Error al eliminar cantón:", error);
                                    Swal.fire({
                                        position: "top-end",
                                        icon: "error",
                                        title: "Error",
                                        text: "Error al comunicarse con el servidor",
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                }
                            }
                        });
                    }
                }
            }
        ]
    });

    async function cargarDatos() {
        try {
            const formData = new FormData();
            formData.append("opcion", "consultar");

            const respuesta = await fetch("DAOCanton.php", {
                method: "POST",
                body: formData
            });

            if (!respuesta.ok) {
                throw new Error("Error al obtener los datos");
            }

            const datos = await respuesta.json();
            tabla.setData(Array.isArray(datos) ? datos : []);

            if (contador) {
                contador.textContent = "Total de Registros: " + (Array.isArray(datos) ? datos.length : 0);
            }
        } catch (error) {
            console.error("Error al cargar cantones:", error);
            tabla.setData([]);
            if (contador) contador.textContent = "Total de Registros: 0";
        }
    }

    if (filtro) {
        filtro.addEventListener("input", () => {
            const valor = filtro.value.trim().toLowerCase();
            if (valor === "") {
                tabla.clearFilter();
            } else {
                tabla.setFilter((data) => {
                    return (
                        (data.nombre && data.nombre.toLowerCase().includes(valor)) ||
                        (data.idcanton && String(data.idcanton).includes(valor))
                    );
                });
            }
        });
    }

    if (btnImprimir) {
        btnImprimir.addEventListener("click", () => {
            tabla.print(false, true);
        });
    }

    await cargarDatos();
});
