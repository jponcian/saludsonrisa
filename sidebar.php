<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="app_inicio.php" class="brand-link"><img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3"><span
            class="brand-text font-weight-light">SaludSonrisa</span></a>
    <div class="sidebar">
        <nav class="mt-2">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);

            $atencion_pages = ['app_atencion_admin.php', 'app_atencion_especialista.php'];
            $is_atencion_active = in_array($current_page, $atencion_pages);

            $admin_pages = ['app_pacientes.php', 'app_facturacion.php', 'app_especialistas.php', 'app_usuarios.php'];
            $is_admin_active = in_array($current_page, $admin_pages);
            ?>
            <ul class="nav nav-pills nav-sidebar flex-column" id="sidebar-menu" data-widget="treeview" role="menu" data-accordion="false">
                <!-- ...otras opciones del menú... -->
                <li class="nav-item has-treeview<?php echo $is_atencion_active ? ' menu-open' : ''; ?>">
                    <a href="#" class="nav-link<?php echo $is_atencion_active ? ' active sidebar-parent-active' : ''; ?>">
                        <i class="nav-icon fas fa-first-aid"></i>
                        <p>Atención 24/7<i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="app_atencion_admin.php" class="nav-link<?php echo ($current_page == 'app_atencion_admin.php') ? ' active sidebar-child-active' : ''; ?>" style="padding-left:2rem;">
                                <i class="nav-icon fas fa-check-circle"></i>
                                <p>Validación</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="app_atencion_especialista.php" class="nav-link<?php echo ($current_page == 'app_atencion_especialista.php') ? ' active sidebar-child-active' : ''; ?>" style="padding-left:2rem;">
                                <i class="nav-icon fas fa-ambulance"></i>
                                <p>Emergencia</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php if (isset($rol) && $rol === 'admin_usuarios'): ?>
                    <li class="nav-item has-treeview<?php echo $is_admin_active ? ' menu-open' : ''; ?>">
                        <a href="#" class="nav-link<?php echo $is_admin_active ? ' active sidebar-parent-active' : ''; ?>">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                                Administración
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="app_pacientes.php"
                                    class="nav-link<?php echo ($current_page == 'app_pacientes.php') ? ' active sidebar-child-active' : ''; ?>"
                                    style="padding-left: 2rem;">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Pacientes</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="app_facturacion.php"
                                    class="nav-link<?php echo ($current_page == 'app_facturacion.php') ? ' active sidebar-child-active' : ''; ?>"
                                    style="padding-left: 2rem;">
                                    <i class="nav-icon fas fa-file-invoice-dollar"></i>
                                    <p>Facturación</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="app_especialistas.php"
                                    class="nav-link<?php echo ($current_page == 'app_especialistas.php') ? ' active sidebar-child-active' : ''; ?>"
                                    style="padding-left: 2rem;">
                                    <i class="nav-icon fas fa-user-md"></i>
                                    <p>Especialistas</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="app_usuarios.php"
                                    class="nav-link<?php echo ($current_page == 'app_usuarios.php') ? ' active sidebar-child-active' : ''; ?>"
                                    style="padding-left: 2rem;">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <p>Usuarios</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="app_demo.php" class="nav-link<?php echo ($current_page == 'app_demo.php') ? ' active sidebar-child-active' : ''; ?>">
                        <i class="nav-icon fas fa-qrcode"></i>
                        <p>Demo QR</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>