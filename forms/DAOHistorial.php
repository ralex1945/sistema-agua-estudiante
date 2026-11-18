<?php

$idmedidor = $_POST['idmedidor'] ?? '';
$opcion = $_POST['opcion'] ?? '';

if ($opcion == "consultar") {
    require_once "../config/conexion.php";
    try {
        $sql = "SELECT
                m.nmedidor,
                CONCAT(c.mes, '-', c.anio) AS periodo,
                c.fecha_lectura,
                c.fecha_vencimiento,
                c.lectura_actual,
                c.lectura_anterior,
                (c.lectura_actual - c.lectura_anterior) AS metros,
                COALESCE(
                    (
                        SELECT
                            CASE
                                WHEN t.tipo_cobro = 'FIJO' THEN t.precio
                                WHEN t.tipo_cobro = 'POR_METRO' THEN
                                    (c.lectura_actual - c.lectura_anterior)
                                    * t.precio
                            END
                        FROM tarifa AS t
                        WHERE (c.lectura_actual - c.lectura_anterior)
                            BETWEEN t.desde AND t.hasta
                        ORDER BY t.desde DESC
                        LIMIT 1
                    ),
                    (
                        SELECT
                            CASE
                                WHEN t.tipo_cobro = 'FIJO' THEN t.precio
                                WHEN t.tipo_cobro = 'POR_METRO' THEN
                                    (c.lectura_actual - c.lectura_anterior)
                                    * t.precio
                            END
                        FROM tarifa AS t
                        ORDER BY t.desde DESC
                        LIMIT 1
                    )
                ) AS monto,
                c.estado
            FROM medidor AS m
            LEFT JOIN consumo AS c ON m.idmedidor = c.idmedidor
            WHERE m.idmedidor = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param("i", $idmedidor);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $registros = [];
        $correlativo = 1;

        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = [
                'numero_medidor'    => $fila['nmedidor'],
                'periodo'           => $fila['periodo'],
                'fecha_lectura'     => $fila['fecha_lectura'],
                'fecha_vencimiento' => $fila['fecha_vencimiento'],
                'lectura_actual'    => $fila['lectura_actual'],
                'lectura_anterior'  => $fila['lectura_anterior'],
                'metros'            => $fila['metros'],
                'estado'            => $fila['estado'],
                'monto'             => "$" . number_format($fila['monto'], 2, ".", ","),
                'correlativo'       => $correlativo++
            ];
        }



        echo json_encode(
            $registros,
            JSON_UNESCAPED_UNICODE
        );

        $stmt->close();
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ]);
    }
}
