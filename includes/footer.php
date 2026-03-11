    </div><!-- /.content-area -->

    <!-- Footer -->
    <footer class="site-footer">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <span>&copy; <?= date('Y') ?> Argonar Construction. All rights reserved.</span>
            <div class="d-flex gap-3">
                <a href="<?= url('terms.php') ?>">Terms of Use</a>
                <a href="<?= url('privacy.php') ?>">Privacy Policy</a>
                <a href="mailto:support@argonar.co">Contact</a>
                <a href="https://www.facebook.com/argonar.co" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
            </div>
        </div>
    </footer>
</div><!-- /.main-content -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (isset($extraJs)): foreach ((array)$extraJs as $js): ?>
<script src="<?= asset('js/' . $js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
