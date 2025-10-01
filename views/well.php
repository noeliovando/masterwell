<?php
require_once 'partials/header.php';
require_once 'models/Well.php';
require_once 'partials/footer.php';
?>

<div class="dg-title-container">
    <h1>Datos Generales de Pozo</h1>
</div>

<form action="<?php echo BASE_PATH; ?>/well" method="get">
    <div class="form-group-search">
        <label for="search_well">UWI:</label>
        <input type="text" id="search_well" name="search_well" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Escriba el UWI o parte del UWI..." >
        <button type="submit">Buscar</button>
    </div>
</form>

<?php if ($error): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="well-layout-container">
    <div class="well-list-panel">
        <?php if (!empty($search_term)): ?>
            <?php if (count($results) > 0): ?>
                <p>Se encontraron <?php echo count($results); ?> resultado(s).</p>
                <div class="well-results-container <?php echo count($results) > 20 ? 'scroll-enabled' : ''; ?>">
                    <table>
                        <thead>
                            <tr>
                                <th>UWI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr>
                                    <td><a href="<?php echo BASE_PATH; ?>/well?search_well=<?php echo urlencode($search_term); ?>&uwi=<?php echo urlencode($row['UWI']); ?>"><?php echo htmlspecialchars($row['UWI'] ?? ''); ?></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No se encontraron pozos que coincidan con el término de búsqueda "<b><?php echo htmlspecialchars($search_term); ?></b>".</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

