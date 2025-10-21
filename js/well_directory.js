// js/well_directory.js
document.addEventListener('DOMContentLoaded', () => {
  // Limpiar filtros
  const clearBtn = document.getElementById('wd-clear');
  if (clearBtn) {
    clearBtn.addEventListener('click', () => {
      const form = document.getElementById('wd-filter-form');
      if (!form) return;
      [...form.querySelectorAll('input[type=text], input[type=date]')].forEach(i => i.value = '');
      form.submit();
    });
  }

  // Enviar al presionar Enter en inputs de filtro
  const filterForm = document.getElementById('wd-filter-form');
  if (filterForm) {
    filterForm.addEventListener('keydown', (ev) => {
      if (ev.key === 'Enter') {
        // evita Enter en fechas que abren calendar
        if (ev.target && (ev.target.tagName === 'INPUT')) {
          ev.preventDefault();
          filterForm.submit();
        }
      }
    });
  }

  // Sidebar collapse
  const collapse = document.getElementById('wd-collapse');
  const sidebar  = document.querySelector('.wd-sidebar');
  if (collapse && sidebar) {
    collapse.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
    });
  }
});
