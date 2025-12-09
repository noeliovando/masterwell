<?php
include __DIR__ . '/../../partials/header.php';

if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
function kv_pairs(array $pairs){
  echo '<ul class="kv-list">';
  foreach($pairs as $label=>$value){
    echo '<li><span class="kv-label">'.h($label).':</span> <span class="kv-value">'.h($value).'</span></li>';
  }
  echo '</ul>';
}

$details = $details ?? [];
$coords  = $coords  ?? [];
$alias   = $alias   ?? [];
$remarks = $remarks ?? [];
$related = $related ?? [];
$uwi     = $uwi     ?? ($details['UWI'] ?? 'N/A');

$backUrl = BASE_PATH . '/well';
$editUrl = BASE_PATH . '/well/edit/' . rawurlencode($uwi); 
$listUrl = BASE_PATH . '/well?uwi=' . urlencode($uwi);
?>

<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/styles/well_directory_details.css?v=2">

<div class="details-wrapper">
  <div class="page-title">
    <div>
      <h1>Detalle del Pozo</h1>
      <p class="page-subtitle"><span class="kv-label">UWI:</span> <strong class="kv-mono"><?php echo h($uwi); ?></strong></p>
    </div>
    <div class="actions">
      <a class="btn" href="<?php echo h($backUrl); ?>">← Volver</a>
      <a class="btn primary" href="<?php echo h($editUrl); ?>">✎ Editar en Well</a>
    </div>
  </div>

  <div class="tabs" id="well-tabs">
    <div class="tab-list" role="tablist" aria-label="Secciones del pozo">
      <button class="tab active" role="tab" data-tab="identificacion" aria-selected="true">Identificación</button>
      <button class="tab" role="tab" data-tab="estado">Estado del hoyo</button>
      <button class="tab" role="tab" data-tab="geopolitica">Ubicación geopolítica</button>
      <button class="tab" role="tab" data-tab="coordenadas">Coordenadas</button>
      <button class="tab" role="tab" data-tab="eventos">Eventos</button>
      <button class="tab" role="tab" data-tab="datos-adicionales">Datos adicionales</button>
      <button class="tab" role="tab" data-tab="secuencia">Secuencia de perforación</button>
      <button class="tab" role="tab" data-tab="perforacion">Datos de perforación</button>
      <button class="tab" role="tab" data-tab="administrativa">Ubicación administrativa</button>
      <button class="tab" role="tab" data-tab="profundidades">Profundidades / Elevaciones</button>
      <button class="tab" role="tab" data-tab="relacionados">Pozos relacionados</button>
      <button class="tab" role="tab" data-tab="observaciones">Observaciones / Alias</button>
    </div>

    <div class="tab-panels">

      <!-- Identificación -->
      <section class="tab-panel active" id="panel-identificacion" role="tabpanel" aria-labelledby="tab-identificacion">
        <h2 class="section-title">Identificación</h2>
        <?php
          kv_pairs([
            'UWI'               => $details['UWI'] ?? $uwi,
            'Nombre del pozo'   => $details['WELL_NAME'] ?? 'N/A',
            'Nombre corto'      => $details['SHORT_NAME'] ?? 'N/A',
            'Nombre en mapa'    => $details['PLOT_NAME'] ?? 'N/A',
            'Nombre Centinela'  => $details['GOVT_ASSIGNED_NO'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Estado del hoyo -->
      <section class="tab-panel" id="panel-estado" role="tabpanel" aria-labelledby="tab-estado">
        <h2 class="section-title">Estado del hoyo</h2>
        <?php
          kv_pairs([
            'Clasificación Lahee (Inicial)' => $details['INITIAL_CLASS_DISPLAY'] ?? ($details['INITIAL_CLASS'] ?? 'N/A'),
            'Clasificación Lahee (Final)'   => $details['CLASS_DESC'] ?? ($details['CLASS'] ?? 'N/A'),
            'Clasificación actual'          => $details['CURRENT_CLASS'] ?? 'N/A',
            'Estado original'               => $details['ORSTATUS_DISPLAY'] ?? ($details['ORSTATUS'] ?? 'N/A'),
            'Estado actual (registro)'      => $details['CRSTATUS'] ?? 'N/A',
            'Tipo de pozo'                  => $details['WELL_TYPE'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Ubicación geopolítica -->
      <section class="tab-panel" id="panel-geopolitica" role="tabpanel" aria-labelledby="tab-geopolitica">
        <h2 class="section-title">Ubicación geopolítica</h2>
        <?php
          kv_pairs([
            'País'                 => $details['COUNTRY'] ?? 'N/A',
            'Cuenca / Subcuenca'   => $details['GEOLOGIC_PROVINCE_DISPLAY'] ?? ($details['GEOLOGIC_PROVINCE'] ?? 'N/A'),
            'Estado / Provincia'   => $details['PROV_ST'] ?? 'N/A',
            'Municipio'            => $details['COUNTY'] ?? 'N/A',
            'Área geográfica'      => $details['GEOGRAPHIC_AREA'] ?? 'N/A',
            'Campo geológico'      => $details['FIELD_DISPLAY'] ?? ($details['FIELD'] ?? 'N/A'),
            'Bloque / Parcela'     => $details['BLOCK_ID'] ?? 'N/A',
            'Localización (tabla)' => $details['LOCATION_TABLE'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Coordenadas (tabla) -->
      <section class="tab-panel" id="panel-coordenadas" role="tabpanel" aria-labelledby="tab-coordenadas">
        <h2 class="section-title">Coordenadas</h2>
        <div class="table-wrap">
          <table class="table">
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
                <td><?php echo h($details['DATUM'] ?? 'N/A'); ?></td>
                <td><?php echo h($coords['CANOA_DATUM'] ?? 'N/A'); ?></td>
                <td><?php echo h($coords['REGVEN_DATUM'] ?? 'N/A'); ?></td>
              </tr>
              <tr>
                <td><strong>Longitud/Este</strong></td>
                <td><?php echo h($coords['LONGITUD'] ?? ($details['LONGITUDE'] ?? 'N/A')); ?></td>
                <td><?php echo h($coords['CANOA_ESTE'] ?? 'N/A'); ?></td>
                <td><?php echo h($coords['REGVEN_ESTE'] ?? 'N/A'); ?></td>
              </tr>
              <tr>
                <td><strong>Latitud/Norte</strong></td>
                <td><?php echo h($coords['LATITUD'] ?? ($details['LATITUDE'] ?? 'N/A')); ?></td>
                <td><?php echo h($coords['CANOA_NORTE'] ?? 'N/A'); ?></td>
                <td><?php echo h($coords['REGVEN_NORTE'] ?? 'N/A'); ?></td>
              </tr>
              <tr>
                <td><strong>Origen</strong></td>
                <td><?php echo 'N/A'; ?></td>
                <td><?php echo h($coords['CANOA_ORIGEN'] ?? 'N/A'); ?></td>
                <td><?php echo h($coords['REGVEN_ORIGEN'] ?? 'N/A'); ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Eventos -->
      <section class="tab-panel" id="panel-eventos" role="tabpanel" aria-labelledby="tab-eventos">
        <h2 class="section-title">Eventos</h2>
        <?php
          kv_pairs([
            'Mudanza Taladro (SPUD)'   => $details['SPUD_DATE'] ?? 'N/A',
            'Inicio perforación'       => $remarks['REMARKS'] ?? ($details['REMARKS'] ?? 'N/A'),
            'Fin perforación'          => $details['FIN_DRILL'] ?? 'N/A',
            'Suspensión (Rig release)' => $details['RIGREL'] ?? 'N/A',
            'Completación'             => $details['COMP_DATE'] ?? 'N/A',
            'Inicio como inyector'     => $details['ONINJECT'] ?? 'N/A',
            'Inicio como productor'    => $details['ONPROD'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Datos adicionales -->
      <section class="tab-panel" id="panel-datos-adicionales" role="tabpanel" aria-labelledby="tab-datos-adicionales">
        <h2 class="section-title">Datos adicionales</h2>
        <?php
          kv_pairs([
            'Pozo descubridor' => $details['DISCOVER_WELL'] ?? 'N/A',
            'Pozo con desvío'  => $details['DEVIATION_FLAG'] ?? 'N/A',
            'Símbolo en mapa'  => $details['PLOT_SYMBOL'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Secuencia de perforación -->
      <section class="tab-panel" id="panel-secuencia" role="tabpanel" aria-labelledby="tab-secuencia">
        <h2 class="section-title">Secuencia de perforación</h2>
        <?php
          kv_pairs([
            'Tipo de hoyo'     => $details['WELL_HDR_TYPE'] ?? 'N/A',
            'Número secuencia' => $details['WELL_NUMBER'] ?? 'N/A',
            'Hoyo principal'   => $details['PARENT_UWI'] ?? 'N/A',
            'Hoyo precedente'  => $details['TIE_IN_UWI'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Datos de perforación -->
      <section class="tab-panel" id="panel-perforacion" role="tabpanel" aria-labelledby="tab-perforacion">
        <h2 class="section-title">Datos de perforación</h2>
        <?php
          kv_pairs([
            'Empresa origen'  => $details['PRIMARY_SOURCE'] ?? 'N/A',
            'Contratista'     => $details['CONTRACTOR'] ?? 'N/A',
            'Código taladro'  => $details['RIG_NO'] ?? 'N/A',
            'Nombre taladro'  => $details['RIG_NAME'] ?? 'N/A',
            'Dirección hoyo'  => $details['HOLE_DIRECTION'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Ubicación administrativa -->
      <section class="tab-panel" id="panel-administrativa" role="tabpanel" aria-labelledby="tab-administrativa">
        <h2 class="section-title">Ubicación administrativa</h2>
        <?php
          kv_pairs([
            'Operadora'           => $details['OPERATOR_DISPLAY'] ?? ($details['OPERATOR'] ?? 'N/A'),
            'Distrito'            => $details['DISTRICT_DISPLAY'] ?? ($details['DISTRICT'] ?? 'N/A'),
            'UE/UP (Agente)'      => $details['AGENT_DISPLAY'] ?? ($details['AGENT'] ?? 'N/A'),
            'Código licencia'     => $details['LEASE_NO_DISPLAY'] ?? ($details['LEASE_NO'] ?? 'N/A'),
            'Nombre licencia'     => $details['LEASE_NAME'] ?? 'N/A',
            'Licenciatario'       => $details['LICENSEE'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Profundidades / Elevaciones -->
      <section class="tab-panel" id="panel-profundidades" role="tabpanel" aria-labelledby="tab-profundidades">
        <h2 class="section-title">Profundidades / Elevaciones</h2>
        <?php
          kv_pairs([
            'Drillers TD'           => $details['DRILLERS_TD'] ?? 'N/A',
            'TVD'                   => $details['TVD'] ?? 'N/A',
            'Log TD'                => $details['LOG_TD'] ?? 'N/A',
            'Log TVD'               => $details['LOG_TVD'] ?? 'N/A',
            'Plugback TD'           => $details['PLUGBACK_TD'] ?? 'N/A',
            'K.O.P.'                => $details['WHIPSTOCK_DEPTH'] ?? 'N/A',
            'Capa de agua'          => $details['WATER_DEPTH'] ?? 'N/A',
            'Ref. elevación'        => $details['ELEVATION_REF'] ?? 'N/A',
            'Elevación'             => $details['ELEVATION'] ?? 'N/A',
            'Elevación del terreno' => $details['GROUND_ELEVATION'] ?? 'N/A',
            'Formación @ TD'        => $details['FORM_AT_TD'] ?? 'N/A',
          ]);
        ?>
      </section>

      <!-- Relacionados -->
      <section class="tab-panel" id="panel-relacionados" role="tabpanel" aria-labelledby="tab-relacionados">
        <h2 class="section-title">Pozos relacionados</h2>
        <?php if(!empty($related) && is_array($related)): ?>
          <div class="table-wrap">
            <table class="table">
              <thead><tr><th>UWI</th><th>Secuencia</th><th>Tipo de hoyo</th><th>Hoyo original</th><th>Hoyo anterior</th></tr></thead>
              <tbody>
                <?php foreach($related as $rw): ?>
                <tr>
                  <td><?php echo h($rw['UWI'] ?? ''); ?></td>
                  <td><?php echo h($rw['SECUENCIA'] ?? ''); ?></td>
                  <td><?php echo h($rw['TIPO_DE_HOYO'] ?? ''); ?></td>
                  <td><?php echo h($rw['HOYO_ORIGINAL'] ?? ''); ?></td>
                  <td><?php echo h($rw['HOYO_ANTERIOR'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p>No hay pozos relacionados disponibles.</p>
        <?php endif; ?>
      </section>

      <!-- Observaciones / Alias -->
      <section class="tab-panel" id="panel-observaciones" role="tabpanel" aria-labelledby="tab-observaciones">
        <h2 class="section-title">Observaciones / Alias</h2>
        <?php
          kv_pairs([
            'Tipo observación' => $remarks['REMARKS_TYPE'] ?? ($details['REMARKS_TYPE'] ?? 'N/A'),
            'Detalle'          => $remarks['REMARKS'] ?? ($details['REMARKS'] ?? 'N/A'),
            'Alias'            => $alias['WELL_ALIAS'] ?? ($details['WELL_ALIAS'] ?? 'N/A'),
          ]);
        ?>
      </section>

    </div>
  </div>
</div>

<script src="<?php echo BASE_PATH; ?>/js/well_directory_details.js?v=2"></script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
