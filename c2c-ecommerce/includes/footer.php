    </div>
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>South African C2C</h5>
                    <p>A platform to buy and sell goods locally.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-white">Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>products/" class="text-white">Browse Products</a></li>
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="<?php echo BASE_URL; ?>users/register.php" class="text-white">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact</h5>
                    <p>Email: info@SouthAfricanC2C.com</p>
                    <p>Phone: +27 12 345 6789</p>
                    <p>Report issues to Admin: admin@SAC2C.com <p>
                </div>
            </div>
            <hr>
            
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>