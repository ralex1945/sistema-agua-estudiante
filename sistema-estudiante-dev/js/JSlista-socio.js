document.addEventListener("DOMContentLoaded", async () => {
    const contenedor = document.getElementById("socios-table");
    const filtro = document.getElementById("table-filter");
    const paginacion = document.getElementById("table-pagination");
    const btnImprimir = document.getElementById("print-table");
    const contador = document.getElementById("total-registros");

    if (!contenedor) return;

    const tabla = new Tabulator(contenedor, {
        locale: "es-es",
        data: [],
        layout: "fitColumns",
        pagination: true,
        paginationElement: paginacion,
        paginationSize: 10,
        paginationSizeSelector: [10, 25, 50, 100],
        movableColumns: true,
        columns: [
            { title: "ID", field: "idsocio", width: 70 },
            { title: "DUI", field: "dui" },
            { title: "Nombre", field: "nombre" },
            { title: "Apellido", field: "apellido" },
            { title: "Teléfono", field: "telefono" },
            { title: "Cantón", field: "canton" },
            { title: "Estado", field: "estado" },
            {
                title: "Acciones", field: "acciones", hozAlign: "center", headerSort: false,
                formatter: function () {
                    return `
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-editar">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar">
                        <i class="bi bi-trash"></i>
                    </button>
                    `;
                },
                cellClick: function (e, cell) {
                    const socio = cell.getRow().getData();

                    if (e.target.closest(".btn-editar")) {
                        location.href = "socio.php?id=" + socio.idsocio;
                    }

                    if (e.target.closest(".btn-eliminar")) {
                        Swal.fire({
                            title: "Poseidón",
                            text: "¿Desea eliminar al socio " + socio.nombre + " " + socio.apellido + "?",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Sí, eliminar",
                            cancelButtonText: "Cancelar"
                        }).then(async (result) => {
                            if (result.isConfirmed) {
                                try {
                                    const datos = new FormData();
                                    datos.append("idsocio", socio.idsocio);
                                    datos.append("opcion", "eliminar");

                                    const respuesta = await fetch("DAOSocio.php", {
                                        method: "POST",
                                        body: datos
                                    });

                                    const res = await respuesta.json();
                                    if (res.estado) {
                                        Swal.fire({
                                            position: "top-end",
                                            icon: "success",
                                            title: res.mensaje,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        cargarSocios();
                                    } else {
                                        Swal.fire("Error", res.mensaje, "error");
                                    }
                                } catch (err) {
                                    Swal.fire("Error", err.message, "error");
                                }
                            }
                        });
                    }
                }
            }
        ]
    });

    async function cargarSocios() {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultar");

            const respuesta = await fetch("DAOSocio.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) return;

            const datosSocios = await respuesta.json();
            tabla.setData(datosSocios);
            if (contador) {
                contador.textContent = "Total de Registros: " + datosSocios.length;
            }
        } catch (error) {
            console.error(error);
        }
    }

    if (filtro) {
        filtro.addEventListener("keyup", function () {
            const valor = filtro.value.trim().toLowerCase();
            tabla.setFilter(function (data) {
                return (
                    (data.dui && data.dui.toLowerCase().includes(valor)) ||
                    (data.nombre && data.nombre.toLowerCase().includes(valor)) ||
                    (data.apellido && data.apellido.toLowerCase().includes(valor)) ||
                    (data.telefono && data.telefono.toLowerCase().includes(valor)) ||
                    (data.canton && data.canton.toLowerCase().includes(valor))
                );
            });
        });
    }

    if (btnImprimir) {
        btnImprimir.addEventListener("click", () => tabla.print(false, true));
    }

    cargarSocios();
});