<div class="well-details-panel">
        <?php if ($well_details): ?>
            <form id="wellDetailsForm" action="<?php echo BASE_PATH; ?>/well/update" method="post">
                <input type="hidden" name="uwi" value="<?php echo htmlspecialchars($well_details['UWI'] ?? 'S/I'); ?>">
                <div class="well-details-header">
                    <h1>Pozo: <?php echo htmlspecialchars($well_details['UWI'] ?? 'S/I'); ?></h1>
                    <div class="well-actions">
                        <!-- Botones de edición/guardado globales eliminados -->
                    </div>
                </div>
                <div class="details-grid">
                    <!-- Columna 1 -->
                    <div class="column-section" style="flex: 1; display: flex; flex-direction: column; gap: 1rem;">
                        <div class="card">
                            <h2>IDENTIFICACIÓN DEL HOYO</h2>
                            <p><strong>UWI:</strong> <?php echo htmlspecialchars($well_details['UWI'] ?? 'S/I'); ?></p>
                            <p><strong>NOMBRE POZO (WELL_NAME):</strong>
                                <span class="read-only-display" data-field="WELL_NAME"><?php echo htmlspecialchars($well_details['WELL_NAME'] ?? 'S/I'); ?></span>
                                <input type="text" name="WELL_NAME" class="editable-field" data-field="WELL_NAME" value="<?php echo htmlspecialchars($well_details['WELL_NAME'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="WELL_NAME">Editar</button>
                                <button type="button" class="save-field-button" data-field="WELL_NAME" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>NOMBRE CORTO:</strong>
                                <span class="read-only-display" data-field="SHORT_NAME"><?php echo htmlspecialchars($well_details['SHORT_NAME'] ?? 'S/I'); ?></span>
                                <input type="text" name="SHORT_NAME" class="editable-field" data-field="SHORT_NAME" value="<?php echo htmlspecialchars($well_details['SHORT_NAME'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="SHORT_NAME">Editar</button>
                                <button type="button" class="save-field-button" data-field="SHORT_NAME" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>NOMBRE EN MAPA:</strong>
                                <span class="read-only-display" data-field="PLOT_NAME"><?php echo htmlspecialchars($well_details['PLOT_NAME'] ?? 'S/I'); ?></span>
                                <input type="text" name="PLOT_NAME" class="editable-field" data-field="PLOT_NAME" value="<?php echo htmlspecialchars($well_details['PLOT_NAME'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="PLOT_NAME">Editar</button>
                                <button type="button" class="save-field-button" data-field="PLOT_NAME" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>NOMBRE CENTINELA:</strong>
                                <span class="read-only-display" data-field="GOVT_ASSIGNED_NO"><?php echo htmlspecialchars($well_details['GOVT_ASSIGNED_NO'] ?? 'S/I'); ?></span>
                                <input type="text" name="GOVT_ASSIGNED_NO" class="editable-field" data-field="GOVT_ASSIGNED_NO" value="<?php echo htmlspecialchars($well_details['GOVT_ASSIGNED_NO'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="GOVT_ASSIGNED_NO">Editar</button>
                                <button type="button" class="save-field-button" data-field="GOVT_ASSIGNED_NO" style="display: none;">Guardar</button>
                            </p>
                        </div>
                        <div class="card">
                            <h2>ESTADO DEL HOYO</h2>
                            <p><strong>CLASIFICACION LAHEE INICIAL:</strong>
                                <span class="read-only-display" data-field="INITIAL_CLASS"><?php echo htmlspecialchars($well_details['INITIAL_CLASS_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="INITIAL_CLASS" class="editable-field" data-field="INITIAL_CLASS" style="display: none;">
                                    <?php foreach ($options['INITIAL_CLASS'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['INITIAL_CLASS'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="INITIAL_CLASS">Editar</button>
                                <button type="button" class="save-field-button" data-field="INITIAL_CLASS" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>CLASIFICACION LAHEE FINAL:</strong>
                                <span class="read-only-display" data-field="CLASS"><?php echo htmlspecialchars($well_details['CLASS_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="CLASS" class="editable-field" data-field="CLASS" style="display: none;">
                                    <?php foreach ($options['CLASS'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['CLASS'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="CLASS">Editar</button>
                                <button type="button" class="save-field-button" data-field="CLASS" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>ESTADO ORIGINAL:</strong>
                                <span class="read-only-display" data-field="ORSTATUS"><?php echo htmlspecialchars($well_details['ORSTATUS_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="ORSTATUS" class="editable-field" data-field="ORSTATUS" style="display: none;">
                                    <?php foreach ($options['ORSTATUS'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['ORSTATUS'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="ORSTATUS">Editar</button>
                                <button type="button" class="save-field-button" data-field="ORSTATUS" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>ESTADO ACTUAL:</strong>
                                <span class="read-only-display" data-field="CRSTATUS"><?php echo htmlspecialchars($well_details['CRSTATUS_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="CRSTATUS" class="editable-field" data-field="CRSTATUS" style="display: none;">
                                    <?php foreach ($options['CRSTATUS'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['CRSTATUS'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="CRSTATUS">Editar</button>
                                <button type="button" class="save-field-button" data-field="CRSTATUS" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>TIPO DE POZO:</strong> <?php echo htmlspecialchars($well_details['WELL_TYPE'] ?? 'S/I'); ?></p>
                        </div>
                        <div class="card">
                            <h2>UBICACIÓN GEOPOLÍTICA</h2>
                            <p><strong>PAIS:</strong>
                                <span class="read-only-display" data-field="COUNTRY"><?php echo htmlspecialchars($well_details['COUNTRY_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="COUNTRY" class="editable-field" data-field="COUNTRY" style="display: none;">
                                    <?php foreach ($options['COUNTRY'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['COUNTRY'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="COUNTRY">Editar</button>
                                <button type="button" class="save-field-button" data-field="COUNTRY" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>CUENCA/SUBCUENCA:</strong>
                                <span class="read-only-display" data-field="GEOLOGIC_PROVINCE"><?php echo htmlspecialchars($well_details['GEOLOGIC_PROVINCE'] ?? 'S/I'); ?></span>
                                <select name="GEOLOGIC_PROVINCE" class="editable-field" data-field="GEOLOGIC_PROVINCE" style="display: none;">
                                    <?php foreach ($options['GEOLOGIC_PROVINCE'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['GEOLOGIC_PROVINCE'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['code'] . ' - ' . $option['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="GEOLOGIC_PROVINCE">Editar</button>
                                <button type="button" class="save-field-button" data-field="GEOLOGIC_PROVINCE" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>ESTADO/PROVINCIA:</strong>
                                <span class="read-only-display" data-field="PROV_ST"><?php echo htmlspecialchars($well_details['PROV_ST_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="PROV_ST" class="editable-field" data-field="PROV_ST" style="display: none;">
                                    <?php foreach ($options['PROV_ST'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['PROV_ST'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="PROV_ST">Editar</button>
                                <button type="button" class="save-field-button" data-field="PROV_ST" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>MUNICIPIO:</strong>
                                <span class="read-only-display" data-field="COUNTY"><?php echo htmlspecialchars($well_details['COUNTY_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="COUNTY" class="editable-field" data-field="COUNTY" style="display: none;">
                                    <?php foreach ($options['COUNTY'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['COUNTY'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="COUNTY">Editar</button>
                                <button type="button" class="save-field-button" data-field="COUNTY" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>AREA GEOGRAFICA:</strong> <?php echo htmlspecialchars($well_details['GEOGRAPHIC_AREA'] ?? 'S/I'); ?></p>
                            <p><strong>CAMPO GEOLOGICO:</strong>
                                <span class="read-only-display" data-field="FIELD"><?php echo htmlspecialchars($well_details['FIELD_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="FIELD" class="editable-field" data-field="FIELD" style="display: none;">
                                    <?php foreach ($options['FIELD'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['FIELD'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['code'] . ' - ' . $option['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="FIELD">Editar</button>
                                <button type="button" class="save-field-button" data-field="FIELD" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>BLOQUE/PARCELA:</strong>
                                <span class="read-only-display" data-field="BLOCK_ID"><?php echo htmlspecialchars($well_details['BLOCK_ID'] ?? 'S/I'); ?></span>
                                <select name="BLOCK_ID" class="editable-field" data-field="BLOCK_ID" style="display: none;">
                                    <?php foreach ($options['BLOCK_ID'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['BLOCK_ID'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="BLOCK_ID">Editar</button>
                                <button type="button" class="save-field-button" data-field="BLOCK_ID" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>LOCALIZACIÓN:</strong>
                                <span class="read-only-display" data-field="LOCATION_TABLE"><?php echo htmlspecialchars($well_details['LOCATION_TABLE'] ?? 'S/I'); ?></span>
                                <input type="text" name="LOCATION_TABLE" class="editable-field" data-field="LOCATION_TABLE" value="<?php echo htmlspecialchars($well_details['LOCATION_TABLE'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="LOCATION_TABLE">Editar</button>
                                <button type="button" class="save-field-button" data-field="LOCATION_TABLE" style="display: none;">Guardar</button>
                            </p>
                        </div>
                        <div class="card">
                            <h2>COORDENADAS</h2>
                            <table class="coordinates-table">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th>Geográficas</th>
                                        <th>UTM - La Canoa</th>
                                        <th>UTM - REGVEN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Datum</strong></td>
                                        <td>S/I</td>
                                        <td><?php echo htmlspecialchars($well_details['CANOA_DATUM'] ?? 'S/I'); ?></td>
                                        <td><?php echo htmlspecialchars($well_details['REGVEN_DATUM'] ?? 'S/I'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Longitud/Este</strong></td>
                                        <td><?php echo htmlspecialchars($well_details['LONGITUD'] ?? 'S/I'); ?></td>
                                        <td><?php echo htmlspecialchars($well_details['CANOA_ESTE'] ?? 'S/I'); ?></td>
                                        <td><?php echo htmlspecialchars($well_details['REGVEN_ESTE'] ?? 'S/I'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Latitud/Norte</strong></td>
                                        <td><?php echo htmlspecialchars($well_details['LATITUD'] ?? 'S/I'); ?></td>
                                        <td><?php echo htmlspecialchars($well_details['CANOA_NORTE'] ?? 'S/I'); ?></td>
                                        <td><?php echo htmlspecialchars($well_details['REGVEN_NORTE'] ?? 'S/I'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Origen</strong></td>
                                        <td>S/I</td>
                                        <td><?php echo htmlspecialchars($well_details['CANOA_ORIGEN'] ?? 'S/I'); ?></td>
                                        <td><?php echo htmlspecialchars($well_details['REGVEN_ORIGEN'] ?? 'S/I'); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card">
                            <h2>EVENTOS</h2>
                            <p><strong>FECHA MUDANZA TALADRO:</strong>
                                <span class="read-only-display" data-field="SPUD_DATE"><?php echo htmlspecialchars($well_details['SPUD_DATE'] ?? 'S/I'); ?></span>
                                <input type="text" name="SPUD_DATE" value="<?php echo htmlspecialchars($well_details['SPUD_DATE'] ?? 'S/I'); ?>" class="editable-field" data-field="SPUD_DATE" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="SPUD_DATE">Editar</button>
                                <button type="button" class="save-field-button" data-field="SPUD_DATE" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>FECHA INICIO PERFORACION:</strong> <?php echo htmlspecialchars($well_details['REMARKS'] ?? 'S/I'); ?></p>
                            <p><strong>FECHA FINAL PERFORACION:</strong>
                                <span class="read-only-display" data-field="FIN_DRILL"><?php echo htmlspecialchars($well_details['FIN_DRILL'] ?? 'S/I'); ?></span>
                                <input type="text" name="FIN_DRILL" value="<?php echo htmlspecialchars($well_details['FIN_DRILL'] ?? 'S/I'); ?>" class="editable-field" data-field="FIN_DRILL" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="FIN_DRILL">Editar</button>
                                <button type="button" class="save-field-button" data-field="FIN_DRILL" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>FECHA SUSPENSION:</strong>
                                <span class="read-only-display" data-field="RIGREL"><?php echo htmlspecialchars($well_details['RIGREL'] ?? 'S/I'); ?></span>
                                <input type="text" name="RIGREL" value="<?php echo htmlspecialchars($well_details['RIGREL'] ?? 'S/I'); ?>" class="editable-field" data-field="RIGREL" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="RIGREL">Editar</button>
                                <button type="button" class="save-field-button" data-field="RIGREL" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>FECHA DE COMPLETACION:</strong>
                                <span class="read-only-display" data-field="COMP_DATE"><?php echo htmlspecialchars($well_details['COMP_DATE'] ?? 'S/I'); ?></span>
                                <input type="text" name="COMP_DATE" value="<?php echo htmlspecialchars($well_details['COMP_DATE'] ?? 'S/I'); ?>" class="editable-field" data-field="COMP_DATE" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="COMP_DATE">Editar</button>
                                <button type="button" class="save-field-button" data-field="COMP_DATE" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>FECHA INICIO COMO INYECTOR:</strong>
                                <span class="read-only-display" data-field="ONINJECT"><?php echo htmlspecialchars($well_details['ONINJECT'] ?? 'S/I'); ?></span>
                                <input type="text" name="ONINJECT" value="<?php echo htmlspecialchars($well_details['ONINJECT'] ?? 'S/I'); ?>" class="editable-field" data-field="ONINJECT" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="ONINJECT">Editar</button>
                                <button type="button" class="save-field-button" data-field="ONINJECT" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>FECHA INICIO COMO PRODUCTOR:</strong>
                                <span class="read-only-display" data-field="ONPROD"><?php echo htmlspecialchars($well_details['ONPROD'] ?? 'S/I'); ?></span>
                                <input type="text" name="ONPROD" value="<?php echo htmlspecialchars($well_details['ONPROD'] ?? 'S/I'); ?>" class="editable-field" data-field="ONPROD" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="ONPROD">Editar</button>
                                <button type="button" class="save-field-button" data-field="ONPROD" style="display: none;">Guardar</button>
                            </p>
                        </div>
                        <div class="card">
                            <h2>DATOS ADICIONALES</h2>
                            <p><strong>POZO DESCUBRIDOR:</strong>
                                <span class="read-only-display" data-field="DISCOVER_WELL"><?php echo htmlspecialchars($well_details['DISCOVER_WELL_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="DISCOVER_WELL" class="editable-field" data-field="DISCOVER_WELL" style="display: none;">
                                    <?php foreach ($options['DISCOVER_WELL'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['DISCOVER_WELL'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="DISCOVER_WELL">Editar</button>
                                <button type="button" class="save-field-button" data-field="DISCOVER_WELL" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>POZO CON DESVIO:</strong>
                                <span class="read-only-display" data-field="DEVIATION_FLAG"><?php echo htmlspecialchars($well_details['DEVIATION_FLAG_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="DEVIATION_FLAG" class="editable-field" data-field="DEVIATION_FLAG" style="display: none;">
                                    <?php foreach ($options['DEVIATION_FLAG'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['DEVIATION_FLAG'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="DEVIATION_FLAG">Editar</button>
                                <button type="button" class="save-field-button" data-field="DEVIATION_FLAG" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>SIMBOLO EN MAPA:</strong>
                                <span class="read-only-display" data-field="PLOT_SYMBOL"><?php echo htmlspecialchars($well_details['PLOT_SYMBOL_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="PLOT_SYMBOL" class="editable-field" data-field="PLOT_SYMBOL" style="display: none;">
                                    <?php foreach ($options['PLOT_SYMBOL'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['PLOT_SYMBOL'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="PLOT_SYMBOL">Editar</button>
                                <button type="button" class="save-field-button" data-field="PLOT_SYMBOL" style="display: none;">Guardar</button>
                            </p>
                        </div>
                    </div>
                    <!-- Columna 2 -->
                    <div class="column-section" style="flex: 1; display: flex; flex-direction: column; gap: 1rem;">
                        <div class="card">
                            <h2>SECUENCIA DE PERFORACIÓN</h2>
                            <p><strong>TIPO DE HOYO:</strong>
                                <span class="read-only-display" data-field="WELL_HDR_TYPE"><?php echo htmlspecialchars($well_details['WELL_HDR_TYPE_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="WELL_HDR_TYPE" class="editable-field" data-field="WELL_HDR_TYPE" style="display: none;">
                                    <?php foreach ($options['WELL_HDR_TYPE'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['WELL_HDR_TYPE'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="WELL_HDR_TYPE">Editar</button>
                                <button type="button" class="save-field-button" data-field="WELL_HDR_TYPE" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>NÚMERO EN LA SECUENCIA:</strong>
                                <span class="read-only-display" data-field="WELL_NUMBER"><?php echo htmlspecialchars($well_details['WELL_NUMBER'] ?? 'S/I'); ?></span>
                                <input type="text" name="WELL_NUMBER" class="editable-field" data-field="WELL_NUMBER" value="<?php echo htmlspecialchars($well_details['WELL_NUMBER'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="WELL_NUMBER">Editar</button>
                                <button type="button" class="save-field-button" data-field="WELL_NUMBER" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>HOYO PRINCIPAL:</strong>
                                <span class="read-only-display" data-field="PARENT_UWI"><?php echo htmlspecialchars($well_details['PARENT_UWI'] ?? 'S/I'); ?></span>
                                <input type="text" name="PARENT_UWI" class="editable-field" data-field="PARENT_UWI" value="<?php echo htmlspecialchars($well_details['PARENT_UWI'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="PARENT_UWI">Editar</button>
                                <button type="button" class="save-field-button" data-field="PARENT_UWI" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>HOYO PRECEDENTE:</strong>
                                <span class="read-only-display" data-field="TIE_IN_UWI"><?php echo htmlspecialchars($well_details['TIE_IN_UWI'] ?? 'S/I'); ?></span>
                                <input type="text" name="TIE_IN_UWI" class="editable-field" data-field="TIE_IN_UWI" value="<?php echo htmlspecialchars($well_details['TIE_IN_UWI'] ?? ''); ?>" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="TIE_IN_UWI">Editar</button>
                                <button type="button" class="save-field-button" data-field="TIE_IN_UWI" style="display: none;">Guardar</button>
                            </p>
                        </div>
                        <div class="card">
                            <h2>DATOS DE PERFORACIÓN</h2>
                            <p><strong>EMPRESA ORIGEN:</strong>
                                <span class="read-only-display" data-field="PRIMARY_SOURCE"><?php echo htmlspecialchars($well_details['PRIMARY_SOURCE_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="PRIMARY_SOURCE" class="editable-field" data-field="PRIMARY_SOURCE" style="display: none;">
                                    <?php foreach ($options['PRIMARY_SOURCE'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['PRIMARY_SOURCE'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="PRIMARY_SOURCE">Editar</button>
                                <button type="button" class="save-field-button" data-field="PRIMARY_SOURCE" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>CONTRATISTA:</strong>
                                <span class="read-only-display" data-field="CONTRACTOR"><?php echo htmlspecialchars($well_details['CONTRACTOR_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="CONTRACTOR" class="editable-field" data-field="CONTRACTOR" style="display: none;">
                                    <?php foreach ($options['CONTRACTOR'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['CONTRACTOR'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="CONTRACTOR">Editar</button>
                                <button type="button" class="save-field-button" data-field="CONTRACTOR" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>CODIGO DEL TALADRO:</strong>
                                <span class="read-only-display" data-field="RIG_NO"><?php echo htmlspecialchars($well_details['RIG_NO_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="RIG_NO" class="editable-field" data-field="RIG_NO" style="display: none;">
                                    <?php foreach ($options['RIG_NO'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['RIG_NO'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="RIG_NO">Editar</button>
                                <button type="button" class="save-field-button" data-field="RIG_NO" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>NOMBRE DEL TALADRO:</strong>
                                <span class="read-only-display" data-field="RIG_NAME"><?php echo htmlspecialchars($well_details['RIG_NAME'] ?? 'S/I'); ?></span>
                                <input type="text" name="RIG_NAME" value="<?php echo htmlspecialchars($well_details['RIG_NAME'] ?? 'S/I'); ?>" class="editable-field" data-field="RIG_NAME" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="RIG_NAME">Editar</button>
                                <button type="button" class="save-field-button" data-field="RIG_NAME" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>DIRECCION DEL HOYO:</strong>
                                <span class="read-only-display" data-field="HOLE_DIRECTION"><?php echo htmlspecialchars($well_details['HOLE_DIRECTION_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="HOLE_DIRECTION" class="editable-field" data-field="HOLE_DIRECTION" style="display: none;">
                                    <?php foreach ($options['HOLE_DIRECTION'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['HOLE_DIRECTION'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="HOLE_DIRECTION">Editar</button>
                                <button type="button" class="save-field-button" data-field="HOLE_DIRECTION" style="display: none;">Guardar</button>
                            </p>
                        </div>
                        <div class="card">
                            <h2>UBICACIÓN ADMINISTRATIVA</h2>
                            <p><strong>OPERADORA:</strong>
                                <span class="read-only-display" data-field="OPERATOR"><?php echo htmlspecialchars($well_details['OPERATOR_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="OPERATOR" class="editable-field" data-field="OPERATOR" style="display: none;">
                                    <?php foreach ($options['OPERATOR'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['OPERATOR'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="OPERATOR">Editar</button>
                                <button type="button" class="save-field-button" data-field="OPERATOR" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>DISTRITO:</strong>
                                <span class="read-only-display" data-field="DISTRICT"><?php echo htmlspecialchars($well_details['DISTRICT_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="DISTRICT" class="editable-field" data-field="DISTRICT" style="display: none;">
                                    <?php foreach ($options['DISTRICT'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['DISTRICT'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="DISTRICT">Editar</button>
                                <button type="button" class="save-field-button" data-field="DISTRICT" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>UE/UP:</strong>
                                <span class="read-only-display" data-field="AGENT"><?php echo htmlspecialchars($well_details['AGENT_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="AGENT" class="editable-field" data-field="AGENT" style="display: none;">
                                    <?php foreach ($options['AGENT'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['AGENT'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="AGENT">Editar</button>
                                <button type="button" class="save-field-button" data-field="AGENT" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>CÓDIGO LICENCIA/CONVENIO:</strong>
                                <span class="read-only-display" data-field="LEASE_NO"><?php echo htmlspecialchars($well_details['LEASE_NO_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="LEASE_NO" class="editable-field" data-field="LEASE_NO" style="display: none;">
                                    <?php foreach ($options['LEASE_NO'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['LEASE_NO'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['display_text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="LEASE_NO">Editar</button>
                                <button type="button" class="save-field-button" data-field="LEASE_NO" style="display: none;">Guardar</button>
                            </p>
                             <p><strong>NOMBRE LICENCIA/CONVENIO:</strong>
                                <span class="read-only-display" data-field="LEASE_NAME"><?php echo htmlspecialchars($well_details['LEASE_NAME'] ?? 'S/I'); ?></span>
                                <input type="text" name="LEASE_NAME" value="<?php echo htmlspecialchars($well_details['LEASE_NAME'] ?? 'S/I'); ?>" class="editable-field" data-field="LEASE_NAME" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="LEASE_NAME">Editar</button>
                                <button type="button" class="save-field-button" data-field="LEASE_NAME" style="display: none;">Guardar</button>
                            </p>
                        </div>
                        <div class="card">
                            <h2>PROFUNDIDADES</h2>
                            <p><strong>PROFUNDIDAD TOTAL:</strong>
                                <span class="read-only-display" data-field="DRILLERS_TD"><?php echo htmlspecialchars($well_details['DRILLERS_TD'] ?? 'S/I'); ?></span>
                                <input type="text" name="DRILLERS_TD" value="<?php echo htmlspecialchars($well_details['DRILLERS_TD'] ?? 'S/I'); ?>" class="editable-field" data-field="DRILLERS_TD" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="DRILLERS_TD">Editar</button>
                                <button type="button" class="save-field-button" data-field="DRILLERS_TD" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>PROFUNDIDAD TOTAL (VERTICALIZADA):</strong> <?php echo htmlspecialchars($well_details['TVD'] ?? 'S/I'); ?></p>
                            <p><strong>PROFUNDIDAD DE REGISTRO:</strong>
                                <span class="read-only-display" data-field="LOG_TD"><?php echo htmlspecialchars($well_details['LOG_TD'] ?? 'S/I'); ?></span>
                                <input type="text" name="LOG_TD" value="<?php echo htmlspecialchars($well_details['LOG_TD'] ?? 'S/I'); ?>" class="editable-field" data-field="LOG_TD" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="LOG_TD">Editar</button>
                                <button type="button" class="save-field-button" data-field="LOG_TD" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>PROFUNDIDAD DEL TAPON:</strong>
                                <span class="read-only-display" data-field="PLUGBACK_TD"><?php echo htmlspecialchars($well_details['PLUGBACK_TD'] ?? 'S/I'); ?></span>
                                <input type="text" name="PLUGBACK_TD" value="<?php echo htmlspecialchars($well_details['PLUGBACK_TD'] ?? 'S/I'); ?>" class="editable-field" data-field="PLUGBACK_TD" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="PLUGBACK_TD">Editar</button>
                                <button type="button" class="save-field-button" data-field="PLUGBACK_TD" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>K.O.P.:</strong>
                                <span class="read-only-display" data-field="WHIPSTOCK_DEPTH"><?php echo htmlspecialchars($well_details['WHIPSTOCK_DEPTH'] ?? 'S/I'); ?></span>
                                <input type="text" name="WHIPSTOCK_DEPTH" value="<?php echo htmlspecialchars($well_details['WHIPSTOCK_DEPTH'] ?? 'S/I'); ?>" class="editable-field" data-field="WHIPSTOCK_DEPTH" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="WHIPSTOCK_DEPTH">Editar</button>
                                <button type="button" class="save-field-button" data-field="WHIPSTOCK_DEPTH" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>PROFUNDIDAD CAPA DE AGUA:</strong>
                                <span class="read-only-display" data-field="WATER_DEPTH"><?php echo htmlspecialchars($well_details['WATER_DEPTH'] ?? 'S/I'); ?></span>
                                <input type="text" name="WATER_DEPTH" value="<?php echo htmlspecialchars($well_details['WATER_DEPTH'] ?? 'S/I'); ?>" class="editable-field" data-field="WATER_DEPTH" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="WATER_DEPTH">Editar</button>
                                <button type="button" class="save-field-button" data-field="WATER_DEPTH" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>REFERENCIA DE ELEVACION:</strong>
                                <span class="read-only-display" data-field="ELEVATION_REF"><?php echo htmlspecialchars($well_details['ELEVATION_REF_DISPLAY'] ?? 'S/I'); ?></span>
                                <select name="ELEVATION_REF" class="editable-field" data-field="ELEVATION_REF" style="display: none;">
                                    <?php foreach ($options['ELEVATION_REF'] as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option['code']); ?>" <?php echo ($well_details['ELEVATION_REF'] ?? '') == $option['code'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($option['description']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="edit-field-button" data-field="ELEVATION_REF">Editar</button>
                                <button type="button" class="save-field-button" data-field="ELEVATION_REF" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>VALOR DE ELEVACION:</strong>
                                <span class="read-only-display" data-field="ELEVATION"><?php echo htmlspecialchars($well_details['ELEVATION'] ?? 'S/I'); ?></span>
                                <input type="text" name="ELEVATION" value="<?php echo htmlspecialchars($well_details['ELEVATION'] ?? 'S/I'); ?>" class="editable-field" data-field="ELEVATION" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="ELEVATION">Editar</button>
                                <button type="button" class="save-field-button" data-field="ELEVATION" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>ELEVACION DEL TERRENO:</strong>
                                <span class="read-only-display" data-field="GROUND_ELEVATION"><?php echo htmlspecialchars($well_details['GROUND_ELEVATION'] ?? 'S/I'); ?></span>
                                <input type="text" name="GROUND_ELEVATION" value="<?php echo htmlspecialchars($well_details['GROUND_ELEVATION'] ?? 'S/I'); ?>" class="editable-field" data-field="GROUND_ELEVATION" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="GROUND_ELEVATION">Editar</button>
                                <button type="button" class="save-field-button" data-field="GROUND_ELEVATION" style="display: none;">Guardar</button>
                            </p>
                            <p><strong>ULTIMA FORMACION ALCANZADA:</strong>
                                <span class="read-only-display" data-field="FORM_AT_TD"><?php echo htmlspecialchars($well_details['FORM_AT_TD'] ?? 'S/I'); ?></span>
                                <input type="text" name="FORM_AT_TD" value="<?php echo htmlspecialchars($well_details['FORM_AT_TD'] ?? 'S/I'); ?>" class="editable-field" data-field="FORM_AT_TD" style="display: none;">
                                <button type="button" class="edit-field-button" data-field="FORM_AT_TD">Editar</button>
                                <button type="button" class="save-field-button" data-field="FORM_AT_TD" style="display: none;">Guardar</button>
                            </p>
                        </div>
                    </div>
                </div>
            </form>



            <!-- Sección de Pozos Relacionados -->
            <div class="main-container">
                <h2 class="detail-section">Pozos Relacionados</h2>
                
                <?php if (isset($related_wells) && !isset($related_wells['error']) && count($related_wells) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="related-wells-table">
                            <thead>
                                <tr>
                                    <th>UWI</th>
                                    <th>Secuencia</th>
                                    <th>Tipo de Hoyo</th>
                                    <th>Hoyo Original</th>
                                    <th>Hoyo Anterior</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($related_wells as $well): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo BASE_PATH; ?>/well?search_well=<?php echo urlencode($well['UWI']); ?>&uwi=<?php echo urlencode($well['UWI']); ?>">
                                                <?php echo htmlspecialchars($well['UWI']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($well['SECUENCIA']); ?></td>
                                        <td><?php echo htmlspecialchars($well['TIPO_DE_HOYO']); ?></td>
                                        <td>
                                            <?php if ($well['HOYO_ORIGINAL'] !== 'N/A'): ?>
                                                <a href="<?php echo BASE_PATH; ?>/well?search_well=<?php echo urlencode($well['HOYO_ORIGINAL']); ?>&uwi=<?php echo urlencode($well['HOYO_ORIGINAL']); ?>">
                                                    <?php echo htmlspecialchars($well['HOYO_ORIGINAL']); ?>
                                                </a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($well['HOYO_ANTERIOR'] !== 'N/A'): ?>
                                                <a href="<?php echo BASE_PATH; ?>/well?search_well=<?php echo urlencode($well['HOYO_ANTERIOR']); ?>&uwi=<?php echo urlencode($well['HOYO_ANTERIOR']); ?>">
                                                    <?php echo htmlspecialchars($well['HOYO_ANTERIOR']); ?>
                                                </a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif (isset($related_wells['error'])): ?>
                    <div class="error">
                        <strong>Error:</strong> <?php echo htmlspecialchars($related_wells['error']); ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray">No se encontraron pozos relacionados.</p>
                <?php endif; ?>
            </div>



        <?php endif; ?>
    </div>
</div>




<!-- Modal de Confirmación -->
<div id="confirmationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar Cambio</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <p><strong>Campo:</strong> <span id="modalFieldName"></span></p>
            <p><strong>Valor anterior:</strong> <span id="modalOldValue"></span></p>
            <p><strong>Nuevo valor:</strong> <span id="modalNewValue"></span></p>
            <p class="warning-text">¿Está seguro de que desea aplicar este cambio?</p>
        </div>
        <div class="modal-footer">
            <button type="button" id="confirmChange" class="btn-confirm">Confirmar</button>
            <button type="button" id="cancelChange" class="btn-cancel">Cancelar</button>
        </div>
    </div>
</div>

<?php require_once 'partials/footer.php'; ?>
<script>
    // Definir BASE_PATH en JavaScript, procesado por PHP
    window.APP_BASE_PATH = "<?php echo BASE_PATH; ?>";
</script>
<?php
    $cache_bust = time(); // Añadir un timestamp para forzar la recarga del script
?>
<script src="<?php echo BASE_PATH; ?>/js/well_edit.js?cb=<?php echo $cache_bust; ?>"></script>

