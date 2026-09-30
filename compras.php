<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Compras - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Tu hoja de estilos externa se mantiene intacta -->
    <link rel="stylesheet" href="css/estilos.css">
    <script>
        if (!localStorage.getItem('token')) {
            window.location.href = 'index.php';
        }
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-custom shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand text-dark fw-bold" href="#">
                <i class="bi bi-box-seam me-2"></i>Sistema de Inventario
            </a>

            <!-- CONTENEDOR: Botón de Historial + Perfil + Salir -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm text-white px-3 shadow-sm" style="background-color: #8c837b; border: none;"
                    data-bs-toggle="modal" data-bs-target="#modalHistorial">
                    <i class="bi bi-clock-history me-1"></i> Historial y Exportación
                </button>

                <span class="navbar-text text-dark mb-0 border-start ps-3 pe-2">
                    Perfil: Administrador
                </span>
                <!-- Botón de Cerrar Sesión -->
                <button onclick="cerrarSesion()" class="btn btn-sm btn-outline-danger shadow-sm" title="Cerrar Sesión">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </button>
            </div>
        </div>
    </nav>

    <!-- Pestañas de Navegación Superior -->
    <div class="container mt-4">
        <ul class="nav nav-tabs" id="pestanasCompras" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-dark" id="tab-alertas" data-bs-toggle="tab"
                    data-bs-target="#seccion-alertas" type="button" role="tab">
                    <i class="bi bi-exclamation-triangle me-2"></i>Alertas y Reportes
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="tab-proveedores" data-bs-toggle="tab"
                    data-bs-target="#seccion-proveedores" type="button" role="tab">
                    <i class="bi bi-truck me-2"></i>Gestión de Proveedores
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="tab-empleados" data-bs-toggle="tab"
                    data-bs-target="#seccion-empleados" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>Gestión de Empleados
                </button>
            </li>
        </ul>
    </div>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content" id="contenidoPestanasCompras">

        <!-- PESTAÑA 1: Alertas y Reportes -->
        <div class="tab-pane fade show active container mt-4" id="seccion-alertas" role="tabpanel">

            <!-- TARJETAS SUPERIORES -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card metric-card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase">Total de Insumos Activos</h6>
                            <h2 id="val-total-insumos" class="mb-0 fw-bold">-</h2>
                            <small class="text-success"><i class="bi bi-arrow-up-short"></i> Actualizado desde
                                BD</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card metric-card metric-card-danger shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase">Insumos con Stock Bajo</h6>
                            <h2 id="val-stock-bajo" class="mb-0 fw-bold text-danger">-</h2>
                            <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Requieren
                                reabastecimiento</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card metric-card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase">Área de Mayor Consumo</h6>
                            <h4 id="val-area-consumo" class="mb-0 fw-bold text-dark mt-2">-</h4>
                            <small id="val-porcentaje-consumo" class="text-muted">Calculando...</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUB-PESTAÑAS PARA NO AMONTONAR (NUEVO DISEÑO) -->
            <ul class="nav nav-pills mb-4 justify-content-center" id="subPestanasReportes" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4" id="pill-tablas" data-bs-toggle="pill"
                        data-bs-target="#sub-tablas" type="button" role="tab">
                        <i class="bi bi-table me-2"></i>Reportes y Alertas
                    </button>
                </li>
                <li class="nav-item ms-2" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="pill-graficas" data-bs-toggle="pill"
                        data-bs-target="#sub-graficas" type="button" role="tab">
                        <i class="bi bi-bar-chart-fill me-2"></i>Análisis Visual
                    </button>
                </li>
            </ul>

            <!-- CONTENIDO DE LAS SUB-PESTAÑAS -->
            <div class="tab-content" id="contenidoSubPestanas">

                <!-- SUB-VISTA 1: Tablas completas -->
                <div class="tab-pane fade show active" id="sub-tablas" role="tabpanel">
                    <!-- Tabla de Alertas -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div
                            class="card-header card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                            <span><i class="bi bi-cart-x me-2"></i>Alertas de Reabastecimiento</span>
                            <div class="d-flex gap-2">
                                <button id="btn-exportar" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Exportar a PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <!-- Se agregó max-height y overflow -->
                            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                <table id="tabla-faltantes" class="table table-hover align-middle mb-0">
                                    <!-- Se agregó position sticky al encabezado -->
                                    <thead class="table-header-custom" style="position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="ps-3">ID</th>
                                            <th>Descripción</th>
                                            <th class="text-center">Stock Actual</th>
                                            <th class="text-center">Stock Mínimo</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-faltantes">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Cargando alertas...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Duración -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header card-header-custom">
                            <i class="bi bi-clock-history me-2"></i>Estimación de Duración de Inventario
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-header-custom" style="position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="ps-3">Insumo</th>
                                            <th class="text-center">Stock Actual</th>
                                            <th class="text-center">Total de Salidas</th>
                                            <th class="text-center">Consumo Promedio Diario</th>
                                            <th class="text-center">Duración Estimada</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-rotacion">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Calculando estimaciones
                                                de tiempo...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- SUB-VISTA 2: Gráficas de Análisis Gerencial -->
                <div class="tab-pane fade" id="sub-graficas" role="tabpanel">
                    <div class="row">
                        <!-- Gráfica 1: Stock (La que ya tenías) -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header card-header-custom">
                                    <i class="bi bi-pie-chart me-2"></i>Estado del Stock
                                </div>
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <canvas id="graficaConsumo" style="max-height: 250px;"></canvas>
                                    <p class="text-muted text-center mt-3 mb-0 small">Relación de stock normal vs bajo.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica 2: Productos Más Solicitados -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header card-header-custom">
                                    <i class="bi bi-bar-chart me-2"></i>Top Productos Solicitados
                                </div>
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <canvas id="graficaTopProductos" style="max-height: 250px;"></canvas>
                                    <p class="text-muted text-center mt-3 mb-0 small">Los 5 insumos con mayor salida.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica 3: Áreas con Mayor Consumo -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header card-header-custom">
                                    <i class="bi bi-diagram-3 me-2"></i>Áreas de Mayor Consumo
                                </div>
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <canvas id="graficaTopAreas" style="max-height: 250px;"></canvas>
                                    <p class="text-muted text-center mt-3 mb-0 small">Departamentos que solicitan más
                                        material.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- Fin Contenido Sub Pestañas -->
        </div>

        <!-- PESTAÑA 2: Gestión de Proveedores -->
        <div class="tab-pane fade container mt-4" id="seccion-proveedores" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                <input type="text" id="input-buscar-proveedor" class="form-control w-100"
                    placeholder="Buscar proveedor por empresa o contacto...">
                <button id="btn-nuevo-proveedor" class="btn text-white text-nowrap shadow-sm"
                    style="background-color: #8c837b; border: none;">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo Proveedor
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header card-header-custom">
                    <i class="bi bi-truck me-2"></i>Listado de Proveedores Registrados
                </div>
                <div class="card-body p-0">
                    <!-- Se agregó max-height y overflow -->
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <!-- Se agregó position sticky al encabezado -->
                            <thead class="table-header-custom" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="ps-3">Empresa</th>
                                    <th>Contacto</th>
                                    <th>Teléfono</th>
                                    <th>Correo</th>
                                    <th>Dirección</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-proveedores">
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Cargando proveedores...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 3: Gestión de Empleados -->
        <div class="tab-pane fade container mt-4" id="seccion-empleados" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                <input type="text" id="input-buscar-empleado" class="form-control w-100"
                    placeholder="Buscar empleado por nombre o correo...">
                <button id="btn-nuevo-empleado" class="btn text-white text-nowrap shadow-sm"
                    style="background-color: #8c837b; border: none;">
                    <i class="bi bi-person-plus me-1"></i>Nuevo Empleado
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header card-header-custom">
                    <i class="bi bi-people me-2"></i>Listado de Empleados del Sistema
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-header-custom">
                                <tr>
                                    <th class="ps-3">Nombre</th>
                                    <th>Correo</th>
                                    <th>Rol</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-empleados">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Cargando empleados...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL EMERGENTE: Historial de Movimientos -->
    <div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 2px solid #8c837b;">
                    <h5 class="modal-title fw-bold text-dark" id="modalHistorialLabel">
                        <i class="bi bi-list-check me-2"></i>Historial Detallado de Movimientos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-header-custom" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="ps-3">Fecha/Hora</th>
                                    <th>Tipo</th>
                                    <th>Insumo / Detalle</th>
                                    <th>Responsable</th>
                                    <th class="text-center">Motivo</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-historial-compras">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Cargando historial...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer" style="background-color: #f8f9fa;">
                    <!-- Botón de Excel integrado en el pie del modal -->
                    <button id="btn-exportar-csv" class="btn btn-outline-success">
                        <i class="bi bi-file-earmark-excel me-1"></i>Exportar a CSV
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Librerías de terceros -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- NUEVO: Conexión limpia a la lógica de JavaScript -->
    <script src="js/compras.js?v=2"></script>
</body>

</html>