<?php
require_once 'db.php';
function obtenerUsuarios() {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->query("SELECT * FROM usuarios ORDER BY usuario ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerUsuarios: " . $e->getMessage());
        echo "Error en obtenerUsuarios: " . $e->getMessage();
        exit;
    }
}
function obtenerColegios() {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->query("SELECT * FROM colegios ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerColegios: " . $e->getMessage());
        echo "Error en obtenerColegios: " . $e->getMessage();
        exit;
    }
}
function colegioExisteNombre($nombre, $exceptoId = null) {
    try {
        $pdo = conectarDB();
        if ($exceptoId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM colegios WHERE LOWER(nombre) = LOWER(?) AND id != ?");
            $stmt->execute([$nombre, $exceptoId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM colegios WHERE LOWER(nombre) = LOWER(?)");
            $stmt->execute([$nombre]);
        }
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error en colegioExisteNombre: " . $e->getMessage());
        echo "Error en colegioExisteNombre: " . $e->getMessage();
        exit;
    }
}
function agregarColegio($nombre, $direccion, $primario, $secundario) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("INSERT INTO colegios (nombre, direccion, primario, secundario) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nombre, $direccion, $primario, $secundario]);
    } catch (PDOException $e) {
        error_log("Error en agregarColegio: " . $e->getMessage());
        echo "Error en agregarColegio: " . $e->getMessage();
        exit;
    }
}
function editarColegio($id, $nombre, $direccion, $primario, $secundario) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("UPDATE colegios SET nombre = ?, direccion = ?, primario = ?, secundario = ? WHERE id = ?");
        return $stmt->execute([$nombre, $direccion, $primario, $secundario, $id]);
    } catch (PDOException $e) {
        error_log("Error en editarColegio: " . $e->getMessage());
        echo "Error en editarColegio: " . $e->getMessage();
        exit;
    }
}
function eliminarColegio($id) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("DELETE FROM colegios WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Error en eliminarColegio: " . $e->getMessage());
        echo "Error en eliminarColegio: " . $e->getMessage();
        exit;
    }
}
function obtenerColegioPorId($id) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM colegios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerColegioPorId: " . $e->getMessage());
        echo "Error en obtenerColegioPorId: " . $e->getMessage();
        exit;
    }
}
function obtenerAgendaCompleta() {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->query("SELECT a.*, c.nombre AS colegio_nombre, t.nombre AS taller_nombre
            FROM agenda a LEFT JOIN colegios c ON a.colegio_id = c.id 
            LEFT JOIN talleres t ON a.taller_id = t.id");
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Preparar consulta para obtener usuarios por agenda_id
        $stmt2 = $pdo->prepare("
            SELECT u.usuario 
            FROM agenda_talleristas at
            JOIN usuarios u ON u.id = at.usuario_id
            WHERE at.agenda_id = ?
        ");

        $agenda = [];
        foreach ($eventos as $evento) {
            $stmt2->execute([$evento['id']]);
            $evento['talleristas'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);
            $agenda[$evento['id']] = $evento;  // indexado por id para seguridad
        }
        return $agenda;
    } catch (PDOException $e) {
        error_log("Error en obtenerAgendaCompleta: " . $e->getMessage());
        echo "Error en obtenerAgendaCompleta: " . $e->getMessage();
        exit;
    }
}function obtenerTalleres() {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->query("SELECT id, nombre FROM talleres ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerTalleres: " . $e->getMessage());
        echo "Error en obtenerTalleres: " . $e->getMessage();
        exit;
    }
}
function agregarEvento($colegio_id, $taller_id, $fecha, $hora) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("INSERT INTO agenda (colegio_id, taller_id, fecha, hora) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$colegio_id, $taller_id, $fecha, $hora]);
    } catch (PDOException $e) {
        error_log("Error en agregarEvento: " . $e->getMessage());
        echo "Error en agregarEvento: " . $e->getMessage();
        exit;
    }
}function obtenerEventoPorId($id) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT * FROM agenda WHERE id = ?");
        $stmt->execute([$id]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$evento) return null;

        $stmt2 = $pdo->prepare("
            SELECT u.usuario 
            FROM agenda_talleristas at
            JOIN usuarios u ON u.id = at.usuario_id
            WHERE at.agenda_id = ?
        ");
        $stmt2->execute([$id]);
        $evento['talleristas'] = $stmt2->fetchAll(PDO::FETCH_COLUMN);

        return $evento;
    } catch (PDOException $e) {
        error_log("Error en obtenerEventoPorId: " . $e->getMessage());
        echo "Error en obtenerEventoPorId: " . $e->getMessage();
        exit;
    }
}
function editarEvento($id, $colegio_id, $taller_id, $fecha, $hora) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("UPDATE agenda SET colegio_id = ?, taller_id = ?, fecha = ?, hora = ? WHERE id = ?");
        return $stmt->execute([$colegio_id, $taller_id, $fecha, $hora, $id]);
    } catch (PDOException $e) {
        error_log("Error en editarEvento: " . $e->getMessage());
        echo "Error en editarEvento: " . $e->getMessage();
        exit;
    }
}
function eliminarEvento($id) {
    try {
        $pdo = conectarDB();

        // Eliminar de agenda_talleristas primero
        $stmt = $pdo->prepare("DELETE FROM agenda_talleristas WHERE agenda_id = ?");
        $stmt->execute([$id]);

        // Luego de agenda
        $stmt = $pdo->prepare("DELETE FROM agenda WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Error en eliminarEvento: " . $e->getMessage());
        return false;
    }
}

