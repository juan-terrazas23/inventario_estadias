const BASE_URL = 'backend';
const token = localStorage.getItem('token');

// --- SEGURIDAD: CIERRE DE SESIÓN POR INACTIVIDAD (15 MINUTOS) ---
let temporizadorInactividad;

function reiniciarTemporizador() {
    clearTimeout(temporizadorInactividad);
    temporizadorInactividad = setTimeout(expulsarUsuario, 600000); // 600000 ms = 10 min
}

function expulsarUsuario() {
    localStorage.removeItem('token');
    localStorage.removeItem('nombre_usuario');
    localStorage.removeItem('rol_usuario');
    
    Swal.fire({
        icon: 'info',
        title: 'Sesión expirada',
        text: 'Tu sesión se cerró por inactividad para proteger el inventario.',
        confirmButtonColor: '#8c837b',
        timer: 3000,
        showConfirmButton: false
    }).then(() => {
        window.location.href = 'index.php';
    });
}

// Escuchar interacciones para reiniciar el reloj
window.onload = reiniciarTemporizador;
document.onmousemove = reiniciarTemporizador;
document.onkeypress = reiniciarTemporizador;
document.onclick = reiniciarTemporizador;
document.onscroll = reiniciarTemporizador;

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
                    const areaMayor = areas[0].area_destino || "Sin registros";
                    let totalConsumo = 0;
                    areas.forEach(a => totalConsumo += parseFloat(a.total_pedidos || 0));

                    let porcentaje = 0;
                    if (totalConsumo > 0) {
                        const mayorConsumo = parseFloat(areas[0].total_pedidos || 0);
                        porcentaje = Math.round((mayorConsumo / totalConsumo) * 100);
                    }

                    document.getElementById('val-area-consumo').textContent = areaMayor;
                    document.getElementById('val-porcentaje-consumo').textContent = `${porcentaje}% del consumo total`;
                } else {
                    document.getElementById('val-area-consumo').textContent = "Sin registros";
                    document.getElementById('val-porcentaje-consumo').textContent = "0% del consumo total";
                }
            } else {
                throw new Error("Respuesta no OK");
            }
        } catch (e) {
            // CORRECCIÓN: Evitamos que se quede "Calculando..."
            document.getElementById('val-area-consumo').textContent = "Error de cálculo";
            document.getElementById('val-porcentaje-consumo').textContent = "Verifica los datos en BD";
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
                throw new Error("Respuesta no OK");
            }
        } catch (e) {
            // CORRECCIÓN: Avisamos visualmente en lugar de congelar la tabla
            document.getElementById('tbody-rotacion').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4"><i class="bi bi-exclamation-triangle"></i> Error al calcular estimaciones. Posible división por cero.</td></tr>`;
        }

        // 4. Cargar Estadísticas Gerenciales (Top Productos y Áreas)
        try {
            const resGerencial = await fetch(`${BASE_URL}/estadisticas_mensajes/get_estadisticas.php`, {
                headers: { 'Authorization': 'Bearer ' + token }
            });

            if (resGerencial.ok) {
                const dataGerencial = await resGerencial.json();
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

    const labels = datos.length ? datos.map(d => d.area_destino) : ['Sin datos'];
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

// --- 1. EXPORTAR ALERTAS A PDF ---
document.getElementById('btn-exportar')?.addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const fechaHoy = new Date().toLocaleDateString('es-MX');

    doc.setFontSize(16);
    doc.setTextColor(40, 40, 40);
    doc.text("H. Congreso del Estado de Durango", 14, 15);
    doc.setFontSize(12);
    doc.setTextColor(100, 100, 100);
    doc.text("Reporte Oficial de Alertas de Reabastecimiento", 14, 23);
    doc.setFontSize(9);
    doc.text(`Fecha de emisión: ${fechaHoy}`, 14, 29);

    doc.autoTable({
        html: '#tabla-faltantes',
        startY: 35,
        theme: 'grid',
        headStyles: { fillColor: [140, 131, 123], textColor: 255, fontStyle: 'bold' },
        styles: { fontSize: 9, valign: 'middle' }
    });

    doc.save('Reporte_Faltantes_Inventario.pdf');
});

