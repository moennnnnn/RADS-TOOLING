<?php
// RADS-TOOLING Chat Support API
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../database.php';

$action = $_GET['action'] ?? '';

// Helper functions
function json_response($success, $data = [], $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function generate_thread_code(): string {
    return strtoupper(bin2hex(random_bytes(8)));
}

function is_admin_authenticated(): bool {
    session_start();
    return isset($_SESSION['admin_id']) && $_SESSION['admin_id'] > 0;
}

// Admin-only actions
$admin_actions = [
    'threads', 'messages_admin', 'send_admin', 
    'faqs_save', 'faqs_delete'
];

if (in_array($action, $admin_actions) && !is_admin_authenticated()) {
    json_response(false, [], 'Unauthorized', 403);
}

try {
    switch ($action) {
        
        /* ========== FAQs ========== */
        case 'faqs_list':
            $stmt = $pdo->query("
                SELECT id, question, answer 
                FROM rt_faqs 
                WHERE is_active = 1 
                ORDER BY id ASC
            ");
            json_response(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'faqs_save':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = isset($input['id']) ? (int)$input['id'] : null;
            $question = trim($input['question'] ?? '');
            $answer = trim($input['answer'] ?? '');
            
            if (empty($question) || empty($answer)) {
                json_response(false, [], 'Question and answer required', 400);
            }
            
            if ($id) {
                $stmt = $pdo->prepare("
                    UPDATE rt_faqs 
                    SET question = ?, answer = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$question, $answer, $id]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO rt_faqs (question, answer, is_active) 
                    VALUES (?, ?, 1)
                ");
                $stmt->execute([$question, $answer]);
                $id = (int)$pdo->lastInsertId();
            }
            
            json_response(true, ['id' => $id], 'FAQ saved');
            break;

        case 'faqs_delete':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $id = (int)($input['id'] ?? 0);
            
            if ($id <= 0) {
                json_response(false, [], 'Invalid FAQ ID', 400);
            }
            
            $stmt = $pdo->prepare("DELETE FROM rt_faqs WHERE id = ?");
            $stmt->execute([$id]);
            
            json_response(true, ['id' => $id], 'FAQ deleted');
            break;

        /* ========== Threads ========== */
        case 'thread_create':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $code = generate_thread_code();
            
            $stmt = $pdo->prepare("
                INSERT INTO rt_chat_threads (thread_code, status) 
                VALUES (?, 'open')
            ");
            $stmt->execute([$code]);
            
            json_response(true, ['thread_code' => $code], 'Thread created');
            break;

        case 'thread_find':
            $code = $_GET['code'] ?? '';
            if (empty($code)) {
                json_response(false, [], 'Missing code', 400);
            }
            
            $stmt = $pdo->prepare("
                SELECT id, thread_code, status 
                FROM rt_chat_threads 
                WHERE thread_code = ?
            ");
            $stmt->execute([$code]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$thread) {
                json_response(false, [], 'Thread not found', 404);
            }
            
            json_response(true, $thread);
            break;

        case 'threads':
            $stmt = $pdo->query("
                SELECT id, thread_code, customer_name, customer_email, 
                       customer_phone, status, last_message_at 
                FROM rt_chat_threads 
                ORDER BY last_message_at DESC
            ");
            json_response(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'thread_close':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $code = $input['thread_code'] ?? '';
            
            if (empty($code)) {
                json_response(false, [], 'Missing code', 400);
            }
            
            $stmt = $pdo->prepare("
                UPDATE rt_chat_threads 
                SET status = 'closed' 
                WHERE thread_code = ?
            ");
            $stmt->execute([$code]);
            
            json_response(true, [], 'Thread closed');
            break;

        /* ========== Messages ========== */
        case 'send_customer':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $code = $input['thread_code'] ?? '';
            $body = trim($input['body'] ?? '');
            $sender = $input['sender'] ?? 'customer';
            
            if (empty($code) || empty($body)) {
                json_response(false, [], 'Missing data', 400);
            }
            
            $stmt = $pdo->prepare("SELECT id FROM rt_chat_threads WHERE thread_code = ?");
            $stmt->execute([$code]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$thread) {
                json_response(false, [], 'Invalid thread', 404);
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO rt_chat_messages (thread_id, sender_type, body) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$thread['id'], $sender, $body]);
            
            $pdo->prepare("
                UPDATE rt_chat_threads 
                SET last_message_at = NOW() 
                WHERE id = ?
            ")->execute([$thread['id']]);
            
            json_response(true, [], 'Message sent');
            break;

        case 'send_admin':
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $code = $input['thread_code'] ?? '';
            $body = trim($input['body'] ?? '');
            
            if (empty($code) || empty($body)) {
                json_response(false, [], 'Missing data', 400);
            }
            
            $stmt = $pdo->prepare("SELECT id FROM rt_chat_threads WHERE thread_code = ?");
            $stmt->execute([$code]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$thread) {
                json_response(false, [], 'Invalid thread', 404);
            }
            
            $admin_id = $_SESSION['admin_id'] ?? null;
            
            $stmt = $pdo->prepare("
                INSERT INTO rt_chat_messages (thread_id, sender_type, sender_id, body) 
                VALUES (?, 'admin', ?, ?)
            ");
            $stmt->execute([$thread['id'], $admin_id, $body]);
            
            $pdo->prepare("
                UPDATE rt_chat_threads 
                SET last_message_at = NOW() 
                WHERE id = ?
            ")->execute([$thread['id']]);
            
            json_response(true, [], 'Message sent');
            break;

        case 'messages_fetch':
            $code = $_GET['thread_code'] ?? '';
            $after_id = (int)($_GET['after_id'] ?? 0);
            
            if (empty($code)) {
                json_response(false, [], 'Missing code', 400);
            }
            
            $stmt = $pdo->prepare("SELECT id FROM rt_chat_threads WHERE thread_code = ?");
            $stmt->execute([$code]);
            $thread = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$thread) {
                json_response(false, [], 'Invalid thread', 404);
            }
            
            if ($after_id > 0) {
                $stmt = $pdo->prepare("
                    SELECT id, sender_type, body, created_at 
                    FROM rt_chat_messages 
                    WHERE thread_id = ? AND id > ? 
                    ORDER BY id ASC
                ");
                $stmt->execute([$thread['id'], $after_id]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT id, sender_type, body, created_at 
                    FROM rt_chat_messages 
                    WHERE thread_id = ? 
                    ORDER BY id ASC
                ");
                $stmt->execute([$thread['id']]);
            }
            
            json_response(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        default:
            json_response(false, [], 'Unknown action', 404);
    }

} catch (PDOException $e) {
    error_log('Chat API Error: ' . $e->getMessage());
    json_response(false, [], 'Database error', 500);
} catch (Exception $e) {
    error_log('Chat API Error: ' . $e->getMessage());
    json_response(false, [], 'Server error', 500);
}