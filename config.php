<?php
// config.php
// Configuración de la base de datos
$db_instances = [
    'PDVSA - Exploración y Producción' => 'oci:dbname=162.122.168.244:1521/FINDPDV',
    'CVP - Empresas Mixtas' => 'oci:dbname=162.122.168.244:1521/CVP',
    'Entrenamiento' => 'oci:dbname=162.122.168.244:1521/FINDTEST',
    //'FINDREG8' => 'oci:dbname=167.134.183.240:1521/FINDREG8',
    
];

// Mapeo de esquemas por instancia de base de datos
$db_schemas = [
    'PDVSA - Exploración y Producción' => [
        'main_schema' => 'PDVSA',
        'codes_schema' => 'CODES',
        'available_tables' => ['WELL_HDR', 'NODES_SECOND', 'WELL_REMARKS', 'WELL_ALIAS', 'FIELD_HDR', 'BUSINESS_ASSOC', 'LEASE']
    ],
    'CVP - Empresas Mixtas' => [
        'main_schema' => 'FINDCVP',
        'codes_schema' => 'CODES',
        'available_tables' => ['WELL_HDR', 'NODES_SECOND'] // Solo las tablas que existen en CVP
        // Nota: Las tablas CODES están disponibles en ambas instancias
    ],
    'Entrenamiento' => [
        'main_schema' => 'PDVSA',
        'codes_schema' => 'CODES',
        'available_tables' => ['WELL_HDR', 'NODES_SECOND', 'WELL_REMARKS', 'WELL_ALIAS', 'FIELD_HDR', 'BUSINESS_ASSOC', 'LEASE']
    ]
];

define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);

// SISTEMA DE ROLES - WELL_ADMIN
// Solo usuarios con rol WELL_ADMIN pueden acceder a la aplicación
// Consulta utilizada: SELECT granted_role FROM user_role_privs WHERE username = UPPER(:username) AND granted_role = 'WELL_ADMIN'
// Implementado el: 06 de Agosto de 2025
?>
