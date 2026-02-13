<?php
// includes/functions.php

// Fungsi untuk mendapatkan novel terbaru dari database
function getLatestNovelsFromDB($limit = 20) {
    global $conn;
    
    $query = "SELECT * FROM novels WHERE status != 'deleted' ORDER BY created_at DESC LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $novels = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Hitung total chapters
        $chapter_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM chapters WHERE novel_id = {$row['id']}");
        $chapter_data = mysqli_fetch_assoc($chapter_count);
        $row['total_chapters'] = $chapter_data['total'];
        
        $novels[] = $row;
    }
    
    return $novels;
}

// Fungsi untuk mendapatkan novel populer
function getPopularNovelsFromDB($limit = 20) {
    global $conn;
    
    $query = "SELECT n.*, 
              (SELECT COUNT(*) FROM reading_history WHERE novel_id = n.id AND read_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) as weekly_views 
              FROM novels n 
              WHERE n.status != 'deleted' 
              ORDER BY weekly_views DESC, n.views DESC 
              LIMIT $limit";
    
    $result = mysqli_query($conn, $query);
    
    $novels = [];
    $rank = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $row['rank'] = $rank++;
        
        // Hitung total chapters
        $chapter_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM chapters WHERE novel_id = {$row['id']}");
        $chapter_data = mysqli_fetch_assoc($chapter_count);
        $row['total_chapters'] = $chapter_data['total'];
        
        $novels[] = $row;
    }
    
    return $novels;
}

// Fungsi untuk mendapatkan novel completed
function getCompletedNovelsFromDB($limit = 10) {
    global $conn;
    
    $query = "SELECT * FROM novels WHERE status = 'completed' ORDER BY last_update DESC LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $novels = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Hitung total chapters
        $chapter_count = mysqli_query($conn, "SELECT COUNT(*) as total FROM chapters WHERE novel_id = {$row['id']}");
        $chapter_data = mysqli_fetch_assoc($chapter_count);
        $row['total_chapters'] = $chapter_data['total'];
        
        $novels[] = $row;
    }
    
    return $novels;
}

// Fungsi untuk mendapatkan novel random
function getRandomNovelsFromDB($limit = 6) {
    global $conn;
    
    $query = "SELECT * FROM novels WHERE status != 'deleted' ORDER BY RAND() LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $novels = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $novels[] = $row;
    }
    
    return $novels;
}

// Fungsi untuk mendapatkan cover image
function getCoverImage($cover) {
    if (empty($cover)) {
        return 'assets/images/no-cover.jpg';
    }
    
    if (strpos($cover, 'http') === 0) {
        return $cover;
    }
    
    return 'uploads/covers/' . $cover;
}

// Fungsi untuk mengecek novel baru (kurang dari 3 hari)
function isNew($date) {
    $created = strtotime($date);
    $now = time();
    $diff = $now - $created;
    
    return $diff < (3 * 24 * 60 * 60); // 3 hari
}

// Fungsi untuk memotong teks
function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));
    
    return $text . '...';
}

// Fungsi untuk format angka
function formatNumber($num) {
    if ($num >= 1000000) {
        return round($num / 1000000, 1) . 'M';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'K';
    }
    return $num;
}

// Fungsi untuk mendapatkan bintang rating
function getStarRating($rating) {
    $full_stars = floor($rating);
    $half_star = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
    
    $stars = '';
    
    // Full stars
    for ($i = 0; $i < $full_stars; $i++) {
        $stars .= '<i class="fas fa-star"></i>';
    }
    
    // Half star
    if ($half_star) {
        $stars .= '<i class="fas fa-star-half-alt"></i>';
    }
    
    // Empty stars
    for ($i = 0; $i < $empty_stars; $i++) {
        $stars .= '<i class="far fa-star"></i>';
    }
    
    return $stars;
}

