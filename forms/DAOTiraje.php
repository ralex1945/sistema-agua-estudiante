<?php
$prefijo = $_POST['prefijo'] ?? '';
$opcion = $_POST['opcion'] ?? '';

if ($opcion == "guardar") {
    require_once "../config/conexion.php";

    //obtener el ultimo prefijo
    $prefijo = getPrefijo($conexion) + 1; //2026 + 1=2027
    $ultimo = 0;
    $estado = "ACTIVO";

    try {
        $conexion->begin_transaction();

        //Desactivar todos los tirajes
        $sql = "UPDATE tiraje SET estado = 'INACTIVO'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $stmt->close();

        //Insertar el nuevo tiraje activo
        $sql = "INSERT INTO tiraje (prefijo, ultimo, estado) VALUES (?, ?, ?)";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("sis", $prefijo, $ultimo, $estado);
        $stmt->execute();
        $stmt->close();

        //Confirmar los cambios
        $conexion->commit();

        echo json_encode([
            "estado" => true,
            "mensaje" => "Datos guardados correctamente"
        ]);
    } catch (mysqli_sql_exception $e) {
        //Revertir los cambios si ocurre un error
        $conexion->rollback();
        echo json_encode([
            "estado" => false,
            "mensaje" => "Ocurrió un error al guardar: " . $e->getMessage()
        ]);
    } finally {
        $conexion->close();
    }
}

if ($opcion == "consultarPrefijo") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT * FROM tiraje WHERE prefijo=?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $prefijo);

        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();

            echo json_encode([
                "estado" => true,
                "datos" => [
                    "ultimo" => (int)$fila['ultimo'],
                    "estado" => $fila['estado']
                ]
            ]);
        } else {
            echo json_encode([
                "estado" => false,
                "datos" => [
                    "ultimo" => 0,
                    "estado" => ""
                ]
            ]);
        }

        $stmt->close();
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error al consultar: " . $e->getMessage()
        ]);
    }
}

if ($opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT * FROM tiraje";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 0) { //NO EXISTE NADA EN LA TABLA TIRAJE
            $prefijo = date("Y");
            $ultimo = 0;
            $estado = "ACTIVO";

            $sqlInsert = "INSERT INTO tiraje (prefijo, ultimo, estado) VALUES (?, ?, ?)";

            $stmtInsert = $conexion->prepare($sqlInsert);
            $stmtInsert->bind_param("sis", $prefijo, $ultimo, $estado);
            $stmtInsert->execute();
            $stmtInsert->close();

            // Consultar nuevamente el registro creado
            $resultado->free();

            $sql = "SELECT * FROM tiraje";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();

            $resultado = $stmt->get_result();
        }

        $registros = [];

        while ($fila = $resultado->fetch_assoc()) {
            $registros[] = [
                'prefijo' => $fila['prefijo']
            ];
        }

        echo json_encode($registros, JSON_UNESCAPED_UNICODE);

        $resultado->free();
        $stmt->close();
        $conexion->close();
    } catch (Throwable $e) {
        echo json_encode([
            "mensaje" => "No se pudieron obtener los registros: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

function getPrefijo($conexion)
{
    $sql = "SELECT max(prefijo) as prefijo FROM tiraje";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        return $fila['prefijo'];
    }

    $stmt->close();
    $conexion->close();
}

