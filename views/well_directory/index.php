<?php
// Ruta a la raíz del proyecto: /var/www/html/masterwell01
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__, 2));
}

// Header global desde /partials/header.php en la raíz del proyecto
include PROJECT_ROOT . '/partials/header.php';

// --- Helpers seguros (evita redeclaración) ---
if (!function_exists('h')) {
  function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// --- Variables que debe pasar el controlador ---
// $rows, $total, $currentPage, $totalPages, $perPage, $sort, $dir
$rows        = $rows        ?? [];
$total       = isset($total) ? (int)$total : 0;
$currentPage = isset($currentPage) ? (int)$currentPage : (int)($_GET['page'] ?? 1);
$totalPages  = isset($totalPages) ? (int)$totalPages : 1;
$perPage     = isset($perPage) ? (int)$perPage : 25;
$sort        = $sort ?? 'SPUD_DATE';
$dir         = $dir  ?? 'DESC';

// Preservar filtros actuales
$filters = [
  'uwi' => $_GET['uwi'] ?? '',
  'location' => $_GET['location'] ?? '',
  'exploitation_unit' => $_GET['exploitation_unit'] ?? '',
  'operator' => $_GET['operator'] ?? '',
  'field' => $_GET['field'] ?? '',
  'district' => $_GET['district'] ?? '',
  'gov' => $_GET['gov'] ?? '',
  'spud_from' => $_GET['spud_from'] ?? '',
  'spud_to' => $_GET['spud_to'] ?? '',
];

// Función para construir URLs preservando filtros + sort + dir + page
$baseUrl = BASE_PATH . '/wells-directory';
$makeUrl = function(array $extra = []) use ($baseUrl) {
  $qs = $_GET;
  foreach ($extra as $k => $v) {
    if ($v === null) { unset($qs[$k]); } else { $qs[$k] = $v; }
  }
  return $baseUrl . '?' . http_build_query($qs);
};
?>

<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/styles/well_directory.css?v=2">

<div class="wd-main">
  <div class="wd-page-head">
    <div>
      <h1>Directorio de Pozos</h1>
      <div class="wd-head-meta">
        <span>Resultados: <?php echo (int)$total; ?></span>
      </div>
    </div>
    <div>
      <a class="wd-btn wd-btn-clear" href="<?php echo h($baseUrl); ?>">Limpiar filtros</a>
    </div>
  </div>

  <!-- ===== Formulario de búsqueda (GET) ===== -->
  <form method="get" action="<?php echo h($baseUrl); ?>" class="wd-search">
    <div class="wd-grid">
      <div>
        <label>UWI</label>
        <input type="text" name="uwi" value="<?php echo h($filters['uwi']); ?>" placeholder="Ej: 007WHZPZ0060 2">
      </div>
      <div>
        <label>Localización</label>
        <input type="text" name="location" value="<?php echo h($filters['location']); ?>" placeholder="Tabla de loc.">
      </div>
      <div>
        <label>Unidad de Explotación</label>
        <input type="text" name="exploitation_unit" value="<?php echo h($filters['exploitation_unit']); ?>" placeholder="AGENT">
      </div>
      <div>
        <label>Operadora</label>
        <input type="text" name="operator" value="<?php echo h($filters['operator']); ?>" placeholder="Operadora">
      </div>

      <div>
        <label>Campo</label>
        <input type="text" name="field" value="<?php echo h($filters['field']); ?>" placeholder="FIELD">
      </div>
      <div>
        <label>Distrito</label>
        <input type="text" name="district" value="<?php echo h($filters['district']); ?>" placeholder="DISTRICT">
      </div>
      <div>
        <label>Gov. Assign No.</label>
        <input type="text" name="gov" value="<?php echo h($filters['gov']); ?>" placeholder="GOVT_ASSIGNED_NO">
      </div>
      <div>
        <label>Spud (rango)</label>
        <div style="display:flex; gap:8px;">
          <input type="date" name="spud_from" value="<?php echo h($filters['spud_from']); ?>">
          <input type="date" name="spud_to"   value="<?php echo h($filters['spud_to']); ?>">
        </div>
      </div>
    </div>
    <div class="wd-actions">
      <div></div>
      <div>
        <button type="submit" class="wd-btn wd-btn-search">Buscar</button>
        <a class="wd-btn wd-btn-clear" href="<?php echo h($baseUrl); ?>">Limpiar</a>
      </div>
    </div>
  </form>

  <!-- ===== Grid ===== -->
  <div class="wd-table-wrap">
    <table class="wd-table">
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
          <th style="min-width:120px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($rows)): ?>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo h($r['UWI'] ?? ''); ?></td>
            <td><?php echo h($r['WELL_NAME'] ?? ''); ?></td>
            <td><?php echo h($r['GOVT_ASSIGNED_NO'] ?? ''); ?></td>
            <td><?php echo h($r['OPERATOR'] ?? ''); ?></td>
            <td>
              <?php
                $code = $r['FIELD'] ?? '';
                $name = $r['FIELD_NAME'] ?? $code;
                echo h($code && $name && $name !== $code ? "{$code} – {$name}" : $name);
              ?>
            </td>
            <td><?php echo h($r['DISTRICT'] ?? ''); ?></td>
            <td><?php echo h($r['LOCATION_TABLE'] ?? ''); ?></td>
            <td><?php echo h($r['AGENT'] ?? ''); ?></td>
            <td><?php echo h($r['SPUD_DATE'] ?? ''); ?></td>
            <td>
              <a class="btn" href="<?php echo h(BASE_PATH . '/wells-directory/details/' . urlencode($r['UWI'] ?? '')); ?>">Ver</a>
              <a class="btn" href="<?php echo h(BASE_PATH . '/wells-directory/edit/' . urlencode($r['UWI'] ?? '')); ?>">Editar</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="10" style="text-align:center;color:#64748b;">Sin resultados</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php
    $window = 2;
    $start = max(1, $currentPage - $window);
    $end   = min($totalPages, $currentPage + $window);
  ?>
  <div class="wd-pager">
    <a class="wd-pager-arrow<?php echo $currentPage <= 1 ? ' disabled' : ''; ?>"
       href="<?php echo h($makeUrl(['page' => max(1, $currentPage - 1)])); ?>"
       aria-label="Anterior">&laquo;</a>

    <?php if ($start > 1): ?>
      <a class="wd-pager-num" href="<?php echo h($makeUrl(['page'=>1])); ?>">1</a>
      <?php if ($start > 2): ?><span class="wd-pager-ellipsis">…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($i = $start; $i <= $end; $i++): ?>
      <?php if ($i == $currentPage): ?>
        <span class="wd-pager-num active"><?php echo $i; ?></span>
      <?php else: ?>
        <a class="wd-pager-num" href="<?php echo h($makeUrl(['page'=>$i])); ?>"><?php echo $i; ?></a>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if ($end < $totalPages): ?>
      <?php if ($end < $totalPages - 1): ?><span class="wd-pager-ellipsis">…</span><?php endif; ?>
      <a class="wd-pager-num" href="<?php echo h($makeUrl(['page'=>$totalPages])); ?>"><?php echo $totalPages; ?></a>
    <?php endif; ?>

    <a class="wd-pager-arrow<?php echo $currentPage >= $totalPages ? ' disabled' : ''; ?>"
       href="<?php echo h($makeUrl(['page' => min($totalPages, $currentPage + 1)])); ?>"
       aria-label="Siguiente">&raquo;</a>
  </div>
</div>

<?php
// Footer global
include PROJECT_ROOT . '/partials/footer.php';
