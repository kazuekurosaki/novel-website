<?php
// config/sumber-data.php
// Konfigurasi Sumber Data Novel dari Website Target

// Website Target Utama
$sumber_data = "sakuranovel.id"; // Website Target / Sumber Data

// Nama-nama Website Sumber Data
$nama_website_sumber_data = "Sakura Novel"; // Nama Website Target
$nama_website_sumber_data2 = "Dragon Novel"; // Nama Website Target
$nama_website_sumber_data3 = "King Novel"; // Nama Website Target
$nama_website_sumber_data4 = "Heaven Novel"; // Nama Website Target
$nama_website_sumber_data5 = "Moon Novel"; // Nama Website Target

// Konfigurasi Sumber Data Lengkap
$sources = [
    'sakuranovel.id' => [
        'name' => 'Sakura Novel',
        'base_url' => 'https://sakuranovel.id',
        'type' => 'wordpress',
        'selectors' => [
            'novel_list' => '//div[contains(@class, "novel-item")]',
            'title' => './/h3/a',
            'url' => './/h3/a/@href',
            'cover' => './/img/@src',
            'chapter' => './/span[contains(@class, "chapter")]',
            'rating' => './/span[contains(@class, "rating")]',
            'detail_title' => '//h1[contains(@class, "novel-title")]',
            'author' => '//div[contains(@class, "author")]',
            'genre' => '//div[contains(@class, "genre")]/a',
            'sinopsis' => '//div[contains(@class, "sinopsis")]',
            'status' => '//div[contains(@class, "status")]',
            'cover_detail' => '//div[contains(@class, "cover")]//img/@src',
            'chapter_list' => '//ul[contains(@class, "chapter-list")]/li/a',
            'chapter_content' => '//div[contains(@class, "chapter-content")]'
        ]
    ],
    'dragonnovel.com' => [
        'name' => 'Dragon Novel',
        'base_url' => 'https://dragonnovel.com',
        'type' => 'custom',
        'selectors' => [
            'novel_list' => '//article[contains(@class, "novel")]',
            'title' => './/h2/a',
            'url' => './/h2/a/@href',
            'cover' => './/img/@src',
            'chapter' => './/span[contains(@class, "chapter-count")]',
            'rating' => './/span[contains(@class, "rate")]',
            'detail_title' => '//h1[contains(@class, "entry-title")]',
            'author' => '//span[contains(@class, "author-name")]',
            'genre' => '//a[contains(@rel, "genre")]',
            'sinopsis' => '//div[contains(@class, "entry-content")]',
            'status' => '//span[contains(@class, "novel-status")]',
            'cover_detail' => '//div[contains(@class, "novel-cover")]//img/@src',
            'chapter_list' => '//div[contains(@class, "chapter-list")]/ul/li/a',
            'chapter_content' => '//div[contains(@class, "reading-content")]'
        ]
    ]
];

// Konfigurasi User Agent
define('USER_AGENTS', [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.212 Safari/537.36'
]);

define('TIMEOUT', 30);
define('MAX_RETRIES', 3);
define('CACHE_TIME', 3600); // Cache 1 jam

// Fungsi untuk mengambil data dengan curl
function fetchUrl($url, $use_proxy = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, USER_AGENTS[array_rand(USER_AGENTS)]);
    curl_setopt($ch, CURLOPT_TIMEOUT, TIMEOUT);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Accept-Encoding: gzip, deflate',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1'
    ]);
    
    if ($use_proxy) {
        // Konfigurasi proxy jika diperlukan
        // curl_setopt($ch, CURLOPT_PROXY, 'proxy.example.com:8080');
        // curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    }
    
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("CURL Error: " . $error);
        return false;
    }
    
    if ($httpCode == 200) {
        return $html;
    }
    
    error_log("HTTP Error: " . $httpCode . " for URL: " . $url);
    return false;
}

