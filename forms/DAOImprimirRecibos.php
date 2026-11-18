<?php
ini_set('display_errors', 0);

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "consultar") {
    require_once "../config/conexion.php";
    try {
        $sql = "SELECT re.nrecibo,ca.nombre,
        ru.nombre AS nombreRuta,
        re.idrecibo AS idrecibos,
        me.nmedidor,
        CONCAT(co.mes, '-', co.anio) AS periodo,
        co.fecha_lectura,
        co.fecha_vencimiento,
        co.lectura_actual,
        co.lectura_anterior,
        (co.lectura_actual - co.lectura_anterior) AS metros,
        re.monto
FROM recibos AS re
INNER JOIN consumo AS co
    ON re.idconsumo=co.idconsumo
INNER JOIN medidor AS me
    ON me.idmedidor=co.idmedidor
INNER JOIN ruta AS ru
    ON ru.idruta=me.idruta
INNER JOIN canton AS ca
    ON ca.idcanton=me.idcanton
where re.estado='PENDIENTE'";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $registros = [];
        $correlativo = 1;

        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = [
                'idrecibos'         => $fila['idrecibos'],
                'nrecibo'           => $fila['nrecibo'],
                'nmedidor'          => $fila['nmedidor'],
                'nombreCanton'      => $fila['nombre'],
                'nombreRuta'        => $fila['nombreRuta'],
                'periodo'           => $fila['periodo'],
                'fecha_lectura'     => $fila['fecha_lectura'],
                'fecha_vencimiento' => $fila['fecha_vencimiento'],
                'lectura_actual'    => $fila['lectura_actual'],
                'lectura_anterior'  => $fila['lectura_anterior'],
                'metros'            => $fila['metros'],
                'monto'             => "$" . number_format((float)$fila['monto'], 2, ".", ","),
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
        ], JSON_UNESCAPED_UNICODE);
    }
}


if ($opcion == 'imprimir') {

    require_once "../config/conexion.php";

    try {
        $recibos = json_decode($_POST['recibos'], true);

        if (empty($recibos)) {
            throw new Exception("No se recibieron recibos.");
        }

        $informacion = [];

        foreach ($recibos as $item) {

            $idrecibos = (int)$item['idrecibos'];

            // Consultar información completa del recibo
            $sql = "SELECT concat(socio.nombre,' ',socio.apellido) as nombreSocio,
            medidor.direccion,
            recibos.nrecibo,
            canton.nombre AS nombreCanton,
            ruta.nombre AS nombreRuta,
            recibos.idrecibo AS idrecibos,
            medidor.nmedidor,
            CONCAT(consumo.mes, '-', consumo.anio) AS periodo,
            DATE_FORMAT(consumo.fecha_lectura, '%d/%m/%Y') AS fecha_lectura,
            DATE_FORMAT(consumo.fecha_vencimiento, '%d/%m/%Y') as fecha_vencimiento,
            consumo.lectura_actual,
            consumo.lectura_anterior,
            (consumo.lectura_actual - consumo.lectura_anterior) AS metros,
            recibos.monto,
            recibos.precio_unitario
FROM recibos
INNER JOIN consumo
    ON recibos.idconsumo=consumo.idconsumo
INNER JOIN medidor
    ON medidor.idmedidor=consumo.idmedidor
INNER JOIN ruta
    ON ruta.idruta=medidor.idruta
INNER JOIN canton
    ON canton.idcanton=medidor.idcanton
INNER JOIN contrato
    ON medidor.idcontrato=contrato.idcontrato
INNER JOIN socio AS socio
    ON contrato.idsocio=socio.idsocio
where recibos.estado='PENDIENTE' AND recibos.idrecibo = ?";

            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $idrecibos);
            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($fila = $resultado->fetch_assoc()) {
                $informacion[] = $fila;
            }

            $stmt->close();
        }

        echo json_encode([
            "estado" => true,
            "mensaje" => "Recibos consultados correctamente.",
            "recibos" => $informacion
        ]);
    } catch (Exception $e) {

        echo json_encode([
            "estado" => false,
            "mensaje" => $e->getMessage()
        ]);
    }
}
