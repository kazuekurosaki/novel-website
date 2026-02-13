<?php
// cron/auto-update.php
// Jalankan setiap jam: 0 * * * * php /path/to/cron/auto-update.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/sumber-data.php';
require_once __DIR__ . '/../includes/functions.php';

// Log function
function writeLog($message) {
    $log_file = __DIR__ . '/../logs/update.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Start update
writeLog("Starting auto-update process...");

try {
    // Auto update dari semua sumber
    $results = autoUpdateAllSources();
    
    // Log hasil
    foreach ($results as $source => $result) {
        writeLog("Source: $source - Inserted: {$result['inserted']}, Updated: {$result['updated']}");
    }
    
    // Update detail novel yang pending
    $pending_query = "SELECT id FROM novels WHERE (description IS NULL OR description = '') AND last_detail_update < DATE_SUB(NOW(), INTERVAL 1 DAY) LIMIT 10";
    $pending_result = mysqli_query($conn, $pending_query);
    
    while ($novel = mysqli_fetch_assoc($pending_result)) {
        writeLog("Updating detail for novel ID: {$novel['id']}");
        getCompleteNovelDetail($novel['id']);
        sleep(2); // Delay untuk menghindari rate limiting
    }
    
    // Update statistik
    $stats = getTotalStats();
    writeLog("Current stats - Novels: {$stats['total_novels']}, Chapters: {$stats['total_chapters']}, Users: {$stats['total_users']}");
    
    // Cleanup old logs
    $old_logs = glob(__DIR__ . '/../logs/*.log');
    foreach ($old_logs as $log) {
        if (filemtime($log) < strtotime('-30 days')) {
            unlink($log);
        }
    }
    
    writeLog("Auto-update completed successfully!\n");
    
} catch (Exception $e) {
    writeLog("ERROR: " . $e->getMessage() . "\n");
}

// Update chapter content untuk chapter yang belum terisi
function updatePendingChapters() {
    global $conn;
    
    $query = "SELECT c.*, n.source_url as novel_url 
              FROM chapters c 
              JOIN novels n ON c.novel_id = n.id 
              WHERE (c.content IS NULL OR c.content = '') 
              AND c.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
              LIMIT 20";
    
    $result = mysqli_query($conn, $query);
    
    while ($chapter = mysqli_fetch_assoc($result)) {
        writeLog("Updating chapter {$chapter['chapter_number']} for novel ID: {$chapter['novel_id']}");
        
        $content = getChapterContentFromSource($chapter['source_url']);
        
        if ($content && $content != "Konten tidak ditemukan") {
            $content_escaped = mysqli_real_escape_string($conn, $content);
            mysqli_query($conn, "UPDATE chapters SET content = '$content_escaped' WHERE id = {$chapter['id']}");
            writeLog("Chapter updated successfully");
        }
        
        sleep(3); // Delay antar request
    }
}

// Panggil fungsi update chapter
updatePendingChapters();

echo "Auto-update completed!\n";
?>
