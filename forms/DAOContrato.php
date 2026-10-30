<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$ncontrato = $_POST['numero'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$monto = $_POST['monto'] ?? '0';
$prima = $_POST['prima'] ?? '0';
$idsocio = $_POST['idsocio'] ?? '';
$idempleado1 = !empty($_POST['empleado1']) ? intval($_POST['empleado1']) : 0;
$cargo1 = $_POST['cargo1'] ?? '';
$idempleado2 = !empty($_POST['empleado2']) ? intval($_POST['empleado2']) : 0;
$cargo2 = $_POST['cargo2'] ?? '';
$idcontrato = $_POST['idcontrato'] ?? '';

$opcion = $_POST['opcion'] ?? '';

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

    ////OBTENER EL NUMERO DE CONTRATO

    $ncontrato = getNumeroContrato($conexion);//0+1
    $prima = 0;

    $sql = "INSERT INTO contrato (ncontrato, fecha, monto, prima, idsocio, idempleado1, cargo1, idempleado2, cargo2) VALUES(?,?,?,?,?,?,?,?,?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssdiisis", $ncontrato, $fecha, $monto, $prima, $idsocio, $idempleado1, $cargo1, $idempleado2, $cargo2);
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

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT c.*, 
                       CONCAT(s.nombre, ' ', s.apellido) AS socio,
                       CONCAT(e1.nombre, ' ', e1.apellido) AS empleado1,
                       CONCAT(e2.nombre, ' ', e2.apellido) AS empleado2
                FROM contrato c
                LEFT JOIN socio s ON c.idsocio = s.idsocio
                LEFT JOIN empleado e1 ON c.idempleado1 = e1.idempleado
                LEFT JOIN empleado e2 ON c.idempleado2 = e2.idempleado
                ORDER BY c.idcontrato DESC";

        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los registros");
        }

        $contratos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $contratos[] = [
                'idcontrato' => $fila['idcontrato'],
                'ncontrato' => $fila['ncontrato'],
                'fecha' => date("d-m-Y", strtotime($fila['fecha'])),
                'monto' => number_format($fila['monto'], 2, '.', ','),
                'prima' => number_format($fila['prima'], 2, '.', ','),
                'idsocio' => $fila['idsocio'],
                'socio' => $fila['socio'] ?? 'N/A',
                'idempleado1' => $fila['idempleado1'],
                'empleado1' => $fila['empleado1'] ?? 'N/A',
                'cargo1' => $fila['cargo1'],
                'idempleado2' => $fila['idempleado2'],
                'empleado2' => $fila['empleado2'] ?? 'N/A',
                'cargo2' => $fila['cargo2']
            ];
        }

        echo json_encode($contratos, JSON_UNESCAPED_UNICODE);

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

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;

    try {
        $sql = "SELECT c.*, s.dui, CONCAT(s.nombre, ' ', s.apellido) AS nombreCompleto
                FROM contrato c
                INNER JOIN socio s ON c.idsocio = s.idsocio
                WHERE c.idcontrato = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idcontrato);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            echo json_encode([
                "estado" => true,
                "datos" => [
                    "idcontrato" => $fila['idcontrato'],
                    "ncontrato" => $fila['ncontrato'],
                    "fecha" => $fila['fecha'],
                    "monto" => $fila['monto'],
                    "idsocio" => $fila['idsocio'],
                    "dui" => $fila['dui'],
                    "nombreCompleto" => $fila['nombreCompleto'],
                    "idempleado1" => $fila['idempleado1'],
                    "cargo1" => $fila['cargo1'],
                    "idempleado2" => $fila['idempleado2'],
                    "cargo2" => $fila['cargo2']
                ]
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Contrato no encontrado"
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
    exit;
}

if ($opcion == "modificar") {
    require_once "../config/conexion.php";

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;

    try {
        $sql = "UPDATE contrato SET fecha=?, monto=?, idsocio=?, idempleado1=?, cargo1=?, idempleado2=?, cargo2=? WHERE idcontrato=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sdiisisi", $fecha, $monto, $idsocio, $idempleado1, $cargo1, $idempleado2, $cargo2, $idcontrato);

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
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error en la base de datos: " . $e->getMessage()
        ]);
    }
    $conexion->close();
    exit;
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    $idcontrato = !empty($_POST['idcontrato']) ? intval($_POST['idcontrato']) : 0;

    try {
        $sql = "DELETE FROM contrato WHERE idcontrato = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idcontrato);

        if ($stmt->execute()) {
            echo json_encode([
                "estado" => true,
                "mensaje" => "Contrato Eliminado Correctamente"
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "mensaje" => "Error al eliminar: " . $stmt->error
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
    } catch (Throwable $e) {
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros"
        ], JSON_UNESCAPED_UNICODE);
    }
    $conexion->close();
}

if ($opcion == "consultarDUISocio") {
    require_once "../config/conexion.php";

    $dui = $_POST['dui'] ?? '';

    try {
        $sql = "SELECT idsocio, CONCAT(nombre,' ', apellido) AS nombreCompleto FROM socio WHERE dui=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $dui);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            echo json_encode([
                "estado" => true,
                "datos" => ['idsocio' => $fila['idsocio'], 'nombreCompleto' => $fila['nombreCompleto']]
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "datos" => "No existe"
            ]);
        }
        $stmt->close();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "datos" => $e->getMessage()
        ]);
    }
    $conexion->close();
}

function getNumeroContrato($conexion)
{
    $sql = "SELECT MAX(ncontrato) AS maximo FROM contrato";

    $resto = $conexion->query($sql);
    $resultado = $resto->fetch_assoc();

    $maximo = 0;

    if ($resultado && $resultado['maximo'] !== null) {
        $maximo = $resultado['maximo'];
    }

    $maximo = $maximo + 1;

    return $maximo;
}
