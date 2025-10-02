<?php require_once 'api/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Atención 24/7 - Validación</title>
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>

<body class="hold-transition sidebar-mini">
    <?php include 'sidebar.php'; ?>
    <div class="content-wrapper p-3">
        <section class="content">
            <div class="container-fluid">
                <h4><i class="fas fa-check-circle text-success mr-2"></i>Validación / Apertura de Atención 24/7</h4>
                <div class="card card-primary card-outline">
                    <div class="card-header p-2">
                        <strong>1. Selección de Paciente</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="text-center">Paciente</label>
                                <select id="selPaciente" class="form-control text-center">
                                    <option value="">Cargando...</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="text-center">Plan</label>
                                <input id="txtPlan" class="form-control text-center" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="text-center">Estado del Plan</label>
                                <div class="text-center">
                                    <span id="estadoPlanBadge" class="badge" style="font-size: 1rem; padding: .5rem 1rem;">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="text-center">Inscripción</label>
                                <input id="txtInscripcion" class="form-control text-center" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="text-center">Inicio Cobertura (45 días)</label>
                                <input id="txtInicioCobertura" class="form-control text-center" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="text-center">Mensualidad</label>
                                <input id="txtMensualidad" class="form-control text-right" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="text-center">Días Restantes para Cobertura</label>
                                <input id="txtDiasRestantes" class="form-control text-center" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info card-outline">
                    <div class="card-header p-2">
                        <strong>2. Consumos del Plan</strong>
                    </div>
                    <div class="card-body">
                        <div class="row" id="resumenConsumos">
                            <!-- KPIs dinámicos -->
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-sm table-bordered" id="tablaConsumos">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Detalle</th>
                                        <th>Cantidad</th>
                                        <th>Especialista</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card card-success card-outline">
                    <div class="card-header p-2">
                        <strong>3. Apertura de Proceso de Atención</strong>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Motivo / Síntomas</label>
                                <input id="txtMotivo" class="form-control" maxlength="140" placeholder="Dolor abdominal, fiebre...">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Nivel de Urgencia</label>
                                <select id="selUrgencia" class="form-control">
                                    <option value="">Seleccione</option>
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                    <option value="critica">Crítica</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Observaciones</label>
                                <input id="txtObs" class="form-control" maxlength="140">
                            </div>
                        </div>
                        <button id="btnAbrirProceso" class="btn btn-success" disabled><i class="fas fa-play mr-1"></i>Abrir Proceso</button>
                        <span id="lblProcesoMsg" class="ml-2 text-muted"></span>
                    </div>
                </div>

                <div class="card card-secondary card-outline">
                    <div class="card-header p-2">
                        <strong>4. Procesos Abiertos</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="tablaProcesos">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Paciente</th>
                                        <th>Motivo</th>
                                        <th>Urgencia</th>
                                        <th>Estado</th>
                                        <th>Creado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/select2/js/select2.full.min.js"></script>
    <script src="dist/js/adminlte.min.js"></script>
    <script src="js/atencion_admin.js"></script>
</body>

</html>/js/adminlte.min.js"></script>
<script src="js/atencion_admin.js"></script>
</body>

</html>