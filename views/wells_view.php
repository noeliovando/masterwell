<?php require_once 'partials/header.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<style>
.action-btn {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.action-btn:hover {
    background-color: #0056b3;
}

.edit-btn {
    background-color: #28a745;
}

.edit-btn:hover {
    background-color: #1e7e34;
}

.view-btn {
    background-color: #17a2b8;
    margin-right: 5px;
}

.view-btn:hover {
    background-color: #138496;
}

/* Estilos para el modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    width: 80%;
    max-width: 800px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.large-modal {
    width: 90%;
    max-width: 1200px;
}

.modal-header {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close-modal {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover {
    color: #000;
}

.modal-body {
    padding: 20px;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 15px 20px;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 8px 8px;
    text-align: right;
}

.btn-cancel {
    background-color: #6c757d;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-cancel:hover {
    background-color: #545b62;
}

.detail-section {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.detail-section h4 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #333;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 5px;
}

.detail-section p {
    margin: 5px 0;
    line-height: 1.4;
}

.well-details-modal {
    max-height: 600px;
    overflow-y: auto;
}

.btn-details {
    text-decoration: none;
}
</style>

<div class="dg-title-container">
    <h1>Vista de Pozos</h1>
</div>

<form action="<?php echo BASE_PATH; ?>/wells-view" method="get" class="search-form" style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:12px;">
    <div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;">
        <div>
            <label>UWI</label>
            <input type="text" name="uwi" value="<?php echo htmlspecialchars($_GET['uwi'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
        </div>
        <div>
            <label>Localización</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
        </div>
        <div>
            <label>Unidad de Explotación</label>
            <input type="text" name="exploitation_unit" value="<?php echo htmlspecialchars($_GET['exploitation_unit'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
        </div>
        <div>
            <label>Operadora</label>
            <input type="text" name="operator" value="<?php echo htmlspecialchars($_GET['operator'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
        </div>

        <div>
            <label>Campo</label>
            <input type="text" name="field" value="<?php echo htmlspecialchars($_GET['field'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
        </div>
        <div>
            <label>Distrito</label>
            <input type="text" name="district" value="<?php echo htmlspecialchars($_GET['district'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
        </div>
        <div>
            <label>Gov. Assign No.</label>
            <input type="text" name="gov" value="<?php echo htmlspecialchars($_GET['gov'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
        </div>
        <div style="display:flex;gap:8px;">
            <div style="flex:1;">
                <label>Spud desde</label>
                <input type="date" name="spud_from" value="<?php echo htmlspecialchars($_GET['spud_from'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
            </div>
            <div style="flex:1;">
                <label>Spud hasta</label>
                <input type="date" name="spud_to" value="<?php echo htmlspecialchars($_GET['spud_to'] ?? ''); ?>" class="input" style="width:100%;padding:6px;border:1px solid #d1d5db;border-radius:4px;">
            </div>
        </div>
    </div>
    <div style="margin-top:10px;display:flex;gap:8px;justify-content:flex-end;">
        <button type="submit" class="btn primary" style="background:#007bff;color:white;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Buscar</button>
        <a class="btn" href="<?php echo BASE_PATH; ?>/wells-view" style="background:#6c757d;color:white;text-decoration:none;padding:8px 16px;border-radius:4px;">Limpiar</a>
    </div>
</form>

<?php
$hasFilters = !empty(array_filter($filters ?? []));
if ($hasFilters): ?>
    <div class="search-results-info">
        <?php if (isset($wells) && is_array($wells) && !isset($wells['error'])): ?>
            <p>Se encontraron <?php echo count($wells); ?> pozo(s) que coinciden con los filtros aplicados</p>
        <?php elseif (isset($wells['error'])): ?>
            <p class="error">Error en la búsqueda: <?php echo htmlspecialchars($wells['error']); ?></p>
        <?php else: ?>
            <p>No se encontraron resultados para los filtros aplicados</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (isset($wells) && is_array($wells) && !isset($wells['error']) && count($wells) > 0): ?>
<table id="wellsTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>UWI</th>
            <th>Nombre</th>
            <th>Gov No.</th>
            <th>Operadora</th>
            <th>Campo</th>
            <th>Distrito</th>
            <th>Localización</th>
            <th>Unidad Expl.</th>
            <th>Spud</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($wells as $well): ?>
            <tr>
                <td><?php echo htmlspecialchars($well['UWI'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['WELL_NAME'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['GOVT_ASSIGNED_NO'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['OPERATOR'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['FIELD'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['DISTRICT'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['LOCATION_TABLE'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['AGENT'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($well['SPUD_DATE'] ?? ''); ?></td>
                <td>
                    <button type="button" class="action-btn view-btn" onclick="viewWellDetails('<?php echo htmlspecialchars($well['UWI']); ?>')">Ver</button>
                    <a href="<?php echo BASE_PATH; ?>/well?search_well=<?php echo urlencode($well['UWI']); ?>&uwi=<?php echo urlencode($well['UWI']); ?>" class="btn-edit">
                        <button type="button" class="action-btn edit-btn">Editar</button>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php elseif ($hasFilters): ?>
    <p>No se encontraron pozos que coincidan con los filtros aplicados.</p>
<?php else: ?>
    <p>Cargando datos de pozos...</p>
<?php endif; ?>

<?php require_once 'partials/footer.php'; ?>

<!-- Modal para ver detalles del pozo -->
<div id="wellDetailsModal" class="modal">
    <div class="modal-content large-modal">
        <div class="modal-header">
            <h3>Detalles del Pozo: <span id="modalWellUWI"></span></h3>
            <span class="close-modal" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="wellDetailsContent">
                <p>Cargando detalles...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal()" class="btn-cancel">Cerrar</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#wellsTable').DataTable({
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        "pageLength": 25,
        "responsive": true
    });
});

// Función para ver detalles del pozo en modal
function viewWellDetails(uwi) {
    $('#modalWellUWI').text(uwi);
    $('#wellDetailsContent').html('<p>Cargando detalles...</p>');
    $('#wellDetailsModal').show();

    // Hacer petición AJAX para obtener detalles
    $.ajax({
        url: '<?php echo BASE_PATH; ?>/wells/details',
        method: 'GET',
        data: { uwi: uwi },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayWellDetails(response.well, response.related_wells);
            } else {
                $('#wellDetailsContent').html('<p class="error">Error: ' + (response.error || 'Error desconocido') + '</p>');
            }
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr.responseText, status, error);
            $('#wellDetailsContent').html('<p class="error">Error al cargar detalles: ' + error + '<br>Status: ' + xhr.status + '<br>Response: ' + xhr.responseText.substring(0, 200) + '...</p>');
        }
    });
}

// Función para mostrar los detalles en el modal
function displayWellDetails(well, relatedWells) {
    let html = '<div class="well-details-modal">';

    // Información básica
    html += '<div class="detail-section">';
    html += '<h4>Información Básica</h4>';
    html += '<p><strong>UWI:</strong> ' + (well.UWI || 'N/A') + '</p>';
    html += '<p><strong>Nombre Pozo:</strong> ' + (well.WELL_NAME || 'N/A') + '</p>';
    html += '<p><strong>Nombre Corto:</strong> ' + (well.SHORT_NAME || 'N/A') + '</p>';
    html += '<p><strong>Nombre en Mapa:</strong> ' + (well.PLOT_NAME || 'N/A') + '</p>';
    html += '<p><strong>Clasificación Inicial:</strong> ' + (well.INITIAL_CLASS || 'N/A') + '</p>';
    html += '<p><strong>Clasificación Final:</strong> ' + (well.CLASS || 'N/A') + '</p>';
    html += '<p><strong>Estado Original:</strong> ' + (well.ORSTATUS || 'N/A') + '</p>';
    html += '<p><strong>Estado Actual:</strong> ' + (well.CRSTATUS || 'N/A') + '</p>';
    html += '<p><strong>Gov. Assign No.:</strong> ' + (well.GOVT_ASSIGNED_NO || 'N/A') + '</p>';
    html += '<p><strong>Unidad de Explotación:</strong> ' + (well.AGENT || 'N/A') + '</p>';
    html += '</div>';

    // Ubicación
    html += '<div class="detail-section">';
    html += '<h4>Ubicación Geopolítica</h4>';
    html += '<p><strong>País:</strong> ' + (well.COUNTRY || 'N/A') + '</p>';
    html += '<p><strong>Provincia Geológica:</strong> ' + (well.GEOLOGIC_PROVINCE || 'N/A') + '</p>';
    html += '<p><strong>Estado/Provincia:</strong> ' + (well.PROV_ST || 'N/A') + '</p>';
    html += '<p><strong>Municipio:</strong> ' + (well.COUNTY || 'N/A') + '</p>';
    html += '<p><strong>Campo:</strong> ' + (well.FIELD || 'N/A') + '</p>';
    html += '<p><strong>Operadora:</strong> ' + (well.OPERATOR || 'N/A') + '</p>';
    html += '<p><strong>Distrito:</strong> ' + (well.DISTRICT || 'N/A') + '</p>';
    html += '<p><strong>Localización:</strong> ' + (well.LOCATION_TABLE || 'N/A') + '</p>';
    html += '</div>';

    // Fechas importantes
    html += '<div class="detail-section">';
    html += '<h4>Fechas Importantes</h4>';
    html += '<p><strong>Fecha Spud:</strong> ' + (well.SPUD_DATE || 'N/A') + '</p>';
    html += '<p><strong>Fecha de Completación:</strong> ' + (well.COMPLETION_DATE || 'N/A') + '</p>';
    html += '<p><strong>Fecha de Abandono:</strong> ' + (well.ABANDONMENT_DATE || 'N/A') + '</p>';
    html += '<p><strong>Fecha de Producción:</strong> ' + (well.PRODUCTION_DATE || 'N/A') + '</p>';
    html += '</div>';

    // Información técnica
    html += '<div class="detail-section">';
    html += '<h4>Información Técnica</h4>';
    html += '<p><strong>Profundidad Total:</strong> ' + (well.TOTAL_DEPTH || 'N/A') + ' ' + (well.TOTAL_DEPTH_UOM || '') + '</p>';
    html += '<p><strong>Profundidad de Perforación:</strong> ' + (well.DRILLING_DEPTH || 'N/A') + ' ' + (well.DRILLING_DEPTH_UOM || '') + '</p>';
    html += '<p><strong>Profundidad de Agua:</strong> ' + (well.WATER_DEPTH || 'N/A') + ' ' + (well.WATER_DEPTH_UOM || '') + '</p>';
    html += '<p><strong>Elevación del Suelo:</strong> ' + (well.GROUND_ELEVATION || 'N/A') + ' ' + (well.GROUND_ELEVATION_UOM || '') + '</p>';
    html += '<p><strong>Elevación del Kelly Bushing:</strong> ' + (well.KB_ELEVATION || 'N/A') + ' ' + (well.KB_ELEVATION_UOM || '') + '</p>';
    html += '</div>';

    // Coordenadas
    html += '<div class="detail-section">';
    html += '<h4>Coordenadas</h4>';
    html += '<p><strong>Latitud:</strong> ' + (well.LATITUDE || 'N/A') + '</p>';
    html += '<p><strong>Longitud:</strong> ' + (well.LONGITUDE || 'N/A') + '</p>';
    html += '<p><strong>Datum:</strong> ' + (well.DATUM || 'N/A') + '</p>';
    html += '<p><strong>Método de Localización:</strong> ' + (well.LOCATION_METHOD || 'N/A') + '</p>';
    html += '</div>';

    // Pozos relacionados
    if (relatedWells && relatedWells.length > 0) {
        html += '<div class="detail-section">';
        html += '<h4>Pozos Relacionados</h4>';
        html += '<table class="related-wells-table" style="width:100%;border-collapse:collapse;">';
        html += '<thead><tr style="background-color:#f8f9fa;"><th style="border:1px solid #dee2e6;padding:8px;">UWI</th><th style="border:1px solid #dee2e6;padding:8px;">Secuencia</th><th style="border:1px solid #dee2e6;padding:8px;">Tipo</th></tr></thead>';
        html += '<tbody>';
        relatedWells.forEach(function(rwell) {
            html += '<tr>';
            html += '<td style="border:1px solid #dee2e6;padding:8px;">' + (rwell.UWI || 'N/A') + '</td>';
            html += '<td style="border:1px solid #dee2e6;padding:8px;">' + (rwell.SECUENCIA || 'N/A') + '</td>';
            html += '<td style="border:1px solid #dee2e6;padding:8px;">' + (rwell.TIPO_DE_HOYO || 'N/A') + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        html += '</div>';
    }

    html += '</div>';
    $('#wellDetailsContent').html(html);
}

// Función para cerrar modal
function closeModal() {
    $('#wellDetailsModal').hide();
}

// Cerrar modal al hacer clic fuera
$(window).click(function(event) {
    if (event.target.id === 'wellDetailsModal') {
        closeModal();
    }
});
</script>