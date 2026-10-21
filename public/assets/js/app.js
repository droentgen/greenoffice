/* Front-end effects: running numbers, growing bars, mascot eyes, parallax, daily tip, intro game. */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Running numbers: <span data-count="1234.5" data-dec="1"> ---------- */
  function fmt(n, d) {
    return Number(n).toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
  }
  function runCount(el) {
    var target = parseFloat(el.getAttribute('data-count')) || 0;
    var dec = parseInt(el.getAttribute('data-dec') || '0', 10);
    if (reduce) { el.textContent = fmt(target, dec); return; }
    var start = null;
    function step(ts) {
      if (!start) start = ts;
      var t = Math.min(1, (ts - start) / 1700);
      var e = 1 - Math.pow(1 - t, 3);
      el.textContent = fmt(target * e, dec);
      if (t < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }
  window.goRunCount = runCount;

  /* ---------- Bars: <div class="grow-bar" data-w="64"> / <div class="grow-h" data-h="80"> ---------- */
  function grow(el) {
    if (el.hasAttribute('data-w')) el.style.width = el.getAttribute('data-w') + '%';
    if (el.hasAttribute('data-h')) el.style.height = el.getAttribute('data-h') + '%';
  }

  var watched = document.querySelectorAll('[data-count], .grow-bar, .grow-h');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        var el = en.target;
        if (el.hasAttribute('data-count')) runCount(el); else grow(el);
        io.unobserve(el);
      });
    }, { threshold: 0.2 });
    watched.forEach(function (el) { io.observe(el); });
  } else {
    watched.forEach(function (el) { if (el.hasAttribute('data-count')) runCount(el); else grow(el); });
  }

  /* ---------- Mascot eyes follow the mouse ---------- */
  var pupils = document.querySelectorAll('.js-pupil');
  if (pupils.length && !reduce) {
    window.addEventListener('mousemove', function (ev) {
      pupils.forEach(function (p) {
        var svg = p.ownerSVGElement; if (!svg) return;
        var r = svg.getBoundingClientRect();
        var dx = ev.clientX - (r.left + r.width / 2), dy = ev.clientY - (r.top + r.height / 2);
        var len = Math.sqrt(dx * dx + dy * dy) || 1;
        var k = Math.min(1, len / 300);
        p.setAttribute('cx', parseFloat(p.dataset.cx) + dx / len * 7 * k);
        p.setAttribute('cy', parseFloat(p.dataset.cy) + dy / len * 6 * k);
      });
    });
  }

  /* ---------- Parallax: elements with data-depth inside [data-parallax] ---------- */
  document.querySelectorAll('[data-parallax]').forEach(function (root) {
    if (reduce) return;
    var layers = root.querySelectorAll('[data-depth]');
    root.addEventListener('mousemove', function (ev) {
      var r = root.getBoundingClientRect();
      var x = ((ev.clientX - r.left) / r.width - 0.5) * 2, y = ((ev.clientY - r.top) / r.height - 0.5) * 2;
      layers.forEach(function (l) {
        var d = parseFloat(l.dataset.depth);
        l.style.transform = 'translate(' + (x * d) + 'px,' + (y * d) + 'px)';
      });
    });
    root.addEventListener('mouseleave', function () { layers.forEach(function (l) { l.style.transform = ''; }); });
  });

  /* ---------- Daily mission tip (footer band) ---------- */
  var tipBox = document.querySelector('[data-tips]');
  if (tipBox) {
    var tips = JSON.parse(tipBox.getAttribute('data-tips'));
    var tipText = document.querySelector('[data-tip-text]');
    document.querySelectorAll('[data-tip-next]').forEach(function (b) {
      b.addEventListener('click', function () {
        var cur = tips.indexOf(tipText.textContent), next = cur;
        while (next === cur && tips.length > 1) next = Math.floor(Math.random() * tips.length);
        tipText.textContent = tips[next];
        tipText.parentElement.classList.remove('anim-pop'); void tipText.parentElement.offsetWidth; tipText.parentElement.classList.add('anim-pop');
      });
    });
  }

  /* ---------- Intro game: Green Check-in (home page) ---------- */
  var game = document.getElementById('checkin');
  if (game) {
    var questions = JSON.parse(game.getAttribute('data-questions'));
    var answers = [];
    var qEl = game.querySelector('[data-q]');
    var resEl = game.querySelector('[data-result]');
    var dots = game.querySelectorAll('[data-dot]');

    function render() {
      var i = answers.length, q = questions[i];
      game.querySelector('[data-q-num]').textContent = i + 1;
      game.querySelector('[data-q-topic]').textContent = q.topic;
      game.querySelector('[data-q-text]').textContent = q.q;
      game.querySelectorAll('[data-q-icon]').forEach(function (ic) { ic.hidden = ic.getAttribute('data-q-icon') !== String(i); });
      dots.forEach(function (d, k) { d.style.background = k < i ? '#C5E063' : (k === i ? '#FFC845' : 'rgba(255,255,255,.2)'); });
      var card = game.querySelector('[data-card]');
      card.classList.remove('anim-pop'); void card.offsetWidth; card.classList.add('anim-pop');
    }

    function finish() {
      var score = answers.reduce(function (a, b) { return a + b; }, 0);
      var level = score >= 7 ? ['Green Legend ตัวจริง', 'คุณคือแบบอย่างของคณะ มาเป็น Green Ambassador ชวนเพื่อนต่อกันเลย']
        : score >= 4 ? ['Green Ranger สายลุย', 'มาถูกทางแล้ว อีกนิดเดียวก็เป็นตำนาน ลองเลือกภารกิจใหม่สักเรื่อง']
        : score >= 1 ? ['Green Sprout กำลังงอก', 'ทุกต้นไม้ใหญ่เริ่มจากต้นกล้า เริ่มจากเรื่องที่ง่ายที่สุดก่อนก็พอ']
        : ['Seed พร้อมงอก', 'ยังไม่เริ่มไม่เป็นไร วันนี้แหละวันดี มีภารกิจง่าย ๆ รออยู่ในเว็บ'];
      qEl.hidden = true; resEl.hidden = false;
      resEl.querySelector('[data-level]').textContent = level[0];
      resEl.querySelector('[data-level-desc]').textContent = level[1];
      resEl.querySelector('[data-score]').textContent = score;
      var list = resEl.querySelector('[data-topics]'); list.innerHTML = '';
      questions.forEach(function (q, k) {
        var a = answers[k];
        var li = document.createElement('div');
        li.className = 'bg-black/20 rounded-2xl px-3.5 py-3';
        var n = document.createElement('div'); n.className = 'text-sm text-[#CFE8E4]'; n.textContent = q.name;
        var s = document.createElement('div'); s.className = 'font-display text-[17px]';
        s.style.color = a === 2 ? '#C5E063' : a === 1 ? '#FFE08A' : '#ffffff';
        s.textContent = a === 2 ? 'คุณทำประจำ' : a === 1 ? 'คุณทำบางครั้ง' : 'ชวนคุณเริ่ม';
        li.appendChild(n); li.appendChild(s); list.appendChild(li);
      });
      try { localStorage.setItem('go_checkin', '1'); } catch (e) {}

      var token = document.querySelector('meta[name="csrf"]');
      fetch(game.getAttribute('data-api'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF': token ? token.content : '' },
        body: JSON.stringify({ answers: answers })
      }).then(function (r) { return r.json(); }).then(function (data) {
        if (!data || !data.ok) return;
        document.querySelectorAll('[data-part="players"]').forEach(function (el) { el.setAttribute('data-count', data.players); runCount(el); });
        data.topics.forEach(function (t) {
          document.querySelectorAll('[data-part="' + t.key + '-pct"]').forEach(function (el) { el.setAttribute('data-count', t.pct); runCount(el); });
          document.querySelectorAll('[data-part="' + t.key + '-count"]').forEach(function (el) { el.setAttribute('data-count', t.count); runCount(el); });
          document.querySelectorAll('[data-part="' + t.key + '-bar"]').forEach(function (el) { el.style.width = t.pct + '%'; });
        });
      }).catch(function () {});
    }

    game.querySelectorAll('[data-answer]').forEach(function (b) {
      b.addEventListener('click', function () {
        answers.push(parseInt(b.getAttribute('data-answer'), 10));
        if (answers.length >= questions.length) finish(); else render();
      });
    });
    function close() { game.hidden = true; document.body.style.overflow = ''; }
    game.querySelectorAll('[data-close]').forEach(function (b) { b.addEventListener('click', close); });
    document.querySelectorAll('[data-play]').forEach(function (b) {
      b.addEventListener('click', function () {
        answers = []; qEl.hidden = false; resEl.hidden = true; render();
        game.hidden = false; document.body.style.overflow = 'hidden';
      });
    });

    var seen = false;
    try { seen = localStorage.getItem('go_checkin') === '1'; } catch (e) {}
    if (!seen || location.hash === '#play') {
      render(); game.hidden = false; document.body.style.overflow = 'hidden';
    }
  }

  /* ---------- Admin: live GHG preview on the statistics form ---------- */
  var ghgOut = document.querySelector('[data-ghg-out]');
  if (ghgOut) {
    var f = document.querySelector('[name="fuel_l"]'), el = document.querySelector('[name="elec_kwh"]');
    var efF = parseFloat(ghgOut.dataset.efFuel), efE = parseFloat(ghgOut.dataset.efElec);
    function upd() { ghgOut.textContent = fmt(((parseFloat(f.value) || 0) * efF + (parseFloat(el.value) || 0) * efE) / 1000, 1); }
    f.addEventListener('input', upd); el.addEventListener('input', upd); upd();
  }

  /* ---------- Admin: news body editor — insert links and uploaded images as [text](url) / ![](url) ---------- */
  document.querySelectorAll('[data-body-editor]').forEach(function (box) {
    var ta = box.querySelector('textarea'), file = box.querySelector('[data-image-file]');
    var status = box.querySelector('[data-editor-status]');
    function insert(text) {
      var s = ta.selectionStart, end = ta.selectionEnd;
      ta.value = ta.value.slice(0, s) + text + ta.value.slice(end);
      ta.focus(); ta.selectionStart = ta.selectionEnd = s + text.length;
    }
    box.querySelector('[data-insert-link]').addEventListener('click', function () {
      var url = prompt('วางลิงก์ (https://…)');
      if (!url) return;
      url = url.trim();
      if (!/^https?:\/\/\S+$/i.test(url)) { status.textContent = 'ลิงก์ต้องขึ้นต้นด้วย http:// หรือ https://'; return; }
      var picked = ta.value.slice(ta.selectionStart, ta.selectionEnd);
      var label = picked || prompt('ข้อความที่จะแสดง', url) || url;
      insert('[' + label.replace(/[\[\]\n]/g, ' ') + '](' + url.replace(/\(/g, '%28').replace(/\)/g, '%29') + ')');
      status.textContent = '';
    });
    box.querySelector('[data-insert-image]').addEventListener('click', function () { file.click(); });
    file.addEventListener('change', function () {
      if (!file.files.length) return;
      var fd = new FormData();
      fd.append('image', file.files[0]);
      fd.append('_csrf', box.closest('form').querySelector('[name="_csrf"]').value);
      status.textContent = 'กำลังอัปโหลดรูป…';
      fetch(box.getAttribute('data-upload-url'), { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return { error: 'อัปโหลดไม่สำเร็จ (รหัส ' + r.status + ')' }; }); })
        .then(function (res) {
          if (res.error) { status.textContent = res.error; return; }
          insert('\n![](' + res.url + ')\n');
          status.textContent = 'แทรกรูปแล้ว';
        })
        .catch(function () { status.textContent = 'อัปโหลดไม่สำเร็จ — ตรวจการเชื่อมต่อแล้วลองใหม่'; })
        .then(function () { file.value = ''; });
    });
  });

  /* ---------- Confirm before deleting ---------- */
  document.querySelectorAll('[data-confirm]').forEach(function (b) {
    b.addEventListener('click', function (ev) { if (!confirm(b.getAttribute('data-confirm'))) ev.preventDefault(); });
  });
})();
