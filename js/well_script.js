document.addEventListener('DOMContentLoaded', function() {
    const wellListItems = document.querySelectorAll('.well-list li');
    const detailsContent = document.getElementById('well-details-content');
    const basePath = document.body.dataset.basePath || ''; // Obtener BASE_PATH si se define en el body

    wellListItems.forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const uwi = this.dataset.uwi;
            
            // Marcar el elemento seleccionado
            wellListItems.forEach(li => li.classList.remove('active'));
            this.classList.add('active');

            // Mostrar estado de carga
            detailsContent.innerHTML = '<p>Cargando detalles...</p>';

            // Realizar la solicitud AJAX
            fetch(`${basePath}/api/well/details/${encodeURIComponent(uwi)}`)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        renderWellDetails(result.data);
                    } else {
                        detailsContent.innerHTML = `<p class="error">Error: ${result.message}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Error en la solicitud AJAX:', error);
                    detailsContent.innerHTML = `<p class="error">Error al cargar los detalles del pozo.</p>`;
                });
        });
    });

    function renderWellDetails(details) {
        let html = `
            <div class="detail-grid">
                <div class="detail-section">
                    <h2>Estado del Hoyo</h2>
                    <p><strong>CLASIFICACION LAHEE INICIAL:</strong> ${details.INITIAL_CLASS || 'N/A'}</p>
                    <p><strong>CLASIFICACION LAHEE FINAL:</strong> ${details.CLASS || 'N/A'}</p>
                    <p><strong>CLASIFICACION ACTUAL:</strong> ${details.CURRENT_CLASS || 'N/A'}</p>
                    <p><strong>ESTADO ORIGINAL:</strong> ${details.ORSTATUS || 'N/A'}</p>
                    <p><strong>ESTADO ACTUAL:</strong> ${details.CRSTATUS || 'N/A'}</p>
                </div>

                <div class="detail-section">
                    <h2>Ubicación Geográfica</h2>
                    <p><strong>PAIS:</strong> ${details.COUNTRY || 'N/A'}</p>
                    <p><strong>CUENCA/SUBCUENCA:</strong> ${details.CUENCA || 'N/A'}</p>
                    <p><strong>ESTADO/PROVINCIA:</strong> ${details.PROV_ST || 'N/A'}</p>
                    <p><strong>MUNICIPIO:</strong> ${details.MUNICIPIO || 'N/A'}</p>
                    <p><strong>CAMPO GEOLOGICO:</strong> ${details.FIELD || 'N/A'}</p>
                    <p><strong>BLOQUE/PARCELA:</strong> ${details.BLOCK_ID || 'N/A'}</p>
                    <p><strong>LOCALIZACIÓN:</strong> ${details.LOCATION_TABLE || 'N/A'}</p>
                </div>

                <div class="detail-section">
                    <h2>Eventos</h2>
                    <p><strong>FECHA MUDANZA TALADRO:</strong> ${details.SPUD_DATE || 'N/A'}</p>
                    <p><strong>FECHA INICIO PERFORACION:</strong> ${details.INICIO_PERF || 'N/A'}</p>
                    <p><strong>FECHA FINAL PERFORACION:</strong> ${details.FIN_DRILL || 'N/A'}</p>
                    <p><strong>FECHA SUSPENSION:</strong> ${details.RIGREL || 'N/A'}</p>
                    <p><strong>FECHA DE COMPLETACION:</strong> ${details.COMP_DATE || 'N/A'}</p>
                    <p><strong>FECHA INICIO COMO INYECTOR:</strong> ${details.ONINJECT || 'N/A'}</p>
                    <p><strong>FECHA INICIO COMO PRODUCTOR:</strong> ${details.ONPROD || 'N/A'}</p>
                </div>

                <div class="detail-section">
                    <h2>Datos Adicionales</h2>
                    <p><strong>POZO DESCUBRIDOR:</strong> ${details.DISCOVER_WELL || 'N/A'}</p>
                    <p><strong>POZO CON DESVIO:</strong> ${details.DEVIATION_FLAG || 'N/A'}</p>
                    <p><strong>SIMBOLO EN MAPA:</strong> ${details.PLOT_SYMBOL || 'N/A'}</p>
                </div>

                <div class="detail-section">
                    <h2>Secuencia de Perforación</h2>
                    <p><strong>TIPO DE HOYO:</strong> ${details.WELL_HDR_TYPE || 'N/A'}</p>
                    <p><strong>NÚMERO EN LA SECUENCIA:</strong> ${details.WELL_NUMBER || 'N/A'}</p>
                    <p><strong>HOYO PRINCIPAL:</strong> ${details.PARENT_UWI || 'N/A'}</p>
                    <p><strong>HOYO PRECEDENTE:</strong> ${details.TIE_IN_UWI || 'N/A'}</p>
                </div>

                <div class="detail-section">
                    <h2>Datos de Perforación</h2>
                    <p><strong>EMPRESA ORIGEN:</strong> ${details.PRIMARY_SOURCE || 'N/A'}</p>
                    <p><strong>CONTRATISTA:</strong> ${details.CONTRACTOR || 'N/A'}</p>
                    <p><strong>CODIGO DEL TALADRO:</strong> ${details.RIG_NO || 'N/A'}</p>
                    <p><strong>NOMBRE DEL TALADRO:</strong> ${details.RIG_NAME || 'N/A'}</p>
                    <p><strong>DIRECCION DEL HOYO:</strong> ${details.HOLE_DIRECTION || 'N/A'}</p>
                </div>

                <div class="detail-section">
                    <h2>Ubicación Administrativa</h2>
                    <p><strong>OPERADORA:</strong> ${details.OPERATOR || 'N/A'}</p>
                    <p><strong>DISTRITO:</strong> ${details.DISTRICT || 'N/A'}</p>
                    <p><strong>UE/UP:</strong> ${details.AGENT || 'N/A'}</p>
                    <p><strong>CODIGO LICENCIA/CONVENIO:</strong> ${details.LEASE_NO || 'N/A'}</p>
                    <p><strong>NOMBRE LICENCIA/CONVENIO:</strong> ${details.LEASE_NAME || 'N/A'}</p>
                    <p><strong>LICENCIA:</strong> ${details.LICENSEE || 'N/A'}</p>
                </div>

                <div class="detail-section">
                    <h2>Profundidades</h2>
                    <p><strong>PROFUNDIDAD TOTAL:</strong> ${details.DRILLERS_TD || 'N/A'}</p>
                    <p><strong>PROFUNDIDAD TOTAL (VERTICALIZADA):</strong> ${details.TVD || 'N/A'}</p>
                    <p><strong>PROFUNDIDAD DE REGISTRO:</strong> ${details.LOG_TD || 'N/A'}</p>
                    <p><strong>PROFUNDIDAD DE REGISTRO (VERTICALIZADA):</strong> ${details.LOG_TVD || 'N/A'}</p>
                    <p><strong>PROFUNDIDAD DEL TAPON:</strong> ${details.PLUGBACK_TD || 'N/A'}</p>
                    <p><strong>K.O.P.:</strong> ${details.WHIPSTOCK_DEPTH || 'N/A'}</p>
                    <p><strong>PROFUNDIDAD CAPA DE AGUA:</strong> ${details.WATER_DEPTH || 'N/A'}</p>
                    <p><strong>REFERENCIA DE ELEVACION:</strong> ${details.ELEVATION_REF || 'N/A'}</p>
                    <p><strong>VALOR DE ELEVACION:</strong> ${details.ELEVATION || 'N/A'}</p>
                    <p><strong>ELEVACION DEL TERRENO:</strong> ${details.GROUND_ELEVATION || 'N/A'}</p>
                    <p><strong>ULTIMA FORMACION ALCANZADA:</strong> ${details.FORM_AT_TD || 'N/A'}</p>
                </div>
            </div>
        `;
        detailsContent.innerHTML = html;
    }
});