// --- 2. EXPORTAR ALERTAS A EXCEL (CSV sin alertas de seguridad) ---
document.getElementById('btn-exportar-csv-alertas')?.addEventListener('click', function () {
    let tabla = document.getElementById('tabla-faltantes');
    let filas = tabla.querySelectorAll('tr');
    let csv = [];

    filas.forEach(fila => {
        let cols = fila.querySelectorAll('th, td');
        let datosFila = [];
        cols.forEach(col => {
            // Limpiamos el texto de saltos de línea ocultos y comillas que rompen el Excel
            let texto = col.innerText.replace(/"/g, '""').replace(/\n/g, ' ').trim();
            datosFila.push(`"${texto}"`);
        });
        csv.push(datosFila.join(','));
    });

    // '\uFEFF' es la firma UTF-8 que Excel necesita para no romper los acentos.
    // '\r\n' asegura que Windows lea las filas correctamente.
    let csvContent = '\uFEFF' + csv.join('\r\n');
    let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    
    let url = window.URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    // Usamos extensión .csv real para que no salte el firewall ni la alerta de Excel
    a.download = 'Reporte_Alertas_Reabastecimiento.csv';
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    
    // Limpieza de memoria
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
});

// --- 3. EXPORTAR HISTORIAL A PDF (Apunta directamente a la tabla corregida) ---
document.getElementById('btn-exportar-pdf-historial')?.addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('landscape'); 
    const fechaHoy = new Date().toLocaleDateString('es-MX');

    doc.setFontSize(16);
    doc.text("H. Congreso del Estado de Durango", 14, 15);
    doc.setFontSize(12);
    doc.text("Historial Detallado de Movimientos", 14, 23);
    doc.setFontSize(9);
    doc.text(`Fecha de emisión: ${fechaHoy}`, 14, 29);

    doc.autoTable({
        html: '#tabla-historial-pdf', // Este ID debe coincidir con el que pusimos en el HTML
        startY: 35,
        theme: 'grid',
        headStyles: { fillColor: [140, 131, 123], textColor: 255 },
        styles: { fontSize: 8, valign: 'middle' }
    });

    doc.save('Historial_Movimientos_Congreso.pdf');
});

