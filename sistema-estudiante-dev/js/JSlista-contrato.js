document.addEventListener("DOMContentLoaded", async () => {
    const contenedor = document.getElementById("contratos-table");
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
            { title: "ID", field: "idcontrato", width: 60 },
            { title: "N° Contrato", field: "numero_contrato" },
            { title: "Socio", field: "socioNombre" },
            { title: "Ruta", field: "rutaNombre" },
            { title: "Medidor", field: "medidor" },
            { title: "Servicio", field: "tipo_servicio" },
            { title: "Fecha", field: "fecha_contrato" },
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
                    const contrato = cell.getRow().getData();

                    if (e.target.closest(".btn-editar")) {
                        location.href = "contrato.php?id=" + contrato.idcontrato;
                    }

                    if (e.target.closest(".btn-eliminar")) {
                        Swal.fire({
                            title: "Poseidón",
                            text: "¿Desea eliminar el contrato " + contrato.numero_contrato + "?",
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
                                    datos.append("idcontrato", contrato.idcontrato);
                                    datos.append("opcion", "eliminar");

                                    const respuesta = await fetch("DAOContrato.php", {
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
                                        cargarContratos();
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

    async function cargarContratos() {
        try {
            const datos = new FormData();
            datos.append("opcion", "consultar");

            const respuesta = await fetch("DAOContrato.php", {
                method: "POST",
                body: datos
            });

            if (!respuesta.ok) return;

            const datosContratos = await respuesta.json();
            tabla.setData(datosContratos);
            if (contador) {
                contador.textContent = "Total de Registros: " + datosContratos.length;
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
                    (data.numero_contrato && data.numero_contrato.toLowerCase().includes(valor)) ||
                    (data.socioNombre && data.socioNombre.toLowerCase().includes(valor)) ||
                    (data.medidor && data.medidor.toLowerCase().includes(valor)) ||
                    (data.tipo_servicio && data.tipo_servicio.toLowerCase().includes(valor))
                );
            });
        });
    }

    if (btnImprimir) {
        btnImprimir.addEventListener("click", () => tabla.print(false, true));
    }

    cargarContratos();
});