// Fungsi untuk mendapatkan genre populer
function getPopularGenres() {
    global $conn;
    
    $query = "SELECT genre, COUNT(*) as count FROM novels WHERE genre != '' AND genre IS NOT NULL GROUP BY genre ORDER BY count DESC LIMIT 20";
    $result = mysqli_query($conn, $query);
    
    $genres = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $genre_list = explode(',', $row['genre']);
        foreach ($genre_list as $genre) {
            $genre = trim($genre);
            if (!empty($genre)) {
                if (!isset($genres[$genre])) {
                    $genres[$genre] = 0;
                }
                $genres[$genre] += $row['count'];
            }
        }
    }
    
    arsort($genres);
    return array_slice($genres, 0, 20);
}

// Fungsi untuk mendapatkan total statistik
function getTotalStats() {
    global $conn;
    
    $stats = [];
    
    // Total novels
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM novels WHERE status != 'deleted'");
    $data = mysqli_fetch_assoc($result);
    $stats['total_novels'] = $data['total'];
    
    // Total chapters
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM chapters");
    $data = mysqli_fetch_assoc($result);
    $stats['total_chapters'] = $data['total'];
    
    // Total users
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
    $data = mysqli_fetch_assoc($result);
    $stats['total_users'] = $data['total'];
    
    // Total readers (unique visitors today)
    $result = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) as total FROM reading_history WHERE DATE(read_at) = CURDATE()");
    $data = mysqli_fetch_assoc($result);
    $stats['total_readers'] = $data['total'];
    
    // Last update
    $result = mysqli_query($conn, "SELECT MAX(created_at) as last FROM novels");
    $data = mysqli_fetch_assoc($result);
    $stats['last_update'] = $data['last'];
    
    return $stats;
}

// Fungsi untuk menambah view novel
function incrementNovelView($novel_id) {
    global $conn;
    
    mysqli_query($conn, "UPDATE novels SET views = views + 1 WHERE id = $novel_id");
}

// Fungsi untuk menambah view chapter
function incrementChapterView($chapter_id) {
    global $conn;
    
    mysqli_query($conn, "UPDATE chapters SET views = views + 1 WHERE id = $chapter_id");
}

// Fungsi untuk mencatat history baca
function addReadingHistory($user_id, $novel_id, $chapter_id) {
    global $conn;
    
    if ($user_id) {
        mysqli_query($conn, "INSERT INTO reading_history (user_id, novel_id, chapter_id) VALUES ($user_id, $novel_id, $chapter_id)");
    }
}

// Fungsi untuk mendapatkan chapter sebelumnya
function getPreviousChapter($novel_id, $current_chapter) {
    global $conn;
    
    $query = "SELECT * FROM chapters WHERE novel_id = $novel_id AND chapter_number < $current_chapter ORDER BY chapter_number DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan chapter selanjutnya
function getNextChapter($novel_id, $current_chapter) {
    global $conn;
    
    $query = "SELECT * FROM chapters WHERE novel_id = $novel_id AND chapter_number > $current_chapter ORDER BY chapter_number ASC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk mendapatkan daftar chapter
function getChapterList($novel_id) {
    global $conn;
    
    $query = "SELECT * FROM chapters WHERE novel_id = $novel_id ORDER BY chapter_number ASC";
    $result = mysqli_query($conn, $query);
    
    $chapters = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $chapters[] = $row;
    }
    
    return $chapters;
}

// Fungsi untuk bookmark
function addBookmark($user_id, $novel_id) {
    global $conn;
    
    $check = mysqli_query($conn, "SELECT id FROM bookmarks WHERE user_id = $user_id AND novel_id = $novel_id");
    
    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO bookmarks (user_id, novel_id) VALUES ($user_id, $novel_id)");
        return true;
    }
    
    return false;
}

function removeBookmark($user_id, $novel_id) {
    global $conn;
    
    mysqli_query($conn, "DELETE FROM bookmarks WHERE user_id = $user_id AND novel_id = $novel_id");
}

function isBookmarked($user_id, $novel_id) {
    global $conn;
    
    $check = mysqli_query($conn, "SELECT id FROM bookmarks WHERE user_id = $user_id AND novel_id = $novel_id");
    return mysqli_num_rows($check) > 0;
}
?>
