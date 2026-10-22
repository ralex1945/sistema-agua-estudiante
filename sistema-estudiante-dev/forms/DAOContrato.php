<?php
$numero_contrato = $_POST['numero_contrato'] ?? '';
$idsocio = $_POST['idsocio'] ?? '';
$idruta = $_POST['idruta'] ?? '';
$medidor = $_POST['medidor'] ?? '';
$tipo_servicio = $_POST['tipo_servicio'] ?? '';
$direccion_servicio = $_POST['direccion_servicio'] ?? '';
$fecha_contrato = !empty($_POST['fecha_contrato']) ? $_POST['fecha_contrato'] : date('Y-m-d');
$estado = $_POST['estado'] ?? 'ACTIVO';
$idcontrato = $_POST['idcontrato'] ?? '';

$opcion = isset($_REQUEST["opcion"]) ? $_REQUEST["opcion"] : "";

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $sql = "INSERT INTO contrato (numero_contrato, idsocio, idruta, medidor, tipo_servicio, direccion_servicio, fecha_contrato, estado) VALUES(?,?,?,?,?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("siisssss", $numero_contrato, $idsocio, $idruta, $medidor, $tipo_servicio, $direccion_servicio, $fecha_contrato, $estado);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Contrato guardado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al guardar el contrato: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $sql = "UPDATE contrato SET numero_contrato=?, idsocio=?, idruta=?, medidor=?, tipo_servicio=?, direccion_servicio=?, fecha_contrato=?, estado=? WHERE idcontrato=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("siisssssi", $numero_contrato, $idsocio, $idruta, $medidor, $tipo_servicio, $direccion_servicio, $fecha_contrato, $estado, $idcontrato);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Contrato modificado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al modificar el contrato: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    $sql = "DELETE FROM contrato WHERE idcontrato=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcontrato);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Contrato eliminado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al eliminar el contrato"
        ]);
    }
    $stmt->close();
    $conexion->close();
}

if ($opcion == "listarSocios") {
    require_once "../config/conexion.php";
    $sql = "SELECT idsocio, concat(dui, ' - ', nombre, ' ', apellido) as socioNombre FROM socio ORDER BY apellido, nombre";
    $res = $conexion->query($sql);
    $socios = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $socios[] = $row;
        }
    }
    echo json_encode($socios, JSON_UNESCAPED_UNICODE);
    $conexion->close();
}

if ($opcion == "listarRutas") {
    require_once "../config/conexion.php";
    $sql = "SELECT idruta, nombre FROM ruta ORDER BY nombre";
    $res = $conexion->query($sql);
    $rutas = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rutas[] = $row;
        }
    }
    echo json_encode($rutas, JSON_UNESCAPED_UNICODE);
    $conexion->close();
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";
    try {
        $sql = "SELECT c.idcontrato, c.numero_contrato, c.idsocio, c.idruta, c.medidor, c.tipo_servicio, 
                       c.direccion_servicio, c.fecha_contrato, c.estado,
                       concat(s.nombre, ' ', s.apellido) as socioNombre,
                       r.nombre as rutaNombre
                FROM contrato c
                LEFT JOIN socio s ON c.idsocio = s.idsocio
                LEFT JOIN ruta r ON c.idruta = r.idruta
                ORDER BY c.idcontrato DESC";
        $resultado = $conexion->query($sql);
        $contratos = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $contratos[] = $fila;
            }
        }
        echo json_encode($contratos, JSON_UNESCAPED_UNICODE);
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode(["mensaje" => "Error al consultar contratos: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

if ($opcion == "consultarIndividual") {
    require_once "../config/conexion.php";
    $sql = "SELECT idcontrato, numero_contrato, idsocio, idruta, medidor, tipo_servicio, direccion_servicio, fecha_contrato, estado FROM contrato WHERE idcontrato=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcontrato);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo json_encode([
            "estado" => true,
            "datos" => $resultado->fetch_assoc()
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "datos" => "No existe el contrato"
        ]);
    }
    $stmt->close();
    $conexion->close();
}
