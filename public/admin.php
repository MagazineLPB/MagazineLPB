<?php
/**
 * Admin Dashboard
 * Main panel for managing articles
 */

require_once __DIR__ . '/../includes/require_admin.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

// Handle article deletion (POST only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    try {
        $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: admin.php?deleted=1');
        exit;
    } catch (Exception $e) {
        $error = 'Failed to delete article.';
    }
}

// Get all articles
$stmt = $db->query("
    SELECT id, title, author, created_at, published 
    FROM articles 
    ORDER BY created_at DESC
");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$successMessage = '';
if (isset($_GET['created'])) {
    $successMessage = 'Article created successfully!';
} elseif (isset($_GET['updated'])) {
    $successMessage = 'Article updated successfully!';
} elseif (isset($_GET['deleted'])) {
    $successMessage = 'Article deleted successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .username {
            color: #666;
            font-size: 14px;
        }
        .logout-btn {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border: 1px solid #667eea;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background: #667eea;
            color: white;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .actions {
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .articles-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f9f9f9;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
        }
        td {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:hover {
            background: #fafafa;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .status.published {
            background: #d4edda;
            color: #155724;
        }
        .status.draft {
            background: #fff3cd;
            color: #856404;
        }
        .action-links {
            display: flex;
            gap: 12px;
        }
        .action-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 13px;
        }
        .action-links a:hover {
            text-decoration: underline;
        }
        .action-links button {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 13px;
            padding: 0;
            text-decoration: none;
        }
        .action-links button:hover {
            text-decoration: underline;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-state h3 {
            margin-bottom: 10px;
            color: #666;
        }
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 12px 8px;
            }
            .action-links {
                flex-direction: column;
                gap: 6px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <div class="user-info">
            <span class="username">Logged in as: <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($successMessage): ?>
            <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="actions">
            <a href="admin-edit.php" class="btn">+ Create New Article</a>
        </div>

        <div class="articles-table">
            <?php if (empty($articles)): ?>
                <div class="empty-state">
                    <h3>No articles yet</h3>
                    <p>Create your first article to get started!</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($article['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($article['author']); ?></td>
                                <td>
                                    <span class="status <?php echo $article['published'] ? 'published' : 'draft'; ?>">
                                        <?php echo $article['published'] ? 'Published' : 'Draft'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($article['created_at'])); ?></td>
                                <td>
                                    <div class="action-links">
                                        <a href="admin-edit.php?id=<?php echo $article['id']; ?>">Edit</a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>