// Fungsi untuk mendapatkan daftar novel dengan caching
function getLatestNovels($source_key = 'sakuranovel.id', $page = 1) {
    global $sources;
    
    if (!isset($sources[$source_key])) {
        return ['error' => 'Source not found'];
    }
    
    $source = $sources[$source_key];
    $cache_key = 'novel_list_' . $source_key . '_' . $page;
    $cached = getCache($cache_key);
    
    if ($cached !== false) {
        return $cached;
    }
    
    // Tentukan URL berdasarkan tipe sumber
    switch($source['type']) {
        case 'wordpress':
            $url = $source['base_url'] . "/page/$page/?post_type=novel";
            break;
        case 'custom':
            $url = $source['base_url'] . "/novel-list/page/$page";
            break;
        default:
            $url = $source['base_url'] . "/daftar-novel/page/$page";
    }
    
    $html = fetchUrl($url);
    
    if (!$html) {
        return ['error' => 'Failed to fetch data'];
    }
    
    $novels = parseNovelList($html, $source);
    
    // Simpan ke cache
    setCache($cache_key, $novels, CACHE_TIME);
    
    return $novels;
}

// Fungsi parsing novel list
function parseNovelList($html, $source) {
    $novels = [];
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $selectors = $source['selectors'];
    
    $items = $xpath->query($selectors['novel_list']);
    
    foreach ($items as $item) {
        try {
            $novel = [
                'title' => extractValue($xpath, $selectors['title'], $item),
                'url' => extractValue($xpath, $selectors['url'], $item, 'attribute'),
                'cover' => extractValue($xpath, $selectors['cover'], $item, 'attribute'),
                'chapter' => extractValue($xpath, $selectors['chapter'], $item),
                'rating' => extractValue($xpath, $selectors['rating'], $item),
                'source' => $source['base_url'],
                'source_key' => array_search($source, $GLOBALS['sources'])
            ];
            
            // Bersihkan URL
            if (!empty($novel['url']) && strpos($novel['url'], 'http') !== 0) {
                $novel['url'] = rtrim($source['base_url'], '/') . '/' . ltrim($novel['url'], '/');
            }
            
            // Bersihkan cover URL
            if (!empty($novel['cover']) && strpos($novel['cover'], 'http') !== 0) {
                $novel['cover'] = rtrim($source['base_url'], '/') . '/' . ltrim($novel['cover'], '/');
            }
            
            if (!empty($novel['title'])) {
                $novels[] = $novel;
            }
        } catch (Exception $e) {
            error_log("Error parsing novel: " . $e->getMessage());
            continue;
        }
    }
    
    return $novels;
}

// Fungsi ekstrak nilai dari DOM
function extractValue($xpath, $selector, $context = null, $type = 'text') {
    if ($context) {
        $nodes = $xpath->query($selector, $context);
    } else {
        $nodes = $xpath->query($selector);
    }
    
    if ($nodes->length > 0) {
        if ($type == 'attribute') {
            return trim($nodes->item(0)->value);
        } else {
            return trim($nodes->item(0)->nodeValue);
        }
    }
    
    return '';
}

// Fungsi cache sederhana (bisa diganti dengan Redis/Memcached)
function getCache($key) {
    $cache_dir = __DIR__ . '/../cache/';
    $cache_file = $cache_dir . md5($key) . '.cache';
    
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_TIME) {
        return unserialize(file_get_contents($cache_file));
    }
    
    return false;
}

function setCache($key, $data, $ttl = 3600) {
    $cache_dir = __DIR__ . '/../cache/';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    $cache_file = $cache_dir . md5($key) . '.cache';
    file_put_contents($cache_file, serialize($data));
}

