<?php
//recibir el idsocio con respecto al dui
$idempleado = $_POST['idempleado'] ?? '';
$dui = $_POST['dui'] ?? '';
$roles = isset($_POST['roles']) ? $_POST['roles'] : [];


$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";


    //echo $usuario."----".$clave;

    $rolesPermitidos = ""; //admin caja // admin, caja

    $rolesPermitidos = implode(', ', $roles); //ALMACENA TODOS LOS ROLES EN UNA SOLA VARIABLE SEPARADOS POR COMA
    //verificar s ya existe el empleado, entonces se modifica sino se inserta

    $sql = "SELECT idempleado FROM usuario WHERE idempleado = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idempleado);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        // Actualizar usuario
        $sql = "UPDATE usuario SET rol = ? WHERE idempleado = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $rolesPermitidos, $idempleado);
    } else {

        // Insertar usuario
        $usuarioHash = password_hash($dui, PASSWORD_DEFAULT);
        $claveHash = password_hash($dui, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (idempleado, rol, usuario, clave) VALUES (?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("isss", $idempleado, $rolesPermitidos, $usuarioHash, $claveHash);
    }

    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Usuario registrado correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al guardar: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conexion->close();
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT e.idempleado, e.dui, e.nombre, e.apellido, u.rol
                FROM empleado AS e
                LEFT JOIN usuario AS u
                    ON e.idempleado = u.idempleado
                WHERE e.idempleado = ?";

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
                    "rol" => $datos["rol"]
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {

            echo json_encode([
                "estado" => false,
                "mensaje" => "No se encontró el empleado"
            ], JSON_UNESCAPED_UNICODE);
        }

        $resultado->free();
        $conexion->close();
    } catch (Throwable $e) {

        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
}
