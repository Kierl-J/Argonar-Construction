    </div><!-- /.content-area -->
</div><!-- /.main-content -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (isset($extraJs)): foreach ((array)$extraJs as $js): ?>
<script src="<?= asset('js/' . $js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
