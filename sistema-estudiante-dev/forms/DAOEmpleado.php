<?php
$dui = $_POST['dui'] ?? '';
$nombre = isset($_REQUEST["nombre"]) ? $_REQUEST["nombre"] : "";
$apellido = isset($_REQUEST["apellido"]) ? $_REQUEST["apellido"] : "";
$telefono = $_POST['telefono'] ?? '';
$isss = $_POST['isss'] ?? '';
$afp = $_POST['afp'] ?? '';
$nup = $_POST['nup'] ?? '';
$renta = $_POST['renta'] ?? '';
$sueldo = !empty($_POST['sueldo']) ? floatval($_POST['sueldo']) : 0.00;
$cargo = $_POST['cargo'] ?? '';
$canton = $_POST['canton'] ?? '';
$estado = $_POST['estado'] ?? 'ACTIVO';
$fecha_ingreso = !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null;
$idempleado = $_POST['idempleado'] ?? '';

$opcion = isset($_REQUEST["opcion"]) ? $_REQUEST["opcion"] : "";

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $sql = "INSERT INTO empleado (dui, nombre, apellido, telefono, isss, afp, nup, renta, sueldo, cargo, canton, estado, fecha_ingreso) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssssdssss", $dui, $nombre, $apellido, $telefono, $isss, $afp, $nup, $renta, $sueldo, $cargo, $canton, $estado, $fecha_ingreso);
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

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $sql = "UPDATE empleado SET dui=?, nombre=?, apellido=?, telefono=?, isss=?, afp=?, nup=?, renta=?, sueldo=?, cargo=?, canton=?, estado=?, fecha_ingreso=? WHERE idempleado=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssssssdssssi", $dui, $nombre, $apellido, $telefono, $isss, $afp, $nup, $renta, $sueldo, $cargo, $canton, $estado, $fecha_ingreso, $idempleado);
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
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    $sql = "DELETE FROM empleado WHERE idempleado=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idempleado);
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

if ($opcion == "consultarDUI") {
    require_once "../config/conexion.php";

    $sql = "SELECT dui FROM empleado WHERE dui=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $dui);

    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Este Dui ya existe"
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
        $sql = "SELECT idempleado, dui, nombre, apellido, telefono, isss, afp, nup, renta, sueldo, cargo, canton, estado, fecha_ingreso, concat(nombre,' ',apellido) as nombreCompleto
                FROM empleado
                ORDER BY apellido, nombre";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los empleados");
        }

        $empleados = [];

        while ($fila = $resultado->fetch_assoc()) {
            $empleados[] = [
                'idempleado' => $fila['idempleado'],
                'dui' => $fila['dui'],
                'nombre' => $fila['nombre'],
                'apellido' => $fila['apellido'],
                'telefono' => $fila['telefono'],
                'isss' => $fila['isss'],
                'afp' => $fila['afp'],
                'nup' => $fila['nup'],
                'renta' => $fila['renta'],
                'sueldo' => $fila['sueldo'],
                'cargo' => $fila['cargo'],
                'canton' => $fila['canton'],
                'estado' => $fila['estado'],
                'fecha_ingreso' => $fila['fecha_ingreso'],
                'nombreCompleto' => $fila['nombreCompleto']
            ];
        }

        echo json_encode(
            $empleados,
            JSON_UNESCAPED_UNICODE
        );

        $resultado->free();
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

if ($opcion == "consultarIndividual") {
    require_once "../config/conexion.php";

    $sql = "SELECT idempleado, dui, nombre, apellido, telefono, isss, afp, nup, renta, sueldo, cargo, canton, estado, fecha_ingreso FROM empleado WHERE idempleado=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idempleado);

    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();
        echo json_encode([
            "estado" => true,
            "datos" => [
                "idempleado" => $datos["idempleado"],
                "dui" => $datos["dui"],
                "nombre" => $datos["nombre"],
                "apellido" => $datos["apellido"],
                "telefono" => $datos["telefono"],
                "isss" => $datos["isss"],
                "afp" => $datos["afp"],
                "nup" => $datos["nup"],
                "renta" => $datos["renta"],
                "sueldo" => $datos["sueldo"],
                "cargo" => $datos["cargo"],
                "canton" => $datos["canton"],
                "estado" => $datos["estado"],
                "fecha_ingreso" => $datos["fecha_ingreso"]
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
}
