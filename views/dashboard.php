<?php
// Datos de muestra - normalmente vendrían de una base de datos
$dashboardData = [
    // Estadísticas generales
    'totalWells' => 4850,
    'activeWells' => 3920,
    'lastUpdate' => date('d/m/Y H:i'),
    
    // Por región
    'regions' => [
        'Occidente' => ['total' => 1250, 'active' => 980, 'production' => 420000],
        'Los Llanos' => ['total' => 850, 'active' => 720, 'production' => 380000],
        'Oriente' => ['total' => 1500, 'active' => 1350, 'production' => 620000],
        'Faja' => ['total' => 800, 'active' => 650, 'production' => 550000],
        'Costa Afuera' => ['total' => 450, 'active' => 220, 'production' => 180000]
    ],
    
    // Empresas mixtas
    'mixedCompanies' => [
        'Petrororaima' => ['wells' => 320, 'production' => 150000, 'region' => 'Oriente'],
        'Petronado' => ['wells' => 280, 'production' => 180000, 'region' => 'Oriente'],
        'Petromacareo' => ['wells' => 350, 'production' => 200000, 'region' => 'Faja'],
        'Petromonagas' => ['wells' => 420, 'production' => 220000, 'region' => 'Oriente'],
        'Petrocedeño' => ['wells' => 380, 'production' => 210000, 'region' => 'Faja'],
        'Petrourica' => ['wells' => 290, 'production' => 160000, 'region' => 'Occidente']
    ],
    
    // Datos para gráficos
    'chartData' => [
        'regions' => ['Occidente', 'Los Llanos', 'Oriente', 'Faja', 'Costa Afuera'],
        'regionProduction' => [420000, 380000, 620000, 550000, 180000],
        'mixedProduction' => [150000, 180000, 200000, 220000, 210000, 160000]
    ]
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MasterWell</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style.css">
</head>
<body>

        <!-- Incluir header -->
        <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="dashboard-container">
        <h1>Dashboard de Gestión de Pozos</h1>
        
        <!-- Sección de Estadísticas Generales -->
        <div class="stats-section">
            <h2>Resumen General</h2>
            <div class="stats-container">
                <div class="stat-card bg-blue">
                    <h3>Total de Pozos</h3>
                    <div class="stat-value"><?= number_format($dashboardData['totalWells']) ?></div>
                </div>
                <div class="stat-card bg-green">
                    <h3>Pozos Activos</h3>
                    <div class="stat-value"><?= number_format($dashboardData['activeWells']) ?></div>
                </div>
                <div class="stat-card bg-orange">
                    <h3>Última Actualización</h3>
                    <div class="stat-value"><?= $dashboardData['lastUpdate'] ?></div>
                </div>
            </div>
        </div>
        
        <!-- Sección por Regiones -->
        <div class="region-section">
            <h2>Estadísticas por Región</h2>
            <div class="region-grid">
                <?php foreach ($dashboardData['regions'] as $name => $data): ?>
                    <div class="region-card card">
                        <h3><?= $name ?></h3>
                        <div class="region-stats">
                            <div class="stat-item">
                                <span class="stat-label">Pozos Totales:</span>
                                <span class="stat-value"><?= number_format($data['total']) ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Activos:</span>
                                <span class="stat-value text-success"><?= number_format($data['active']) ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Producción:</span>
                                <span class="stat-value"><?= number_format($data['production']) ?> bpd</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Sección de Empresas Mixtas -->
        <div class="mixed-section">
            <h2>Empresas Mixtas</h2>
            <div class="mixed-grid">
                <?php foreach ($dashboardData['mixedCompanies'] as $name => $data): ?>
                    <div class="mixed-card card">
                        <h3><?= $name ?></h3>
                        <div class="mixed-stats">
                            <div class="stat-item">
                                <span class="stat-label">Pozos:</span>
                                <span class="stat-value"><?= number_format($data['wells']) ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Producción:</span>
                                <span class="stat-value"><?= number_format($data['production']) ?> bpd</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Región:</span>
                                <span class="stat-value"><?= $data['region'] ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Sección de Gráficos -->
        <div class="chart-section">
            <div class="chart-row">
                <div class="chart-container card">
                    <h2>Tipos de Datos</h2>
                    <canvas id="regionProductionChart" height="180"></canvas>
                </div>
                <div class="chart-container card">
                    <h2>Producción por Región</h2>
                    <canvas id="mixedProductionChart" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/dashboard-charts.js"></script>
    <script>
    // Pasar datos PHP a JavaScript
    const chartData = <?= json_encode($dashboardData['chartData']) ?>;
    </script>
</body>
</html>