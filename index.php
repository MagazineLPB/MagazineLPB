<?php
/**
 * Homepage
 * Lists all published articles
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

// Get all published articles
$stmt = $db->query("
    SELECT id, title, slug, author, excerpt, created_at 
    FROM articles 
    WHERE published = 1
    ORDER BY created_at DESC
");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magazine</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #fff;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 30px 20px;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            text-decoration: none;
        }
        .admin-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        .admin-link:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 40px;
        }
        .article-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 30px;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .article-title {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.3;
        }
        .article-meta {
            display: flex;
            gap: 16px;
            font-size: 14px;
            color: #666;
            margin-bottom: 16px;
        }
        .article-excerpt {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
        }
        .empty-state {
            text-align: center;
            padding: 100px 20px;
            color: #999;
        }
        .empty-state h2 {
            margin-bottom: 10px;
            color: #666;
        }
        @media (max-width: 768px) {
            .articles-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            .header-content {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">Magazine</a>
            <a href="admin.php" class="admin-link">Admin</a>
        </div>
    </div>

    <div class="container">
        <?php if (empty($articles)): ?>
            <div class="empty-state">
                <h2>No articles published yet</h2>
                <p>Check back soon for new content!</p>
            </div>
        <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                    <a href="article.php?slug=<?php echo urlencode($article['slug']); ?>" class="article-card">
                        <h2 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h2>
                        <div class="article-meta">
                            <span><?php echo htmlspecialchars($article['author']); ?></span>
                            <span>•</span>
                            <span><?php echo date('M j, Y', strtotime($article['created_at'])); ?></span>
                        </div>
                        <p class="article-excerpt"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>