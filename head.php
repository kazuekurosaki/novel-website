<?php
// head.php
require_once 'meta.php';

// Fungsi untuk menghasilkan meta tags dinamis
function generateMetaTags($title = '', $description = '', $keywords = '', $image = '', $url = '') {
    global $site_name, $site_description, $site_keywords, $site_url, $site_logo;
    
    $meta_title = !empty($title) ? $title . ' - ' . $site_name : $site_name;
    $meta_desc = !empty($description) ? $description : $site_description;
    $meta_keywords = !empty($keywords) ? $keywords . ', ' . $site_keywords : $site_keywords;
    $meta_image = !empty($image) ? $image : $site_logo;
    $meta_url = !empty($url) ? $url : $site_url;
    
    ob_start();
    ?>
    <title><?php echo htmlspecialchars($meta_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
    <meta name="author" content="<?php echo $GLOBALS['author']; ?>">
    <meta name="copyright" content="<?php echo $GLOBALS['copyright']; ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($meta_url); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($meta_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($meta_image); ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo htmlspecialchars($meta_url); ?>">
    <meta property="twitter:title" content="<?php echo htmlspecialchars($meta_title); ?>">
    <meta property="twitter:description" content="<?php echo htmlspecialchars($meta_desc); ?>">
    <meta property="twitter:image" content="<?php echo htmlspecialchars($meta_image); ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($meta_url); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $GLOBALS['site_favicon']; ?>">
    <link rel="shortcut icon" href="<?php echo $GLOBALS['site_favicon']; ?>" type="image/x-icon" />
    <?php
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php 
    if (function_exists('generateMetaTags')) {
        echo generateMetaTags(
            isset($page_title) ? $page_title : '',
            isset($page_description) ? $page_description : '',
            isset($page_keywords) ? $page_keywords : '',
            isset($page_image) ? $page_image : '',
            isset($page_url) ? $page_url : ''
        );
    }
    ?>
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css?v=1.0.0">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <?php if (!empty($google_analytics_id) && $enable_seo): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $google_analytics_id; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $google_analytics_id; ?>');
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="wrapper">
