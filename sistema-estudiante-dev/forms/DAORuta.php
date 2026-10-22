<?php
header('Content-Type: application/json; charset=utf-8');

$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$idruta = $_POST['idruta'] ?? '';

$opcion = $_REQUEST['opcion'] ?? '';

//$idempleado = $_POST['ideempleado'] ?? ''; 

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $sql = "INSERT INTO ruta (nombre, descripcion) VALUES(?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $nombre, $descripcion);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Datos Guardados Correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al guardar los datos: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $sql = "UPDATE ruta SET nombre=?, descripcion=? WHERE idruta=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssi", $nombre, $descripcion, $idruta);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Datos Modificados Correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al modificar los datos: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    $sql = "DELETE FROM ruta WHERE idruta=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idruta);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Datos Eliminados Correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al eliminar los datos: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
    exit;
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT idruta, nombre, descripcion FROM ruta ORDER BY nombre";
        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar las rutas");
        }

        $rutas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $rutas[] = [
                'idruta' => $fila['idruta'],
                'nombre' => $fila['nombre'],
                'descripcion' => $fila['descripcion'],
                'Cobertura' => $fila['descripcion']
            ];
        }

        echo json_encode($rutas, JSON_UNESCAPED_UNICODE);
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

    $sql = "SELECT idruta, nombre, descripcion FROM ruta WHERE idruta=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idruta);

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();
        echo json_encode([
            "estado" => true,
            "datos" => [
                "idruta" => $datos["idruta"],
                "nombre" => $datos["nombre"],
                "descripcion" => $datos["descripcion"]
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
