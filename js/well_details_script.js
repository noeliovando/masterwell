document.addEventListener('DOMContentLoaded', () => {
  const tabs = document.querySelectorAll('.tabs .tab');
  const panels = document.querySelectorAll('.tab-panel');

  function activate(id) {
    tabs.forEach(t => {
      const is = t.dataset.tab === id;
      t.classList.toggle('active', is);
      t.setAttribute('aria-selected', is ? 'true' : 'false');
      const panel = document.getElementById('panel-' + t.dataset.tab);
      if (panel) panel.classList.toggle('active', is);
    });
  }

  tabs.forEach(tab => {
    tab.addEventListener('click', () => activate(tab.dataset.tab));
  });

  // primera activa si no hay ninguna
  const first = document.querySelector('.tabs .tab');
  if (first) activate(first.dataset.tab);
});
