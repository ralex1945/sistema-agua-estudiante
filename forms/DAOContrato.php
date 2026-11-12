<?php
$idsocio = $_POST['idsocio'] ?? '';
$empleado1 = $_POST['empleado1'] ?? '';
$cargo1 = $_POST['cargo1'] ?? '';
$empleado2 = $_POST['empleado2'] ?? '';
$cargo2 = $_POST['cargo2'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$monto = $_POST['monto'] ?? '';

$idcontrato = $_POST['idcontrato'] ?? '';
$opcion = isset($_REQUEST["opcion"]) ? $_REQUEST["opcion"] : "";

if ($opcion == "generarNumero") {
    require_once "../config/conexion.php";
    $num = getNumeroContrato($conexion);
    echo json_encode([
        "estado" => true,
        "numero" => $num
    ]);
    $conexion->close();
    exit;
}

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    $prima = 0;
    $ncontrato = getNumeroContrato($conexion);

    $sql = "INSERT INTO contrato (ncontrato, fecha, monto, prima, idsocio, idempleado1, cargo1, idempleado2, cargo2) 
    VALUES(?,?,?,?,?,?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssddiisis", $ncontrato, $fecha, $monto, $prima, $idsocio, $empleado1, $cargo1, $empleado2, $cargo2);
    if ($stmt->execute()) {
        echo json_encode([
            "estado" => true,
            "mensaje" => "Datos Guardados Correctamente"
        ]);
    } else {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al guardar los datos"
        ]);
    }
    $stmt->close();
    $conexion->close();
}
if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;
    $idsocio = !empty($_POST['idsocio']) ? intval($_POST['idsocio']) : 0;
    $empleado1 = !empty($_POST['empleado1']) ? intval($_POST['empleado1']) : 0;
    $empleado2 = !empty($_POST['empleado2']) ? intval($_POST['empleado2']) : 0;

    $sql = "UPDATE contrato SET fecha=?, monto=?, idsocio=?, idempleado1=?, cargo1=?, idempleado2=?, cargo2=? WHERE idcontrato=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sdiisisi", $fecha, $monto, $idsocio, $empleado1, $cargo1, $empleado2, $cargo2, $idcontrato);

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

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;

    $sql = "DELETE FROM contrato WHERE idcontrato=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcontrato);
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
            "mensaje" => "a"
        ]);
    }

    $stmt->close();
    $conexion->close();
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT c.idcontrato, s.idsocio,s.dui, s.nombre,s.apellido, c.ncontrato, c.fecha, c.monto, m.nmedidor, 
CASE WHEN m.idcontrato IS NOT NULL THEN 'Asignado' ELSE 'No asignado' END AS asignado
FROM contrato AS c INNER JOIN socio AS s 
ON s.idsocio = c.idsocio LEFT JOIN medidor as m ON m.idcontrato= c.idcontrato;";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los registros");
        }

        $registros = [];
        $correlativo = 0;

        while ($fila = $resultado->fetch_assoc()) {
            //$empleados[] = $fila;
            $registros[] = [
                'idcontrato' => $fila['idcontrato'],
                'dui' => $fila['dui'],
                'nombre' => $fila['nombre'],
                'apellido' => $fila['apellido'],
                'fecha' => date('d-m-Y', strtotime($fila['fecha'])),
                'ncontrato' => $fila['ncontrato'],
                'monto' => number_format($fila['monto'], 2, '.', ','),
                'asignado' => $fila['asignado'],
                'correlativo' => $correlativo++
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
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
}
if ($opcion == "consultarIndividual") {
    require_once "../config/conexion.php";

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;

    $sql = "SELECT c.*, s.dui, CONCAT(s.nombre, ' ', s.apellido) AS nombreCompleto
            FROM contrato c
            INNER JOIN socio s ON c.idsocio = s.idsocio
            WHERE c.idcontrato = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idcontrato);

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $datos = $resultado->fetch_assoc();
        echo json_encode([
            "estado" => true,
            "datos" => [
                'idcontrato' => $datos['idcontrato'],
                'ncontrato' => $datos['ncontrato'],
                'fecha' => $datos['fecha'],
                'monto' => $datos['monto'],
                'idsocio' => $datos['idsocio'],
                'dui' => $datos['dui'],
                'nombreCompleto' => $datos['nombreCompleto'],
                'idempleado1' => $datos['idempleado1'],
                'cargo1' => $datos['cargo1'],
                'idempleado2' => $datos['idempleado2'],
                'cargo2' => $datos['cargo2']
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

if ($opcion == "consultarEmpleados") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT idempleado, CONCAT(nombre, ' ', apellido) as nombre FROM empleado ORDER BY nombre, apellido";
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
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($opcion == "consultarLectores") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT e.idempleado, CONCAT(e.nombre, ' ', e.apellido) AS nombre
                FROM empleado AS e
                INNER JOIN cargo AS c
                    ON c.idcargo = e.idcargo
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

        echo json_encode(
            $empleados,
            JSON_UNESCAPED_UNICODE
        );

        $resultado->free();
        $conexion->close();
    } catch (Throwable $e) {

        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
}

function getNumeroContrato($conexion)
{
    $sql = "SELECT MAX(ncontrato) AS maximo FROM contrato";
    $resultado = $conexion->query($sql);

    $maximo = 0;

    if ($fila = $resultado->fetch_assoc()) {
        $maximo = $fila['maximo'];
    }

    return $maximo + 1;
}
