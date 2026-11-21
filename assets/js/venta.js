// Registro de venta (RF3): carrito en el navegador; el servidor valida el stock al confirmar.
(function () {
  const buscar = document.getElementById('buscar');
  const talla = document.getElementById('talla');
  const resultados = document.getElementById('resultados');
  const tbody = document.querySelector('#carrito tbody');
  const totalEl = document.getElementById('total');
  const btn = document.getElementById('confirmar');
  const itemsInput = document.getElementById('items');
  const carrito = new Map(); // id -> {producto, cantidad}
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const soles = (n) => 'S/ ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  let t;

  async function cargar() {
    const r = await fetch(`api/stock.php?q=${encodeURIComponent(buscar.value)}&talla=${encodeURIComponent(talla.value)}`);
    if (r.status === 401) { location.href = 'login.php'; return; }
    const data = await r.json();
    resultados.innerHTML = '';
    for (const p of data.productos) {
      const s = Number(p.stock_actual);
      const div = document.createElement('div');
      if (s === 0) div.className = 'disabled';
      div.innerHTML = `<span>${esc(p.marca)} ${esc(p.modelo)} <span class="muted">${esc(p.color)} · T${esc(p.talla)}</span></span>
        <span>${soles(p.precio)} · <span class="badge ${s === 0 ? 'out' : (s <= data.stock_bajo ? 'low' : 'ok')}">${s} ud.</span></span>`;
      if (s > 0) div.addEventListener('click', () => agregar(p));
      resultados.appendChild(div);
    }
    if (!data.productos.length) resultados.innerHTML = '<div class="muted">Sin resultados</div>';
  }

  function agregar(p) {
    const it = carrito.get(p.id_producto) || { p, cantidad: 0 };
    if (it.cantidad < Number(p.stock_actual)) it.cantidad++;
    carrito.set(p.id_producto, it);
    pintar();
  }

  function pintar() {
    tbody.innerHTML = '';
    let total = 0;
    for (const [id, it] of carrito) {
      const sub = it.cantidad * Number(it.p.precio);
      total += sub;
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${esc(it.p.marca)} ${esc(it.p.modelo)} <span class="muted">${esc(it.p.color)}</span></td><td>${esc(it.p.talla)}</td>
        <td class="num">${soles(it.p.precio)}</td>
        <td><input class="qty" type="number" min="1" max="${Number(it.p.stock_actual)}" value="${it.cantidad}"></td>
        <td class="num">${soles(sub)}</td><td class="right"><button type="button" class="btn small secondary">Quitar</button></td>`;
      tr.querySelector('input').addEventListener('change', (e) => {
        it.cantidad = Math.max(1, Math.min(Number(it.p.stock_actual), parseInt(e.target.value, 10) || 1));
        pintar();
      });
      tr.querySelector('button').addEventListener('click', () => { carrito.delete(id); pintar(); });
      tbody.appendChild(tr);
    }
    if (!carrito.size) tbody.innerHTML = '<tr><td colspan="6" class="muted">Busque un producto y haga clic para agregarlo.</td></tr>';
    totalEl.textContent = soles(total);
    btn.disabled = carrito.size === 0;
    itemsInput.value = JSON.stringify([...carrito.values()].map((it) => ({ id: it.p.id_producto, cantidad: it.cantidad })));
  }

  buscar.addEventListener('input', () => { clearTimeout(t); t = setTimeout(cargar, 180); });
  talla.addEventListener('input', () => { clearTimeout(t); t = setTimeout(cargar, 180); });
  document.getElementById('form-venta').addEventListener('submit', () => { btn.disabled = true; btn.textContent = 'Registrando…'; });
  cargar();
  pintar();
})();
