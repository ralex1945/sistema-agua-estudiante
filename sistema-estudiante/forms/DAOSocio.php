<?php
$dui = $_POST['dui'] ?? '';
$nombre = isset($_REQUEST["nombre"]) ? $_REQUEST["nombre"] : "";
$apellido = isset($_REQUEST["apellido"]) ? $_REQUEST["apellido"] : "";
$telefono = $_POST['telefono'] ?? '';
$profesion = $_POST['profesion'] ?? '';
$nit = $_POST['nit'] ?? '';
$canton = !empty($_POST['canton']) ? intval($_POST['canton']) : (!empty($_POST['idcanton']) ? intval($_POST['idcanton']) : 0);
$direccion = $_POST['direccion'] ?? '';
$fecha = !empty($_POST['fecha']) ? $_POST['fecha'] : (!empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null);
$idsocio = $_POST['idsocio'] ?? '';

$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    try {
        $colFecha = "fecha_nacimiento";
        $test = $conexion->query("SHOW COLUMNS FROM socio LIKE 'fecha_nacimiento'");
        if (!$test || $test->num_rows == 0) {
            $colFecha = "fechanacimiento";
        }

        $sql = "INSERT INTO socio (dui, nombre, apellido, telefono, $colFecha, profesion, idcanton, nit, direccion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssiss", $dui, $nombre, $apellido, $telefono, $fecha, $profesion, $canton, $nit, $direccion);

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

    try {
        $colFecha = "fecha_nacimiento";
        $test = $conexion->query("SHOW COLUMNS FROM socio LIKE 'fecha_nacimiento'");
        if (!$test || $test->num_rows == 0) {
            $colFecha = "fechanacimiento";
        }

        $sql = "UPDATE socio SET dui=?, nombre=?, apellido=?, telefono=?, $colFecha=?, profesion=?, idcanton=?, nit=?, direccion=? WHERE idsocio=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssssssissi", $dui, $nombre, $apellido, $telefono, $fecha, $profesion, $canton, $nit, $direccion, $idsocio);

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
}

if ($opcion == "eliminar") {
    require_once "../config/conexion.php";

    try {
        $sql = "DELETE FROM socio WHERE idsocio=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idsocio);

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
        $sql = "SELECT dui FROM socio WHERE dui=?";
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
        $sql = "SELECT * FROM socio ORDER BY apellido, nombre";
        $resultado = $conexion->query($sql);

        if (!$resultado) {
            throw new Exception("Error al consultar los registros");
        }

        $socios = [];
        while ($fila = $resultado->fetch_assoc()) {
            $fechaRaw = $fila['fecha_nacimiento'] ?? ($fila['fechanacimiento'] ?? null);

            $socios[] = [
                'idsocio' => $fila['idsocio'],
                'dui' => $fila['dui'],
                'nombre' => $fila['nombre'],
                'apellido' => $fila['apellido'],
                'telefono' => $fila['telefono'],
                'fecha_nacimiento' => !empty($fechaRaw) ? date("d-m-Y", strtotime($fechaRaw)) : '',
                'profesion' => $fila['profesion'],
                'nit' => $fila['nit'],
                'direccion' => $fila['direccion'],
                'idcanton' => getCanton($fila['idcanton'], $conexion)
            ];
        }

        echo json_encode($socios, JSON_UNESCAPED_UNICODE);
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
        $sql = "SELECT * FROM socio WHERE idsocio=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idsocio);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $datos = $resultado->fetch_assoc();
            $fechaNac = $datos["fecha_nacimiento"] ?? ($datos["fechanacimiento"] ?? '');

            echo json_encode([
                "estado" => true,
                "datos" => [
                    "idsocio" => $datos["idsocio"],
                    "dui" => $datos["dui"],
                    "nombre" => $datos["nombre"],
                    "apellido" => $datos["apellido"],
                    "telefono" => $datos["telefono"],
                    "fecha_nacimiento" => $fechaNac,
                    "profesion" => $datos["profesion"],
                    "idcanton" => $datos["idcanton"],
                    "nit" => $datos["nit"],
                    "direccion" => $datos["direccion"]
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

function getCanton($id, $conexion)
{
    $sql = "SELECT nombre FROM canton WHERE idcanton = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);

    $stmt->execute();
    $resultado = $stmt->get_result();

    $canton = "";

    if ($fila = $resultado->fetch_assoc()) {
        $canton = $fila['nombre'];
    }

    $stmt->close();
    return $canton;
}
