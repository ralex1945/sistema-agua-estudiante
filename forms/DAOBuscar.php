<?php

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT re.nrecibo,
            so.dui,
            concat(so.nombre,' ',so.apellido) AS nombre,
            re.idrecibos,
            me.nmedidor,
            CONCAT(co.mes, '-', co.anio) AS periodo,
            re.metros,
            re.monto
        FROM recibos AS re
        INNER JOIN consumo AS co
            ON re.idconsumo=co.idconsumo
        INNER JOIN medidor AS me
            ON me.idmedidor=co.idmedidor
        INNER JOIN contrato AS con
            ON con.idcontrato=me.idcontrato
        INNER JOIN socio AS so
            ON so.idsocio=con.idsocio
        where re.estado='PENDIENTE'";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $registros = [];
        $correlativo = 1;

        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = [
                'nrecibo'           => $fila['nrecibo'],
                'nmedidor'          => $fila['nmedidor'],
                'dui'               => $fila['dui'],
                'idrecibos'         => $fila['idrecibos'],
                'periodo'           => $fila['periodo'],
                'metros'            => $fila['metros'],
                'monto'             => "$" . number_format($fila['monto'], 2, ".", ","),
                'nombre'            => $fila['nombre']
            ];
        }
        echo json_encode(
            $registros,
            JSON_UNESCAPED_UNICODE
        );

        $resultado->free();
        $stmt->close();
    } catch (Throwable $e) {
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
    $conexion->close();
}
