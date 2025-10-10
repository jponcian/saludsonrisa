<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="app_inicio.php" class="brand-link"><img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3"><span
            class="brand-text font-weight-light">SaludSonrisa</span></a>
    <div class="sidebar">
        <nav class="mt-2">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            if (!isset($permisos_usuario) || !is_array($permisos_usuario)) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $permisos_usuario = $_SESSION['permisos'] ?? [];
                if (!is_array($permisos_usuario)) {
                    $permisos_usuario = [];
                }
            }
            $permisos_paginas = array_map('intval', $permisos_usuario);

            require_once 'api/conexion.php';
            // Obtener todas las páginas activas, ordenadas por grupo y orden
            $stmt = $pdo->prepare('SELECT * FROM paginas WHERE activo = 1 ORDER BY FIELD(grupo, "Atención 24/7", "Administración", "Sistemas", "Otros"), orden, nombre');
            $stmt->execute();
            $paginas_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por campo 'grupo' si existe, si no, usar 'Otros'
            $grupos = [];
            foreach ($paginas_db as $pagina) {
                if (!in_array((int)$pagina['id'], $permisos_paginas, true)) continue;
                $grupo = $pagina['grupo'] ?? 'Otros';
                if (!isset($grupos[$grupo])) $grupos[$grupo] = [];
                $grupos[$grupo][] = $pagina;
            }
            ?>
            <ul class="nav nav-pills nav-sidebar flex-column" id="sidebar-menu" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Menú dinámico por permisos -->
                <?php
                $ordenGrupos = ['Atención 24/7', 'Administración', 'Sistemas', 'Otros'];
                foreach ($ordenGrupos as $grupo) {
                    if (!empty($grupos[$grupo])) {
                        echo '<li class="nav-item has-treeview">';
                        echo '<a href="#" class="nav-link">';
                        if ($grupo === 'Administración') echo '<i class="nav-icon fas fa-briefcase-medical"></i>';
                        if ($grupo === 'Sistemas') echo '<i class="nav-icon fas fa-sliders-h"></i>';
                        if ($grupo === 'Atención 24/7') echo '<i class="nav-icon fas fa-first-aid"></i>';
                        if ($grupo === 'Otros') echo '<i class="nav-icon fas fa-qrcode"></i>';
                        echo '<p>' . $grupo . ($grupo !== 'Otros' ? '<i class="right fas fa-angle-left"></i>' : '') . '</p>';
                        echo '</a>';
                        echo '<ul class="nav nav-treeview">';
                        foreach ($grupos[$grupo] as $item) {
                            echo '<li class="nav-item">';
                            echo '<a href="' . htmlspecialchars($item['ruta']) . '" class="nav-link' . ($current_page == $item['ruta'] ? ' active sidebar-child-active' : '') . '" style="padding-left:2rem;">';
                            echo '<i class="nav-icon ' . htmlspecialchars($item['icon'] ?? 'fas fa-file') . '"></i>';
                            echo '<p>' . htmlspecialchars($item['nombre']) . '</p>';
                            echo '</a>';
                            echo '</li>';
                        }
                        echo '</ul>';
                        echo '</li>';
                    }
                }
                ?>
            </ul>
        </nav>
    </div>
</aside>