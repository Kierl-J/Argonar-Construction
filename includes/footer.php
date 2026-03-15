<footer class="site-footer">
    <div class="container">
        &copy; <?= date('Y') ?> Argonar Tournament. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= base_url("js/$js") ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
