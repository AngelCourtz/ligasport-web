<?php
/** Página pública del chatbot para clientes (no requiere iniciar sesión). */
require __DIR__ . '/includes/bootstrap.php';
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Asistente virtual · LIGASPORT</title>
<link rel="stylesheet" href="assets/css/app.css">
<style>
  body { background: linear-gradient(135deg, #16181d 0%, #2b0d0f 100%); min-height: 100vh; display: grid; place-items: center; padding: 16px; }
  .chat { width: 100%; max-width: 440px; height: min(680px, calc(100vh - 32px)); background: #fff; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,.4); }
  .chat header { background: var(--red); color: #fff; padding: 14px 16px; display: flex; gap: 10px; align-items: center; }
  .chat header img { width: 38px; height: 38px; border-radius: 50%; background: #fff; object-fit: cover; }
  .chat header small { display: block; opacity: .85; }
  .msgs { flex: 1; overflow-y: auto; padding: 14px; background: #f5f6f8; display: flex; flex-direction: column; gap: 8px; }
  .m { max-width: 82%; padding: 9px 12px; border-radius: 14px; line-height: 1.4; white-space: pre-wrap; }
  .m.bot { background: #fff; border: 1px solid var(--line); align-self: flex-start; border-bottom-left-radius: 4px; }
  .m.yo { background: var(--ink); color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
  .m a { display: inline-block; margin-top: 6px; background: #25d366; color: #fff; padding: 5px 10px; border-radius: 8px; text-decoration: none; font-weight: 600; }
  .chips { display: flex; flex-wrap: wrap; gap: 6px; padding: 8px 12px; border-top: 1px solid var(--line); }
  .chips button { border: 1px solid #fecdca; background: #fff5f5; color: var(--red-dark); border-radius: 999px; padding: 5px 11px; font: inherit; font-size: 13px; cursor: pointer; }
  .send { display: flex; gap: 8px; padding: 10px 12px; border-top: 1px solid var(--line); }
</style>
</head>
<body>
<div class="chat">
  <header><img src="assets/img/logo.jpg" alt=""><div><strong>Asistente LIGASPORT</strong><small>Responde al instante · Zapatillas y ropa original</small></div></header>
  <div class="msgs" id="msgs"></div>
  <div class="chips" id="chips"></div>
  <form class="send" id="f"><input id="txt" placeholder="Escriba su consulta… ej.: Dunk Low talla 42" autocomplete="off" maxlength="300"><button class="btn">Enviar</button></form>
</div>
<script src="assets/js/chatbot.js"></script>
</body>
</html>
