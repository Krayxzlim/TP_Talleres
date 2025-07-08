<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Portal de Talleres</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">Acerca</a>
                </li>

                <?php if (isset($_SESSION['usuario'])): ?>
                    <?php
                        $foto_perfil = "https://www.gravatar.com/avatar?d=mp"; // imagen por defecto
                        if (!empty($_SESSION['usuario']['foto']) && file_exists("uploads/" . $_SESSION['usuario']['foto'])) {
                            $foto_perfil = "uploads/" . $_SESSION['usuario']['foto'];
                        }
                    ?>

                    <?php if ($_SESSION['usuario']['rol'] === 'tallerista'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="colegios.php">Gestionar Colegios</a>
                        </li>
                    <?php endif; ?>
                        <?php if ($_SESSION['usuario']['rol'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_usuarios.php">Administrar Usuarios</a>
                        </li>
                    <?php endif; ?>
                    <!-- Drop de usuario -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?= htmlspecialchars($foto_perfil) ?>" alt="Perfil" width="32" height="32" class="rounded-circle me-2 border border-light shadow-sm">
                            <span class="text-capitalize fw-semibold"><?= htmlspecialchars($_SESSION['usuario']['usuario']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="perfil.php">
                                    <i class="bi bi-person-circle"></i> Mi Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger d-flex align-items-center gap-2" href="#" onclick="logoutConLimpieza()">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                    <script>
                    function logoutConLimpieza() {
                        sessionStorage.removeItem('bienvenidaMostrada');
                        window.location.href = 'logout.php';
                    }
                    </script>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registro.php">Registro</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
