<?php
header('Content-Type: application/json; charset=utf-8');

$nombre = $_POST['nombre'] ?? '';
$idcanton = $_POST['idcanton'] ?? '';

$opcion = $_REQUEST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $sql = "INSERT INTO canton (nombre) VALUES(?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $nombre);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Cantón guardado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al guardar el cantón: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $sql = "UPDATE canton SET nombre=? WHERE idcanton=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $nombre, $idcanton);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Cantón modificado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al modificar el cantón: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    $sql = "DELETE FROM canton WHERE idcanton=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcanton);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Cantón eliminado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al eliminar el cantón: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT idcanton, nombre FROM canton ORDER BY nombre";
        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los cantones");
        }

        $cantones = [];
        while ($fila = $resultado->fetch_assoc()) {
            $cantones[] = [
                'idcanton' => $fila['idcanton'],
                'nombre' => $fila['nombre']
            ];
        }

        echo json_encode($cantones, JSON_UNESCAPED_UNICODE);
        $resultado->free();
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($opcion == "consultarIndividual") {
    require_once "../config/conexion.php";

    $sql = "SELECT idcanton, nombre FROM canton WHERE idcanton=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcanton);

    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();
        echo json_encode([
            "estado" => true,
            "datos" => [
                "idcanton" => $datos["idcanton"],
                "nombre" => $datos["nombre"]
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
    exit;
}
