<?php
ini_set('display_errors', 0);

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "consultar") {
    require_once "../config/conexion.php";
    try {
        $sql = "SELECT 
        ca.nombre,
        ru.nombre AS nombreRuta,
        c.idconsumo,
        m.nmedidor,
        CONCAT(c.mes, '-', c.anio) AS periodo,
        c.fecha_lectura,
        c.fecha_vencimiento,
        c.lectura_actual,
        c.lectura_anterior,

        (c.lectura_actual - c.lectura_anterior) AS metros,

        t.precio AS precio_unitario,

        CASE
            WHEN t.tipo_cobro = 'FIJO' THEN t.precio
            WHEN t.tipo_cobro = 'POR_METRO' THEN
                (c.lectura_actual - c.lectura_anterior) * t.precio
        END AS monto,

        t.tipo_cobro,
        c.estado

    FROM medidor AS m

    INNER JOIN canton AS ca
        ON ca.idcanton = m.idcanton

    INNER JOIN ruta AS ru
        ON ru.idruta = m.idruta

    LEFT JOIN consumo AS c
        ON c.idmedidor = m.idmedidor

    LEFT JOIN tarifa AS t
        ON t.idtarifa = (
            SELECT t2.idtarifa
            FROM tarifa AS t2
            WHERE (c.lectura_actual - c.lectura_anterior)
                BETWEEN t2.desde AND t2.hasta
            ORDER BY t2.desde DESC
            LIMIT 1
        )

    WHERE NOT EXISTS (
        SELECT 1
        FROM recibos AS r
        WHERE r.idconsumo = c.idconsumo
    );";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $registros = [];
        $correlativo = 1;

        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = [
                'idconsumo'         => $fila['idconsumo'],
                'nmedidor'          => $fila['nmedidor'],
                'nombreCanton'      => $fila['nombre'],
                'nombreRuta'        => $fila['nombreRuta'],
                'periodo'           => $fila['periodo'],
                'fecha_lectura'     => $fila['fecha_lectura'],
                'fecha_vencimiento' => $fila['fecha_vencimiento'],
                'lectura_actual'    => $fila['lectura_actual'],
                'lectura_anterior'  => $fila['lectura_anterior'],
                'metros'            => $fila['metros'],
                'estado'            => $fila['estado'],
                'precio_unitario'   => "$" . number_format((float)$fila['precio_unitario'], 2, ".", ","),
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
        ]);
    }
}

if ($opcion == 'generar') {

    require_once "../config/conexion.php";
    //header('Content-Type: application/json; charset=utf-8');

    $recibos = json_decode($_POST['recibos'] ?? '', true);

    if (!is_array($recibos) || empty($recibos)) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "No hay recibos seleccionados"
        ]);
        exit;
    }

    $fechaGeneracion = date("Y-m-d H:i:s");
    $resultado = [];

    try {

        // Iniciar transacción
        $conexion->begin_transaction();

        // Bloquear correlativo
        $sql = "SELECT prefijo, ultimo
                FROM tiraje
                WHERE estado = 'ACTIVO'
                FOR UPDATE";

        $consulta = $conexion->query($sql);

        // Comprobar que exista un único tiraje activo
        if ($consulta->num_rows != 1) {
            throw new Exception(
                "Debe existir exactamente un tiraje activo"
            );
        }

        $fila = $consulta->fetch_assoc();

        $nuevoNumero = (int)$fila['ultimo'];
        $prefijo = $fila['prefijo'];

        // Preparar INSERT una sola vez
        $sql = "INSERT INTO recibos
                (idconsumo, nrecibo, fecha_generacion,
                 metros, precio_unitario, monto, pagado, pendiente, estado)
                VALUES (?, ?, ?, ?, ? ,?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        foreach ($recibos as $item) {

            $idconsumo = (int)$item['idconsumo'];
            $metros = (float)$item['metros'];
            //$precio_unitario = (float)$item['precio_unitario'];

            $monto = (float) str_replace(['$', ','], '', $item['monto']); //Elimina $ y comas de los miles
            $precio_unitario = (float) str_replace(['$', ','], '', $item['precio_unitario']); //Elimina $ y comas de los miles

            $pagado = 0;
            $estado = "PENDIENTE";

            // Incrementar número por cada recibo
            $nuevoNumero++; //23->00023

            // Formato 00001
            $numeroFormateado = str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);

            $nrecibo = $prefijo . "-" . $numeroFormateado;

            $stmt->bind_param("issidddds", $idconsumo, $nrecibo, $fechaGeneracion, $metros, $precio_unitario, $monto, $pagado, $monto, $estado);

            if (!$stmt->execute()) {
                throw new Exception(
                    "Error al generar recibo: " . $nrecibo
                );
            }

            $resultado[] = [
                "idconsumo" => $idconsumo,
                "nrecibo" => $nrecibo,
                "metros" => $metros,
                "monto" => $monto
            ];
        }

        $stmt->close();

        // Actualizar último número generado
        $sql = "UPDATE tiraje
                SET ultimo = ?
                WHERE estado = 'ACTIVO'";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $nuevoNumero);
        $stmt->execute();
        $stmt->close();

        // Confirmar transacción
        $conexion->commit();

        echo json_encode([
            "estado" => true,
            "mensaje" => "Recibos generados satisfactoriamente. Total Generados: " . count($resultado),
            "total" => count($resultado),
            "datos" => $resultado
        ]);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode([
            "estado" => false,
            "mensaje" => $e->getMessage()
        ]);
    } finally {
        $conexion->close();
    }
}
