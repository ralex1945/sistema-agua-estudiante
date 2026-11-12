<?php
$empleado = $_POST['empleado'] ?? '';
$ruta = $_POST['ruta'] ?? '';
$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";
    try {
        $sql = "INSERT INTO empleado_ruta (idempleado, idruta) VALUES(?,?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ii", $empleado, $ruta);

        if ($stmt->execute()) {
            echo json_encode([
                "estado" => true,
                "mensaje" => "Datos Guardados Correctamente"
            ]);
        }
        $stmt->close();
    } catch (mysqli_sql_exception $e) { // Cuando el empleado ya tiene asignada la ruta, arroja una excepción
        if ($e->getCode() == 1062) {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Este empleado ya tiene asignada esa ruta"
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Ocurrió un error al guardar la ruta"
            ]);
        }
    } finally {
        $conexion->close();
    }
}

if ($opcion == "eliminar") {
    $idempleado_ruta = $_POST['idempleado_ruta'] ?? '';
    require_once "../config/conexion.php";

    $sql = "DELETE FROM empleado_ruta WHERE idempleado_ruta=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idempleado_ruta);

    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Datos Eliminados Correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al eliminar los datos"
        ]);
    }

    $stmt->close();
    $conexion->close();
}
