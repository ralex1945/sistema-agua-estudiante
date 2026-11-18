<?php
session_start();
$loginUsuario = $_POST['loginUsuario'] ?? '';
$loginClave = $_POST['loginClave'] ?? '';

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    $sql = "SELECT u.usuario, u.clave, u.rol, e.idempleado, e.nombre FROM usuario AS u INNER JOIN empleado AS e ON e.idempleado=u.idempleado";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $loginCorrecto = false;
    $rol = "";
    $idempleado = 0;

    while ($fila = $resultado->fetch_assoc()) {

        $usuarioValido = password_verify($loginUsuario, $fila['usuario']);
        $claveValida = password_verify($loginClave, $fila['clave']);

        if ($usuarioValido && $claveValida) {

            $loginCorrecto = true;
            $rol = $fila['rol'];
            $idempleado = $fila['idempleado'];
            $nombreEmpleado = $fila['nombre'];

            break;
        }
    }

    if ($loginCorrecto) {
        $_SESSION['autenticado'] = true;
        $_SESSION['idempleado'] = $idempleado;
        $_SESSION['nombreEmpleado'] = $nombreEmpleado;
        $_SESSION['rol'] = $rol;
        echo json_encode([
            "estado" => true,
            "datos" => [
                "rol" => $rol,
                "idempleado" => $idempleado,
                "nombreEmpleado" => $nombreEmpleado
            ]
        ]);
    } else {
        session_unset();
        session_destroy();

        $_SESSION['autenticado'] = false;

        echo json_encode([
            "estado" => false,
            "mensaje" => "Usuario o contraseña incorrectos"
        ]);
    }
}
?>
