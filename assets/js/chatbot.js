// Interfaz del chatbot por reglas (RF6). Todo el texto se inserta con textContent.
(function () {
  const msgs = document.getElementById('msgs');
  const chips = document.getElementById('chips');
  const form = document.getElementById('f');
  const txt = document.getElementById('txt');
  const atajos = {
    'Ver disponibilidad': '¿Qué modelos tienen disponibles?',
    'Precios': 'precio',
    'Ubicación y horario': '¿Dónde están y en qué horario atienden?',
    'Envíos': '¿Hacen envíos a provincia?',
    'Formas de pago': '¿Cómo puedo pagar?',
    'Hablar con un vendedor': 'Quiero hablar con un vendedor',
  };

  function burbuja(texto, quien, whatsapp) {
    const d = document.createElement('div');
    d.className = 'm ' + quien;
    d.textContent = texto;
    if (whatsapp) {
      const a = document.createElement('a');
      a.href = whatsapp; a.target = '_blank'; a.rel = 'noopener';
      a.textContent = 'Escribir por WhatsApp';
      d.appendChild(document.createElement('br'));
      d.appendChild(a);
    }
    msgs.appendChild(d);
    msgs.scrollTop = msgs.scrollHeight;
  }

  function sugerencias(lista) {
    chips.innerHTML = '';
    (lista || []).forEach((s) => {
      const b = document.createElement('button');
      b.type = 'button'; b.textContent = s;
      b.addEventListener('click', () => enviar(atajos[s] || s));
      chips.appendChild(b);
    });
  }

  async function enviar(texto, silencioso) {
    if (!silencioso) burbuja(texto, 'yo');
    try {
      const r = await fetch('api/chatbot.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mensaje: texto }) });
      const data = await r.json();
      burbuja(data.respuesta, 'bot', data.whatsapp);
      sugerencias(data.sugerencias);
    } catch (e) {
      burbuja('No pudimos conectar con el asistente. Intente de nuevo en un momento.', 'bot');
    }
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const v = txt.value.trim();
    if (!v) return;
    txt.value = '';
    enviar(v);
  });
  enviar('hola', true);
})();
