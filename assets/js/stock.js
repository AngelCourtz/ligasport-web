// Consulta rápida de stock (RF4): agrupa por modelo y color y muestra el stock de cada talla.
(function () {
  const q = document.getElementById('q');
  const talla = document.getElementById('talla');
  const res = document.getElementById('res');
  const timer = document.getElementById('timer');
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const soles = (n) => 'S/ ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  let t;

  async function buscar() {
    const t0 = performance.now();
    const r = await fetch(`api/stock.php?q=${encodeURIComponent(q.value)}&talla=${encodeURIComponent(talla.value)}`);
    if (r.status === 401) { location.href = 'login.php'; return; }
    const data = await r.json();
    const ms = Math.round(performance.now() - t0);
    const grupos = new Map();
    for (const p of data.productos) {
      const k = `${p.marca}|${p.modelo}|${p.color}`;
      if (!grupos.has(k)) grupos.set(k, { ...p, tallas: [] });
      grupos.get(k).tallas.push(p);
    }
    timer.textContent = `${data.productos.length} resultado(s) · consulta resuelta en ${ms} ms`;
    if (!grupos.size) { res.innerHTML = '<section class="card muted">No se encontraron productos con ese criterio.</section>'; return; }
    res.innerHTML = [...grupos.values()].map((g) => {
      const total = g.tallas.reduce((s, p) => s + Number(p.stock_actual), 0);
      const sizes = g.tallas.map((p) => {
        const s = Number(p.stock_actual);
        const cls = s === 0 ? 'out' : (s <= data.stock_bajo ? 'low' : '');
        return `<div class="size ${cls}"><b>${esc(p.talla)}</b><small>${s} ud.</small></div>`;
      }).join('');
      return `<section class="card"><div class="row" style="align-items:center"><div><h2 style="margin:0">${esc(g.marca)} ${esc(g.modelo)}</h2>
        <span class="muted">${esc(g.color)} · ${soles(g.precio)}</span></div><span class="spacer"></span>
        <span class="badge ${total ? 'ok' : 'out'}">${total ? total + ' unidades' : 'Agotado'}</span></div>
        <div class="sizes" style="margin-top:12px">${sizes}</div></section>`;
    }).join('');
  }
  q.addEventListener('input', () => { clearTimeout(t); t = setTimeout(buscar, 180); });
  talla.addEventListener('change', buscar);
  buscar();
})();
