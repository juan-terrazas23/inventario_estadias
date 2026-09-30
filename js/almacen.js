// --- CONFIGURACIÓN BASE Y SEGURIDAD ---
const BASE_URL = 'backend';
const token = localStorage.getItem('token');

if (!token) {
    window.location.href = 'index.php';
}

let inventarioGlobal = []; 
let carritoRequisicion = []; 
let articuloTemporal = null; 
let historialEntregas = []; 

let carritoEntradas = [];
let articuloTemporalEntrada = null;

const inputDescripcion = document.getElementById('descripcion');
const inputCantidad = document.getElementById('cantidad');
const tablaInventario = document.getElementById('tabla-inventario');
const btnAgregar = document.getElementById('btn-agregar');
const listaArticulos = document.getElementById('lista-articulos');
const btnRegistrar = document.getElementById('btn-registrar');
const inputFecha = document.getElementById('fecha');
const selectArea = document.getElementById('area');
const inputPersonaResponsable = document.getElementById('persona-responsable');
const inputMotivo = document.getElementById('motivo'); 
const selectNuevaUnidad = document.getElementById('nueva-unidad');

async function cargarCatalogosDinamicos() {
    try {
        const resAreas = await fetch(`${BASE_URL}/areas/get_areas.php`, {
            headers: { 'Authorization': 'Bearer ' + token }
        });
        if (resAreas.ok) {
            const areas = await resAreas.json();
            selectArea.innerHTML = '<option value="">Selecciona un área...</option>';
            areas.forEach(a => selectArea.innerHTML += `<option value="${a.id}">${a.nombre}</option>`);
        }

        if (selectNuevaUnidad) {
            const resUnidades = await fetch(`${BASE_URL}/unidades/get_unidades.php`, {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            if (resUnidades.ok) {
                const unidades = await resUnidades.json();
                selectNuevaUnidad.innerHTML = '<option value="">Selecciona una unidad...</option>';
                unidades.forEach(u => selectNuevaUnidad.innerHTML += `<option value="${u.id}">${u.nombre}</option>`);
            }
        }

        if (inputProveedor) {
            const resProv = await fetch(`${BASE_URL}/proveedores/get_proveedores.php`, {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            if (resProv.ok) {
                const proveedores = await resProv.json();
                inputProveedor.innerHTML = '<option value="">Selecciona un proveedor...</option>';
                proveedores.forEach(p => inputProveedor.innerHTML += `<option value="${p.id}">${p.empresa}</option>`);
            }
        }
    } catch (error) {
        console.error("Error al cargar catálogos:", error);
    }
}

async function cargarInventarioDesdeBD() {
    try {
        const respuesta = await fetch(`${BASE_URL}/productos/get_productos.php?limite=200`, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            }
        });
        
        if (!respuesta.ok) throw new Error("No autorizado");
        
        const jsonData = await respuesta.json();
        const productosRaw = jsonData.datos ? jsonData.datos : jsonData;
        
        if (!Array.isArray(productosRaw)) {
            console.error("El formato recibido del backend no es un arreglo:", jsonData);
            return;
        }
        
        inventarioGlobal = productosRaw.map(p => ({
            id: p.id,
            descripcion: p.nombre, 
            stock: parseInt(p.stock),
            stock_minimo: parseInt(p.stock_minimo) || 5,
            categoria_id: parseInt(p.categoria_id) 
        })); 
        
        inventarioGlobal.sort((a, b) => parseInt(a.id) - parseInt(b.id));
        
        renderizarCatalogoGeneral();
        inputNuevoId.value = generarIdAutomatico();
    } catch (error) {
        console.error('Error al cargar inventario:', error);
        Swal.fire('Sesión Expirada', 'Por favor inicia sesión nuevamente.', 'error').then(() => {
            window.location.href = 'index.php';
        });
    }
}

function renderizarTabla(datos) {
    tablaInventario.innerHTML = "";
    if (datos.length === 0) {
        tablaInventario.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No se encontraron artículos.</td></tr>`;
        return;
    }

    datos.forEach(articulo => {
        let alertaStock = articulo.stock <= articulo.stock_minimo 
            ? '<span class="badge rounded-pill ms-2 shadow-sm" style="background-color: #8c837b; color: white; font-size: 0.75rem;"><i class="bi bi-exclamation-triangle me-1"></i>Bajo</span>' 
            : '';
        
        let fila = `
            <tr>
                <td class="text-muted">${articulo.id}</td>
                <td class="fw-medium">${articulo.descripcion}</td>
                <td>${articulo.stock} ${alertaStock}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarArticulo('${articulo.id}')">
                        Seleccionar
                    </button>
                </td>
            </tr>
        `;
        tablaInventario.innerHTML += fila;
    });
}

inputDescripcion.addEventListener('input', (evento) => {
    const textoBuscado = evento.target.value.toLowerCase();
    articuloTemporal = null; 

    if (textoBuscado.trim() === '') {
        tablaInventario.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Escribe en "Buscar Insumo"...</td></tr>`;
        return;
    }
    const resultados = inventarioGlobal.filter(articulo => articulo.descripcion.toLowerCase().includes(textoBuscado));
    renderizarTabla(resultados);
});

window.seleccionarArticulo = function(idArticulo) {
    articuloTemporal = inventarioGlobal.find(a => a.id == idArticulo);
    inputDescripcion.value = articuloTemporal.descripcion;
    inputCantidad.focus();
    tablaInventario.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Artículo seleccionado. Ingresa la cantidad.</td></tr>`;
};

btnAgregar.addEventListener('click', () => {
    const cantidad = parseInt(inputCantidad.value);

    if (!articuloTemporal) {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor, busca y selecciona un artículo de la tabla primero.', confirmButtonColor: '#8c837b' });
        return;
    }
    if (isNaN(cantidad) || cantidad <= 0) {
        Swal.fire({ icon: 'warning', title: 'Cantidad Inválida', text: 'Por favor ingresa una cantidad válida mayor a cero.', confirmButtonColor: '#8c837b' });
        return;
    }
    if (cantidad > articuloTemporal.stock) {
        Swal.fire({ icon: 'error', title: 'Stock Insuficiente', text: `Solo quedan ${articuloTemporal.stock} unidades de este artículo.`, confirmButtonColor: '#8c837b' });
        return;
    }

    carritoRequisicion.push({
        id: articuloTemporal.id,
        descripcion: articuloTemporal.descripcion,
        cantidad: cantidad
    });

    articuloTemporal = null;
    inputDescripcion.value = '';
    inputCantidad.value = '';
    tablaInventario.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Escribe en "Buscar Insumo"...</td></tr>`;

    actualizarVistaCarrito();
});

