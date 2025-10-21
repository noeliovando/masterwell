// public/js/well_directory_edit.js
document.addEventListener('DOMContentLoaded', () => {
  // Tabs
  const tabs = document.querySelectorAll('#edit-tabs .tab');
  const panels = document.querySelectorAll('.tab-panel');
  tabs.forEach(t => {
    t.addEventListener('click', () => {
      const id = t.dataset.tab;
      tabs.forEach(x => x.classList.remove('active'));
      panels.forEach(p => p.classList.remove('active'));
      t.classList.add('active');
      const panel = document.getElementById('tab-' + id);
      if (panel) panel.classList.add('active');
    });
  });

  // Save
  const form = document.getElementById('wd-edit-form');
  const btnSave = document.getElementById('wd-save');
  const toast = document.getElementById('wd-toast');

  function showToast(msg, ok = true) {
    toast.textContent = msg;
    toast.className = 'wd-toast ' + (ok ? 'ok' : 'err');
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
  }

  btnSave.addEventListener('click', async () => {
    try {
      btnSave.disabled = true;

      // Serializa como JSON
      const fd = new FormData(form);
      const payload = {};
      fd.forEach((v,k) => payload[k] = v);

      const resp = await fetch(`${window.APP_BASE_PATH || ''}/wells-directory/update`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await resp.json();
      if (data.ok) {
        showToast('Guardado con éxito', true);
        if (data.redirect) {
          setTimeout(() => { window.location.href = data.redirect; }, 800);
        }
      } else {
        showToast(data.message || 'No se pudo guardar.', false);
      }
    } catch (e) {
      console.error(e);
      showToast('Error de red o servidor.', false);
    } finally {
      btnSave.disabled = false;
    }
  });
});
