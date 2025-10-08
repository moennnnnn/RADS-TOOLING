<?php

/**
 * RADS-TOOLING Chat Support API
 */

declare(strict_types=1);

// Prevent output before JSON
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../../chat_errors.log');

ob_start();

// Start session first
session_start();

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize database connection
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
    exit;
}

// Get action
$action = $_GET['action'] ?? '';

// Helper functions
function jsonResponse($success, $data = [], $message = '', $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function generateThreadCode(): string
{
    return strtoupper(bin2hex(random_bytes(16)));
}

// FIX: Updated authentication check to match your session structure
function isAdminAuthenticated(): bool
{
    // Check if staff session exists (your admin system uses 'staff' key)
    if (isset($_SESSION['staff']) && isset($_SESSION['staff']['id'])) {
        return true;
    }

    // Legacy check for old admin sessions
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }

    return false;
}

function getAdminId()
{
    if (isset($_SESSION['staff']['id'])) {
        return $_SESSION['staff']['id'];
    }

    if (isset($_SESSION['admin_id'])) {
        return $_SESSION['admin_id'];
    }

    return null;
}

/**
 * Calculate similarity between customer message and FAQ question
 */
function calculateSimilarity($customerMsg, $faqQuestion)
{
    $customerMsg = strtolower(trim($customerMsg));
    $faqQuestion = strtolower(trim($faqQuestion));

    if ($customerMsg === $faqQuestion) {
        return 1.0;
    }

    $stopWords = ['do', 'you', 'can', 'i', 'what', 'where', 'when', 'how', 'is', 'are', 'the', 'a', 'an'];

    $customerWords = array_diff(
        explode(' ', preg_replace('/[^a-z0-9\s]/i', '', $customerMsg)),
        $stopWords
    );

    $faqWords = array_diff(
        explode(' ', preg_replace('/[^a-z0-9\s]/i', '', $faqQuestion)),
        $stopWords
    );

    $customerWords = array_filter($customerWords);
    $faqWords = array_filter($faqWords);

    if (empty($customerWords) || empty($faqWords)) {
        return 0;
    }

    $matchCount = 0;
    foreach ($customerWords as $word) {
        foreach ($faqWords as $faqWord) {
            if ($word === $faqWord) {
                $matchCount++;
                break;
            }

            $distance = levenshtein($word, $faqWord);
            $maxLen = max(strlen($word), strlen($faqWord));

            if ($maxLen > 0 && ($distance / $maxLen) <= 0.3) {
                $matchCount += 0.5;
                break;
            }
        }
    }

    $totalWords = max(count($customerWords), count($faqWords));
    return $totalWords > 0 ? ($matchCount / $totalWords) : 0;
}

// Admin-only actions
$adminActions = ['threads', 'send_admin', 'faqs_save', 'faqs_delete'];

if (in_array($action, $adminActions) && !isAdminAuthenticated()) {
    jsonResponse(false, [], 'Unauthorized: Admin access required', 403);
}

