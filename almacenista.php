<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Almacén - Inventario</title>
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
            <!-- CONTENEDOR: Historial + Perfil + Salir -->
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#modalHistorial">
                    <i class="bi bi-clock-history me-1"></i> Historial
                </button>
                <span class="navbar-text text-dark border-start ps-3 pe-2">
                    Perfil: Almacenista
                </span>
                <!-- NUEVO: Botón de Cerrar Sesión -->
                <button id="btn-cerrar-sesion" class="btn btn-sm btn-outline-danger shadow-sm" title="Cerrar Sesión">
                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                </button>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <ul class="nav nav-tabs" id="pestanasAlmacen" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold text-dark" id="tab-salidas" data-bs-toggle="tab"
                    data-bs-target="#seccion-salidas" type="button" role="tab">
                    <i class="bi bi-box-arrow-right me-2"></i>Despachar Requisición
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="tab-entradas" data-bs-toggle="tab"
                    data-bs-target="#seccion-entradas" type="button" role="tab">
                    <i class="bi bi-box-arrow-in-down me-2"></i>Recibir Proveedor
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold text-dark" id="tab-catalogo" data-bs-toggle="tab"
                    data-bs-target="#seccion-catalogo" type="button" role="tab">
                    <i class="bi bi-journal-plus me-2"></i>Catálogo de Insumos
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="contenidoPestanas">

        <div class="tab-pane fade show active container mt-4" id="seccion-salidas" role="tabpanel">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header card-header-custom">
                            <i class="bi bi-card-text me-2"></i>Datos de la Requisición
                        </div>
                        <div class="card-body">
                            <form id="form-entrega">
                                <div class="mb-3">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha">
                                </div>
                                <div class="mb-3">
                                    <label for="area" class="form-label">Área Solicitante</label>
                                    <!-- Las áreas se cargarán dinámicamente desde el JS -->
                                    <select class="form-select" id="area">
                                        <option value="">Cargando áreas...</option>
                                    </select>
                                </div>
                                <!-- Campo para la persona responsable / a quién se entrega -->
                                <div class="mb-3">
                                    <label for="persona-responsable" class="form-label">Entregar a (Persona Responsable)
                                        *</label>
                                    <input type="text" id="persona-responsable" class="form-control"
                                        placeholder="Nombre de quien recibe el material" required>
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Buscar Insumo</label>
                                    <input type="text" class="form-control" id="descripcion"
                                        placeholder="Ej. plumas, hojas...">
                                </div>
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad a entregar</label>
                                    <input type="number" class="form-control" id="cantidad" min="1" placeholder="0">
                                </div>
                                <!-- Campo opcional para el motivo -->
                                <div class="mb-3">
                                    <label for="motivo" class="form-label text-muted">Motivo
                                        <small>(Opcional)</small></label>
                                    <input type="text" id="motivo" class="form-control"
                                        placeholder="Ej. Préstamo temporal, ajuste, etc.">
                                </div>
                                <button type="button" id="btn-agregar" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar a la lista
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-custom" style="background-color: #dcd7d0;">
                            <i class="bi bi-list-check me-2"></i>Artículos en esta entrega
                        </div>
                        <ul class="list-group list-group-flush" id="lista-articulos">
                            <li class="list-group-item text-center text-muted py-3" id="mensaje-vacio">
                                La lista está vacía.
                            </li>
                        </ul>
                        <div class="card-body">
                            <button type="button" id="btn-registrar" class="btn btn-custom w-100">Registrar Entrega
                                Completa</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-custom">
                            <i class="bi bi-search me-2"></i>Búsqueda de Insumos
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-header-custom">
                                        <tr>
                                            <th class="ps-3">ID</th>
                                            <th>Descripción</th>
                                            <th>Stock Actual</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-inventario">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Escribe en "Buscar Insumo" para ver coincidencias...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade container mt-4" id="seccion-entradas" role="tabpanel">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header card-header-custom">
                            <i class="bi bi-truck me-2"></i>Datos de la Recepción
                        </div>
                        <div class="card-body">
                            <form id="form-entrada">
                                <div class="mb-3">
                                    <label for="fecha-entrada" class="form-label">Fecha de Llegada</label>
                                    <input type="date" class="form-control" id="fecha-entrada">
                                </div>
                                <div class="mb-3">
                                    <label for="proveedor" class="form-label">Proveedor</label>
                                    <!-- Se cambia el input text por un select dinámico -->
                                    <select class="form-select" id="proveedor">
                                        <option value="">Cargando proveedores...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="descripcion-entrada" class="form-label">Buscar Insumo a Sumar</label>
                                    <input type="text" class="form-control" id="descripcion-entrada"
                                        placeholder="Ej. plumas, hojas...">
                                </div>
                                <div class="mb-3">
                                    <label for="cantidad-entrada" class="form-label">Cantidad Recibida</label>
                                    <input type="number" class="form-control" id="cantidad-entrada" min="1"
                                        placeholder="0">
                                </div>
                                <button type="button" id="btn-agregar-entrada" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-plus-circle me-2"></i>Agregar a la lista de entrada
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-custom" style="background-color: #dcd7d0;">
                            <i class="bi bi-list-check me-2"></i>Artículos Recibidos
                        </div>
                        <ul class="list-group list-group-flush" id="lista-articulos-entrada">
                            <li class="list-group-item text-center text-muted py-3" id="mensaje-vacio-entrada">
                                La lista está vacía.
                            </li>
                        </ul>
                        <div class="card-body">
                            <button type="button" id="btn-registrar-entrada" class="btn btn-custom w-100">Registrar
                                Entrada al Stock</button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-custom">
                            <i class="bi bi-search me-2"></i>Catálogo de Insumos
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-header-custom">
                                        <tr>
                                            <th class="ps-3">ID</th>
                                            <th>Descripción</th>
                                            <th>Stock Actual</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-inventario-entrada">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Escribe en "Buscar Insumo a Sumar" para ver coincidencias...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade container mt-4" id="seccion-catalogo" role="tabpanel">
            <div class="row">
                <!-- FORMULARIO DE NUEVO INSUMO -->
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header card-header-custom">
                            <i class="bi bi-plus-square me-2"></i>Registrar Nuevo Insumo
                        </div>
                        <div class="card-body">
                            <form id="form-nuevo-articulo">
                                <div class="mb-3">
                                    <label for="nuevo-id" class="form-label">ID del Artículo (Automático)</label>
                                    <input type="text" class="form-control bg-light" id="nuevo-id" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="nueva-descripcion" class="form-label">Descripción</label>
                                    <input type="text" class="form-control" id="nueva-descripcion"
                                        placeholder="Ej. Lápices de madera No. 2">
                                </div>

                                <!-- NUEVO: Selector de Categoría -->
                                <div class="mb-3">
                                    <label for="nueva-categoria" class="form-label">Categoría</label>
                                    <select class="form-select" id="nueva-categoria">
                                        <option value="">Selecciona...</option>
                                        <option value="1">Papelería</option>
                                        <option value="2">Limpieza</option>
                                        <option value="3">Desechables</option>
                                        <option value="4">Cafetería</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="nueva-unidad" class="form-label">Unidad de Medida</label>
                                    <select class="form-select" id="nueva-unidad">
                                        <option value="">Cargando unidades...</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="nuevo-stock-inicial" class="form-label">Stock Inicial</label>
                                    <input type="number" class="form-control" id="nuevo-stock-inicial" min="0"
                                        placeholder="0">
                                </div>

                                <!-- NUEVO: Campo de Stock Mínimo -->
                                <div class="mb-3">
                                    <label for="nuevo-stock-minimo" class="form-label">Stock Mínimo (Alerta)</label>
                                    <input type="number" class="form-control" id="nuevo-stock-minimo" min="1"
                                        placeholder="Ej. 5">
                                </div>
                                <button type="button" id="btn-guardar-articulo" class="btn btn-custom w-100">
                                    <i class="bi bi-check-circle me-2"></i>Guardar en el Catálogo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TABLA DEL CATÁLOGO -->
                <div class="col-md-8">

                    <!-- NUEVO: Barra de Búsqueda y Filtro -->
                    <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="buscar-catalogo" class="form-control"
                                placeholder="Buscar insumo por nombre...">
                        </div>
                        <select id="filtro-categoria" class="form-select" style="max-width: 220px;">
                            <option value="">Todas las categorías</option>
                            <option value="1">Papelería</option>
                            <option value="2">Limpieza</option>
                            <option value="3">Desechables</option>
                            <option value="4">Cafetería</option>
                        </select>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-custom">
                            <i class="bi bi-table me-2"></i>Listado General de Insumos
                        </div>
                        <div class="card-body p-0">
                            <!-- Se agregó max-height y overflow -->
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <!-- Se agregó position sticky -->
                                    <thead class="table-header-custom" style="position: sticky; top: 0; z-index: 1;">
                                        <tr>
                                            <th class="ps-3">ID</th>
                                            <th>Descripción</th>
                                            <th>Stock Actual</th>
                                            <th>Mínimo</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-gestion-catalogo">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="background-color: #f4f3f0;">
                <div class="modal-header card-header-custom">
                    <h5 class="modal-title text-dark fw-bold" id="modalHistorialLabel">
                        <i class="bi bi-list-columns-reverse me-2"></i>Registro de Entregas
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Busca esto en tu almacenista.php dentro del modalHistorial -->
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-header-custom" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th class="ps-3">Fecha / Hora</th>
                                <th>Tipo</th>
                                <th>Insumo / Destino</th>
                                <th>Responsable</th>
                                <th class="text-center">Motivo</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-historial">
                            <!-- Se llenará dinámicamente desde la BD -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #dcd7d0;">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/almacen.js?v=4"></script>
</body>

</html>