function actualizarVistaCarrito() {
    listaArticulos.innerHTML = ''; 

    if (carritoRequisicion.length === 0) {
        listaArticulos.innerHTML = `<li class="list-group-item text-center text-muted py-3" id="mensaje-vacio">La lista está vacía.</li>`;
        return; 
    }
        
    carritoRequisicion.forEach((item, index) => {
        listaArticulos.innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted d-block">${item.id}</small>
                    ${item.descripcion}
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge rounded-pill bg-success me-2">${item.cantidad}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary border-0 py-0" onclick="modificarItemCarrito(${index}, 'salida')" title="Editar cantidad"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0" onclick="eliminarItemCarrito(${index}, 'salida')" title="Quitar de la lista"><i class="bi bi-trash"></i></button>
                </div>
            </li>
        `;
    });
}

btnRegistrar.addEventListener('click', async () => {
    if (inputFecha.value === '' || selectArea.value === '' || inputPersonaResponsable.value.trim() === '') {
        Swal.fire({ icon: 'info', title: 'Faltan datos', text: 'Por favor completa la Fecha, el Área y la Persona Responsable.', confirmButtonColor: '#8c837b' });
        return;
    }

    if (carritoRequisicion.length === 0) {
        Swal.fire({ icon: 'info', title: 'Lista Vacía', text: 'No has agregado ningún artículo a la lista de entrega.', confirmButtonColor: '#8c837b' });
        return;
    }

    try {
        for (let itemCarrito of carritoRequisicion) {
            const personaRecibe = inputPersonaResponsable ? inputPersonaResponsable.value.trim() : '';
            
            if (personaRecibe === '') {
                Swal.fire('Atención', 'Debes indicar a qué persona se le entrega el material.', 'warning');
                return; 
            }

            const textoMotivo = inputMotivo ? inputMotivo.value.trim() : '';

            const bodyData = {
                producto_id: itemCarrito.id,
                tipo: 'salida',
                cantidad: itemCarrito.cantidad,
                persona_responsable: personaRecibe,
                area_id: parseInt(selectArea.value)
            };

            if (textoMotivo !== '') {
                bodyData.motivo = textoMotivo;
            }

            await fetch(`${BASE_URL}/movimientos/post_movimiento.php`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(bodyData)
            });
        }
    
        const horaActual = new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
        const resumenInsumos = carritoRequisicion.map(item => `<span class="badge bg-secondary me-1">${item.cantidad}x</span> ${item.descripcion}`).join('<br>');
        const totalArticulos = carritoRequisicion.reduce((total, item) => total + item.cantidad, 0);

        historialEntregas.push({
            hora: horaActual,
            area: selectArea.options[selectArea.selectedIndex].text,
            insumos: resumenInsumos,
            total: totalArticulos
        });

        actualizarVistaHistorial();

        Swal.fire({ 
            icon: 'success', 
            title: '¡Entrega Registrada!', 
            text: 'El inventario ha sido actualizado en la base de datos.',
            confirmButtonColor: '#8c837b',
            timer: 2500, 
            showConfirmButton: false
        });

        carritoRequisicion = []; 
        actualizarVistaCarrito(); 
        inputFecha.value = '';
        selectArea.value = '';
        inputPersonaResponsable.value = ''; 
        inputDescripcion.value = '';
        inputCantidad.value = '';
        tablaInventario.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Escribe en "Buscar Insumo" para ver coincidencias...</td></tr>`;

        cargarInventarioDesdeBD();
        actualizarVistaHistorial();

        fetch(`${BASE_URL}/estadisticas_mensajes/verificar_alertas_stock.php`, {
            headers: { 'Authorization': 'Bearer ' + token }
        }).catch(e => console.log('Alerta correo:', e));

    } catch (error) {
        console.error('Error al registrar la entrega:', error);
    }
});

