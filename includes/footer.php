<footer class="site-footer">
    <div class="container">
        <div style="margin-bottom:0.75rem;">
            <a href="<?= base_url('rules.php') ?>" style="color:var(--accent-light); text-decoration:none; font-size:0.85rem; font-weight:600; margin:0 0.5rem;">Rules</a>
            <span style="color:var(--border);">|</span>
            <a href="<?= base_url('contact.php') ?>" style="color:var(--accent-light); text-decoration:none; font-size:0.85rem; font-weight:600; margin:0 0.5rem;">Contact</a>
        </div>
        &copy; <?= date('Y') ?> Argonar Software OPC. All rights reserved.
    </div>
</footer>

<div class="mobile-sticky-bar" id="mobileStickyBar">
    <a href="#games" class="mobile-sticky-btn">
        <i class="bi bi-controller"></i> Register Now
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= base_url("js/$js") ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
<script>
// Countdown timer
(function() {
    var timer = document.querySelector('.countdown-timer');
    if (!timer) return;
    var target = new Date(timer.dataset.target + 'T00:00:00').getTime();
    function tick() {
        var now = Date.now();
        var diff = target - now;
        if (diff <= 0) {
            document.getElementById('cdDays').textContent = '0';
            document.getElementById('cdHours').textContent = '0';
            document.getElementById('cdMins').textContent = '0';
            document.getElementById('cdSecs').textContent = '0';
            return;
        }
        var d = Math.floor(diff / 86400000);
        var h = Math.floor((diff % 86400000) / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        document.getElementById('cdDays').textContent = d;
        document.getElementById('cdHours').textContent = h < 10 ? '0' + h : h;
        document.getElementById('cdMins').textContent = m < 10 ? '0' + m : m;
        document.getElementById('cdSecs').textContent = s < 10 ? '0' + s : s;
    }
    tick();
    setInterval(tick, 1000);
})();

// Copy link
function copyLink(btn) {
    navigator.clipboard.writeText('https://argonar.co').then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}

// Mobile sticky bar - show when scrolled past hero
(function() {
    var bar = document.getElementById('mobileStickyBar');
    var hero = document.querySelector('.hero');
    if (!bar || !hero) return;
    function check() {
        var bottom = hero.getBoundingClientRect().bottom;
        bar.classList.toggle('visible', bottom < 0);
    }
    window.addEventListener('scroll', check, { passive: true });
    check();
})();

// Nav toggle
(function() {
    var toggle = document.getElementById('navToggle');
    var links = document.getElementById('navLinks');
    if (!toggle || !links) return;
    toggle.addEventListener('click', function() {
        links.classList.toggle('open');
    });
})();
</script>
</body>
</html>
