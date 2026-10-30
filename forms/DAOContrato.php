<?php
$ncontrato = $_POST['numero'] ?? '';
$fecha = $_POST['fecha'] ?? '';
$monto = !empty($_POST['monto']) ? floatval($_POST['monto']) : 0.00;
$prima = !empty($_POST['prima']) ? floatval($_POST['prima']) : 0.00;
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

    if (empty($ncontrato)) {
        $ncontrato = getNumeroContrato($conexion);
    }

    try {
        $sql = "INSERT INTO contrato (ncontrato, fecha, monto, prima, idsocio, idempleado1, cargo1, idempleado2, cargo2)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssddiisis", $ncontrato, $fecha, $monto, $prima, $idsocio, $idempleado1, $cargo1, $idempleado2, $cargo2);

        if ($stmt->execute()) {
            echo json_encode([
                "estado" => true,
                "mensaje" => "Contrato Guardado Correctamente"
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

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT * FROM contrato ORDER BY idcontrato DESC";

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
                'idempleado1' => $fila['idempleado1'],
                'cargo1' => $fila['cargo1'],
                'idempleado2' => $fila['idempleado2'],
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
    try {
        $sql = "SELECT MAX(idcontrato) as ultimo FROM contrato";

        $stmt =$conexion=>prepare($sql);
        
        $stmt->execute();

        $resultado = $stmt->get_result();

        $maximo=0;

        if($fila=$resultado=>fetch_assoc()){
            $maximo=$fila['maximo'];
        })

        stmt=>close();

        return $cargo;
        
    } catch (Throwable $e) {
    }
}
