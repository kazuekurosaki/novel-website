<?php
// includes/header.php
?>
<header class="header">
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <a href="index.php">
                    <span class="logo-icon"><i class="fas fa-book-open"></i></span>
                    <span class="logo-text">Novel<span>Online</span></span>
                </a>
            </div>
            
            <div class="header-search">
                <form action="search.php" method="GET" id="searchForm">
                    <div class="search-wrapper">
                        <input type="text" 
                               name="q" 
                               id="searchInput"
                               placeholder="Cari judul novel, author, genre..." 
                               value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                               autocomplete="off">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="searchResults" class="search-results"></div>
                </form>
            </div>
            
            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu">
                    <button class="user-menu-btn">
                        <img src="<?php echo $_SESSION['user_avatar'] ?? 'assets/images/default-avatar.png'; ?>" 
                             alt="<?php echo $_SESSION['username']; ?>">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <a href="profile.php"><i class="fas fa-user"></i> Profil</a>
                        <a href="bookmarks.php"><i class="fas fa-bookmark"></i> Bookmark</a>
                        <a href="history.php"><i class="fas fa-history"></i> Riwayat</a>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="admin/index.php"><i class="fas fa-cog"></i> Admin Panel</a>
                        <?php endif; ?>
                        <hr>
                        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="login.php" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="register.php" class="btn-register">
                    <i class="fas fa-user-plus"></i> Daftar
                </a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <nav class="main-nav">
            <ul class="nav-menu">
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'genre.php' ? 'active' : ''; ?>">
                    <a href="genre.php"><i class="fas fa-tags"></i> Genre</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'popular.php' ? 'active' : ''; ?>">
                    <a href="popular.php"><i class="fas fa-fire"></i> Populer</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'completed.php' ? 'active' : ''; ?>">
                    <a href="completed.php"><i class="fas fa-check-circle"></i> Completed</a>
                </li>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'latest.php' ? 'active' : ''; ?>">
                    <a href="latest.php"><i class="fas fa-clock"></i> Terbaru</a>
                </li>
                <li class="nav-item-dropdown">
                    <a href="#"><i class="fas fa-ellipsis-h"></i> Lainnya <i class="fas fa-chevron-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="ranking.php"><i class="fas fa-trophy"></i> Ranking</a></li>
                        <li><a href="rekomendasi.php"><i class="fas fa-thumbs-up"></i> Rekomendasi</a></li>
                        <li><a href="random.php"><i class="fas fa-random"></i> Random Novel</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Kontak</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>

<script>
$(document).ready(function() {
    // Mobile menu toggle
    $('.mobile-menu-toggle').click(function() {
        $('.main-nav').slideToggle();
    });
    
    // Search autocomplete
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: 'ajax/search-suggest.php',
                    method: 'GET',
                    data: { q: query },
                    success: function(response) {
                        $('#searchResults').html(response).show();
                    }
                });
            }, 500);
        } else {
            $('#searchResults').hide();
        }
    });
    
    // Close search results when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.search-wrapper').length) {
            $('#searchResults').hide();
        }
    });
    
    // User menu dropdown
    $('.user-menu-btn').click(function(e) {
        e.stopPropagation();
        $('.user-dropdown').toggleClass('show');
    });
    
    $(document).click(function() {
        $('.user-dropdown').removeClass('show');
    });
});
</script>
