<?php
// views/dashboard.php
// Dashboard con SOLO datos reales de la base de datos Oracle
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


        
        <!-- SECCIÓN 1: ESTADÍSTICAS GENERALES REALES -->
        <div class="stats-section">
            <h2>Resumen General</h2>
            <div class="stats-container">
                <div class="stat-card bg-blue">
                    <h3>Total de Pozos</h3>
                    <div class="stat-value"><?= number_format($totalWells ?? 0) ?></div>

                </div>
                <div class="stat-card bg-green">
                    <h3>Pozos Activos</h3>
                    <div class="stat-value"><?= number_format($activeWells ?? 0) ?></div>

                </div>
                <div class="stat-card bg-purple">
                    <h3>Completados 2025</h3>
                    <div class="stat-value"><?= number_format($completedThisYear ?? 0) ?></div>

                </div>
                <div class="stat-card bg-orange">
                    <h3>Última Actualización</h3>
                    <div class="stat-value" style="font-size: 0.9em;"><?= $lastUpdate ?></div>

                </div>
            </div>
        </div>
        

        
        <!-- SECCIÓN 3: ESTADÍSTICAS POR REGIÓN -->
        <div class="regional-section">
            <h2>Estadísticas por Región</h2>
            <div class="regional-alert">
                <p><strong>📅 Próximamente:</strong> Estos datos se actualizarán con información real de la base de datos.</p>
            </div>
            
            <div class="regional-grid">
                <!-- Occidente -->
                <div class="regional-card">
                    <h3>Occidente</h3>
                    <div class="regional-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos Totales:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Activos:</span>
                            <span class="stat-number text-success">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                    </div>
                </div>
                
                <!-- Los Llanos -->
                <div class="regional-card">
                    <h3>Los Llanos</h3>
                    <div class="regional-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos Totales:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Activos:</span>
                            <span class="stat-number text-success">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                    </div>
                </div>
                
                <!-- Oriente -->
                <div class="regional-card">
                    <h3>Oriente</h3>
                    <div class="regional-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos Totales:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Activos:</span>
                            <span class="stat-number text-success">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                    </div>
                </div>
                
                <!-- Faja -->
                <div class="regional-card">
                    <h3>Faja</h3>
                    <div class="regional-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos Totales:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Activos:</span>
                            <span class="stat-number text-success">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                    </div>
                </div>
                
                <!-- Costa Afuera -->
                <div class="regional-card">
                    <h3>Costa Afuera</h3>
                    <div class="regional-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos Totales:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Activos:</span>
                            <span class="stat-number text-success">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- SECCIÓN 4: EMPRESAS MIXTAS -->
        <div class="mixed-companies-section">
            <h2>Empresas Mixtas</h2>
            <div class="regional-alert">
                <p><strong>📅 Próximamente:</strong> Estos datos se actualizarán con información real de la base de datos.</p>
            </div>
            
            <div class="mixed-companies-grid">
                <!-- Petrororaima -->
                <div class="company-card">
                    <h3>Petrororaima</h3>
                    <div class="company-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Región:</span>
                            <span class="stat-region">Oriente</span>
                        </div>
                    </div>
                </div>
                
                <!-- Petronado -->
                <div class="company-card">
                    <h3>Petronado</h3>
                    <div class="company-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Región:</span>
                            <span class="stat-region">Oriente</span>
                        </div>
                    </div>
                </div>
                
                <!-- Petromacareo -->
                <div class="company-card">
                    <h3>Petromacareo</h3>
                    <div class="company-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Región:</span>
                            <span class="stat-region">Faja</span>
                        </div>
                    </div>
                </div>
                
                <!-- Petromonagas -->
                <div class="company-card">
                    <h3>Petromonagas</h3>
                    <div class="company-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Región:</span>
                            <span class="stat-region">Oriente</span>
                        </div>
                    </div>
                </div>
                
                <!-- Petrocedeño -->
                <div class="company-card">
                    <h3>Petrocedeño</h3>
                    <div class="company-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Región:</span>
                            <span class="stat-region">Faja</span>
                        </div>
                    </div>
                </div>
                
                <!-- Petrourica -->
                <div class="company-card">
                    <h3>Petrourica</h3>
                    <div class="company-stats">
                        <div class="stat-item">
                            <span class="stat-label">Pozos:</span>
                            <span class="stat-number">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Producción:</span>
                            <span class="stat-number">0 bpd</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Región:</span>
                            <span class="stat-region">Occidente</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

                                <!-- INFORMACIÓN TÉCNICA -->
        <div class="info-section-corporate">
            <h2>Información Técnica del Sistema</h2>
            <div class="info-grid-corporate">
                <div class="info-card-corporate">
                    <h4>Instancia de Base de Datos</h4>
                    <p><?= $_SESSION['db_credentials']['db_instance'] ?? 'No disponible' ?></p>
                </div>
                <div class="info-card-corporate">
                    <h4>Usuario Conectado</h4>
                    <p><?= $_SESSION['db_credentials']['user'] ?? 'No disponible' ?></p>
                </div>
                <div class="info-card-corporate">
                    <h4>Hora de Carga</h4>
                    <p><?= date('d/m/Y H:i:s') ?></p>
                </div>
                <div class="info-card-corporate">
                    <h4>Seguridad</h4>
                    <p>Rol WELL_ADMIN Verificado</p>
                </div>

            </div>
        </div>

    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>


</body>
</html>