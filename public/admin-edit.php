<?php
/**
 * Article Editor
 * Create and edit articles
 */

require_once __DIR__ . '/../includes/require_admin.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

// Determine if creating or editing
$isEdit = isset($_GET['id']);
$articleId = $isEdit ? (int)$_GET['id'] : null;

// Initialize variables
$title = '';
$author = '';
$content = '';
$excerpt = '';
$published = 0;
$error = '';

// Load existing article for editing
if ($isEdit) {
    $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$article) {
        header('Location: admin.php');
        exit;
    }
    
    $title = $article['title'];
    $author = $article['author'];
    $content = $article['content'];
    $excerpt = $article['excerpt'];
    $published = $article['published'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;
    
    // Validation
    if (empty($title)) {
        $error = 'Title is required.';
    } elseif (empty($author)) {
        $error = 'Author is required.';
    } elseif (empty($content)) {
        $error = 'Content is required.';
    } else {
        try {
            // Generate slug from title (only on creation)
            if ($isEdit) {
                // Keep existing slug on edit
                $slug = $article['slug'];
            } else {
                // Generate new slug on creation
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            }
            
            // Auto-generate excerpt if empty (first 200 chars of content)
            if (empty($excerpt)) {
                $excerpt = substr(strip_tags($content), 0, EXCERPT_LENGTH);
                if (strlen($content) > EXCERPT_LENGTH) {
                    $excerpt .= '...';
                }
            }
            
            if ($isEdit) {
                // Update existing article
                $stmt = $db->prepare("
                    UPDATE articles 
                    SET title = ?, slug = ?, author = ?, content = ?, excerpt = ?, 
                        published = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $author, $content, $excerpt, $published, $articleId]);
                header('Location: admin.php?updated=1');
                exit;
            } else {
                // Create new article
                $stmt = $db->prepare("
                    INSERT INTO articles (title, slug, author, content, excerpt, published)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $slug, $author, $content, $excerpt, $published]);
                header('Location: admin.php?created=1');
                exit;
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'An article with a similar title already exists. Please use a different title.';
            } else {
                $error = 'Failed to save article. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Create'; ?> Article</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
            color: #333;
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
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 24px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        textarea#content {
            min-height: 400px;
            font-family: inherit;
            font-size: 14px;
        }
        .hint {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        @media (max-width: 768px) {
            .card {
                padding: 24px;
            }
            .form-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo $isEdit ? 'Edit' : 'Create'; ?> Article</h1>
        <a href="admin.php" class="back-link">← Back to Dashboard</a>
    </div>

    <div class="container">
        <div class="card">
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required
                        value="<?php echo htmlspecialchars($title); ?>"
                        placeholder="Enter article title">
                </div>

                <div class="form-group">
                    <label for="author">Author *</label>
                    <input 
                        type="text" 
                        id="author" 
                        name="author" 
                        required
                        value="<?php echo htmlspecialchars($author); ?>"
                        placeholder="Enter author name">
                </div>

                <div class="form-group">
                    <label for="excerpt">Excerpt</label>
                    <textarea 
                        id="excerpt" 
                        name="excerpt"
                        placeholder="Brief summary (optional - will auto-generate from content if empty)"><?php echo htmlspecialchars($excerpt); ?></textarea>
                    <div class="hint">A short summary for article previews (max 200 characters)</div>
                </div>

                <div class="form-group">
                    <label for="content">Content * (HTML)</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        required
                        placeholder="Write your article content in HTML format..."><?php echo htmlspecialchars($content); ?></textarea>
                    <div class="hint">Use HTML tags: &lt;p&gt;, &lt;h2&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;a&gt;, &lt;img&gt;</div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input 
                            type="checkbox" 
                            id="published" 
                            name="published"
                            <?php echo $published ? 'checked' : ''; ?>>
                        <label for="published">Publish this article</label>
                    </div>
                    <div class="hint">Uncheck to save as draft</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $isEdit ? 'Update Article' : 'Create Article'; ?>
                    </button>
                    <a href="admin.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>