<?php
// ===== Error visibility (safe-by-default) =====
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../backend/config/app.php';

// If $pdo isn't provided by app.php, fall back to Database singleton
if (!isset($pdo) || !$pdo) {
    // Try to resolve via config/database.php if your app structure uses it
    $dbConfig = __DIR__ . '/../backend/config/database.php';
    if (file_exists($dbConfig)) {
        require_once $dbConfig;
        if (class_exists('Database')) {
            try {
                $pdo = Database::getInstance()->getConnection();
            } catch (Throwable $e) {
                error_log('Testimonials DB connection error: ' . $e->getMessage());
                $pdo = null;
            }
        }
    }
}

$testimonials = [];
$stats = ['count' => 0, 'avg' => 0];

if ($pdo instanceof PDO) {
    try {
        // Summary (count + average of released)
        $qStats = $pdo->query("
            SELECT COUNT(*) AS cnt, COALESCE(ROUND(AVG(rating),1),0) AS avg_rating
            FROM feedback
            WHERE is_released = 1
        ");
        $row = $qStats->fetch(PDO::FETCH_ASSOC);
        $stats['count'] = (int)($row['cnt'] ?? 0);
        $stats['avg']   = (float)($row['avg_rating'] ?? 0);

        // Released testimonials list (newest first, up to 50)
        $stmt = $pdo->prepare("
            SELECT 
                f.rating,
                f.comment,
                f.created_at,
                COALESCE(c.full_name, 'Customer') AS customer_name
            FROM feedback f
            INNER JOIN customers c ON c.id = f.customer_id
            WHERE f.is_released = 1
            ORDER BY COALESCE(f.released_at, f.created_at) DESC
            LIMIT 50
        ");
        $stmt->execute();
        $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('Testimonials query error: ' . $e->getMessage());
        $testimonials = [];
    }
} else {
    // Could not connect to DB; show empty state without breaking the page
    error_log('Testimonials: no PDO connection available.');
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function safeDate(?string $s): string {
    if (!$s) return '—';
    $t = strtotime($s);
    return $t ? date('M d, Y', $t) : e($s);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Testimonials - RADS Tooling</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Poppins',sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height:100vh; padding:2rem;
        }
        .container { max-width:1200px; margin:0 auto; }
        .header { text-align:center; color:#fff; margin-bottom:2rem; }
        .header h1 { font-size:2.5rem; margin-bottom:.25rem; }
        .header p { font-size:1rem; opacity:.9; }
        .summary-bar {
            display:flex; gap:1rem; justify-content:center; align-items:center;
            color:#fff; margin-bottom:2.5rem;
        }
        .summary-chip {
            display:inline-flex; align-items:center; gap:.5rem;
            background: rgba(255,255,255,.15); padding:.5rem .9rem; border-radius:999px;
            font-weight:600;
        }
        .summary-chip .material-symbols-rounded { font-size: 18px; }
        .back-link {
            display:inline-flex; align-items:center; gap:.5rem;
            color:#fff; text-decoration:none; padding:.6rem 1rem; background:rgba(255,255,255,.2);
            border-radius:8px; margin-bottom:1.25rem; transition:background .2s ease;
        }
        .back-link:hover { background: rgba(255,255,255,.3); }

        .testimonials-grid {
            display:grid; grid-template-columns:repeat(auto-fill, minmax(320px,1fr)); gap:1.5rem;
        }
        .testimonial-card {
            background:#fff; padding:1.25rem 1.25rem 1rem; border-radius:12px;
            box-shadow:0 10px 30px rgba(0,0,0,.18); transition:transform .25s ease;
        }
        .testimonial-card:hover { transform: translateY(-4px); }
        .rating { display:flex; gap:.2rem; margin-bottom:.6rem; }
        .star { color:#fbbf24; font-size:1.3rem; line-height:1; }
        .star.empty { color:#d1d5db; }

        .comment {
            color:#4b5563; line-height:1.6; margin:.35rem 0 0.9rem;
            font-style:italic; white-space:pre-wrap;
        }
        .customer-info {
            display:flex; align-items:center; gap:.6rem; padding-top:.7rem; border-top:1px solid #e5e7eb;
        }
        .avatar {
            width:36px; height:36px; border-radius:50%;
            display:inline-flex; align-items:center; justify-content:center;
            background:#eef2ff; color:#4f46e5; font-weight:700;
        }
        .customer-name { font-weight:600; color:#667eea; }
        .customer-date { font-size:.875rem; color:#9ca3af; }
        .empty-state { text-align:center; color:#fff; padding:4rem 2rem; }
        .empty-state .icon { font-size:4rem; margin-bottom:1rem; opacity:.6; }
    </style>
</head>
<body>
    <div class="container">
        <a href="/RADS-TOOLING/public/index.php" class="back-link">
            <span class="material-symbols-rounded">arrow_back</span>
            Back to Home
        </a>

        <div class="header">
            <h1>Customer Testimonials</h1>
            <p>See what our customers say about us</p>
        </div>

        <div class="summary-bar">
            <span class="summary-chip">
                <span class="material-symbols-rounded">star</span>
                Average: <?php echo number_format($stats['avg'], 1); ?> / 5
            </span>
            <span class="summary-chip">
                <span class="material-symbols-rounded">reviews</span>
                <?php echo (int)$stats['count']; ?> verified <?php echo $stats['count'] == 1 ? 'review' : 'reviews'; ?>
            </span>
        </div>

        <?php if (empty($testimonials)): ?>
            <div class="empty-state">
                <div class="icon">
                    <span class="material-symbols-rounded">sentiment_satisfied</span>
                </div>
                <h2>No testimonials yet</h2>
                <p>Be the first to share your experience!</p>
            </div>
        <?php else: ?>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $t): 
                    $name = trim($t['customer_name'] ?? 'Customer');
                    $initials = '';
                    if ($name !== '') {
                        $parts = preg_split('/\s+/', $name);
                        $initials = strtoupper(mb_substr($parts[0], 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
                    }
                    $rating = (int)($t['rating'] ?? 0);
                    $comment = trim((string)($t['comment'] ?? ''));
                    ?>
                    <div class="testimonial-card">
                        <div class="rating" aria-label="<?php echo $rating; ?> out of 5 stars">
                            <?php for ($i=1; $i<=5; $i++): ?>
                                <span class="star <?php echo $i <= $rating ? '' : 'empty'; ?>">★</span>
                            <?php endfor; ?>
                        </div>

                        <?php if ($comment !== ''): ?>
                            <div class="comment">
                                “<?php echo nl2br(e($comment)); ?>”
                            </div>
                        <?php endif; ?>

                        <div class="customer-info">
                            <span class="avatar" aria-hidden="true"><?php echo e($initials ?: 'C'); ?></span>
                            <span class="customer-name"><?php echo e($name); ?></span>
                            <span class="customer-date">• <?php echo safeDate($t['created_at'] ?? null); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
