<?php
// includes/footer.php
?>
    </div> <!-- .wrapper -->

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Tentang NovelOnline</h3>
                    <p>NovelOnline adalah platform baca novel online gratis dengan koleksi terlengkap. Update setiap hari dengan novel-novel terbaru dari berbagai genre.</p>
                    <div class="social-links">
                        <a href="<?php echo $facebook_url; ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?php echo $twitter_url; ?>" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
                        <a href="<?php echo $instagram_url; ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        <a href="#" target="_blank" rel="noopener"><i class="fab fa-telegram-plane"></i></a>
                        <a href="#" target="_blank" rel="noopener"><i class="fab fa-discord"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h3>Genre Populer</h3>
                    <ul class="footer-links">
                        <li><a href="genre.php?genre=romance"><i class="fas fa-heart"></i> Romance</a></li>
                        <li><a href="genre.php?genre=fantasy"><i class="fas fa-dragon"></i> Fantasy</a></li>
                        <li><a href="genre.php?genre=action"><i class="fas fa-fist-raised"></i> Action</a></li>
                        <li><a href="genre.php?genre=comedy"><i class="fas fa-laugh"></i> Comedy</a></li>
                        <li><a href="genre.php?genre=horror"><i class="fas fa-ghost"></i> Horror</a></li>
                        <li><a href="genre.php?genre=slice-of-life"><i class="fas fa-coffee"></i> Slice of Life</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Informasi</h3>
                    <ul class="footer-links">
                        <li><a href="about.php"><i class="fas fa-info-circle"></i> Tentang Kami</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Kontak</a></li>
                        <li><a href="privacy.php"><i class="fas fa-shield-alt"></i> Kebijakan Privasi</a></li>
                        <li><a href="terms.php"><i class="fas fa-file-contract"></i> Syarat & Ketentuan</a></li>
                        <li><a href="disclaimer.php"><i class="fas fa-exclamation-triangle"></i> Disclaimer</a></li>
                        <li><a href="sitemap.php"><i class="fas fa-sitemap"></i> Sitemap</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h3>Statistik</h3>
                    <ul class="footer-stats">
                        <li><i class="fas fa-book"></i> Total Novel: <span id="totalNovels">-</span></li>
                        <li><i class="fas fa-file-alt"></i> Total Chapter: <span id="totalChapters">-</span></li>
                        <li><i class="fas fa-users"></i> Pengguna: <span id="totalUsers">-</span></li>
                        <li><i class="fas fa-clock"></i> Update Terakhir: <span id="lastUpdate">-</span></li>
                    </ul>
                    
                    <div class="newsletter">
                        <h4>Langganan Newsletter</h4>
                        <form id="newsletterForm">
                            <input type="email" placeholder="Email Anda" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="copyright">
                    &copy; <?php echo date('Y'); ?> <?php echo $site_name; ?>. Hak Cipta Dilindungi.
                </div>
                <div class="footer-links-bottom">
                    <a href="privacy.php">Privacy</a>
                    <a href="terms.php">Terms</a>
                    <a href="cookies.php">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" title="Kembali ke atas">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner" style="display:none;">
        <div class="spinner"></div>
    </div>

    <!-- Notification Container -->
    <div id="notificationContainer" class="notification-container"></div>

    <!-- JavaScript Files -->
    <script src="js/main.js?v=1.0.0"></script>
    
    <script>
    // Load footer stats
    $(document).ready(function() {
        $.ajax({
            url: 'ajax/get-stats.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#totalNovels').text(data.total_novels || '0');
                $('#totalChapters').text(data.total_chapters || '0');
                $('#totalUsers').text(data.total_users || '0');
                $('#lastUpdate').text(data.last_update || '-');
            }
        });
        
        // Back to top button
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('#backToTop').fadeIn();
            } else {
                $('#backToTop').fadeOut();
            }
        });
        
        $('#backToTop').click(function() {
            $('html, body').animate({scrollTop: 0}, 500);
        });
        
        // Newsletter form
        $('#newsletterForm').submit(function(e) {
            e.preventDefault();
            const email = $(this).find('input[type="email"]').val();
            
            $.ajax({
                url: 'ajax/subscribe.php',
                method: 'POST',
                data: { email: email },
                success: function(response) {
                    showNotification('Berhasil berlangganan newsletter!', 'success');
                    $('#newsletterForm')[0].reset();
                }
            });
        });
    });
    
    // Global notification function
    function showNotification(message, type = 'info') {
        const notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('#notificationContainer').append(notification);
        
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    </script>
</body>
</html>
