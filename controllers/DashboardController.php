<?php
class DashboardController
{
    public function index()
    {
        // Datos de muestra
        $data = [
            'totalWells' => 4850,
            'activeWells' => 3920,
            'lastUpdate' => date('d/m/Y H:i'),
            
            'regionStats' => [
                'occidente' => ['total' => 1250, 'active' => 980, 'production' => 420000],
                'llanos' => ['total' => 850, 'active' => 720, 'production' => 380000],
                'oriente' => ['total' => 1500, 'active' => 1350, 'production' => 620000],
                'faja' => ['total' => 800, 'active' => 650, 'production' => 550000],
                'costa_afuera' => ['total' => 450, 'active' => 220, 'production' => 180000]
            ],
            
            'mixedStats' => [
                'petrororaima' => ['wells' => 320, 'production' => 150000, 'region' => 'oriente'],
                'petronado' => ['wells' => 280, 'production' => 180000, 'region' => 'oriente'],
                'petromacareo' => ['wells' => 350, 'production' => 200000, 'region' => 'faja'],
                'petromonagas' => ['wells' => 420, 'production' => 220000, 'region' => 'oriente'],
                'petrocedeño' => ['wells' => 380, 'production' => 210000, 'region' => 'faja'],
                'petrourica' => ['wells' => 290, 'production' => 160000, 'region' => 'occidente']
            ],
            
            'chartData' => [
                'regions' => ['Occidente', 'Los Llanos', 'Oriente', 'Faja', 'Costa Afuera'],
                'regionProduction' => [420000, 380000, 620000, 550000, 180000],
                'mixedProduction' => [150000, 180000, 200000, 220000, 210000, 160000]
            ]
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