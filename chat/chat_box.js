/* Asistente Virtual - Veterinaria 5 de Abril */
(function () {
  'use strict';
  var root = document.getElementById('chat-root');
  if (!root) return;

  var endpoint = root.getAttribute('data-endpoint') || '';
  var siteName = root.getAttribute('data-name') || 'Veterinaria 5 de Abril';
  var wa = root.getAttribute('data-wa') || '56995999482';
  var waBase = 'https://wa.me/' + wa + '?text=';

  var ICON = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>';

  var QUICK = ['Precios', 'Vacunas', 'Castración', 'Horarios', 'Hablar por WhatsApp'];

  root.innerHTML =
    '<button id="chat-toggle" aria-label="Abrir asistente" title="Chatea con nosotros">' + ICON + '</button>' +
    '<div id="chat-panel" aria-hidden="true">' +
      '<div class="chat-head">' +
        '<div><div class="chat-title">Asistente Virtual</div><div class="chat-sub">' + siteName + '</div></div>' +
        '<button id="chat-close" aria-label="Cerrar">×</button>' +
      '</div>' +
      '<div class="chat-body" id="chat-body"></div>' +
      '<div class="chat-quick" id="chat-quick"></div>' +
      '<form id="chat-form" class="chat-input">' +
        '<input id="chat-field" type="text" placeholder="Escribe tu consulta…" autocomplete="off">' +
        '<button type="submit">Enviar</button>' +
      '</form>' +
    '</div>';

  var toggle = document.getElementById('chat-toggle');
  var panel = document.getElementById('chat-panel');
  var closeBtn = document.getElementById('chat-close');
  var body = document.getElementById('chat-body');
  var quick = document.getElementById('chat-quick');
  var form = document.getElementById('chat-form');
  var field = document.getElementById('chat-field');

  var initialized = false;

  QUICK.forEach(function (label) {
    var b = document.createElement('button');
    b.type = 'button';
    b.textContent = label;
    b.addEventListener('click', function () {
      if (label === 'Hablar por WhatsApp') {
        window.open(waBase + encodeURIComponent('Hola, quiero agendar una hora en Veterinaria 5 de Abril.'), '_blank');
      } else {
        send(label);
      }
    });
    quick.appendChild(b);
  });

  function openChat() {
    panel.setAttribute('aria-hidden', 'false');
    panel.classList.add('open');
    toggle.style.display = 'none';
    if (!initialized) {
      initialized = true;
      addMsg('bot', '¡Hola! Soy el asistente virtual de ' + siteName + '.\nPregúntame por precios, vacunas, castración u horarios. O elige una opción de abajo.');
    }
    field.focus();
  }

  function closeChat() {
    panel.classList.remove('open');
    panel.setAttribute('aria-hidden', 'true');
    toggle.style.display = '';
  }

  toggle.addEventListener('click', openChat);
  closeBtn.addEventListener('click', closeChat);

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var v = field.value.trim();
    if (!v) return;
    field.value = '';
    send(v);
  });

  function send(text) {
    addMsg('user', text);
    addMsg('bot', '…', 'typing');
    fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        removeTyping();
        if (data && data.ok && data.response) {
          addMsg('bot', data.response);
        } else {
          addMsg('bot', 'No pude responder en este momento. Escríbenos por WhatsApp y te atendemos al toque.');
        }
      })
      .catch(function () {
        removeTyping();
        addMsg('bot', 'Hubo un problema de conexión. Escríbenos por WhatsApp: +56 9 9599 9482.');
      });
  }

  function removeTyping() {
    var t = body.querySelector('.typing');
    if (t) t.remove();
  }

  function addMsg(role, text, extra) {
    var wrap = document.createElement('div');
    wrap.className = 'chat-msg ' + (role === 'user' ? 'user' : 'bot') + (extra ? ' ' + extra : '');
    var inner = document.createElement('div');
    inner.className = 'bubble';
    if (extra === 'typing') {
      inner.innerHTML = '<span class="dot"></span><span class="dot"></span><span class="dot"></span>';
    } else {
      inner.textContent = text;
      var links = text.match(/https?:\/\/[^\s]+/g) || [];
      links.forEach(function (url) {
        var a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.rel = 'noopener';
        a.textContent = url.length > 60 ? url.slice(0, 57) + '…' : url;
        inner.innerHTML = inner.innerHTML.replace(url, a.outerHTML);
      });
    }
    wrap.appendChild(inner);
    body.appendChild(wrap);
    body.scrollTop = body.scrollHeight;
  }
})();