<?php
header('Content-Type: application/json; charset=utf-8');

$nombre = $_POST['nombre'] ?? '';
$idcargo = $_POST['idcargo'] ?? '';

$opcion = $_REQUEST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $sql = "INSERT INTO cargo (nombre) VALUES(?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $nombre);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Cargo guardado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al guardar el cargo: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $sql = "UPDATE cargo SET nombre=? WHERE idcargo=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $nombre, $idcargo);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Cargo modificado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al modificar el cargo: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    $sql = "DELETE FROM cargo WHERE idcargo=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcargo);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Cargo eliminado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al eliminar el cargo: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT idcargo, nombre FROM cargo ORDER BY nombre";
        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los cargos");
        }

        $cargos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $cargos[] = [
                'idcargo' => $fila['idcargo'],
                'nombre' => $fila['nombre']
            ];
        }

        echo json_encode($cargos, JSON_UNESCAPED_UNICODE);
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

    $sql = "SELECT idcargo, nombre FROM cargo WHERE idcargo=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcargo);

    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();
        echo json_encode([
            "estado" => true,
            "datos" => [
                "idcargo" => $datos["idcargo"],
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