// Fungsi auto-update multi-source
function autoUpdateAllSources() {
    global $sources, $conn;
    
    $results = [];
    
    foreach ($sources as $source_key => $source) {
        $page = 1;
        $total_inserted = 0;
        $total_updated = 0;
        
        // Ambil 5 halaman pertama dari setiap sumber
        while ($page <= 5) {
            $novels = getLatestNovels($source_key, $page);
            
            if (empty($novels) || isset($novels['error'])) {
                break;
            }
            
            foreach ($novels as $novel) {
                // Cek di database
                $check = mysqli_query($conn, "SELECT id FROM novels WHERE source_url = '" . mysqli_real_escape_string($conn, $novel['url']) . "'");
                
                if (mysqli_num_rows($check) > 0) {
                    // Update
                    $row = mysqli_fetch_assoc($check);
                    mysqli_query($conn, "UPDATE novels SET 
                        last_check = NOW() 
                        WHERE id = {$row['id']}");
                    $total_updated++;
                } else {
                    // Insert
                    $title = mysqli_real_escape_string($conn, $novel['title']);
                    $source_url = mysqli_real_escape_string($conn, $novel['url']);
                    $cover = mysqli_real_escape_string($conn, $novel['cover']);
                    $source_name = mysqli_real_escape_string($conn, $source['name']);
                    
                    $query = "INSERT INTO novels (title, source_url, cover_image, source_name, status, created_at, last_check) 
                             VALUES ('$title', '$source_url', '$cover', '$source_name', 'pending', NOW(), NOW())";
                    
                    if (mysqli_query($conn, $query)) {
                        $total_inserted++;
                    }
                }
            }
            
            $page++;
            sleep(1); // Delay untuk menghindari blocking
        }
        
        $results[$source_key] = [
            'inserted' => $total_inserted,
            'updated' => $total_updated
        ];
    }
    
    return $results;
}

// Fungsi untuk mendapatkan detail novel lengkap
function getCompleteNovelDetail($novel_id) {
    global $conn;
    
    $result = mysqli_query($conn, "SELECT * FROM novels WHERE id = $novel_id");
    $novel = mysqli_fetch_assoc($result);
    
    if (!$novel || empty($novel['source_url'])) {
        return null;
    }
    
    // Cari sumber berdasarkan URL
    $source_key = null;
    foreach ($GLOBALS['sources'] as $key => $source) {
        if (strpos($novel['source_url'], $source['base_url']) !== false) {
            $source_key = $key;
            break;
        }
    }
    
    if (!$source_key) {
        return $novel;
    }
    
    $source = $GLOBALS['sources'][$source_key];
    $html = fetchUrl($novel['source_url']);
    
    if (!$html) {
        return $novel;
    }
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $selectors = $source['selectors'];
    
    // Update detail novel
    $update_data = [];
    
    $title = extractValue($xpath, $selectors['detail_title']);
    if (!empty($title)) {
        $update_data['title'] = $title;
    }
    
    $author = extractValue($xpath, $selectors['author']);
    if (!empty($author)) {
        $update_data['author'] = $author;
    }
    
    $genre = extractGenre($xpath, $selectors['genre']);
    if (!empty($genre)) {
        $update_data['genre'] = $genre;
    }
    
    $sinopsis = extractValue($xpath, $selectors['sinopsis']);
    if (!empty($sinopsis)) {
        $update_data['description'] = mysqli_real_escape_string($conn, $sinopsis);
    }
    
    $status = extractValue($xpath, $selectors['status']);
    if (!empty($status)) {
        $update_data['status'] = strtolower($status);
    }
    
    $cover = extractValue($xpath, $selectors['cover_detail'], null, 'attribute');
    if (!empty($cover)) {
        if (strpos($cover, 'http') !== 0) {
            $cover = rtrim($source['base_url'], '/') . '/' . ltrim($cover, '/');
        }
        $update_data['cover_image'] = $cover;
    }
    
    // Update database
    if (!empty($update_data)) {
        $sets = [];
        foreach ($update_data as $field => $value) {
            $sets[] = "$field = '" . mysqli_real_escape_string($conn, $value) . "'";
        }
        $sets[] = "last_detail_update = NOW()";
        
        $query = "UPDATE novels SET " . implode(', ', $sets) . " WHERE id = $novel_id";
        mysqli_query($conn, $query);
    }
    
    // Ambil daftar chapter
    $chapters = extractChapterList($xpath, $source);
    
    // Simpan chapters
    foreach ($chapters as $chapter) {
        $chapter_number = (int)$chapter['number'];
        $chapter_title = mysqli_real_escape_string($conn, $chapter['title']);
        $chapter_url = mysqli_real_escape_string($conn, $chapter['url']);
        
        $check_chapter = mysqli_query($conn, "SELECT id FROM chapters WHERE novel_id = $novel_id AND chapter_number = $chapter_number");
        
        if (mysqli_num_rows($check_chapter) == 0) {
            mysqli_query($conn, "INSERT INTO chapters (novel_id, chapter_number, chapter_title, source_url, created_at) 
                                VALUES ($novel_id, $chapter_number, '$chapter_title', '$chapter_url', NOW())");
        }
    }
    
    // Ambil data terbaru
    $result = mysqli_query($conn, "SELECT * FROM novels WHERE id = $novel_id");
    return mysqli_fetch_assoc($result);
}

// Fungsi ekstrak genre
function extractGenre($xpath, $selector) {
    $genres = [];
    $nodes = $xpath->query($selector);
    
    foreach ($nodes as $node) {
        $genres[] = trim($node->nodeValue);
    }
    
    return implode(', ', $genres);
}

// Fungsi ekstrak daftar chapter
function extractChapterList($xpath, $source) {
    $chapters = [];
    $selectors = $source['selectors'];
    
    $items = $xpath->query($selectors['chapter_list']);
    
    foreach ($items as $item) {
        $title = trim($item->nodeValue);
        $url = $item->getAttribute('href');
        
        // Ekstrak nomor chapter
        preg_match('/(\d+)/', $title, $matches);
        $number = isset($matches[1]) ? (int)$matches[1] : count($chapters) + 1;
        
        if (!empty($url) && strpos($url, 'http') !== 0) {
            $url = rtrim($source['base_url'], '/') . '/' . ltrim($url, '/');
        }
        
        $chapters[] = [
            'number' => $number,
            'title' => $title,
            'url' => $url
        ];
    }
    
    // Balik urutan jika perlu (chapter terbaru di atas)
    return array_reverse($chapters);
}

// Fungsi mendapatkan konten chapter
function getChapterContentFromSource($chapter_url) {
    // Cari sumber berdasarkan URL
    $source_key = null;
    foreach ($GLOBALS['sources'] as $key => $source) {
        if (strpos($chapter_url, $source['base_url']) !== false) {
            $source_key = $key;
            break;
        }
    }
    
    if (!$source_key) {
        return "Sumber tidak ditemukan";
    }
    
    $source = $GLOBALS['sources'][$source_key];
    $html = fetchUrl($chapter_url);
    
    if (!$html) {
        return "Gagal mengambil konten chapter";
    }
    
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    $content_nodes = $xpath->query($source['selectors']['chapter_content']);
    
    if ($content_nodes->length > 0) {
        $content = $dom->saveHTML($content_nodes->item(0));
        return cleanContent($content);
    }
    
    return "Konten tidak ditemukan";
}

// Fungsi membersihkan konten
function cleanContent($html) {
    // Hapus script
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    
    // Hapus style
    $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
    
    // Hapus komentar
    $html = preg_replace('/<!--.*?-->/s', '', $html);
    
    // Hapus atribut yang tidak perlu
    $html = preg_replace('/\s+(style|class|id|onclick|onload)="[^"]*"/i', '', $html);
    
    // Konversi br ke newline
    $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
    
    // Hapus tag yang tersisa tapi simpan teksnya
    $html = strip_tags($html, '<p><div><span><strong><em><b><i><u>');
    
    return trim($html);
}

// JANGAN DIGANTI
?>
