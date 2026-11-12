<?php
//DAOLectura.php
ini_set('display_errors', 0);

$nmedidor = $_POST['nmedidor'] ?? '';

$idmedidor = $_POST['idmedidor'] ?? '';
$lecturaActual = $_POST['lecturaActual'] ?? '';
$lecturaAnterior = $_POST['lecturaAnterior'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$empleado = $_POST['empleado'] ?? '';
$fechaVencimiento = $_POST['fechaVencimiento'] ?? '';
$aviso = $_POST['aviso'] ?? '';
$anio = $_POST['anio'] ?? '';
$mes = $_POST['mes'] ?? '';

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $estado = "PENDIENTE";
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    //idconsumo idmedidor idempleado fecha_lectura lectura_anterior lectura_actual
    //fecha_vencimiento aviso mes anio

    try {
        $sql = "INSERT INTO consumo (idmedidor, idempleado, fecha_lectura, lectura_anterior, lectura_actual, fecha_vencimiento, aviso, mes, anio, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iisiissiis", $idmedidor, $empleado, $fecha, $lecturaAnterior, $lecturaActual, $fechaVencimiento, $aviso, $mes, $anio, $estado);
        if ($stmt->execute()) {
            echo json_encode([
                "estado" => true,
                "mensaje" => "Datos Guardados Correctamente"
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Error al guardar los datos"
            ]);
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) { //cuando el empleado ya tiene asignada la ruta, arroja una execption
        if ($e->getCode() == 1062) {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Este medidor YA tiene registrado este PERIODO"
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Ocurrió un error al guardar"
            ]);
        }
    } finally {
        $conexion->close();
    }
}

if ($opcion == "consultarConsumo") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT m.idmedidor, concat(s.nombre,' ', s.apellido) AS nombre, m.nmedidor, m.estado, co.fecha_lectura, IF(co.lectura_anterior IS NULL, m.lectura_inicial, co.lectura_actual) AS lectura_anterior, ca.nombre as canton, ru.nombre as ruta, m.direccion
        FROM contrato AS c
        INNER JOIN socio AS s
            ON s.idsocio = c.idsocio
        INNER JOIN medidor AS m
            ON m.idcontrato = c.idcontrato
        INNER JOIN canton AS ca
            ON ca.idcanton=m.idcanton
        INNER JOIN ruta AS ru
            ON ru.idruta=m.idruta
        LEFT JOIN consumo AS co
            ON co.idmedidor=m.idmedidor
        where m.nmedidor=?
        ORDER BY co.idconsumo DESC LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $nmedidor);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            echo json_encode([
                "estado" => true,
                "datos" => [
                    'idmedidor' => $fila['idmedidor'],
                    'nombre' => $fila['nombre'],
                    'fecha_lectura' => $fila['fecha_lectura'],
                    'lectura_anterior' => $fila['lectura_anterior'],
                    'canton' => $fila['canton'],
                    'ruta' => $fila['ruta'],
                    'direccion' => $fila['direccion']
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
        ]);
    }
}
