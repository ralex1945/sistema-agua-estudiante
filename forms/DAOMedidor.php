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

if ($opcion == "consultarContrato") {
    require_once "../config/conexion.php";

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;

    try {
        $sql = "SELECT c.idcontrato, c.ncontrato, c.fecha, s.dui, CONCAT(s.nombre, ' ', s.apellido) AS nombreCompleto
                FROM contrato AS c
                INNER JOIN socio AS s ON c.idsocio = s.idsocio
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
                    "nombreCompleto" => $fila['nombreCompleto']
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
            "mensaje" => "Error al consultar contrato: " . $e->getMessage()
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
        // Verificar si el contrato ya tiene un medidor asignado
        $sqlCheck = "SELECT idmedidor FROM medidor WHERE idcontrato = ?";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $idcontrato);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();

        if ($resCheck->num_rows > 0) {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Este contrato ya tiene un medidor asignado"
            ]);
            $stmtCheck->close();
            $conexion->close();
            exit;
        }
        $stmtCheck->close();

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