// --- HISTORIAL PERMANENTE CONECTADO A LA BASE DE DATOS ---
async function actualizarVistaHistorial() {
    const tablaHistorial = document.getElementById('tabla-historial');
    if (!tablaHistorial) return;

    try {
        const respuesta = await fetch(`${BASE_URL}/movimientos/get_movimientos.php?limite=50`, {
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token }
        });

        if (respuesta.ok) {
            const resultado = await respuesta.json();
            const movimientos = resultado.datos || [];

            tablaHistorial.innerHTML = '';

            if (movimientos.length === 0) {
                tablaHistorial.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">No hay movimientos registrados en el sistema.</td></tr>`;
                return;
            }

            movimientos.forEach(m => {
                let partesFecha = m.fecha ? m.fecha.split(' ') : ['', ''];
                let fechaDia = partesFecha[0] || '';
                let horaMin = partesFecha[1] ? partesFecha[1].substring(0, 5) : '';

                let tipoBadge = m.tipo.toLowerCase() === 'entrada' 
                    ? '<span class="badge bg-success">Entrada</span>' 
                    : '<span class="badge bg-secondary">Salida</span>';

                let destinoOProcedencia = m.area ? `Área: ${m.area}` : (m.proveedor ? `Prov: ${m.proveedor}` : 'N/A');

                tablaHistorial.innerHTML += `
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
            });
        }
    } catch (error) {
        console.error("Error al cargar el historial desde la base de datos:", error);
    }
}

// --- LÓGICA PARA ENTRADAS DE PROVEEDORES ---
const inputDescEntrada = document.getElementById('descripcion-entrada');
const inputCantEntrada = document.getElementById('cantidad-entrada');
const tablaInvEntrada = document.getElementById('tabla-inventario-entrada');
const btnAgregarEntrada = document.getElementById('btn-agregar-entrada');
const listaArticulosEntrada = document.getElementById('lista-articulos-entrada');
const btnRegistrarEntrada = document.getElementById('btn-registrar-entrada');
const inputFechaEntrada = document.getElementById('fecha-entrada');
const inputProveedor = document.getElementById('proveedor');

function renderizarTablaEntrada(datos) {
    if (!tablaInvEntrada) return;
    tablaInvEntrada.innerHTML = "";
    if (datos.length === 0) {
        tablaInvEntrada.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No se encontraron artículos.</td></tr>`;
        return;
    }

    datos.forEach(articulo => {
        let fila = `
            <tr>
                <td class="text-muted ps-3">${articulo.id}</td>
                <td class="fw-medium">${articulo.descripcion}</td>
                <td>${articulo.stock}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="seleccionarArticuloEntrada('${articulo.id}')">
                        Seleccionar
                    </button>
                </td>
            </tr>
        `;
        tablaInvEntrada.innerHTML += fila;
    });
}

if (inputDescEntrada) {
    inputDescEntrada.addEventListener('input', (evento) => {
        const textoBuscado = evento.target.value.toLowerCase();
        articuloTemporalEntrada = null; 

        if (textoBuscado.trim() === '') {
            tablaInvEntrada.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Escribe en "Buscar Insumo a Sumar"...</td></tr>`;
            return;
        }
        const resultados = inventarioGlobal.filter(articulo => articulo.descripcion.toLowerCase().includes(textoBuscado));
        renderizarTablaEntrada(resultados);
    });
}

window.seleccionarArticuloEntrada = function(idArticulo) {
    articuloTemporalEntrada = inventarioGlobal.find(a => a.id == idArticulo);
    inputDescEntrada.value = articuloTemporalEntrada.descripcion;
    inputCantEntrada.focus();
    tablaInvEntrada.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Artículo seleccionado. Ingresa la cantidad recibida.</td></tr>`;
};

if (btnAgregarEntrada) {
    btnAgregarEntrada.addEventListener('click', () => {
        const cantidad = parseInt(inputCantEntrada.value);

        if (!articuloTemporalEntrada) {
            Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor, busca y selecciona un artículo de la tabla primero.', confirmButtonColor: '#8c837b' });
            return;
        }
        if (isNaN(cantidad) || cantidad <= 0) {
            Swal.fire({ icon: 'warning', title: 'Cantidad Inválida', text: 'Por favor ingresa una cantidad válida mayor a cero.', confirmButtonColor: '#8c837b' });
            return;
        }

        carritoEntradas.push({
            id: articuloTemporalEntrada.id,
            descripcion: articuloTemporalEntrada.descripcion,
            cantidad: cantidad
        });

        articuloTemporalEntrada = null;
        inputDescEntrada.value = '';
        inputCantEntrada.value = '';
        tablaInvEntrada.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Escribe en "Buscar Insumo a Sumar"...</td></tr>`;

        actualizarVistaCarritoEntrada();
    });
}

function actualizarVistaCarritoEntrada() {
    if (!listaArticulosEntrada) return;
    listaArticulosEntrada.innerHTML = ''; 

    if (carritoEntradas.length === 0) {
        listaArticulosEntrada.innerHTML = `<li class="list-group-item text-center text-muted py-3" id="mensaje-vacio-entrada">La lista está vacía.</li>`;
        return; 
    }
        
    carritoEntradas.forEach((item, index) => {
        listaArticulosEntrada.innerHTML += `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted d-block">${item.id}</small>
                    ${item.descripcion}
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge rounded-pill bg-success me-2">+${item.cantidad}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary border-0 py-0" onclick="modificarItemCarrito(${index}, 'entrada')" title="Editar cantidad"><i class="bi bi-pencil"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 py-0" onclick="eliminarItemCarrito(${index}, 'entrada')" title="Quitar de la lista"><i class="bi bi-trash"></i></button>
                </div>
            </li>
        `;
    });
}

if (btnRegistrarEntrada) {
    btnRegistrarEntrada.addEventListener('click', async () => {
        if (inputFechaEntrada.value === '' || inputProveedor.value.trim() === '') {
            Swal.fire({ icon: 'info', title: 'Faltan datos', text: 'Por favor completa la Fecha y el Proveedor.', confirmButtonColor: '#8c837b' });
            return;
        }

        if (carritoEntradas.length === 0) {
            Swal.fire({ icon: 'info', title: 'Lista Vacía', text: 'No has agregado ningún artículo a la recepción.', confirmButtonColor: '#8c837b' });
            return;
        }

        try {
           for (let itemCarrito of carritoEntradas) {
                await fetch(`${BASE_URL}/movimientos/post_movimiento.php`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        producto_id: itemCarrito.id,         
                        tipo: 'entrada',                     
                        cantidad: itemCarrito.cantidad,      
                        persona_responsable: "Encargado Almacén", 
                        proveedor_id: parseInt(inputProveedor.value), 
                        motivo: `Recepción de proveedor` 
                    })
                });
            }

            Swal.fire({ 
                icon: 'success', 
                title: '¡Entrada Registrada!', 
                text: 'El inventario ha sido incrementado en la base de datos.',
                confirmButtonColor: '#8c837b',
                timer: 2500, 
                showConfirmButton: false
            });

            carritoEntradas = []; 
            actualizarVistaCarritoEntrada(); 
            inputFechaEntrada.value = '';
            inputProveedor.value = '';
            inputDescEntrada.value = '';
            inputCantEntrada.value = '';
            tablaInvEntrada.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">Escribe en "Buscar Insumo a Sumar" para ver coincidencias...</td></tr>`;

            cargarInventarioDesdeBD();
            actualizarVistaHistorial();
        } catch (error) {
            console.error('Error al registrar entrada:', error);
        }
    });
}

// --- LÓGICA PARA GESTIÓN DE CATÁLOGO ---
const inputNuevoId = document.getElementById('nuevo-id');
const inputNuevaDesc = document.getElementById('nueva-descripcion');
const inputNuevoStockIni = document.getElementById('nuevo-stock-inicial');
const inputNuevoStockMin = document.getElementById('nuevo-stock-minimo');
const selectNuevaCategoria = document.getElementById('nueva-categoria'); 
const btnGuardarArticulo = document.getElementById('btn-guardar-articulo');
const tablaGestionCatalogo = document.getElementById('tabla-gestion-catalogo');

const inputBuscarCatalogo = document.getElementById('buscar-catalogo');
const selectFiltroCategoria = document.getElementById('filtro-categoria');
function renderizarCatalogoGeneral(datos = inventarioGlobal) {
    if (!tablaGestionCatalogo) return;
    tablaGestionCatalogo.innerHTML = "";
    
    if (datos.length === 0) {
        tablaGestionCatalogo.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-4">No se encontraron artículos con ese filtro.</td></tr>`;
        return;
    }

    datos.forEach(articulo => {
        tablaGestionCatalogo.innerHTML += `
            <tr>
                <td class="ps-3 text-muted">${articulo.id}</td>
                <td class="fw-medium">${articulo.descripcion}</td>
                <td>${articulo.stock}</td>
                <td>${articulo.stock_minimo}</td>
            </tr>
        `;
    });
}

function filtrarCatalogo() {
    if (!inputBuscarCatalogo || !selectFiltroCategoria) return;
    const texto = inputBuscarCatalogo.value.toLowerCase();
    const categoria = selectFiltroCategoria.value;

    const filtrados = inventarioGlobal.filter(articulo => {
        const matchTexto = articulo.descripcion.toLowerCase().includes(texto);
        const matchCat = (categoria === "") || (articulo.categoria_id == categoria);
        return matchTexto && matchCat;
    });

    renderizarCatalogoGeneral(filtrados);
}

if(inputBuscarCatalogo) inputBuscarCatalogo.addEventListener('input', filtrarCatalogo);
if(selectFiltroCategoria) selectFiltroCategoria.addEventListener('change', filtrarCatalogo);

if (btnGuardarArticulo) {
    btnGuardarArticulo.addEventListener('click', async () => {
        const descripcion = inputNuevaDesc.value.trim().toUpperCase();
        const stock = parseInt(inputNuevoStockIni.value);
        const stockMinimo = parseInt(inputNuevoStockMin.value); 
        const categoriaSeleccionada = selectNuevaCategoria.value;

        if (descripcion === '' || isNaN(stock) || isNaN(stockMinimo) || selectNuevaUnidad.value === '' || categoriaSeleccionada === '') {
            Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Por favor llena la descripción, la categoría, la unidad, el stock inicial y el stock mínimo.', confirmButtonColor: '#8c837b' });
            return;
        }

        try {
            const respuesta = await fetch(`${BASE_URL}/productos/post_producto.php`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ 
                    nombre: descripcion, 
                    precio: 0, 
                    stock: stock, 
                    stock_minimo: stockMinimo, 
                    categoria_id: parseInt(categoriaSeleccionada),
                    unidad_id: parseInt(selectNuevaUnidad.value) 
                })
            });

            if (respuesta.ok) {
                Swal.fire({ 
                    icon: 'success', 
                    title: '¡Artículo Registrado!', 
                    text: `Se ha guardado en el catálogo.`,
                    confirmButtonColor: '#8c837b',
                    timer: 2000, 
                    showConfirmButton: false
                });

                inputNuevaDesc.value = '';
                selectNuevaUnidad.value = ''; 
                selectNuevaCategoria.value = ''; 
                inputNuevoStockIni.value = '';
                inputNuevoStockMin.value = ''; 
                
                cargarInventarioDesdeBD();
            } else {
                const errorInfo = await respuesta.json();
                Swal.fire('Error', errorInfo.error || 'No se pudo guardar el artículo.', 'error');
            }
        } catch (error) {
            console.error('Error al guardar artículo:', error);
        }
    });
}

window.editarArticulo = function(id) {
    const articulo = inventarioGlobal.find(a => a.id == id);
    if (articulo) {
        Swal.fire({
            title: `Editar Artículo: ${articulo.id}`,
            html: `
                <input id="swal-input-desc" class="swal2-input" value="${articulo.descripcion}" placeholder="Descripción">
            `,
            confirmButtonText: 'Guardar Cambios',
            confirmButtonColor: '#8c837b',
            focusConfirm: false,
            preConfirm: () => {
                return {
                    descripcion: document.getElementById('swal-input-desc').value
                }
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    await fetch(`${BASE_URL}/productos/put_producto.php`, {
                        method: 'PUT',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify({
                            id: id,
                            nombre: result.value.descripcion,
                            precio: 0,
                            categoria_id: 1
                        })
                    });

                    cargarInventarioDesdeBD();
                    Swal.fire({ icon: 'success', title: 'Actualizado', text: 'Los cambios se guardaron correctamente.', confirmButtonColor: '#8c837b', timer: 1500, showConfirmButton: false });
                } catch (error) {
                    console.error('Error al actualizar:', error);
                }
            }
        });
    }
};

