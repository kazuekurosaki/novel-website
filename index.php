<?php
// index.php
session_start();
require_once 'config/database.php';
require_once 'config/sumber-data.php';
require_once 'includes/functions.php';

$page_title = "Beranda";
$page_description = "Baca novel online gratis terbaru dan terpopuler. Koleksi lengkap novel Indonesia dan terjemahan.";
$page_keywords = "novel online, baca novel gratis, novel terbaru";

include 'head.php';
include 'includes/header.php';

// Ambil novel terbaru dari database
$latest_novels = getLatestNovelsFromDB(12);
$popular_novels = getPopularNovelsFromDB(12);
$completed_novels = getCompletedNovelsFromDB(8);
$random_novels = getRandomNovelsFromDB(6);
?>

<main class="main-content">
    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="container">
            <div class="hero-content">
                <h1>Selamat Datang di <?php echo $site_name; ?></h1>
                <p>Temukan ribuan novel terbaik dan terbaru. Baca gratis kapan saja, di mana saja.</p>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number" id="totalNovelsHero">0</span>
                        <span class="stat-label">Novel</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" id="totalChaptersHero">0</span>
                        <span class="stat-label">Chapter</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" id="totalReadersHero">0</span>
                        <span class="stat-label">Pembaca</span>
                    </div>
                </div>
                <div class="hero-search">
                    <form action="search.php" method="GET">
                        <input type="text" name="q" placeholder="Cari judul novel..." required>
                        <button type="submit"><i class="fas fa-search"></i> Cari</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <!-- Novel Terbaru -->
        <section class="novel-section">
            <div class="section-header">
                <div class="section-title">
                    <h2><i class="fas fa-clock"></i> Novel Terbaru</h2>
                    <p>Update novel terbaru hari ini</p>
                </div>
                <a href="latest.php" class="view-all">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="novel-grid">
                <?php foreach ($latest_novels as $novel): ?>
                <div class="novel-card" data-id="<?php echo $novel['id']; ?>">
                    <div class="novel-cover">
                        <a href="detail.php?id=<?php echo $novel['id']; ?>">
                            <img src="<?php echo getCoverImage($novel['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($novel['title']); ?>"
                                 loading="lazy">
                        </a>
                        <span class="novel-status <?php echo $novel['status']; ?>">
                            <?php echo ucfirst($novel['status']); ?>
                        </span>
                        <?php if (isNew($novel['created_at'])): ?>
                        <span class="novel-badge new">Baru</span>
                        <?php endif; ?>
                    </div>
                    <div class="novel-info">
                        <h3 class="novel-title">
                            <a href="detail.php?id=<?php echo $novel['id']; ?>">
                                <?php echo htmlspecialchars($novel['title']); ?>
                            </a>
                        </h3>
                        <div class="novel-meta">
                            <span class="novel-author">
                                <i class="fas fa-pen-fancy"></i> <?php echo htmlspecialchars($novel['author'] ?: 'Unknown'); ?>
                            </span>
                            <span class="novel-chapter">
                                <i class="fas fa-book"></i> <?php echo $novel['total_chapters']; ?> Chapter
                            </span>
                        </div>
                        <div class="novel-rating">
                            <div class="stars">
                                <?php echo getStarRating($novel['rating']); ?>
                            </div>
                            <span class="rating-value">(<?php echo $novel['rating']; ?>)</span>
                        </div>
                        <p class="novel-desc">
                            <?php echo truncateText($novel['description'], 100); ?>
                        </p>
                        <div class="novel-genres">
                            <?php 
                            $genres = explode(',', $novel['genre']);
                            $genres = array_slice($genres, 0, 3);
                            foreach ($genres as $genre): 
                                if (!empty(trim($genre))):
                            ?>
                            <a href="genre.php?genre=<?php echo urlencode(trim($genre)); ?>" class="genre-tag">
                                <?php echo trim($genre); ?>
                            </a>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Novel Populer -->
        <section class="novel-section">
            <div class="section-header">
                <div class="section-title">
                    <h2><i class="fas fa-fire"></i> Novel Populer</h2>
                    <p>Novel paling banyak dibaca minggu ini</p>
                </div>
                <a href="popular.php" class="view-all">
                    Lihat Semua <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="novel-grid">
                <?php foreach ($popular_novels as $novel): ?>
                <div class="novel-card">
                    <div class="novel-cover">
                        <a href="detail.php?id=<?php echo $novel['id']; ?>">
                            <img src="<?php echo getCoverImage($novel['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($novel['title']); ?>"
                                 loading="lazy">
                        </a>
                        <span class="novel-rank">#<?php echo $novel['rank']; ?></span>
                    </div>
                    <div class="novel-info">
                        <h3 class="novel-title">
                            <a href="detail.php?id=<?php echo $novel['id']; ?>">
                                <?php echo htmlspecialchars($novel['title']); ?>
                            </a>
                        </h3>
                        <div class="novel-meta">
                            <span class="novel-views">
                                <i class="fas fa-eye"></i> <?php echo formatNumber($novel['views']); ?> views
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Novel Completed -->
        <section class="novel-section bg-light">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <h2><i class="fas fa-check-circle"></i> Novel Completed</h2>
                        <p>Novel dengan status tamat/lengkap</p>
                    </div>
                    <a href="completed.php" class="view-all">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <div class="novel-list-horizontal">
                    <?php foreach ($completed_novels as $novel): ?>
                    <div class="novel-horizontal-card">
                        <div class="novel-cover-small">
                            <a href="detail.php?id=<?php echo $novel['id']; ?>">
                                <img src="<?php echo getCoverImage($novel['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($novel['title']); ?>"
                                     loading="lazy">
                            </a>
                        </div>
                        <div class="novel-info-small">
                            <h4><a href="detail.php?id=<?php echo $novel['id']; ?>"><?php echo htmlspecialchars($novel['title']); ?></a></h4>
                            <span class="chapter-count"><?php echo $novel['total_chapters']; ?> Chapter</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Genre Populer -->
        <section class="genre-section">
            <div class="section-header">
                <div class="section-title">
                    <h2><i class="fas fa-tags"></i> Genre Populer</h2>
                    <p>Jelajahi novel berdasarkan genre</p>
                </div>
            </div>
            
            <div class="genre-cloud">
                <?php 
                $popular_genres = getPopularGenres();
                foreach ($popular_genres as $genre => $count): 
                ?>
                <a href="genre.php?genre=<?php echo urlencode($genre); ?>" class="genre-cloud-item" style="font-size: <?php echo 14 + ($count * 0.5); ?>px;">
                    <?php echo ucfirst($genre); ?> <span class="genre-count">(<?php echo $count; ?>)</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Random Novel -->
        <section class="novel-section">
            <div class="section-header">
                <div class="section-title">
                    <h2><i class="fas fa-random"></i> Rekomendasi Untukmu</h2>
                    <p>Novel pilihan untuk kamu baca</p>
                </div>
                <button onclick="loadMoreRecommendations()" class="btn-refresh">
                    <i class="fas fa-sync-alt"></i> Ganti Rekomendasi
                </button>
            </div>
            
            <div class="novel-grid" id="recommendationGrid">
                <?php foreach ($random_novels as $novel): ?>
                <div class="novel-card">
                    <div class="novel-cover">
                        <a href="detail.php?id=<?php echo $novel['id']; ?>">
                            <img src="<?php echo getCoverImage($novel['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($novel['title']); ?>"
                                 loading="lazy">
                        </a>
                    </div>
                    <div class="novel-info">
                        <h3 class="novel-title">
                            <a href="detail.php?id=<?php echo $novel['id']; ?>">
                                <?php echo htmlspecialchars($novel['title']); ?>
                            </a>
                        </h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<script>
