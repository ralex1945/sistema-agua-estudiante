<?php
session_start();
//recibir el idsocio con respecto al dui

$nrecibo = $_POST['nrecibo'] ?? '';
$idrecibos = $_POST['idrecibos'] ?? '';
$monto = $_POST['monto'] ?? ''; //es lo que he recibido de pagos
$recibido = $_POST['recibido'] ?? ''; //4
$pagado = $_POST['pagado'] ?? ''; //0
$fecha = $_POST['fecha'] ?? '';
$empleado = $_POST['empleado'] ?? '';

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";
    ///se actualiza recibos y consumo en estado

    $fechaReal = date("Y-m-d H:i:s");

    try {

        // Validar datos recibidos
        if (
            empty($idrecibos) || empty($fecha) || empty($empleado) || !isset($monto)
        ) {
            throw new Exception("Faltan datos para registrar el pago");
        }

        $idrecibos = (int) $idrecibos;
        $empleado = (int) $empleado;
        //$monto = (float) str_replace(['$', ','], '', $monto);
        $recibido = (float) str_replace(['$', ','], '', $recibido);
        $pagado = (float) str_replace(['$', ','], '', $pagado);

        /////////////////////////


        /////////////////////////

        if ($recibido <= 0) {
            throw new Exception("El monto del abono debe ser mayor a cero");
        }

        // INICIAR TRANSACCIÓN
        $conexion->begin_transaction();

        // 1. OBTENER RECIBO Y BLOQUEARLO
        $sql = "SELECT monto, pagado FROM recibos WHERE idrecibos = ? FOR UPDATE";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idrecibos);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $recibo = $resultado->fetch_assoc();

        $stmt->close();

        if (!$recibo) {
            throw new Exception("No existe el recibo seleccionado");
        }

        $monto = (float) $recibo['monto'];
        $pagadoAnterior = (float) $recibo['pagado'];

        // Convertir a centavos
        $montoCentavos = (int) round($monto * 100);
        $recibidoCentavos = (int) round($recibido * 100);
        $pagadoAnteriorCentavos = (int) round($pagadoAnterior * 100);

        // 2. CALCULAR SALDO PENDIENTE
        $saldoAnterior = $montoCentavos - $pagadoAnteriorCentavos; //4.2-0=4.2

        if ($saldoAnterior <= 0) {
            throw new Exception("Este recibo ya se encuentra PAGADO");
        }

        // Evitar abonos superiores al saldo
        if ($recibido > $saldoAnterior) {
            throw new Exception(
                "El abono supera el saldo pendiente. Saldo disponible: $" .
                    number_format($saldoAnterior, 2, '.', '')
            );
        }

        // 3. REGISTRAR ABONO
        $sql = "INSERT INTO pagos
            (idrecibos, fecha, idempleado, monto, fechareal)
            VALUES (?, ?, ?, ?, ?)";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "isids",
            $idrecibos,
            $fecha,
            $empleado,
            $recibido,
            $fechaReal
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al registrar el pago");
        }

        $stmt->close();

        // 4. SUMAR TODOS LOS ABONOS
        $sql = "SELECT COALESCE(SUM(monto), 0) AS total_pagado
            FROM pagos
            WHERE idrecibos = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idrecibos);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();

        $stmt->close();

        $totalPagado = (float) $fila['total_pagado'];

        // 5. CALCULAR SALDO
        $saldo = max(0, $monto - $totalPagado);

        // 6. DETERMINAR ESTADO
        $estado = ($saldo <= 0.001) ? "PAGADO" : "PENDIENTE";

        // 7. ACTUALIZAR RECIBO
        $sql = "UPDATE recibos
            SET pagado = ?, estado = ?
            WHERE idrecibos = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "dsi",
            $totalPagado,
            $estado,
            $idrecibos
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el recibo");
        }

        $stmt->close();

        // 8. ACTUALIZAR ESTADO DEL CONSUMO
        $sql = "UPDATE consumo c
            INNER JOIN recibos r
                ON c.idconsumo = r.idconsumo
            SET c.estado = ?
            WHERE r.idrecibos = ?";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "si",
            $estado,
            $idrecibos
        );

        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el consumo");
        }

        $stmt->close();

        // 9. CONFIRMAR TRANSACCIÓN
        $conexion->commit();

        // 10. RESPUESTA JSON
        echo json_encode([
            "estado" => true,
            "mensaje" => "Abono registrado correctamente"
        ]);
    } catch (Throwable $e) {

        // CANCELAR TODAS LAS OPERACIONES
        $conexion->rollback();

        echo json_encode([
            "estado" => false,
            "mensaje" => $e->getMessage()
        ]);
    }
}
if ($opcion == "consultarRECIBO") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT concat(so.nombre,' ',so.apellido) AS nombre,
            re.idrecibos,
            me.nmedidor,
            CONCAT(co.mes, '-', co.anio) AS periodo,
            re.metros,
            re.monto,
            re.pagado,
            re.estado
        FROM recibos AS re
        INNER JOIN consumo AS co
            ON re.idconsumo=co.idconsumo
        INNER JOIN medidor AS me
            ON me.idmedidor=co.idmedidor
        INNER JOIN contrato AS con
            ON con.idcontrato=me.idcontrato
        INNER JOIN socio AS so
            ON so.idsocio=con.idsocio
        where re.nrecibo = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $nrecibo);

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            echo json_encode([
                "estado" => true,
                "datos" => [
                    'idrecibos' => $fila['idrecibos'],
                    'nombre' => $fila['nombre'],
                    'estado' => $fila['estado'],
                    'nmedidor' => $fila['nmedidor'],
                    'periodo' => $fila['periodo'],
                    'monto'             => number_format($fila['monto'], 2, ".", ","),
                    'pagado'            => number_format($fila['pagado'], 2, ".", ","),
                    'pendiente'         => number_format($fila['monto'] - $fila['pagado'], 2, ".", ",")
                ]
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "datos" => "No Existe"
            ]);
        }

        $stmt->close();
        $conexion->close();
    } catch (Throwable $e) {

        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
}

if ($opcion == "consultarPorId") {

    require_once "../config/conexion.php";

    try {
        $sql = "SELECT concat(so.nombre,' ',so.apellido) AS nombre,
            re.idrecibos,
            re.nrecibo,
            me.nmedidor,
            CONCAT(co.mes, '-', co.anio) AS periodo,
            re.metros,
            re.monto,
            re.pagado,
            re.estado
        FROM recibos AS re
        INNER JOIN consumo AS co
            ON re.idconsumo=co.idconsumo
        INNER JOIN medidor AS me
            ON me.idmedidor=co.idmedidor
        INNER JOIN contrato AS con
            ON con.idcontrato=me.idcontrato
        INNER JOIN socio AS so
            ON so.idsocio=con.idsocio
        where re.idrecibos = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idrecibos);

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            echo json_encode([
                "estado" => true,
                "datos" => [
                    'idrecibos' => $fila['idrecibos'],
                    'nrecibo' => $fila['nrecibo'],
                    'nombre' => $fila['nombre'],
                    'estado' => $fila['estado'],
                    'nmedidor' => $fila['nmedidor'],
                    'periodo' => $fila['periodo'],
                    'monto'             => number_format($fila['monto'], 2, ".", ","),
                    'pagado'            => number_format($fila['pagado'], 2, ".", ","),
                    'pendiente'         => number_format($fila['monto'] - $fila['pagado'], 2, ".", ",")
                ]
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "datos" => "No Existe"
            ]);
        }

        $stmt->close();
        $conexion->close();
    } catch (Throwable $e) {

        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
}
