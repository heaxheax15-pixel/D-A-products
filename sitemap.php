<?php
declare(strict_types=1);
define('DA_APP', true);
require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$origin = site_origin();

$urls = [];

$staticPages = [
    ['loc' => $origin . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['loc' => $origin . '/index.php#products', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['loc' => $origin . '/privacy-policy.php', 'priority' => '0.3', 'changefreq' => 'yearly'],
];
foreach ($staticPages as $p) {
    $urls[] = [
        'loc' => $p['loc'],
        'lastmod' => date('c'),
        'changefreq' => $p['changefreq'],
        'priority' => $p['priority'],
    ];
}

try {
    foreach (get_products(true) as $prod) {
        if (empty($prod['slug'])) continue;
        $urls[] = [
            'loc' => $origin . '/product.php?slug=' . urlencode((string) $prod['slug']),
            'lastmod' => !empty($prod['created_at']) ? date('c', strtotime((string) $prod['created_at'])) : date('c'),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ];
    }
} catch (Throwable $e) {
    error_log('[D&A sitemap] products: ' . $e->getMessage());
}

try {
    foreach (get_articles() as $aid => $art) {
        $urls[] = [
            'loc' => $origin . '/article.php?id=' . (int) $aid,
            'lastmod' => date('c'),
            'changefreq' => 'monthly',
            'priority' => '0.5',
        ];
    }
} catch (Throwable $e) {
    error_log('[D&A sitemap] articles: ' . $e->getMessage());
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo '    <lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1) . "</lastmod>\n";
    echo '    <changefreq>' . htmlspecialchars($u['changefreq'], ENT_XML1) . "</changefreq>\n";
    echo '    <priority>' . htmlspecialchars($u['priority'], ENT_XML1) . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
