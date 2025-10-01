document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco más para asegurar que el DOM esté completamente cargado
    setTimeout(function() {
        const wellDetailsForm = document.getElementById('wellDetailsForm');
        const uwiInput = wellDetailsForm ? wellDetailsForm.querySelector('input[name="uwi"]') : null;
        // Usar la variable global definida en views/well.php
        const BASE_PATH = window.APP_BASE_PATH || '';
        
        // Variables para búsqueda asíncrona
        let searchTimeout;
        let searchResults = [];
        let currentSearchTerm = ''; 

        if (!wellDetailsForm || !uwiInput) {
            console.warn('Formulario de detalles del pozo o UWI no encontrado. Esto puede ser normal si no hay un pozo seleccionado.');
            // No retornar aquí, permitir que el resto del código se ejecute para la búsqueda
        }

            const uwi = uwiInput ? uwiInput.value : '';

    // Variables para el modal de confirmación
    const modal = document.getElementById('confirmationModal');
    const modalFieldName = document.getElementById('modalFieldName');
    const modalOldValue = document.getElementById('modalOldValue');
    const modalNewValue = document.getElementById('modalNewValue');
    const confirmButton = document.getElementById('confirmChange');
    const cancelButton = document.getElementById('cancelChange');
    const closeModal = document.querySelector('.close-modal');

    // Variables para almacenar datos del cambio pendiente
    let pendingChange = {
        fieldName: '',
        oldValue: '',
        newValue: '',
        displayText: '',
        parentP: null,
        editableField: null,
        saveButton: null
    };

    // Hide all editable fields and save buttons initially
    document.querySelectorAll('.editable-field').forEach(field => {
        field.style.display = 'none';
    });
    document.querySelectorAll('.save-field-button').forEach(button => {
        button.style.display = 'none';
    });

    // Función para mostrar el modal de confirmación
    function showConfirmationModal(fieldName, oldValue, newValue, displayText, parentP, editableField, saveButton) {
        // Obtener el nombre del campo para mostrar
        const fieldLabel = getFieldLabel(fieldName);
        
        // Actualizar el contenido del modal
        modalFieldName.textContent = fieldLabel;
        modalOldValue.textContent = oldValue || 'N/A';
        modalNewValue.textContent = displayText || newValue || 'N/A';
        
        // Almacenar datos del cambio pendiente
        pendingChange = {
            fieldName: fieldName,
            oldValue: oldValue,
            newValue: newValue,
            displayText: displayText,
            parentP: parentP,
            editableField: editableField,
            saveButton: saveButton
        };
        
        // Mostrar el modal
        modal.style.display = 'block';
    }

    // Función para obtener el nombre del campo
    function getFieldLabel(fieldName) {
        const fieldLabels = {
            'WELL_NAME': 'NOMBRE POZO',
            'SHORT_NAME': 'NOMBRE CORTO',
            'PLOT_NAME': 'NOMBRE EN MAPA',
            'GOVT_ASSIGNED_NO': 'NOMBRE CENTINELA',
            'INITIAL_CLASS': 'CLASIFICACION LAHEE INICIAL',
            'CLASS': 'CLASIFICACION LAHEE FINAL',
            'ORSTATUS': 'ESTADO ORIGINAL',
            'CRSTATUS': 'ESTADO ACTUAL',
            'COUNTRY': 'PAIS',
            'GEOLOGIC_PROVINCE': 'CUENCA/SUBCUENCA',
            'PROV_ST': 'ESTADO/PROVINCIA',
            'COUNTY': 'MUNICIPIO',
            'FIELD': 'CAMPO GEOLOGICO',
            'BLOCK_ID': 'BLOQUE/PARCELA',
            'LOCATION_TABLE': 'LOCALIZACIÓN',
            'WELL_HDR_TYPE': 'TIPO DE HOYO',
            'WELL_NUMBER': 'NÚMERO EN LA SECUENCIA',
            'PARENT_UWI': 'HOYO PRINCIPAL',
            'TIE_IN_UWI': 'HOYO PRECEDENTE',
            'SPUD_DATE': 'FECHA MUDANZA TALADRO',
            'FIN_DRILL': 'FECHA FINAL PERFORACION',
            'RIGREL': 'FECHA SUSPENSION',
            'COMP_DATE': 'FECHA DE COMPLETACION',
            'ONINJECT': 'FECHA INICIO COMO INYECTOR',
            'ONPROD': 'FECHA INICIO COMO PRODUCTOR',
            'DISCOVER_WELL': 'POZO DESCUBRIDOR',
            'DEVIATION_FLAG': 'POZO CON DESVIO',
            'PLOT_SYMBOL': 'SIMBOLO EN MAPA',
            'PRIMARY_SOURCE': 'EMPRESA DE ORIGEN',
            'CONTRACTOR': 'CONTRATISTA',
            'RIG_NO': 'CODIGO DEL TALADRO',
            'RIG_NAME': 'NOMBRE DEL TALADRO',
            'HOLE_DIRECTION': 'DIRECCION DEL HOYO',
            'OPERATOR': 'OPERADORA',
            'DISTRICT': 'DISTRITO',
            'AGENT': 'UE/UP',
            'LEASE_NO': 'LICENCIA/CONVENIO',
            'LEASE_NAME': 'NOMBRE DEL ARRENDAMIENTO',
            'LICENSEE': 'LICENCIATARIO',
            'DRILLERS_TD': 'PROFUNDIDAD DEL TALADRO',
            'TVD': 'PROFUNDIDAD VERTICAL VERDADERA',
            'LOG_TD': 'PROFUNDIDAD DE REGISTRO',
            'LOG_TVD': 'PROFUNDIDAD VERTICAL VERDADERA DE REGISTRO',
            'PLUGBACK_TD': 'PROFUNDIDAD DE TAPÓN',
            'WHIPSTOCK_DEPTH': 'PROFUNDIDAD DE DESVÍO',
            'WATER_DEPTH': 'PROFUNDIDAD DE AGUA',
            'ELEVATION_REF': 'REFERENCIA DE ELEVACIÓN',
            'ELEVATION': 'ELEVACIÓN',
            'GROUND_ELEVATION': 'ELEVACIÓN DEL TERRENO',
            'FORM_AT_TD': 'ÚLTIMA FORMACIÓN ALCANZADA'
        };
        
        return fieldLabels[fieldName] || fieldName;
    }

    // Función para ejecutar el cambio confirmado
    function executeConfirmedChange() {
        const { fieldName, newValue, displayText, parentP, editableField, saveButton } = pendingChange;
        
        // Send AJAX request
        fetch(BASE_PATH + '/well/updateField', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                uwi: uwi,
                field: fieldName,
                value: newValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update read-only display with the correct text
                const readOnlyDisplay = parentP.querySelector(`.read-only-display[data-field="${fieldName}"]`);
                readOnlyDisplay.textContent = displayText;
                readOnlyDisplay.style.display = 'inline-block';

                // Hide editable field and save button
                editableField.style.display = 'none';
                saveButton.style.display = 'none';

                // Show edit button
                const editButton = parentP.querySelector(`.edit-field-button[data-field="${fieldName}"]`);
                editButton.style.display = 'inline-block';

                // Show a non-blocking success message
                const successMessage = document.createElement('span');
                successMessage.textContent = ' ¡Guardado!';
                successMessage.style.color = 'green';
                successMessage.style.fontWeight = 'bold';
                successMessage.classList.add('temp-success-message');
                
                // Insert after the edit button and remove any previous message
                const existingMessage = parentP.querySelector('.temp-success-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                editButton.insertAdjacentElement('afterend', successMessage);

                        // Remove the message after 2 seconds
        setTimeout(() => {
            successMessage.remove();
        }, 2000);

    } else {
        alert('Error al actualizar el campo: ' + data.message);
    }
})
.catch(error => {
    console.error('Error:', error);
    alert('Error de red o servidor al actualizar el campo.');
});
}

    // ===== FUNCIONES DE BÚSQUEDA ASÍNCRONA =====
    
    /**
     * Realizar búsqueda asíncrona de pozos
     * @param {string} term Término de búsqueda
     */
    function performSearch(term) {
        if (!term || term.length < 2) {
            hideSearchResults();
            return;
        }

        // Mostrar indicador de carga
        showLoadingIndicator();

        fetch(`${BASE_PATH}/well/search?term=${encodeURIComponent(term)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                hideLoadingIndicator();
                if (Array.isArray(data)) {
                    searchResults = data;
                    currentSearchTerm = term;
                    displaySearchResults(data);
                } else {
                    console.error('Formato de respuesta inválido:', data);
                    hideSearchResults();
                }
            })
            .catch(error => {
                console.error('Error en búsqueda:', error);
                hideLoadingIndicator();
                hideSearchResults();
            });
    }

    /**
     * Mostrar resultados de búsqueda
     * @param {Array} results Resultados de búsqueda
     */
    function displaySearchResults(results) {
        const searchInput = document.getElementById('search_well');
        if (!searchInput) return;

        // Crear contenedor de resultados si no existe
        let resultsContainer = document.getElementById('search-results-container');
        if (!resultsContainer) {
            resultsContainer = document.createElement('div');
            resultsContainer.id = 'search-results-container';
            resultsContainer.className = 'search-results-container';
            searchInput.parentNode.appendChild(resultsContainer);
        }

        // Limpiar resultados anteriores
        resultsContainer.innerHTML = '';

        if (results.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No se encontraron pozos</div>';
            resultsContainer.style.display = 'block';
            return;
        }

        // Crear lista de resultados
        const resultsList = document.createElement('ul');
        resultsList.className = 'search-results-list';

        results.forEach(well => {
            const listItem = document.createElement('li');
            listItem.className = 'search-result-item';
            listItem.innerHTML = `
                <div class="well-uwi">${well.uwi}</div>
                <div class="well-name">${well.well_name || 'Sin nombre'}</div>
                <div class="well-short">${well.short_name || ''}</div>
            `;
            
            // Evento click para seleccionar pozo
            listItem.addEventListener('click', () => {
                selectWell(well.uwi);
            });
            
            resultsList.appendChild(listItem);
        });

        resultsContainer.appendChild(resultsList);
        resultsContainer.style.display = 'block';
    }

    /**
     * Ocultar resultados de búsqueda
     */
    function hideSearchResults() {
        const resultsContainer = document.getElementById('search-results-container');
        if (resultsContainer) {
            resultsContainer.style.display = 'none';
        }
    }

    /**
     * Mostrar indicador de carga
     */
    function showLoadingIndicator() {
        const searchInput = document.getElementById('search_well');
        if (!searchInput) return;

        let loadingIndicator = document.getElementById('loading-indicator');
        if (!loadingIndicator) {
            loadingIndicator = document.createElement('div');
            loadingIndicator.id = 'loading-indicator';
            loadingIndicator.className = 'loading-indicator';
            loadingIndicator.innerHTML = '<span>Buscando...</span>';
            searchInput.parentNode.appendChild(loadingIndicator);
        }

        loadingIndicator.style.display = 'block';
    }

    /**
     * Ocultar indicador de carga
     */
    function hideLoadingIndicator() {
        const loadingIndicator = document.getElementById('loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    }

    /**
     * Seleccionar pozo y cargar detalles
     * @param {string} uwi UWI del pozo seleccionado
     */
    function selectWell(uwi) {
        // Ocultar resultados de búsqueda
        hideSearchResults();
        
        // Actualizar campo de búsqueda
        const searchInput = document.getElementById('search_well');
        if (searchInput) {
            searchInput.value = uwi;
        }
        
        // Redirigir a la página de detalles del pozo
        window.location.href = `${BASE_PATH}/well?search_well=${encodeURIComponent(uwi)}&uwi=${encodeURIComponent(uwi)}`;
    }

    // ===== EVENT LISTENERS PARA BÚSQUEDA ASÍNCRONA =====
    
    // Event listener para búsqueda con debounce
    const searchInput = document.getElementById('search_well');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.trim();
            
            // Limpiar timeout anterior
            clearTimeout(searchTimeout);
            
            // Ocultar resultados si el campo está vacío
            if (!term || term.length < 2) {
                hideSearchResults();
                return;
            }
            
            // Configurar nuevo timeout para búsqueda
            searchTimeout = setTimeout(() => {
                performSearch(term);
            }, 300); // 300ms de debounce
        });

        // Ocultar resultados al hacer click fuera
        document.addEventListener('click', function(event) {
            const resultsContainer = document.getElementById('search-results-container');
            if (resultsContainer && !searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
                hideSearchResults();
            }
        });

        // Event listener para tecla Enter
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const term = this.value.trim();
                if (term) {
                    // Si hay resultados, seleccionar el primero
                    if (searchResults.length > 0) {
                        selectWell(searchResults[0].uwi);
                    } else {
                        // Realizar búsqueda normal
                        this.form.submit();
                    }
                }
            }
        });
    }

    // Event listeners para el modal (solo si existe)
    if (confirmButton) {
        confirmButton.addEventListener('click', function() {
            executeConfirmedChange();
            if (modal) modal.style.display = 'none';
        });
    }

    if (cancelButton) {
        cancelButton.addEventListener('click', function() {
            if (modal) modal.style.display = 'none';
            // Reset the editable field to its original value
            const { parentP, editableField, oldValue } = pendingChange;
            if (editableField && oldValue) {
                if (editableField.tagName === 'SELECT') {
                    // For select fields, try to find the original option
                    for (let option of editableField.options) {
                        if (option.text === oldValue || option.value === oldValue) {
                            editableField.value = option.value;
                            break;
                        }
                    }
                } else {
                    editableField.value = oldValue;
                }
            }
        });
    }

    if (closeModal) {
        closeModal.addEventListener('click', function() {
            if (modal) modal.style.display = 'none';
        });
    }

    // Cerrar modal al hacer clic fuera de él
    if (modal) {
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }

    // Add event listeners for all edit buttons
    const editButtons = document.querySelectorAll('.edit-field-button');
    if (editButtons.length > 0) {
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const fieldName = this.dataset.field;
                const parentP = this.closest('p');
                if (!parentP) return;

                const readOnlyDisplay = parentP.querySelector(`.read-only-display[data-field="${fieldName}"]`);
                const editableField = parentP.querySelector(`.editable-field[data-field="${fieldName}"]`);
                const saveButton = parentP.querySelector(`.save-field-button[data-field="${fieldName}"]`);

                if (readOnlyDisplay) readOnlyDisplay.style.display = 'none';
                if (editableField) editableField.style.display = 'inline-block';
                this.style.display = 'none';
                if (saveButton) saveButton.style.display = 'inline-block';
            });
        });
    }

    // Add event listeners for all save buttons
    const saveButtons = document.querySelectorAll('.save-field-button');
    if (saveButtons.length > 0) {
        saveButtons.forEach(button => {
            button.addEventListener('click', function() {
                const fieldName = this.dataset.field;
                const parentP = this.closest('p');
                if (!parentP) return;

                const editableField = parentP.querySelector(`.editable-field[data-field="${fieldName}"]`);
                const readOnlyDisplay = parentP.querySelector(`.read-only-display[data-field="${fieldName}"]`);
                
                if (!editableField || !readOnlyDisplay) return;

                const newValue = editableField.value;
                const oldValue = readOnlyDisplay.textContent;

                let displayText = newValue;
                if (editableField.tagName === 'SELECT') {
                    const selectedOption = editableField.options[editableField.selectedIndex];
                    if (selectedOption) {
                        displayText = selectedOption.text;
                    }
                }

                // Mostrar modal de confirmación en lugar de guardar directamente
                showConfirmationModal(fieldName, oldValue, newValue, displayText, parentP, editableField, this);
            });
        });
    }
    }, 100); // Pequeño delay para asegurar que el DOM esté completamente cargado
});
