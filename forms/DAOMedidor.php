<?php
header('Content-Type: application/json; charset=utf-8');

$idcontrato = $_POST['idcontrato'] ?? '';
$nmedidor = $_POST['nmedidor'] ?? '';
$lectura_inicial = $_POST['inicio'] ?? '';
$idcanton = $_POST['canton'] ?? '';
$idruta = $_POST['ruta'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$referencia = $_POST['referencia'] ?? '';
$estado = $_POST['estado'] ?? '';

$opcion = $_REQUEST['opcion'] ?? '';

if ($opcion == "consultarMedidor") {
    require_once "../config/conexion.php";

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;

    try {
        $sql = "SELECT c.idcontrato, c.ncontrato, c.fecha, s.dui, CONCAT(s.nombre, ' ', s.apellido) AS nombreCompleto,
                       m.idmedidor, m.nmedidor, m.lectura_inicial, m.idcanton, m.idruta, m.direccion, m.referencia, m.estado
                FROM contrato AS c
                INNER JOIN socio AS s ON c.idsocio = s.idsocio
                LEFT JOIN medidor AS m ON c.idcontrato = m.idcontrato
                WHERE c.idcontrato = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idcontrato);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            echo json_encode([
                "estado" => true,
                "datos" => [
                    "idcontrato" => $fila['idcontrato'],
                    "ncontrato" => $fila['ncontrato'],
                    "fecha" => $fila['fecha'],
                    "dui" => $fila['dui'],
                    "nombreCompleto" => $fila['nombreCompleto'],
                    "idmedidor" => $fila['idmedidor'],
                    "nmedidor" => $fila['nmedidor'],
                    "lectura_inicial" => $fila['lectura_inicial'],
                    "idcanton" => $fila['idcanton'],
                    "idruta" => $fila['idruta'],
                    "direccion" => $fila['direccion'],
                    "referencia" => $fila['referencia'],
                    "estado" => $fila['estado']
                ]
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Contrato no encontrado"
            ]);
        }
        $stmt->close();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al consultar medidor: " . $e->getMessage()
        ]);
    }
    $conexion->close();
    exit;
}

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $idcontrato = intval($idcontrato);
    $lectura_inicial = intval($lectura_inicial);
    $idcanton = intval($idcanton);
    $idruta = intval($idruta);

    try {
        $sql = "INSERT INTO medidor (idcontrato, nmedidor, lectura_inicial, idcanton, idruta, direccion, referencia, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isiissss", $idcontrato, $nmedidor, $lectura_inicial, $idcanton, $idruta, $direccion, $referencia, $estado);

        if ($stmt->execute()) {
            echo json_encode([
                "estado" => true,
                "mensaje" => "Medidor Asignado Correctamente"
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Error al guardar el medidor: " . $stmt->error
            ]);
        }
        $stmt->close();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error en la base de datos: " . $e->getMessage()
        ]);
    }
    $conexion->close();
    exit;
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $idmedidor = !empty($_POST['idmedidor']) ? intval($_POST['idmedidor']) : 0;
    $lectura_inicial = intval($lectura_inicial);
    $idcanton = intval($idcanton);
    $idruta = intval($idruta);

    try {
        $sql = "UPDATE medidor SET nmedidor=?, lectura_inicial=?, idcanton=?, idruta=?, direccion=?, referencia=?, estado=?
                WHERE idmedidor=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("siissssi", $nmedidor, $lectura_inicial, $idcanton, $idruta, $direccion, $referencia, $estado, $idmedidor);

        if ($stmt->execute()) {
            echo json_encode([
                "estado" => true,
                "mensaje" => "Medidor Modificado Correctamente"
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Error al modificar el medidor: " . $stmt->error
            ]);
        }
        $stmt->close();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error en la base de datos: " . $e->getMessage()
        ]);
    }
    $conexion->close();
    exit;
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT m.idmedidor, m.idcontrato, m.nmedidor, m.lectura_inicial, m.idcanton, m.idruta, m.direccion, m.referencia, m.estado,
                       c.ncontrato, c.fecha, s.dui, s.nombre, s.apellido,
                       can.nombre AS canton, r.nombre AS ruta
                FROM medidor AS m
                INNER JOIN contrato AS c ON m.idcontrato = c.idcontrato
                INNER JOIN socio AS s ON c.idsocio = s.idsocio
                LEFT JOIN canton AS can ON m.idcanton = can.idcanton
                LEFT JOIN ruta AS r ON m.idruta = r.idruta
                ORDER BY m.idmedidor ASC";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los registros: " . $conexion->error);
        }

        $registros = [];
        $correlativo = 1;

        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = [
                'idmedidor' => $fila['idmedidor'],
                'idcontrato' => $fila['idcontrato'],
                'ncontrato' => $fila['ncontrato'],
                'nmedidor' => $fila['nmedidor'],
                'dui' => $fila['dui'],
                'nombre' => $fila['nombre'],
                'apellido' => $fila['apellido'],
                'cliente' => $fila['nombre'] . ' ' . $fila['apellido'],
                'lectura_inicial' => $fila['lectura_inicial'],
                'canton' => $fila['canton'] ?? '',
                'ruta' => $fila['ruta'] ?? '',
                'direccion' => $fila['direccion'],
                'referencia' => $fila['referencia'],
                'estado' => $fila['estado'],
                'fecha' => !empty($fila['fecha']) ? date('d-m-Y', strtotime($fila['fecha'])) : '',
                'correlativo' => $correlativo++
            ];
        }

        echo json_encode($registros, JSON_UNESCAPED_UNICODE);
        $resultado->free();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al obtener los registros: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    $conexion->close();
    exit;
}