$(document).ready(function() {
    // Load hero stats with animation
    $.ajax({
        url: 'ajax/get-stats.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            animateNumber('#totalNovelsHero', 0, data.total_novels, 1000);
            animateNumber('#totalChaptersHero', 0, data.total_chapters, 1000);
            animateNumber('#totalReadersHero', 0, data.total_readers, 1000);
        }
    });
    
    // Infinite scroll
    let page = 2;
    let loading = false;
    
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 500) {
            if (!loading) {
                loading = true;
                loadMoreNovels(page);
                page++;
            }
        }
    });
});

function animateNumber(selector, start, end, duration) {
    let current = start;
    const increment = (end - start) / (duration / 16);
    const timer = setInterval(function() {
        current += increment;
        $(selector).text(Math.floor(current));
        
        if (current >= end) {
            clearInterval(timer);
            $(selector).text(end);
        }
    }, 16);
}

function loadMoreNovels(page) {
    $('#loadingSpinner').show();
    
    $.ajax({
        url: 'ajax/load-more.php',
        method: 'GET',
        data: { page: page },
        success: function(response) {
            if (response) {
                $('.novel-grid:first').append(response);
            }
            $('#loadingSpinner').hide();
            loading = false;
        },
        error: function() {
            $('#loadingSpinner').hide();
            loading = false;
        }
    });
}

function loadMoreRecommendations() {
    $('#recommendationGrid').addClass('loading');
    
    $.ajax({
        url: 'ajax/get-recommendations.php',
        method: 'GET',
        success: function(response) {
            $('#recommendationGrid').html(response).removeClass('loading');
        }
    });
}
</script>

<?php
include 'includes/footer.php';
?>
