<?php
$dui = $_POST['dui'] ?? '';
$nombre = isset($_REQUEST["nombre"]) ? $_REQUEST["nombre"] : "";
$apellido = isset($_REQUEST["apellido"]) ? $_REQUEST["apellido"] : "";
$telefono = $_POST['telefono'] ?? '';
$isss = $_POST['isss'] ?? '';
$afp = $_POST['afp'] ?? '';
$nafp = $_POST['nafp'] ?? $_POST['nup'] ?? '';
$renta = $_POST['renta'] ?? '';
$sueldo = !empty($_POST['sueldo']) ? floatval($_POST['sueldo']) : 0.00;
$cargo = !empty($_POST['cargo']) ? intval($_POST['cargo']) : 0;
$canton = !empty($_POST['canton']) ? intval($_POST['canton']) : 0;
$estado = $_POST['estado'] ?? 'ACTIVO';
$fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : (!empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null);
$idempleado = $_POST['idempleado'] ?? '';

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    try {
        $sql = "INSERT INTO empleado (dui, nombre, apellido, telefono, fecha_ingreso, n_isss, nup, afp, renta, sueldo, idcargo, idcanton, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssssssiis", $dui, $nombre, $apellido, $telefono, $fecha, $isss, $nafp, $afp, $renta, $sueldo, $cargo, $canton, $estado);

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
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error en la base de datos: " . $e->getMessage()
        ]);
    }
    $conexion->close();
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $sql = "UPDATE empleado set dui=?, nombre=?, apellido=?, telefono=?, fecha_ingreso=?, n_isss=?, nup=?, afp=?, renta=?, sueldo=?,
        idcargo=?, idcanton=?, estado=? WHERE idempleado=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        "ssssssssssiisi",
        $dui,
        $nombre,
        $apellido,
        $telefono,
        $fecha,
        $isss,
        $nafp,
        $afp,
        $renta,
        $sueldo,
        $cargo,
        $canton,
        $estado,
        $idempleado
    );
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Datos Modificados Correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al modificar los datos"
        ]);
    }

    $stmt->close();
    $conexion->close();
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    try {
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
                "mensaje" => "Error al eliminar los datos: " . $stmt->error
            ]);
        }
        $stmt->close();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al eliminar: " . $e->getMessage()
        ]);
    }
    $conexion->close();
}

if ($opcion == "consultarDUI") {
    require_once "../config/conexion.php";

    try {
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
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => $e->getMessage()
        ]);
    }
    $conexion->close();
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT * FROM empleado ORDER BY apellido, nombre";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los registros");
        }

        $empleados = [];
        while ($fila = $resultado->fetch_assoc()) {
            //$empleados[] = $fila;
            $empleados[] = [
                'idempleado' => $fila['idempleado'],
                'dui' => $fila['dui'],
                'nombre' => $fila['nombre'],
                'apellido' => $fila['apellido'],
                'telefono' => $fila['telefono'],
                'fecha_ingreso' => date("d-m-Y", strtotime($fila['fecha_ingreso'])),
                'n_isss' => $fila['n_isss'],
                'nup' => $fila['nup'],
                'afp' => $fila['afp'],
                'renta' => $fila['renta'],
                'sueldo' => number_format($fila['sueldo'], 2, '.', ','),
                'idcargo' => getCargo($fila['idcargo'], $conexion),
                'idcanton' => getCanton($fila['idcanton'], $conexion),
                'estado' => $fila['estado']
            ];
        }

        echo json_encode(
            $empleados,
            JSON_UNESCAPED_UNICODE
        );

        $resultado->free();
    } catch (Throwable $e) {

        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
    $conexion->close();
}

if ($opcion == "consultarIndividual") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT idempleado, dui, nombre, apellido, telefono, fecha_ingreso, n_isss, nup, afp, renta, sueldo, idcargo, idcanton, estado FROM empleado WHERE idempleado=?";
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
                    "fecha_ingreso" => $datos["fecha_ingreso"],
                    "n_isss" => $datos["n_isss"],
                    "nup" => $datos["nup"],
                    "afp" => $datos["afp"],
                    "renta" => $datos["renta"],
                    "sueldo" => $datos["sueldo"],
                    "idcargo" => $datos["idcargo"],
                    "idcanton" => $datos["idcanton"],
                    "estado" => $datos["estado"]
                ]
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "datos" => "No Existe"
            ]);
        }
        $stmt->close();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => $e->getMessage()
        ]);
    }
    $conexion->close();
}

if ($opcion == "consultarLectores") {
    require_once "../config/conexion.php";
    try {
        $sql = "SELECT e.idempleado, CONCAT(e.nombre, ' ', e.apellido) as nombre 
                FROM empleado AS e 
                INNER JOIN cargo AS c ON c.idcargo = e.idcargo 
                WHERE c.nombre = 'LECTOR' 
                ORDER BY e.nombre, e.apellido";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los registros");
        }

        $empleados = [];
        while ($fila = $resultado->fetch_assoc()) {
            $empleados[] = [
                'idempleado' => $fila['idempleado'],
                'nombre' => $fila['nombre']
            ];
        }

        echo json_encode($empleados, JSON_UNESCAPED_UNICODE);

        $resultado->free();
        $conexion->close();
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            "estado" => false,
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
}


function getCargo($id, $conexion)
{
    $sql = "SELECT nombre FROM cargo WHERE idcargo = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    $stmt->execute();
    $resultado = $stmt->get_result();

    $cargo = "";

    if ($fila = $resultado->fetch_assoc()) {
        $cargo = $fila['nombre'];
    }

    $stmt->close();
    //$conexion->close();

    return $cargo;
}

function getCanton($id, $conexion)
{
    $sql = "SELECT nombre FROM canton WHERE idcanton = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    $stmt->execute();
    $resultado = $stmt->get_result();

    $cargo = "";

    if ($fila = $resultado->fetch_assoc()) {
        $cargo = $fila['nombre'];
    }

    $stmt->close();
    //$conexion->close();

    return $cargo;
}
