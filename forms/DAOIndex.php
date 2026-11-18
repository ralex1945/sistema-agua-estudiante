<?php
$opcion = $_POST['opcion'] ?? '';

if ($opcion == "consulta" || $opcion == "consultar") {
    require_once "../config/conexion.php";

    try {
        $sql = "SELECT IFNULL(SUM(monto), 0) AS total_mes
                FROM pagos
                WHERE fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
                AND fecha < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $fila = $resultado->fetch_assoc();

        $sumaMensual = number_format($fila['total_mes'], 2, ".", ",");

        //////////////////////////////////////
        $sql = "SELECT COUNT(*) AS pendientesPago
                FROM consumo AS c
                INNER JOIN medidor AS m
                    ON c.idmedidor = m.idmedidor
                WHERE m.estado = 'ACTIVO'
                AND c.estado = 'PENDIENTE'";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $fila = $resultado->fetch_assoc();
        $pendientesPago = $fila['pendientesPago'];

        //////////////////////////////////////
        $sql = "SELECT COUNT(*) AS numeroSocio
                FROM socio";

        $stmt = $conexion->prepare($sql);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $fila = $resultado->fetch_assoc();
        $numeroSocio = $fila['numeroSocio'];

        //////////////////////////////////////
        echo json_encode([
            "estado" => true,
            "sumaMensual" => $sumaMensual,
            "pendientesPago" => $pendientesPago,
            "numeroSocio" => $numeroSocio
        ], JSON_UNESCAPED_UNICODE);

        $resultado->free();
        $stmt->close();
        $conexion->close();
    } catch (Exception $e) {
        echo json_encode([
            "estado" => false,
            "mensaje" => "Error: " . $e->getMessage()
        ]);
    }
}
?>