function asignarTallerista($agenda_id, $tallerista_usuario) {
    try {
        $pdo = conectarDB();

        // Obtener id de usuario a partir del nombre de usuario
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$tallerista_usuario]);
        $usuario_id = $stmt->fetchColumn();
        if (!$usuario_id) {
            return "Usuario no encontrado.";
        }

        // Verificar si ya está asignado
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agenda_talleristas WHERE agenda_id = ? AND usuario_id = ?");
        $stmt->execute([$agenda_id, $usuario_id]);
        if ($stmt->fetchColumn() > 0) {
            return "Ese tallerista ya está asignado.";
        }

        // Verificar cantidad asignada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agenda_talleristas WHERE agenda_id = ?");
        $stmt->execute([$agenda_id]);
        if ($stmt->fetchColumn() >= 2) {
            return "Ya hay 2 talleristas asignados.";
        }

        // Asignar
        $stmt = $pdo->prepare("INSERT INTO agenda_talleristas (agenda_id, usuario_id) VALUES (?, ?)");
        if ($stmt->execute([$agenda_id, $usuario_id])) {
            return "ok";
        } else {
            return "Error al asignar tallerista.";
        }
    } catch (PDOException $e) {
        error_log("Error en asignarTallerista: " . $e->getMessage());
        return "Error en asignarTallerista: " . $e->getMessage();
    }
}
function eliminarUsuarioPorNombre($usuario) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE usuario = ?");
        return $stmt->execute([$usuario]);
    } catch (PDOException $e) {
        error_log("Error al eliminar usuario: " . $e->getMessage());
        return false;
    }
}
function cambiarRolUsuario($usuario, $nuevoRol) {
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE usuario = ?");
        return $stmt->execute([$nuevoRol, $usuario]);
    } catch (PDOException $e) {
        error_log("Error al cambiar rol: " . $e->getMessage());
        return false;
    }
}
function eliminarTalleristaDeEvento($agenda_id, $usuario_nombre) {
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
        return false; // Bloquear si no es admin
    }
    try {
        $pdo = conectarDB();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario_nombre]);
        $usuario_id = $stmt->fetchColumn();

        if (!$usuario_id) return false;

        $stmt = $pdo->prepare("DELETE FROM agenda_talleristas WHERE agenda_id = ? AND usuario_id = ?");
        return $stmt->execute([$agenda_id, $usuario_id]);
    } catch (PDOException $e) {
        error_log("Error en eliminarTalleristaDeEvento: " . $e->getMessage());
        return false;
    }
}


