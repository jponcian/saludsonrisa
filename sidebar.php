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

            // Definir las páginas y sus IDs
            $paginas_menu = [
                1 => ['file' => 'app_pacientes.php', 'icon' => 'fas fa-users', 'label' => 'Pacientes', 'group' => 'Administración'],
                2 => ['file' => 'app_facturacion.php', 'icon' => 'fas fa-file-invoice-dollar', 'label' => 'Facturación', 'group' => 'Administración'],
                3 => ['file' => 'app_especialistas.php', 'icon' => 'fas fa-user-md', 'label' => 'Especialistas', 'group' => 'Administración'],
                4 => ['file' => 'app_usuarios.php', 'icon' => 'fas fa-users-cog', 'label' => 'Usuarios', 'group' => 'Sistemas'],
                5 => ['file' => 'app_atencion_admin.php', 'icon' => 'fas fa-check-circle', 'label' => 'Validación', 'group' => 'Atención 24/7'],
                6 => ['file' => 'app_atencion_especialista.php', 'icon' => 'fas fa-ambulance', 'label' => 'Emergencia', 'group' => 'Atención 24/7'],
                8 => ['file' => 'app_acceso_admin.php', 'icon' => 'fas fa-user-shield', 'label' => 'Gestión de Roles', 'group' => 'Sistemas'],
                9 => ['file' => 'app_demo.php', 'icon' => 'fas fa-qrcode', 'label' => 'Demo QR', 'group' => 'Otros'],
            ];
            // Agrupar por grupo
            // Ordenar los grupos: Atención primero
            $grupos = ['Atención 24/7' => [], 'Administración' => [], 'Sistemas' => [], 'Otros' => []];
            foreach ($permisos_paginas as $pid) {
                if (isset($paginas_menu[$pid])) {
                    $grupo = $paginas_menu[$pid]['group'];
                    $grupos[$grupo][] = $paginas_menu[$pid];
                }
            }
            ?>
            <ul class="nav nav-pills nav-sidebar flex-column" id="sidebar-menu" data-widget="treeview" role="menu"
                data-accordion="false">
                <!-- Menú dinámico por permisos -->
                <?php foreach ($grupos as $grupo => $items): ?>
                    <?php if (!empty($items)): ?>
                        <li class="nav-item has-treeview">
                            <a href="#" class="nav-link">
                                <?php if ($grupo === 'Administración'): ?><i
                                        class="nav-icon fas fa-briefcase-medical"></i><?php endif; ?>
                                <?php if ($grupo === 'Sistemas'): ?><i class="nav-icon fas fa-sliders-h"></i><?php endif; ?>
                                <?php if ($grupo === 'Atención 24/7'): ?><i
                                        class="nav-icon fas fa-first-aid"></i><?php endif; ?>
                                <?php if ($grupo === 'Otros'): ?><i class="nav-icon fas fa-qrcode"></i><?php endif; ?>
                                <p><?php echo $grupo; ?><?php if ($grupo !== 'Otros'): ?><i
                                            class="right fas fa-angle-left"></i><?php endif; ?></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <?php foreach ($items as $item): ?>
                                    <li class="nav-item">
                                        <a href="<?php echo $item['file']; ?>"
                                            class="nav-link<?php echo ($current_page == $item['file']) ? ' active sidebar-child-active' : ''; ?>"
                                            style="padding-left:2rem;">
                                            <i class="nav-icon <?php echo $item['icon']; ?>"></i>
                                            <p><?php echo $item['label']; ?></p>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>