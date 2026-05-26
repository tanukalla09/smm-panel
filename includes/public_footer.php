</main>

<footer class="landing-footer">
    <div class="container px-4">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <h6 class="logo-gradient"><span class="logo-icon">⚡</span> <?= SITE_NAME ?></h6>
                <p class="small">Premium SMM panel for professionals.</p>
            </div>
            <div class="col-md-2">
                <h6>Platform</h6>
                <a href="<?= SITE_URL ?>/login.php">Login</a>
                <a href="<?= SITE_URL ?>/register.php">Register</a>
                <a href="<?= SITE_URL ?>/user/services.php">Services</a>
            </div>
            <div class="col-md-2">
                <h6>Support</h6>
                <a href="<?= SITE_URL ?>/help.php">Help Center</a>
                <a href="<?= SITE_URL ?>/contact.php">Contact</a>
                <a href="<?= SITE_URL ?>/user/tickets.php">Support Tickets</a>
            </div>
            <div class="col-md-2">
                <h6>Legal</h6>
                <a href="<?= SITE_URL ?>/terms.php">Terms of Service</a>
                <a href="<?= SITE_URL ?>/privacy.php">Privacy Policy</a>
            </div>
        </div>
        <hr>
        <div class="text-center small">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/animations.js"></script>
</body>
</html>
