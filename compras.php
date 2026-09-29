<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Compras - Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
                <!-- NUEVO: Botón de Cerrar Sesión -->
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
                        data-bs-target="#sub-tablas" type="button" role="tab" style="color: #495057;">
                        <i class="bi bi-table me-2"></i>Reportes y Alertas
                    </button>
                </li>
                <li class="nav-item ms-2" role="presentation">
                    <button class="nav-link rounded-pill px-4" id="pill-graficas" data-bs-toggle="pill"
                        data-bs-target="#sub-graficas" type="button" role="tab" style="color: #495057;">
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
                                <!-- Se eliminó el botón CSV de aquí -->
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

    <!-- Estilo extra para el botón activo de las subpestañas -->
    <style>
        .nav-pills .nav-link.active {
            background-color: #8c837b !important;
            color: white !important;
        }
    </style>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const BASE_URL = 'backend';
        const token = localStorage.getItem('token');

        let instanciaGrafica = null;
        let chartTopProductos = null;
        let chartTopAreas = null;

        let listaProveedoresGlobal = [];
        let listaEmpleadosGlobal = [];

        async function cargarDatosCompras() {
            try {
                // 1. Cargar productos generales
                const resProductos = await fetch(`${BASE_URL}/productos/get_productos.php?limite=200`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const jsonData = await resProductos.json();
                const productosRaw = jsonData.datos ? jsonData.datos : jsonData;

                const productos = productosRaw.map(p => ({
                    id: p.id,
                    descripcion: p.nombre,
                    stock: parseInt(p.stock),
                    stock_minimo: parseInt(p.stock_minimo) || 5,
                    stock_maximo: parseInt(p.stock_maximo) || 20
                }));

                document.getElementById('val-total-insumos').textContent = productos.length;

                const faltantes = productos.filter(p => p.stock <= p.stock_minimo);
                document.getElementById('val-stock-bajo').textContent = faltantes.length;

                const tbodyFaltantes = document.getElementById('tbody-faltantes');
                if (faltantes.length === 0) {
                    tbodyFaltantes.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">¡Excelente! No hay insumos con stock bajo.</td></tr>`;
                } else {
                    tbodyFaltantes.innerHTML = faltantes.map(p => {
                        let esCritico = p.stock <= (p.stock_minimo / 2);
                        let badge = esCritico ? '<span class="badge bg-danger rounded-pill px-3">Crítico</span>' : '<span class="badge bg-warning text-dark rounded-pill px-3">Por Agotarse</span>';
                        let cantidadSugerida = (p.stock_maximo || 20) - p.stock;
                        if (cantidadSugerida < 0) cantidadSugerida = 0;

                        return `
                        <tr>
                            <td class="ps-3 text-muted">${p.id}</td>
                            <td class="fw-medium">${p.descripcion}</td>
                            <td class="text-center text-danger fw-bold">${p.stock}</td>
                            <td class="text-center text-muted">${p.stock_minimo}</td>
                            <td class="text-center">${badge}</td>
                        </tr>`;
                    }).join('');
                }

                // Cargar gráfica de Stock
                const conStock = productos.filter(p => p.stock > p.stock_minimo).length;
                inicializarGrafica([conStock, faltantes.length]);

                // 2. Cargar Estadísticas (Área Mayor Consumo)
                try {
                    const resEstadisticas = await fetch(`${BASE_URL}/estadisticas_mensajes/get_estadisticas.php`, { headers: { 'Authorization': 'Bearer ' + token } });
                    if (resEstadisticas.ok) {
                        const estadisticas = await resEstadisticas.json();
                        const areas = estadisticas.top_areas || [];

                        if (areas.length > 0) {
                            // 1. Tomamos el área número 1 (la que más consumió)
                            const areaMayor = areas[0].area_destino || "Sin registros";

                            // 2. Sumamos todo para calcular el porcentaje real
                            let totalConsumo = 0;
                            areas.forEach(a => totalConsumo += parseFloat(a.total_pedidos || 0));

                            let porcentaje = 0;
                            if (totalConsumo > 0) {
                                const mayorConsumo = parseFloat(areas[0].total_pedidos || 0);
                                porcentaje = Math.round((mayorConsumo / totalConsumo) * 100);
                            }

                            // 3. Pintamos los valores en la tarjeta
                            document.getElementById('val-area-consumo').textContent = areaMayor;
                            document.getElementById('val-porcentaje-consumo').textContent = `${porcentaje}% del consumo total`;
                        } else {
                            document.getElementById('val-area-consumo').textContent = "Sin registros";
                            document.getElementById('val-porcentaje-consumo').textContent = "0% del consumo total";
                        }
                    }
                } catch (e) {
                    document.getElementById('val-area-consumo').textContent = "Sin datos";
                }

                // 3. Cargar Análisis de Tiempos y Costos (Rotación)
                try {
                    const resAnalisis = await fetch(`${BASE_URL}/estadisticas_mensajes/get_analisis_costos.php`, { headers: { 'Authorization': 'Bearer ' + token } });
                    const tbodyRotacion = document.getElementById('tbody-rotacion');

                    if (resAnalisis.ok) {
                        const dataAnalisis = await resAnalisis.json();
                        const rotacion = dataAnalisis.rotacion_inventario || [];

                        if (rotacion.length === 0) {
                            tbodyRotacion.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Aún no hay suficientes salidas registradas para calcular.</td></tr>`;
                        } else {
                            tbodyRotacion.innerHTML = rotacion.map(r => `
                                <tr>
                                    <td class="ps-3 fw-medium">${r.nombre || 'N/A'}</td>
                                    <td class="text-center">${r.stock_actual || 0}</td>
                                    <td class="text-center">${r.total_salidas || 0}</td>
                                    <td class="text-center">${r.consumo_promedio_diario || 0}</td>
                                    <td class="text-center fw-bold" style="color: #8c837b;"><i class="bi bi-calendar-check me-1"></i>${r.dias_estimados_restantes || 'N/A'}</td>
                                </tr>`).join('');
                        }
                    } else {
                        tbodyRotacion.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">Registra más salidas para generar la estimación.</td></tr>`;
                    }
                } catch (e) {
                    document.getElementById('tbody-rotacion').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Error al conectar con la estimación.</td></tr>`;
                }

                // 4. NUEVO: Cargar Estadísticas Gerenciales (Top Productos y Áreas)
                try {
                    // Asegúrate de que este archivo PHP exista y tenga este nombre exacto
                    const resGerencial = await fetch(`${BASE_URL}/estadisticas_mensajes/get_estadisticas.php`, {
                        headers: { 'Authorization': 'Bearer ' + token }
                    });

                    if (resGerencial.ok) {
                        const dataGerencial = await resGerencial.json();

                        // Renderizar las nuevas gráficas
                        renderizarGraficaTopProductos(dataGerencial.top_productos || dataGerencial.datos?.top_productos || []);
                        renderizarGraficaTopAreas(dataGerencial.top_areas || dataGerencial.datos?.top_areas || []);
                    }
                } catch (e) {
                    console.error("Error al cargar gráficas gerenciales:", e);
                }

            } catch (error) {
                console.error('Error al cargar los datos de compras:', error);
                document.getElementById('tbody-faltantes').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Error de conexión.</td></tr>`;
            }
        }

        // --- FUNCIONES DE RENDERIZADO DE GRÁFICAS ---
        function inicializarGrafica(datosConsumo) {
            const ctx = document.getElementById('graficaConsumo').getContext('2d');
            if (instanciaGrafica) instanciaGrafica.destroy();

            instanciaGrafica = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Stock Normal', 'Stock Bajo / Crítico'],
                    datasets: [{
                        data: datosConsumo,
                        backgroundColor: ['#8c837b', '#d9534f'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }

        function renderizarGraficaTopProductos(datos) {
            const ctx = document.getElementById('graficaTopProductos').getContext('2d');
            if (chartTopProductos) chartTopProductos.destroy();

            const labels = datos.length ? datos.map(d => d.nombre) : ['Sin datos'];
            // Cambiamos d.total_pedido por d.total_salidas
            const values = datos.length ? datos.map(d => d.total_salidas) : [0];

            chartTopProductos = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Cantidades Solicitadas',
                        data: values,
                        backgroundColor: '#8c837b',
                        borderRadius: 4
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });
        }

        function renderizarGraficaTopAreas(datos) {
            const ctx = document.getElementById('graficaTopAreas').getContext('2d');
            if (chartTopAreas) chartTopAreas.destroy();

            // Cambiamos d.area por d.area_destino
            const labels = datos.length ? datos.map(d => d.area_destino) : ['Sin datos'];
            // Cambiamos d.total_consumido por d.total_pedidos
            const values = datos.length ? datos.map(d => d.total_pedidos) : [0];

            chartTopAreas = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: ['#8c837b', '#d9534f', '#f0ad4e', '#5bc0de', '#5cb85c'],
                        borderWidth: 0
                    }]
                },
                options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
            });
        }

        // --- PROVEEDORES ---
        async function cargarProveedores() {
            try {
                const res = await fetch(`${BASE_URL}/proveedores/get_proveedores.php`, { headers: { 'Authorization': 'Bearer ' + token } });
                if (res.ok) {
                    listaProveedoresGlobal = await res.json();
                    renderizarProveedores(listaProveedoresGlobal);
                } else {
                    document.getElementById('tbody-proveedores').innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Error al obtener proveedores.</td></tr>';
                }
            } catch (error) { console.error("Error cargando proveedores:", error); }
        }

        function renderizarProveedores(proveedores) {
            const tbody = document.getElementById('tbody-proveedores');
            if (proveedores.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No se encontraron proveedores.</td></tr>';
                return;
            }
            tbody.innerHTML = proveedores.map(p => `
                <tr>
                    <td class="ps-3 fw-bold">${p.empresa}</td>
                    <td>${p.contacto || 'N/A'}</td>
                    <td>${p.telefono || 'N/A'}</td>
                    <td>${p.correo || 'N/A'}</td>
                    <td>${p.direccion || 'N/A'}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="editarProveedor(${p.id}, '${p.empresa}', '${p.contacto || ''}', '${p.telefono || ''}', '${p.correo || ''}', '${p.direccion || ''}')" title="Modificar"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarProveedor(${p.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`).join('');
        }

        window.editarProveedor = function (id, empresa, contacto, telefono, correo, direccion) {
            Swal.fire({
                title: 'Modificar Proveedor',
                html: `
                    <input id="swal-edit-empresa" class="swal2-input" value="${empresa}" placeholder="Empresa">
                    <input id="swal-edit-contacto" class="swal2-input" value="${contacto}" placeholder="Contacto">
                    <input id="swal-edit-telefono" class="swal2-input" value="${telefono}" placeholder="Teléfono">
                    <input id="swal-edit-correo" class="swal2-input" value="${correo}" placeholder="Correo">
                    <input id="swal-edit-direccion" class="swal2-input" value="${direccion}" placeholder="Dirección">
                `,
                confirmButtonText: 'Guardar Cambios', confirmButtonColor: '#8c837b', focusConfirm: false,
                preConfirm: () => {
                    return { id: id, empresa: document.getElementById('swal-edit-empresa').value, contacto: document.getElementById('swal-edit-contacto').value, telefono: document.getElementById('swal-edit-telefono').value, correo: document.getElementById('swal-edit-correo').value, direccion: document.getElementById('swal-edit-direccion').value }
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`${BASE_URL}/proveedores/put_proveedor.php`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }, body: JSON.stringify(result.value) });
                        if (res.ok) { Swal.fire('¡Actualizado!', 'Los datos se actualizaron con éxito.', 'success'); cargarProveedores(); }
                        else { const err = await res.json(); Swal.fire('Error', err.error || 'No se pudo actualizar.', 'error'); }
                    } catch (e) { console.error(e); }
                }
            });
        };

        window.eliminarProveedor = async function (id) {
            const confirmacion = await Swal.fire({ title: '¿Estás seguro?', text: "El proveedor se dará de baja.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d9534f', cancelButtonColor: '#8c837b', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' });
            if (confirmacion.isConfirmed) {
                try {
                    const res = await fetch(`${BASE_URL}/proveedores/delete_proveedor.php?id=${id}`, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token } });
                    if (res.ok) { await Swal.fire({ icon: 'success', title: 'Eliminado', text: 'El proveedor fue dado de baja.', timer: 1500, showConfirmButton: false }); cargarProveedores(); }
                    else { const err = await res.json(); Swal.fire('Error', err.error || 'No se pudo eliminar.', 'error'); }
                } catch (error) { console.error(error); Swal.fire('Error', 'No se pudo conectar.', 'error'); }
            }
        };

        document.getElementById('input-buscar-proveedor').addEventListener('input', (e) => {
            const texto = e.target.value.toLowerCase();
            renderizarProveedores(listaProveedoresGlobal.filter(p => (p.empresa && p.empresa.toLowerCase().includes(texto)) || (p.contacto && p.contacto.toLowerCase().includes(texto))));
        });

        // --- EMPLEADOS ---
        async function cargarEmpleados() {
            try {
                const res = await fetch(`${BASE_URL}/usuarios/get_usuarios.php`, { headers: { 'Authorization': 'Bearer ' + token } });
                if (res.ok) {
                    const data = await res.json();
                    listaEmpleadosGlobal = data.datos ? data.datos : data;
                    renderizarEmpleados(listaEmpleadosGlobal);
                } else {
                    document.getElementById('tbody-empleados').innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Ruta no encontrada.</td></tr>';
                }
            } catch (error) { console.error("Error:", error); }
        }

        function renderizarEmpleados(empleados) {
            const tbody = document.getElementById('tbody-empleados');
            if (empleados.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No se encontraron empleados.</td></tr>';
                return;
            }
            tbody.innerHTML = empleados.map(e => `
                <tr>
                    <td class="ps-3 fw-bold">${e.nombre || e.usuario || 'N/A'}</td>
                    <td>${e.correo || 'N/A'}</td>
                    <td><span class="badge bg-secondary rounded-pill">${e.rol || 'N/A'}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-secondary me-1" onclick="editarEmpleado('${e.id}', '${e.nombre || e.usuario || ''}')" title="Cambiar Contraseña"><i class="bi bi-key"></i></button>
                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarEmpleado('${e.id}')" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>`).join('');
        }

        window.editarEmpleado = function (id, nombreEmpleado) {
            Swal.fire({
                title: `Cambiar Contraseña`, text: `Empleado: ${nombreEmpleado}`,
                html: `<input id="swal-edit-pass" type="password" class="swal2-input" placeholder="Nueva contraseña">`,
                confirmButtonText: 'Actualizar', confirmButtonColor: '#8c837b', focusConfirm: false,
                preConfirm: () => { return { usuario_id: id, nueva_password: document.getElementById('swal-edit-pass').value } }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const datosForm = result.value;
                        if (!datosForm.nueva_password || datosForm.nueva_password.trim() === '') { Swal.fire('Atención', 'La contraseña no puede estar vacía.', 'warning'); return; }
                        const resPass = await fetch(`${BASE_URL}/usuarios/reset_password.php`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }, body: JSON.stringify(datosForm) });
                        if (resPass.ok) { Swal.fire({ icon: 'success', title: '¡Actualizado!', timer: 1500, showConfirmButton: false }); }
                        else { const err = await resPass.json(); Swal.fire('Error', err.error || 'No se pudo actualizar.', 'error'); }
                    } catch (error) { console.error(error); }
                }
            });
        };

        document.getElementById('input-buscar-empleado').addEventListener('input', (e) => {
            const texto = e.target.value.toLowerCase();
            renderizarEmpleados(listaEmpleadosGlobal.filter(emp => (emp.nombre && emp.nombre.toLowerCase().includes(texto)) || (emp.correo && emp.correo.toLowerCase().includes(texto)) || (emp.usuario && emp.usuario.toLowerCase().includes(texto))));
        });

        window.eliminarEmpleado = async function (id) {
            const confirmacion = await Swal.fire({ title: '¿Estás seguro?', text: "Se eliminará el empleado permanentemente.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d9534f', cancelButtonColor: '#8c837b', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' });
            if (confirmacion.isConfirmed) {
                try {
                    const res = await fetch(`${BASE_URL}/usuarios/delete_usuario.php?id=${id}`, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token } });
                    if (res.ok) { await Swal.fire({ icon: 'success', title: 'Eliminado', timer: 1500, showConfirmButton: false }); listaEmpleadosGlobal = listaEmpleadosGlobal.filter(e => e.id != id); renderizarEmpleados(listaEmpleadosGlobal); }
                    else { const err = await res.json(); Swal.fire('Error', err.error || 'No se pudo eliminar.', 'error'); }
                } catch (error) { console.error(error); }
            }
        };

        // --- BOTONES NUEVO PROVEEDOR / NUEVO EMPLEADO ---
        document.getElementById('btn-nuevo-proveedor').addEventListener('click', () => {
            Swal.fire({
                title: 'Registrar Nuevo Proveedor',
                html: `<input id="swal-empresa" class="swal2-input" placeholder="Nombre de la Empresa"><input id="swal-contacto" class="swal2-input" placeholder="Persona de Contacto"><input id="swal-telefono" class="swal2-input" placeholder="Teléfono"><input id="swal-correo" class="swal2-input" placeholder="Correo Electrónico"><input id="swal-direccion" class="swal2-input" placeholder="Dirección">`,
                confirmButtonText: 'Guardar Proveedor', confirmButtonColor: '#8c837b', focusConfirm: false,
                preConfirm: () => { return { empresa: document.getElementById('swal-empresa').value, contacto: document.getElementById('swal-contacto').value, telefono: document.getElementById('swal-telefono').value, correo: document.getElementById('swal-correo').value, direccion: document.getElementById('swal-direccion').value } }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`${BASE_URL}/proveedores/post_proveedor.php`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }, body: JSON.stringify(result.value) });
                        if (res.ok) { Swal.fire('¡Registrado!', 'El proveedor se guardó con éxito.', 'success'); cargarProveedores(); }
                        else { Swal.fire('Error', 'No se pudo registrar el proveedor.', 'error'); }
                    } catch (e) { console.error(e); }
                }
            });
        });

        document.getElementById('btn-nuevo-empleado').addEventListener('click', () => {
            Swal.fire({
                title: 'Registrar Nuevo Empleado',
                html: `<input id="swal-nombre" class="swal2-input" placeholder="Nombre completo"><input id="swal-email" class="swal2-input" placeholder="Correo electrónico"><input id="swal-pass" type="password" class="swal2-input" placeholder="Contraseña"><select id="swal-rol" class="swal2-input"><option value="almacen">Almacén</option><option value="recursos">Recursos / Compras</option></select>`,
                confirmButtonText: 'Crear Empleado', confirmButtonColor: '#8c837b', focusConfirm: false,
                preConfirm: () => { return { nombre: document.getElementById('swal-nombre').value, correo: document.getElementById('swal-email').value, password: document.getElementById('swal-pass').value, rol: document.getElementById('swal-rol').value } }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch(`${BASE_URL}/usuarios/post_usuario.php`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }, body: JSON.stringify(result.value) });
                        if (res.ok) { Swal.fire('¡Registrado!', 'El empleado se dio de alta.', 'success'); cargarEmpleados(); }
                        else { Swal.fire('Error', 'No se pudo registrar.', 'error'); }
                    } catch (e) { console.error(e); }
                }
            });
        });

        document.getElementById('btn-exportar').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const fechaHoy = new Date().toLocaleDateString('es-MX');
            doc.setFontSize(16); doc.text("Reporte de Alertas de Reabastecimiento", 14, 20);
            doc.setFontSize(10); doc.setTextColor(100); doc.text(`Fecha de emisión: ${fechaHoy}`, 14, 28);
            doc.autoTable({ html: '#tabla-faltantes', startY: 35, theme: 'grid', headStyles: { fillColor: [140, 131, 123], textColor: 255 }, styles: { fontSize: 9, halign: 'center' }, columnStyles: { 1: { halign: 'left', cellWidth: 80 } } });
            doc.save('Reporte_Faltantes_Inventario.pdf');
        });

        document.getElementById('btn-exportar-csv').addEventListener('click', async () => {
            try {
                const respuesta = await fetch('backend/movimientos/exportar_movimientos.php', { method: 'GET', headers: { 'Authorization': 'Bearer ' + token } });
                if (respuesta.ok) {
                    const blob = await respuesta.blob(); const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a'); a.href = url; a.download = 'historial_movimientos_congreso.csv';
                    document.body.appendChild(a); a.click(); a.remove(); window.URL.revokeObjectURL(url);
                } else { Swal.fire({ icon: 'error', title: 'Acceso Denegado', text: 'No tienes permisos.' }); }
            } catch (error) { Swal.fire({ icon: 'error', title: 'Error de conexión' }); }
        });

        // --- HISTORIAL DE MOVIMIENTOS ---
        async function cargarHistorialCompras() {
            try {
                // Usamos la misma ruta que usa el panel de almacén
                const respuesta = await fetch(`${BASE_URL}/movimientos/get_movimientos.php?limite=30`, {
                    method: 'GET',
                    headers: { 'Authorization': 'Bearer ' + token }
                });

                if (respuesta.ok) {
                    const resultado = await respuesta.json();
                    const movimientos = resultado.datos || [];
                    const tbody = document.getElementById('tbody-historial-compras');

                    if (movimientos.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No hay movimientos registrados en el sistema.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = movimientos.map(m => {
                        let partesFecha = m.fecha ? m.fecha.split(' ') : ['', ''];
                        let fechaDia = partesFecha[0] || '';
                        let horaMin = partesFecha[1] ? partesFecha[1].substring(0, 5) : '';

                        let tipoBadge = m.tipo.toLowerCase() === 'entrada'
                            ? '<span class="badge bg-success">Entrada</span>'
                            : '<span class="badge bg-secondary">Salida</span>';

                        let destinoOProcedencia = m.area ? `Área: ${m.area}` : (m.proveedor ? `Prov: ${m.proveedor}` : 'N/A');

                        return `
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold d-block text-dark">${horaMin}</span>
                                    <small class="text-muted">${fechaDia}</small>
                                </td>
                                <td>${tipoBadge}</td>
                                <td>
                                    <span class="fw-medium text-dark">${m.producto}</span>
                                    <small class="text-muted d-block">Cant: <b>${m.cantidad}</b> | ${destinoOProcedencia}</small>
                                </td>
                                <td><span class="text-secondary">${m.persona_responsable || 'N/A'}</span></td>
                                <td class="text-center"><small class="text-muted">${m.motivo || 'Sin motivo'}</small></td>
                            </tr>
                        `;
                    }).join('');
                }
            } catch (error) {
                console.error("Error al cargar el historial:", error);
                document.getElementById('tbody-historial-compras').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Error al cargar historial.</td></tr>`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarDatosCompras();
            cargarProveedores();
            cargarEmpleados();
            cargarHistorialCompras(); // <-- Así queda agregada
        });

        // --- CERRAR SESIÓN ---
        function cerrarSesion() {
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "Saldrás del panel de compras de forma segura.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
                cancelButtonColor: '#8c837b',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Borramos el token de seguridad
                    localStorage.removeItem('token');
                    // Redirigimos al login
                    window.location.href = 'index.php';
                }
            });
        }
    </script>
</body>

</html>