function generarIdAutomatico() {
    if (inventarioGlobal.length === 0) return "1";
    const ids = inventarioGlobal.map(a => parseInt(a.id) || 0);
    const maxId = Math.max(...ids);
    return maxId + 1;
}

// --- FUNCIONES PARA EDITAR/ELIMINAR DE LAS LISTAS PROVISIONALES ---
window.modificarItemCarrito = async function(index, tipo) {
    const carrito = tipo === 'salida' ? carritoRequisicion : carritoEntradas;
    const item = carrito[index];
    
    const { value: nuevaCantidad } = await Swal.fire({
        title: 'Modificar Cantidad',
        text: item.descripcion,
        input: 'number',
        inputValue: item.cantidad,
        showCancelButton: true,
        confirmButtonColor: '#8c837b',
        cancelButtonColor: '#d9534f',
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        inputAttributes: { min: 1, step: 1 }
    });

    if (nuevaCantidad) {
        const cantidadFinal = parseInt(nuevaCantidad);
        
        if (tipo === 'salida') {
            const articuloBD = inventarioGlobal.find(a => a.id == item.id);
            if (articuloBD && cantidadFinal > articuloBD.stock) {
                Swal.fire({ icon: 'error', title: 'Stock Insuficiente', text: `Solo quedan ${articuloBD.stock} unidades.`, confirmButtonColor: '#8c837b' });
                return;
            }
        }
        
        carrito[index].cantidad = cantidadFinal;
        tipo === 'salida' ? actualizarVistaCarrito() : actualizarVistaCarritoEntrada();
    }
};

window.eliminarItemCarrito = function(index, tipo) {
    if (tipo === 'salida') {
        carritoRequisicion.splice(index, 1);
        actualizarVistaCarrito();
    } else {
        carritoEntradas.splice(index, 1);
        actualizarVistaCarritoEntrada();
    }
};

// Inicialización de eventos al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    cargarInventarioDesdeBD();
    cargarCatalogosDinamicos();
    actualizarVistaHistorial(); 
    
    const btnCerrarSesion = document.getElementById('btn-cerrar-sesion');
    if (btnCerrarSesion) {
        btnCerrarSesion.addEventListener('click', () => {
            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "Saldrás del panel de almacén de forma segura.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
                cancelButtonColor: '#8c837b',
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    localStorage.removeItem('token');
                    window.location.href = 'index.php';
                }
            });
        });
    }
});