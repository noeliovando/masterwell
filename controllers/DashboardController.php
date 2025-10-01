<?php
class DashboardController
{
    public function index()
    {
        // Incluir el modelo Well para acceder a las funciones de datos reales
        require_once __DIR__ . '/../models/Well.php';
        
        // Obtener SOLO las estadísticas generales que necesitamos
        $generalStats = Well::getDashboardGeneralStats();
        
        // Formatear la fecha de última actualización si viene de BD
        if (isset($generalStats['lastUpdate']) && $generalStats['lastUpdate']) {
            if ($generalStats['lastUpdate'] instanceof DateTime) {
                $generalStats['lastUpdate'] = $generalStats['lastUpdate']->format('d/m/Y H:i');
            } elseif (is_string($generalStats['lastUpdate'])) {
                // Si viene como string de Oracle, intentar convertir
                $dateTime = DateTime::createFromFormat('d-M-y H.i.s.u A', $generalStats['lastUpdate']);
                if ($dateTime) {
                    $generalStats['lastUpdate'] = $dateTime->format('d/m/Y H:i');
                }
            }
        } else {
            $generalStats['lastUpdate'] = date('d/m/Y H:i');
        }
        

        
        $data = [
            // SOLO LAS 4 ESTADÍSTICAS DE LAS TARJETAS
            'totalWells' => $generalStats['totalWells'] ?? 0,
            'activeWells' => $generalStats['activeWells'] ?? 0,
            'completedThisYear' => $generalStats['completedThisYear'] ?? 0,
            'lastUpdate' => $generalStats['lastUpdate']
        ];

        // Cargar la vista directamente
        $this->loadView('dashboard', $data);
    }
    


    /**
     * Método para cargar vistas
     */
    protected function loadView($viewName, $data = [])
    {
        // Extraer variables para la vista
        extract($data);
        
        // Ruta al archivo de vista
        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';
        
        // Verificar si existe la vista
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Error: La vista {$viewName} no existe");
        }
    }
}