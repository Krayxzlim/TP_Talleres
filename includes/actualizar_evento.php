<?php
require_once 'db.php';
require_once 'funciones.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? null;
$fecha = $data['fecha'] ?? null;
$hora = $data['hora'] ?? null;

if (!$id || !$fecha || !$hora) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $pdo = conectarDB();
    $stmt = $pdo->prepare("UPDATE agenda SET fecha = ?, hora = ? WHERE id = ?");
    $stmt->execute([$fecha, $hora, $id]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    error_log("Error al actualizar evento: " . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Error al actualizar en BD']);
}