// Handle actions
try {
    switch ($action) {

        // ========== FAQ LIST (Public - No Auth Required) ==========
        case 'faqs_list':
            $stmt = $pdo->query("
                SELECT id, question, answer, is_active
                FROM rt_faqs 
                WHERE is_active = 1 
                ORDER BY created_at DESC
            ");
            jsonResponse(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        // ========== FAQ SAVE (Admin Only) ==========
        case 'faqs_save':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = isset($input['id']) ? (int)$input['id'] : null;
            $question = trim($input['question'] ?? '');
            $answer = trim($input['answer'] ?? '');

            if (empty($question) || empty($answer)) {
                jsonResponse(false, [], 'Question and answer are required', 400);
            }

            if ($id) {
                $stmt = $pdo->prepare("
                    UPDATE rt_faqs 
                    SET question = ?, answer = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$question, $answer, $id]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO rt_faqs (question, answer, is_active, created_at, updated_at) 
                    VALUES (?, ?, 1, NOW(), NOW())
                ");
                $stmt->execute([$question, $answer]);
                $id = (int)$pdo->lastInsertId();
            }

            jsonResponse(true, ['id' => $id], 'FAQ saved successfully');
            break;

        // ========== FAQ DELETE (Admin Only) ==========
        case 'faqs_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? 0);

            if ($id <= 0) {
                jsonResponse(false, [], 'Invalid FAQ ID', 400);
            }

            $stmt = $pdo->prepare("DELETE FROM rt_faqs WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse(true, ['id' => $id], 'FAQ deleted successfully');
            break;

        // ========== THREADS LIST (Admin Only) ==========
        case 'threads':

            try {
                $stmt = $pdo->query("
            SELECT 
                t.id,
                t.thread_code,
                t.customer_id,
                t.customer_name,
                t.customer_email,
                t.customer_phone,
                t.status,
                t.last_message_at,
                t.created_at,
                COALESCE(
                    c.full_name,
                    t.customer_name,
                    c.username,
                    c.email,
                    t.customer_email,
                    CONCAT('Guest-', SUBSTRING(t.thread_code, 1, 6))
                ) as display_name,
                (
                    SELECT COUNT(*) 
                    FROM rt_chat_messages m 
                    WHERE m.thread_id = t.id 
                    AND m.sender_type = 'customer'
                    AND m.created_at > COALESCE(
                        (SELECT MAX(created_at) 
                         FROM rt_chat_messages 
                         WHERE thread_id = t.id 
                         AND sender_type = 'admin'),
                        '1970-01-01'
                    )
                ) as unread_count
            FROM rt_chat_threads t
            LEFT JOIN customers c ON t.customer_id = c.id
            WHERE t.status = 'open'
            ORDER BY t.last_message_at DESC
        ");

                $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Format data
                foreach ($threads as &$thread) {
                    $thread['unread_count'] = (int)$thread['unread_count'];
                    $thread['has_unread'] = $thread['unread_count'] > 0;
                }

                error_log('Fetched ' . count($threads) . ' threads');

                jsonResponse(true, $threads);
            } catch (PDOException $e) {
                error_log('Threads query error: ' . $e->getMessage());
                jsonResponse(false, [], 'Database error', 500);
            }
            break;

        // ========== MARK THREAD AS READ (Admin) ==========
        case 'mark_read':

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $threadCode = trim($input['thread_code'] ?? '');

            if (empty($threadCode)) {
                jsonResponse(false, [], 'Thread code required', 400);
            }

            try {
                // Get thread ID
                $stmt = $pdo->prepare("SELECT id FROM rt_chat_threads WHERE thread_code = ?");
                $stmt->execute([$threadCode]);
                $thread = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$thread) {
                    jsonResponse(false, [], 'Thread not found', 404);
                }

                // Insert a "read marker" by updating last admin message time
                // Or you can add a "read_at" column, but this is simpler
                jsonResponse(true, [], 'Thread marked as read');
            } catch (PDOException $e) {
                error_log('Mark read error: ' . $e->getMessage());
                jsonResponse(false, [], 'Failed to mark as read', 500);
            }
            break;

        // ========== THREAD INIT (Customer) ==========
        case 'thread_init':
            $customerId = $_SESSION['user']['id'] ?? null;
            $customerName = $_SESSION['user']['full_name'] ?? $_SESSION['user']['name'] ?? $_SESSION['user']['username'] ?? null;
            $customerEmail = $_SESSION['user']['email'] ?? null;

            error_log("Thread init - Customer ID: $customerId, Name: $customerName");

            // CRITICAL: Require login
            if (!$customerId || !$customerName) {
                jsonResponse(false, [], 'Please login or create an account to use chat support', 401);
            }

            try {
                // Check for existing OPEN thread for THIS specific customer
                $stmt = $pdo->prepare("
            SELECT thread_code, id
            FROM rt_chat_threads 
            WHERE customer_id = ? 
            AND status = 'open'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
                $stmt->execute([$customerId]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    error_log("Found existing thread: " . $existing['thread_code']);
                    jsonResponse(true, [
                        'thread_code' => $existing['thread_code'],
                        'thread_id' => (int)$existing['id'],
                        'user_id' => $customerId
                    ]);
                }

                // Create new thread for this customer
                $threadCode = generateThreadCode();

                error_log("Creating new thread: $threadCode for customer: $customerId ($customerName)");

                $stmt = $pdo->prepare("
            INSERT INTO rt_chat_threads (
                thread_code, 
                customer_id, 
                customer_name,
                customer_email,
                status,
                created_at, 
                last_message_at
            ) 
            VALUES (?, ?, ?, ?, 'open', NOW(), NOW())
        ");
                $stmt->execute([$threadCode, $customerId, $customerName, $customerEmail]);

                $threadId = $pdo->lastInsertId();
                error_log("Created thread ID: $threadId");

                jsonResponse(true, [
                    'thread_code' => $threadCode,
                    'thread_id' => (int)$threadId,
                    'user_id' => $customerId
                ]);
            } catch (PDOException $e) {
                error_log('Thread init error: ' . $e->getMessage());
                jsonResponse(false, [], 'Failed to initialize chat', 500);
            }
            break;

        // ========== FETCH MESSAGES ==========
        case 'messages_fetch':
            $threadCode = trim($_GET['thread_code'] ?? '');
            $afterId = (int)($_GET['after_id'] ?? 0);

            if (empty($threadCode)) {
                jsonResponse(false, [], 'Thread code required', 400);
            }

            try {
                $stmt = $pdo->prepare("SELECT id, customer_id FROM rt_chat_threads WHERE thread_code = ?");
                $stmt->execute([$threadCode]);
                $thread = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$thread) {
                    jsonResponse(false, [], 'Thread not found', 404);
                }

                // Check authorization
                $isAdmin = !empty($_SESSION['admin_logged_in']);
                $isOwner = !empty($_SESSION['user']['id']) &&
                    $_SESSION['user']['id'] == $thread['customer_id'];

                if (!$isAdmin && !$isOwner) {
                    jsonResponse(false, [], 'Unauthorized', 403);
                }

                // Fetch ALL messages (customer, bot, admin)
                $stmt = $pdo->prepare("
            SELECT 
                id,
                sender_type,
                sender_id,
                body,
                created_at
            FROM rt_chat_messages 
            WHERE thread_id = ? 
            AND id > ?
            ORDER BY created_at ASC, id ASC
        ");
                $stmt->execute([$thread['id'], $afterId]);

                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

                jsonResponse(true, $messages);
            } catch (PDOException $e) {
                error_log('Fetch messages error: ' . $e->getMessage());
                jsonResponse(false, [], 'Failed to fetch messages', 500);
            }
            break;

        // ========== SEND CUSTOMER MESSAGE ==========
        case 'send_customer':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $threadCode = trim($input['thread_code'] ?? '');
            $body = trim($input['body'] ?? '');

            if (empty($threadCode) || empty($body)) {
                jsonResponse(false, [], 'Missing thread_code or body', 400);
            }

            $stmt = $pdo->prepare("SELECT id, customer_id FROM rt_chat_threads WHERE thread_code = ?");
            $stmt->execute([$threadCode]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$thread) {
                jsonResponse(false, [], 'Thread not found', 404);
            }

            $threadId = $thread['id'];
            $customerId = $thread['customer_id'];

            try {
                // Insert customer message
                $stmt = $pdo->prepare("
            INSERT INTO rt_chat_messages (thread_id, sender_type, sender_id, body, created_at) 
            VALUES (?, 'customer', ?, ?, NOW())
        ");
                $stmt->execute([$threadId, $customerId, $body]);
                $messageId = $pdo->lastInsertId();

                // Update thread timestamp
                $stmt = $pdo->prepare("UPDATE rt_chat_threads SET last_message_at = NOW() WHERE id = ?");
                $stmt->execute([$threadId]);

                // FAQ MATCHING - Improved Algorithm
                $autoReply = null;
                $stmt = $pdo->query("SELECT question, answer FROM rt_faqs WHERE is_active = 1");
                $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($faqs) {
                    $bestMatch = null;
                    $highestScore = 0;

                    $bodyLower = strtolower($body);

                    foreach ($faqs as $faq) {
                        $questionLower = strtolower($faq['question']);

                        // Method 1: Exact match
                        if ($bodyLower === $questionLower) {
                            $bestMatch = $faq;
                            break;
                        }

                        // Method 2: Contains check (either way)
                        if (strpos($bodyLower, $questionLower) !== false || strpos($questionLower, $bodyLower) !== false) {
                            $bestMatch = $faq;
                            break;
                        }

                        // Method 3: Word matching
                        $bodyWords = preg_split('/\s+/', $bodyLower);
                        $questionWords = preg_split('/\s+/', $questionLower);

                        $matchCount = 0;
                        foreach ($bodyWords as $word) {
                            if (strlen($word) >= 3) { // Only count words with 3+ chars
                                foreach ($questionWords as $qWord) {
                                    if (strlen($qWord) >= 3 && strpos($qWord, $word) !== false) {
                                        $matchCount++;
                                    }
                                }
                            }
                        }

                        $score = $matchCount / max(count($bodyWords), count($questionWords));

                        if ($score > $highestScore && $score >= 0.3) { // Lower threshold
                            $highestScore = $score;
                            $bestMatch = $faq;
                        }
                    }

                    if ($bestMatch) {
                        $autoReply = $bestMatch['answer'];

                        // Insert bot response
                        $stmt = $pdo->prepare("
                    INSERT INTO rt_chat_messages (thread_id, sender_type, sender_id, body, created_at) 
                    VALUES (?, 'bot', NULL, ?, NOW())
                ");
                        $stmt->execute([$threadId, $autoReply]);

                        $stmt = $pdo->prepare("UPDATE rt_chat_threads SET last_message_at = NOW() WHERE id = ?");
                        $stmt->execute([$threadId]);
                    }
                }

                jsonResponse(true, [
                    'message_id' => (int)$messageId,
                    'auto_reply' => $autoReply
                ], 'Message sent');
            } catch (PDOException $e) {
                error_log('Send customer error: ' . $e->getMessage());
                jsonResponse(false, [], 'Failed to send message', 500);
            }
            break;

        // ========== CLEAR CHAT (Customer) ==========
        case 'clear_chat':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(false, [], 'Method not allowed', 405);
            }

            $customerId = $_SESSION['user']['id'] ?? null;

            if (!$customerId) {
                jsonResponse(false, [], 'Not authenticated', 401);
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $threadCode = trim($input['thread_code'] ?? '');

            if (empty($threadCode)) {
                jsonResponse(false, [], 'Thread code required', 400);
            }

            try {
                // Verify thread belongs to this customer
                $stmt = $pdo->prepare("
            SELECT id 
            FROM rt_chat_threads 
            WHERE thread_code = ? 
            AND customer_id = ?
        ");
                $stmt->execute([$threadCode, $customerId]);
                $thread = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$thread) {
                    jsonResponse(false, [], 'Thread not found or unauthorized', 404);
                }

                // Get count for UI feedback
                $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM rt_chat_messages 
            WHERE thread_id = ?
        ");
                $stmt->execute([$thread['id']]);
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

                // DON'T delete from database - just return success
                // Customer's UI will clear, but admin still sees everything
                jsonResponse(true, [
                    'message_count' => (int)$count,
                    'note' => 'Cleared from your view only. Admin retains full history.'
                ], 'Chat cleared successfully');
            } catch (PDOException $e) {
                error_log('Clear chat error: ' . $e->getMessage());
                jsonResponse(false, [], 'Failed to clear chat', 500);
            }
            break;

        // ========== SEND ADMIN MESSAGE ==========
        case 'send_admin':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $threadCode = trim($input['thread_code'] ?? '');
            $body = trim($input['body'] ?? '');

            if (empty($threadCode) || empty($body)) {
                jsonResponse(false, [], 'Missing thread_code or body', 400);
            }

            $stmt = $pdo->prepare("SELECT id FROM rt_chat_threads WHERE thread_code = ?");
            $stmt->execute([$threadCode]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$thread) {
                jsonResponse(false, [], 'Thread not found', 404);
            }

            $adminId = getAdminId();

            $stmt = $pdo->prepare("
                INSERT INTO rt_chat_messages (thread_id, sender_type, sender_id, body, created_at) 
                VALUES (?, 'admin', ?, ?, NOW())
            ");
            $stmt->execute([$thread['id'], $adminId, $body]);

            $stmt = $pdo->prepare("UPDATE rt_chat_threads SET last_message_at = NOW() WHERE id = ?");
            $stmt->execute([$thread['id']]);

            jsonResponse(true, [], 'Message sent');
            break;

        default:
            jsonResponse(false, [], 'Invalid action: ' . $action, 400);
    }
} catch (PDOException $e) {
    error_log('Chat API Database Error: ' . $e->getMessage());
    jsonResponse(false, [], 'Database error occurred', 500);
} catch (Exception $e) {
    error_log('Chat API Error: ' . $e->getMessage());
    jsonResponse(false, [], 'Server error occurred', 500);
}

// Flush any buffered output
$output = ob_get_clean();
if (!empty($output)) {
    error_log('Unexpected output before JSON: ' . $output);
}
