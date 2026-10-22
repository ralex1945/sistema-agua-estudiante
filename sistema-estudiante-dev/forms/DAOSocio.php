<?php
$dui = $_POST['dui'] ?? '';
$nombre = isset($_REQUEST["nombre"]) ? $_REQUEST["nombre"] : "";
$apellido = isset($_REQUEST["apellido"]) ? $_REQUEST["apellido"] : "";
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$canton = $_POST['canton'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$fecha_registro = !empty($_POST['fecha_registro']) ? $_POST['fecha_registro'] : date('Y-m-d');
$estado = $_POST['estado'] ?? 'ACTIVO';
$idsocio = $_POST['idsocio'] ?? '';

$opcion = isset($_REQUEST["opcion"]) ? $_REQUEST["opcion"] : "";

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $sql = "INSERT INTO socio (dui, nombre, apellido, telefono, correo, canton, direccion, fecha_registro, estado) VALUES(?,?,?,?,?,?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssssss", $dui, $nombre, $apellido, $telefono, $correo, $canton, $direccion, $fecha_registro, $estado);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Socio guardado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al guardar el socio: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $sql = "UPDATE socio SET dui=?, nombre=?, apellido=?, telefono=?, correo=?, canton=?, direccion=?, fecha_registro=?, estado=? WHERE idsocio=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssssssi", $dui, $nombre, $apellido, $telefono, $correo, $canton, $direccion, $fecha_registro, $estado, $idsocio);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Socio modificado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al modificar el socio: " . $stmt->error
        ]);
    }
    $stmt->close();
    $conexion->close();
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    $sql = "DELETE FROM socio WHERE idsocio=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idsocio);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Socio eliminado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al eliminar el socio"
        ]);
    }
    $stmt->close();
    $conexion->close();
}

if ($opcion == "consultarDUI") {
    require_once "../config/conexion.php";

    $sql = "SELECT dui FROM socio WHERE dui=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $dui);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Este DUI ya pertenece a un socio registrado"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Disponible"
        ]);
    }
    $stmt->close();
    $conexion->close();
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT idsocio, dui, nombre, apellido, telefono, correo, canton, direccion, fecha_registro, estado, concat(nombre,' ',apellido) as nombreCompleto FROM socio ORDER BY apellido, nombre";
        $resultado = $conexion->query($sql);

        $socios = [];
        while ($fila = $resultado->fetch_assoc()) {
            $socios[] = $fila;
        }

        echo json_encode($socios, JSON_UNESCAPED_UNICODE);
        $resultado->free();
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode(["mensaje" => "Error al obtener socios: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

if ($opcion == "consultarIndividual") {
    require_once "../config/conexion.php";

    $sql = "SELECT idsocio, dui, nombre, apellido, telefono, correo, canton, direccion, fecha_registro, estado FROM socio WHERE idsocio=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idsocio);
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
            "datos" => "No existe el socio"
        ]);
    }
    $stmt->close();
    $conexion->close();
}
