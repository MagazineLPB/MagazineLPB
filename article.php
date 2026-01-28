<?php
/**
 * Single Article Page
 * Displays a single article by slug
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

// Get slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Get article by slug (only if published)
$stmt = $db->prepare("
    SELECT id, title, slug, author, content, created_at, updated_at
    FROM articles 
    WHERE slug = ? AND published = 1
");
$stmt->execute([$slug]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

// If article not found, redirect to homepage
if (!$article) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Magazine</title>
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
            line-height: 1.7;
        }
        .header {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 30px 20px;
        }
        .header-content {
            max-width: 800px;
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
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 60px 20px 100px;
        }
        .article-header {
            margin-bottom: 40px;
        }
        .article-title {
            font-size: 42px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        .article-meta {
            display: flex;
            gap: 16px;
            font-size: 15px;
            color: #666;
        }
        .article-content {
            font-size: 18px;
            color: #333;
            line-height: 1.8;
        }
        .article-content h1,
        .article-content h2,
        .article-content h3 {
            margin-top: 40px;
            margin-bottom: 20px;
            font-weight: 700;
            line-height: 1.3;
        }
        .article-content h1 {
            font-size: 36px;
        }
        .article-content h2 {
            font-size: 30px;
        }
        .article-content h3 {
            font-size: 24px;
        }
        .article-content p {
            margin-bottom: 20px;
        }
        .article-content a {
            color: #667eea;
            text-decoration: none;
        }
        .article-content a:hover {
            text-decoration: underline;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 30px 0;
        }
        .article-content ul,
        .article-content ol {
            margin: 20px 0 20px 30px;
        }
        .article-content li {
            margin-bottom: 10px;
        }
        .article-content blockquote {
            border-left: 4px solid #667eea;
            padding-left: 20px;
            margin: 30px 0;
            color: #555;
            font-style: italic;
        }
        .article-content code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 16px;
        }
        .article-content pre {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 20px 0;
        }
        .article-content pre code {
            background: none;
            padding: 0;
        }
        .article-content strong {
            font-weight: 700;
        }
        .article-content em {
            font-style: italic;
        }
        @media (max-width: 768px) {
            .article-title {
                font-size: 32px;
            }
            .article-content {
                font-size: 16px;
            }
            .article-content h1 {
                font-size: 28px;
            }
            .article-content h2 {
                font-size: 24px;
            }
            .article-content h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">Magazine</a>
            <a href="index.php" class="back-link">← All Articles</a>
        </div>
    </div>

    <div class="container">
        <article>
            <div class="article-header">
                <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                <div class="article-meta">
                    <span>By <?php echo htmlspecialchars($article['author']); ?></span>
                    <span>•</span>
                    <span><?php echo date('F j, Y', strtotime($article['created_at'])); ?></span>
                </div>
            </div>
            <div class="article-content">
                <?php echo $article['content']; ?>
            </div>
        </article>
    </div>
</body>
</html>