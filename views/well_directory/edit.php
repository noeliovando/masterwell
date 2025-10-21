<?php
include __DIR__ . '/../../partials/header.php';

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$details = $details ?? [];
$uwi = $details['UWI'] ?? 'N/A';
$cancelUrl = BASE_PATH . '/wells-directory/details/' . urlencode($uwi);
?>

<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/styles/well_directory.css">
<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/styles/well_directory_details.css">

<div class="details-wrapper wd-edit">
  <div class="page-title">
    <div>
      <h1>Editar Pozo</h1>
      <p class="page-subtitle"><span class="kv-label">UWI:</span> <strong class="kv-mono"><?php echo h($uwi); ?></strong></p>
    </div>
    <div class="actions">
      <a class="btn" href="<?php echo h($cancelUrl); ?>">← Cancelar</a>
      <button id="wd-save" class="btn primary">Guardar</button>
    </div>
  </div>

  <div class="tabs" id="edit-tabs">
    <div class="tab-list" role="tablist">
      <button class="tab active" data-tab="ident">Identificación</button>
      <button class="tab" data-tab="adm">Ubicación/Administrativa</button>
      <button class="tab" data-tab="fechas">Fechas</button>
      <button class="tab" data-tab="prof">Profundidades</button>
    </div>

    <form id="wd-edit-form" class="wd-form">
      <input type="hidden" name="uwi" value="<?php echo h($uwi); ?>">

      <!-- Identificación -->
      <section class="tab-panel active" id="tab-ident">
        <div class="wd-grid">
          <div class="wd-field">
            <label>Nombre del pozo</label>
            <input type="text" name="WELL_NAME" value="<?php echo h($details['WELL_NAME'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Nombre corto</label>
            <input type="text" name="SHORT_NAME" value="<?php echo h($details['SHORT_NAME'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Nombre en mapa (Plot)</label>
            <input type="text" name="PLOT_NAME" value="<?php echo h($details['PLOT_NAME'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Código (Gov/centinela)</label>
            <input type="text" name="GOVT_ASSIGNED_NO" value="<?php echo h($details['GOVT_ASSIGNED_NO'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Localización</label>
            <input type="text" name="LOCATION_TABLE" value="<?php echo h($details['LOCATION_TABLE'] ?? ''); ?>">
          </div>
        </div>
      </section>

      <!-- Administrativa -->
      <section class="tab-panel" id="tab-adm">
        <div class="wd-grid">
          <div class="wd-field">
            <label>Operadora (código)</label>
            <input type="text" name="OPERATOR" value="<?php echo h($details['OPERATOR'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Distrito (código)</label>
            <input type="text" name="DISTRICT" value="<?php echo h($details['DISTRICT'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Unidad de Explotación (código)</label>
            <input type="text" name="AGENT" value="<?php echo h($details['AGENT'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Campo (código)</label>
            <input type="text" name="FIELD" value="<?php echo h($details['FIELD'] ?? ''); ?>">
          </div>
        </div>
      </section>

      <!-- Fechas -->
      <section class="tab-panel" id="tab-fechas">
        <div class="wd-grid">
          <div class="wd-field">
            <label>Spud</label>
            <input type="date" name="SPUD_DATE" value="<?php echo h(substr((string)($details['SPUD_DATE'] ?? ''),0,10)); ?>">
          </div>
          <div class="wd-field">
            <label>Fin drill</label>
            <input type="date" name="FIN_DRILL" value="<?php echo h(substr((string)($details['FIN_DRILL'] ?? ''),0,10)); ?>">
          </div>
          <div class="wd-field">
            <label>Rig release</label>
            <input type="date" name="RIGREL" value="<?php echo h(substr((string)($details['RIGREL'] ?? ''),0,10)); ?>">
          </div>
          <div class="wd-field">
            <label>Completion</label>
            <input type="date" name="COMP_DATE" value="<?php echo h(substr((string)($details['COMP_DATE'] ?? ''),0,10)); ?>">
          </div>
          <div class="wd-field">
            <label>On inject</label>
            <input type="date" name="ONINJECT" value="<?php echo h(substr((string)($details['ONINJECT'] ?? ''),0,10)); ?>">
          </div>
          <div class="wd-field">
            <label>On prod</label>
            <input type="date" name="ONPROD" value="<?php echo h(substr((string)($details['ONPROD'] ?? ''),0,10)); ?>">
          </div>
        </div>
      </section>

      <!-- Profundidades -->
      <section class="tab-panel" id="tab-prof">
        <div class="wd-grid">
          <div class="wd-field">
            <label>Drillers TD</label>
            <input type="number" step="0.01" name="DRILLERS_TD" value="<?php echo h($details['DRILLERS_TD'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>TVD</label>
            <input type="number" step="0.01" name="TVD" value="<?php echo h($details['TVD'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Log TD</label>
            <input type="number" step="0.01" name="LOG_TD" value="<?php echo h($details['LOG_TD'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Log TVD</label>
            <input type="number" step="0.01" name="LOG_TVD" value="<?php echo h($details['LOG_TVD'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Plugback TD</label>
            <input type="number" step="0.01" name="PLUGBACK_TD" value="<?php echo h($details['PLUGBACK_TD'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>K.O.P (Whipstock)</label>
            <input type="number" step="0.01" name="WHIPSTOCK_DEPTH" value="<?php echo h($details['WHIPSTOCK_DEPTH'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Water depth</label>
            <input type="number" step="0.01" name="WATER_DEPTH" value="<?php echo h($details['WATER_DEPTH'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Ref. elevación (código)</label>
            <input type="text" name="ELEVATION_REF" value="<?php echo h($details['ELEVATION_REF'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Elevación</label>
            <input type="number" step="0.01" name="ELEVATION" value="<?php echo h($details['ELEVATION'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Elevación terreno</label>
            <input type="number" step="0.01" name="GROUND_ELEVATION" value="<?php echo h($details['GROUND_ELEVATION'] ?? ''); ?>">
          </div>
          <div class="wd-field">
            <label>Formación @ TD</label>
            <input type="text" name="FORM_AT_TD" value="<?php echo h($details['FORM_AT_TD'] ?? ''); ?>">
          </div>
        </div>
      </section>
    </form>
  </div>

  <div id="wd-toast" class="wd-toast" role="status" aria-live="polite" style="display:none;"></div>
</div>

<script src="<?php echo BASE_PATH; ?>/js/well_directory_edit.js"></script>
<?php include __DIR__ . '/../../partials/footer.php'; ?>
