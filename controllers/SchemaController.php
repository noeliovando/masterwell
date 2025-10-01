<?php
// controllers/SchemaController.php
require_once __DIR__ . '/../models/Schema.php';

class SchemaController {
    public function index() {
        $tableName = 'PDVSA.WELL_HDR';
        $well_hdr_fields = [
            'UWI', 'WELL_NAME', 'SHORT_NAME', 'PLOT_NAME', 'GOVT_ASSIGNED_NO',
            'INITIAL_CLASS', 'CLASS', 'CURRENT_CLASS', 'ORSTATUS', 'CRSTATUS',
            'COUNTRY', 'GEOLOGIC_PROVINCE', 'PROV_ST', 'COUNTY', 'FIELD',
            'BLOCK_ID', 'LOCATION_TABLE', 'SPUD_DATE', 'FIN_DRILL', 'RIGREL',
            'COMP_DATE', 'ONINJECT', 'ONPROD', 'DISCOVER_WELL', 'DEVIATION_FLAG',
            'PLOT_SYMBOL', 'WELL_HDR_TYPE', 'WELL_NUMBER', 'PARENT_UWI',
            'TIE_IN_UWI', 'PRIMARY_SOURCE', 'CONTRACTOR', 'RIG_NO', 'RIG_NAME',
            'HOLE_DIRECTION', 'OPERATOR', 'DISTRICT', 'AGENT', 'LEASE_NO',
            'LEASE_NAME', 'LICENSEE', 'DRILLERS_TD', 'TVD', 'LOG_TD', 'LOG_TVD',
            'PLUGBACK_TD', 'WHIPSTOCK_DEPTH', 'WATER_DEPTH', 'ELEVATION_REF',
            'ELEVATION', 'GROUND_ELEVATION', 'FORM_AT_TD'
        ];

        $schema = Schema::getTableSchema($tableName, $well_hdr_fields);
        $fieldDescriptions = Schema::getFieldDescriptions();

        require_once __DIR__ . '/../views/schema.php';
    }
}
?>