// --- 4. EXPORTAR HISTORIAL A CSV (Desde el Backend) ---
document.getElementById('btn-exportar-csv')?.addEventListener('click', async () => {
    try {
        const respuesta = await fetch('backend/movimientos/exportar_movimientos.php', { 
            method: 'GET', 
            headers: { 'Authorization': 'Bearer ' + token } 
        });

        if (respuesta.ok) {
            const blob = await respuesta.blob(); 
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a'); 
            a.href = url; 
            a.download = `historial_movimientos_congreso_${new Date().toISOString().slice(0,10)}.csv`;
            document.body.appendChild(a); 
            a.click(); 
            a.remove(); 
            window.URL.revokeObjectURL(url);
        } else { 
            Swal.fire({ icon: 'error', title: 'Acceso Denegado' }); 
        }
    } catch (error) { 
        Swal.fire({ icon: 'error', title: 'Error de conexión' }); 
    }
});
// --- HISTORIAL DE MOVIMIENTOS (AGRUPADO PARA COMPRAS) ---
async function cargarHistorialCompras() {
    try {
        const respuesta = await fetch(`${BASE_URL}/movimientos/get_movimientos.php?limite=100`, {
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

            // Algoritmo de agrupación
            const movimientosAgrupados = {};

            movimientos.forEach(m => {
                let partesFecha = m.fecha ? m.fecha.split(' ') : ['', ''];
                let fechaDia = partesFecha[0] || '';
                let horaMin = partesFecha[1] ? partesFecha[1].substring(0, 5) : '';
                
                let llaveRequisicion = `${fechaDia}_${horaMin}_${m.persona_responsable}_${m.tipo}`;

                // En la declaración del objeto agrupado:
                if (!movimientosAgrupados[llaveRequisicion]) {
                    movimientosAgrupados[llaveRequisicion] = {
                        fechaDia: fechaDia,
                        horaMin: horaMin,
                        tipo: m.tipo,
                        // m.persona_responsable es el que recibe, m.usuario_sistema es tu almacenista
                        responsable: `<b>Recibió:</b> ${m.persona_responsable || 'N/A'}<br><small class="text-muted">Despachó: ${m.usuario_sistema || 'Sistema'}</small>`,
                        destinoOProcedencia: m.area ? `Área: ${m.area}` : (m.proveedor ? `Prov: ${m.proveedor}` : 'N/A'),
                        motivo: m.motivo,
                        insumos: [] 
                    };
                }
                movimientosAgrupados[llaveRequisicion].insumos.push(`<b>${m.cantidad}x</b> ${m.producto}`);
            });

            // Dibujar la tabla
            tbody.innerHTML = Object.values(movimientosAgrupados).map(grupo => {
                let tipoBadge = grupo.tipo.toLowerCase() === 'entrada'
                    ? '<span class="badge bg-success">Entrada</span>'
                    : '<span class="badge bg-secondary">Salida</span>';

                let listaInsumosHTML = grupo.insumos.map(ins => 
                    `<span class="d-block border-bottom border-light pb-1 mb-1" style="font-size: 0.9rem;">
                        <i class="bi bi-caret-right text-muted"></i> ${ins}
                     </span>`
                ).join('');

                return `
                    <tr>
                        <td class="ps-3 align-top">
                            <span class="fw-bold d-block text-dark">${grupo.horaMin}</span>
                            <small class="text-muted">${grupo.fechaDia}</small>
                        </td>
                        <td class="align-top">${tipoBadge}</td>
                        <td class="align-top">
                            <small class="text-muted d-block mb-2">${grupo.destinoOProcedencia}</small>
                            <div class="bg-light p-2 rounded">${listaInsumosHTML}</div>
                        </td>
                        <td class="align-top"><span class="text-secondary fw-medium">${grupo.responsable || 'N/A'}</span></td>
                        <td class="text-center align-top"><small class="text-muted">${grupo.motivo || 'Sin motivo'}</small></td>
                    </tr>
                `;
            }).join('');
        }
    } catch (error) {
        console.error("Error al cargar el historial:", error);
        document.getElementById('tbody-historial-compras').innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Error al cargar historial.</td></tr>`;
    }
}

// --- GESTIÓN DE ÁREAS / DEPARTAMENTOS ---
let listaAreasGlobal = [];

async function cargarAreas() {
    try {
        const res = await fetch(`${BASE_URL}/areas/get_areas.php`, { headers: { 'Authorization': 'Bearer ' + token } });
        if (res.ok) {
            listaAreasGlobal = await res.json();
            renderizarAreas(listaAreasGlobal);
        } else {
            document.getElementById('tbody-areas').innerHTML = '<tr><td colspan="3" class="text-center text-danger py-4">Error al obtener áreas.</td></tr>';
        }
    } catch (error) { console.error("Error cargando áreas:", error); }
}

function renderizarAreas(areas) {
    const tbody = document.getElementById('tbody-areas');
    if (areas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No se encontraron áreas registradas.</td></tr>';
        return;
    }
    tbody.innerHTML = areas.map(a => `
        <tr>
            <td class="ps-3 text-muted">${a.id}</td>
            <td class="fw-bold text-dark">${a.nombre}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick="editarArea(${a.id}, '${a.nombre}')" title="Modificar Nombre"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="eliminarArea(${a.id})" title="Dar de baja"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`).join('');
}

// Botón: Nueva Área
const btnNuevaArea = document.getElementById('btn-nueva-area');
if (btnNuevaArea) {
    btnNuevaArea.addEventListener('click', () => {
        Swal.fire({
            title: 'Registrar Nueva Área',
            html: `<input id="swal-nombre-area" class="swal2-input" placeholder="Ej. CENTRO DE COPIADO">`,
            confirmButtonText: 'Guardar Área', confirmButtonColor: '#8c837b', focusConfirm: false,
            preConfirm: () => { return { nombre: document.getElementById('swal-nombre-area').value.trim().toUpperCase() } }
        }).then(async (result) => {
            if (result.isConfirmed && result.value.nombre !== '') {
                try {
                    const res = await fetch(`${BASE_URL}/areas/post_area.php`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }, body: JSON.stringify(result.value) });
                    if (res.ok) { Swal.fire('¡Registrado!', 'El área se guardó con éxito.', 'success'); cargarAreas(); }
                    else { Swal.fire('Error', 'No se pudo registrar el área.', 'error'); }
                } catch (e) { console.error(e); }
            }
        });
    });
}

// Modificar Área
window.editarArea = function (id, nombre) {
    Swal.fire({
        title: 'Modificar Área',
        html: `<input id="swal-edit-area" class="swal2-input" value="${nombre}" placeholder="Nombre del Área">`,
        confirmButtonText: 'Guardar Cambios', confirmButtonColor: '#8c837b', focusConfirm: false,
        preConfirm: () => { return { id: id, nombre: document.getElementById('swal-edit-area').value.trim().toUpperCase() } }
    }).then(async (result) => {
        if (result.isConfirmed && result.value.nombre !== '') {
            try {
                const res = await fetch(`${BASE_URL}/areas/put_area.php`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token }, body: JSON.stringify(result.value) });
                if (res.ok) { Swal.fire('¡Actualizado!', 'El nombre se actualizó con éxito.', 'success'); cargarAreas(); }
                else { Swal.fire('Error', 'No se pudo actualizar.', 'error'); }
            } catch (e) { console.error(e); }
        }
    });
};

// Eliminar Área
window.eliminarArea = async function (id) {
    const confirmacion = await Swal.fire({ title: '¿Dar de baja esta área?', text: "Ya no aparecerá en el almacén para hacerle entregas.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d9534f', cancelButtonColor: '#8c837b', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' });
    if (confirmacion.isConfirmed) {
        try {
            const res = await fetch(`${BASE_URL}/areas/delete_area.php?id=${id}`, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token } });
            if (res.ok) { await Swal.fire({ icon: 'success', title: 'Eliminada', timer: 1500, showConfirmButton: false }); cargarAreas(); }
            else { Swal.fire('Error', 'No se pudo eliminar el área. Es probable que tenga movimientos en el historial.', 'error'); }
        } catch (error) { console.error(error); }
    }
};

// Buscador en tiempo real
const inputBuscarArea = document.getElementById('input-buscar-area');
if (inputBuscarArea) {
    inputBuscarArea.addEventListener('input', (e) => {
        const texto = e.target.value.toLowerCase();
        renderizarAreas(listaAreasGlobal.filter(a => a.nombre.toLowerCase().includes(texto)));
    });
}

// --- FILTRO REACTIVO PARA EL HISTORIAL DE COMPRAS ---
document.addEventListener('DOMContentLoaded', () => {
    // Tus llamadas iniciales existentes...
    cargarDatosCompras();
    cargarProveedores();
    cargarEmpleados();
    cargarHistorialCompras(); 
    cargarAreas();

    // NUEVO: Lógica del buscador
    const inputBuscarHistorialCompras = document.getElementById('input-buscar-historial-compras');
    if (inputBuscarHistorialCompras) {
        inputBuscarHistorialCompras.addEventListener('input', function() {
            const texto = this.value.toLowerCase();
            const filas = document.querySelectorAll('#tbody-historial-compras tr'); 

            filas.forEach(fila => {
                const contenido = fila.textContent.toLowerCase();
                fila.style.display = contenido.includes(texto) ? '' : 'none';
            });
        });
    }
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
            localStorage.removeItem('token');
            window.location.href = 'index.php';
        }
    });
}