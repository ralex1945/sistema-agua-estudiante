<?php
//recibir el idsocio con respecto al dui
$desde = $_POST['desde'] ?? '';
$hasta = $_POST['hasta'] ?? '';
$tipo = $_POST['tipo'] ?? '';
$precio = $_POST['precio'] ?? '';

//$idcontrato = $_POST['idcontrato'] ?? '';

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    //idtarifa desde hasta tipo_cobro precio
    $sql = "INSERT INTO tarifa (desde, hasta, tipo_cobro, precio) VALUES(?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iiss", $desde, $hasta, $tipo, $precio);
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
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT * FROM tarifa ORDER BY idtarifa";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los registros");
        }

        $registros = [];

        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = [
                'idtarifa' => $fila['idtarifa'],
                'desde' => $fila['desde'],
                'hasta' => $fila['hasta'],
                'precio' => number_format($fila['precio'], 2, '.', ','),
                'tipo' => $fila['tipo_cobro']
            ];
        }

        echo json_encode(
            $registros,
            JSON_UNESCAPED_UNICODE
        );

        $resultado->free();
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}
