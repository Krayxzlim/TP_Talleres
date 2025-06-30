<?php
session_start();
require_once("includes/db.php");  // archivo conexión PDO

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanear entrada
    $usuario_input = trim($_POST['usuario']);
    $password = $_POST['password'];

    if (empty($usuario_input) || empty($password)) {
        $mensaje = "Por favor completa todos los campos.";
    } else {
        try {
            $pdo = conectarDB();

            // Buscar usuario por nombre
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
            $stmt->execute([$usuario_input]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['contraseña'])) {
                // Contraseña correcta, guardar sesión
                unset($usuario['contraseña']); // por seguridad, no guardar pass en sesión
                $_SESSION['usuario'] = $usuario;
                header("Location: index.php");
                exit;
            } else {
                $mensaje = "Usuario o contraseña incorrectos.";
            }
        } catch (PDOException $e) {
            // Redirigir a página de error si falla conexión DB
            header("Location: error.php");
            exit;
        }
    }
}

include("includes/header.php");
include("includes/nav.php");
?>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <form method="post" class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>

        <?php if (!empty($mensaje)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <div class="mb-3">
          <label for="usuario" class="form-label">Usuario</label>
          <input type="text" class="form-control" id="usuario" name="usuario" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Ingresar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>
