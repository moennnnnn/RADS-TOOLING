<?php
// backend/api/auth.php - Authentication API (staff + customer)
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../includes/guard.php';
require_once dirname(__DIR__, 2) . '/includes/phone_util.php';


// Include mailer if it exists
$mailerPath = __DIR__ . '/../lib/mailer.php';
if (file_exists($mailerPath)) {
    require_once $mailerPath;
}

class AuthAPI
{
    private PDO $conn;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Throwable $e) {
            $this->send(false, 'Database connection failed');
        }
    }

    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'login':
                $this->login();
                break;
            case 'logout':
                $this->logout();
                break;
            case 'check_session':
                $this->checkSession();
                break;
            case 'register':
                $this->register();
                break;
            case 'check_username':
                $this->checkUsername();
                break;
            case 'check_email':
                $this->checkEmail();
                break;
            case 'verify_email':
                $this->verifyEmail();
                break;
            case 'resend_verification':
                $this->resendVerification();
                break;
            case 'check_phone':
                $this->checkPhone();
                break;
            default:
                $this->send(false, 'Invalid action');
        }
    }

    /* -------------------- Availability checks (case-insensitive) -------------------- */

    private function checkEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['email'])) {
            $this->send(false, 'Email is required');
            return;
        }

        $email = trim((string)$input['email']);

        try {
            $stmt = $this->conn->prepare('SELECT id FROM customers WHERE email = BINARY ? LIMIT 1');
            $stmt->execute([$email]);
            $exists = (bool)$stmt->fetch();
            $this->send(true, 'Email check completed', ['available' => !$exists]);
        } catch (Throwable $e) {
            error_log("Email check error: " . $e->getMessage());
            $this->send(false, 'Email check failed');
        }
    }

    private function checkUsername(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['username'])) {
            $this->send(false, 'Username is required');
            return;
        }

        $username = trim((string)$input['username']);

        try {
            $stmt = $this->conn->prepare('SELECT id FROM customers WHERE username = BINARY ? LIMIT 1');
            $stmt->execute([$username]);
            $exists = (bool)$stmt->fetch();
            $this->send(true, 'Username check completed', ['available' => !$exists]);
        } catch (Throwable $e) {
            error_log("Username check error: " . $e->getMessage());
            $this->send(false, 'Username check failed');
        }
    }

    private function checkPhone(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        try {
            $phone = normalize_ph_phone($in['phone_local'] ?? $in['phone'] ?? '');
        } catch (RuntimeException $e) {
            $this->send(true, 'Invalid', ['available' => false, 'invalid' => true]);
            return;
        }
        $stmt = $this->conn->prepare('SELECT 1 FROM customers WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        $this->send(true, 'OK', ['available' => !$stmt->fetchColumn()]);
    }


    /* ------------------------------ Email verification ------------------------------ */

    private function verifyEmail(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        $code  = trim($input['code'] ?? '');

        if ($email === '' || $code === '') {
            $this->send(false, 'Email and verification code are required');
            return;
        }

        try {
            $stmt = $this->conn->prepare('
            SELECT id, verification_expires 
            FROM customers 
            WHERE email = BINARY ?
            AND verification_code = ?
            AND email_verified = 0
            ');
            $stmt->execute([$email, $code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->send(false, 'Invalid verification code');
                return;
            }

            if (strtotime($user['verification_expires']) < time()) {
                $this->send(false, 'Verification code has expired. Please request a new one.');
                return;
            }

            $updateStmt = $this->conn->prepare('
                UPDATE customers 
                SET email_verified = 1,
                    verification_code = NULL,
                    verification_expires = NULL
                WHERE id = ?
            ');
            $updateStmt->execute([$user['id']]);

            $this->send(true, 'Email verified successfully');
        } catch (PDOException $e) {
            error_log("Email verification error: " . $e->getMessage());
            $this->send(false, 'Verification failed. Please try again.');
        }
    }

    private function resendVerification(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');

        if ($email === '') {
            $this->send(false, 'Email is required');
            return;
        }

        try {
            $stmt = $this->conn->prepare('
            SELECT id, full_name, email_verified 
            FROM customers 
            WHERE email = BINARY ?
            ');
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->send(false, 'Email not found');
                return;
            }

            if ((int)$user['email_verified'] === 1) {
                $this->send(false, 'Email is already verified');
                return;
            }

            $verificationCode    = sprintf('%06d', random_int(100000, 999999));
            $verificationExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $updateStmt = $this->conn->prepare('
                UPDATE customers 
                SET verification_code = ?, verification_expires = ?
                WHERE id = ?
            ');
            $updateStmt->execute([$verificationCode, $verificationExpires, $user['id']]);

            if (class_exists('Mailer')) {
                $mailer = new Mailer();
                $ok = $mailer->sendVerificationCode($email, $user['full_name'], $verificationCode);
                if (!$ok) error_log("[MAIL] sendVerificationCode returned false for {$email}");
            } else {
                error_log("Verification code for {$email}: {$verificationCode}");
            }

            $this->send(true, 'Verification code has been resent to your email');
        } catch (PDOException $e) {
            error_log("Resend verification error: " . $e->getMessage());
            $this->send(false, 'Failed to resend verification code');
        }
    }

    /* --------------------------------- Registration -------------------------------- */

    private function registerCustomer(array $input): void
    {
        // Validate required fields
        $required = ['first_name', 'last_name', 'email', 'username', 'password'];
        foreach ($required as $field) {
            if (empty(trim((string)($input[$field] ?? '')))) {
                $this->send(false, "Field '$field' is required");
                return;
            }
        }

        $firstName = trim((string)$input['first_name']);
        $lastName  = trim((string)$input['last_name']);
        $email     = trim((string)$input['email']);
        $username  = trim((string)$input['username']);
        $password  = (string)$input['password'];

        // --- Normalize phone using util (accepts phone_local or phone) ---
        try {
            $phone = normalize_ph_phone($input['phone_local'] ?? $input['phone'] ?? '');
        } catch (RuntimeException $e) {
            $this->send(false, 'Enter a valid PH mobile (+63 + 10 digits).');
            return;
        }

        // --- Uniqueness check for phone ---
        $stmt = $this->conn->prepare('SELECT id FROM customers WHERE phone = ? LIMIT 1');
        $stmt->execute([$phone]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->send(false, 'Mobile number is already taken');
            return;
        }
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->send(false, 'Invalid email format');
            return;
        }

        // Validate password strength (minimum 6 characters; keep your rule)
        if (strlen($password) < 8) {
            $this->send(false, 'Password must be at least 8 characters long');
            return;
        }

        // Validate username (alphanumeric + underscore, 3-20 chars)
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $this->send(false, 'Username must be 3-20 characters (letters, numbers, underscore only)');
            return;
        }

        try {
            // CASE-SENSITIVE duplicates for username and email
            $stmt = $this->conn->prepare('
            SELECT id FROM customers
            WHERE username = BINARY ? OR email = BINARY ?
            LIMIT 1
            ');
            $stmt->execute([$username, $email]);
            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->send(false, 'Email or username already exists');
                return;
            }

            $hashedPassword       = password_hash($password, PASSWORD_DEFAULT);
            $verificationCode     = sprintf('%06d', random_int(100000, 999999));
            $verificationExpires  = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $fullName             = $firstName . ' ' . $lastName;

            $stmt = $this->conn->prepare('
                INSERT INTO customers (
                    username, full_name, email, phone, password, 
                    email_verified, verification_code, verification_expires, created_at
                ) VALUES (?, ?, ?, ?, ?, 0, ?, ?, NOW())
            ');
            $stmt->execute([
                $username,
                $fullName,
                $email,
                $phone,
                $hashedPassword,
                $verificationCode,
                $verificationExpires
            ]);

            $customerId = (int)$this->conn->lastInsertId();

            // Send verification email if mailer exists
            if (class_exists('Mailer')) {
                $mailer = new Mailer();
                $mailer->sendVerificationCode($email, $fullName, $verificationCode);
            } else {
                error_log("Verification code for {$email}: {$verificationCode}");
            }

            $this->send(true, 'Account created successfully. Please check your email for verification code.', [
                'customer_id'         => $customerId,
                'requiresVerification' => true
            ]);
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            if (($e->errorInfo[1] ?? 0) === 1062) {
                $this->send(false, 'Email or username already exists');
            } else {
                $this->send(false, 'Registration failed. Please try again.');
            }
        }
    }

    private function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->send(false, 'Invalid JSON data');
            return;
        }

        $audience = (string)($input['audience'] ?? '');
        if ($audience !== 'customer') {
            $this->send(false, 'Invalid audience for registration');
            return;
        }

        $this->registerCustomer($input);
    }

    /* -------------------------------------- Login ---------------------------------- */

    private function loginCustomer(string $login, string $password): void
    {
        $isEmail = (strpos($login, '@') !== false);

        if ($isEmail) {
            // Case-sensitive email
            $stmt = $this->conn->prepare('
            SELECT id, username, full_name, email, password, email_verified
            FROM customers
            WHERE email = BINARY ?
            LIMIT 1
        ');
            $stmt->execute([$login]);
        } else {
            // Case-sensitive username
            $stmt = $this->conn->prepare('
            SELECT id, username, full_name, email, password, email_verified
            FROM customers
            WHERE username = BINARY ?
            LIMIT 1
        ');
            $stmt->execute([$login]);
        }

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->send(false, 'Invalid credentials');
            return;
        }

        // 🔴 Not verified → tell frontend to go to verify.php
        if ((int)$user['email_verified'] !== 1) {
            $verifyUrl = '/RADS-TOOLING/customer/verify.php?email=' . urlencode($user['email']);
            $this->send(true, 'Verification required', [
                'verify_required' => true,
                'email'           => $user['email'],
                'redirect'        => $verifyUrl
            ]);
            return;
        }

        // ✅ Verified → proceed
        session_regenerate_id(true);
        guard_after_login_hardening();

        $_SESSION['customer'] = [
            'id'        => (int)$user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
        ];
        $_SESSION['user'] = [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'name'     => $user['full_name'],
            'role'     => 'Customer',
            'aud'      => 'customer',
        ];

        $token = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $token;

        $this->send(true, 'Login successful', [
            'user'           => $_SESSION['user'],
            'session_token'  => $token,
            'redirect'       => '/RADS-TOOLING/customer/homepage.php'
        ]);
    }

    private function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->send(false, 'Invalid JSON data');
            return;
        }

        $username = trim((string)($input['username'] ?? '')); // can be username OR email
        $password = (string)($input['password'] ?? '');
        $audience = (string)($input['audience'] ?? 'staff');

        if ($username === '' || $password === '') {
            $this->send(false, 'Username and password are required');
            return;
        }

        if (!in_array($audience, ['staff', 'customer'], true)) {
            $this->send(false, 'Invalid audience');
            return;
        }

        try {
            if ($audience === 'staff') {
                $this->loginStaff($username, $password);
            } else {
                $this->loginCustomer($username, $password);
            }
        } catch (Throwable $e) {
            error_log("Login error: " . $e->getMessage());
            $this->send(false, 'Authentication failed');
        }
    }

    /* --------------------------------- Staff login --------------------------------- */

    private function loginStaff(string $username, string $password): void
    {
        $stmt = $this->conn->prepare(
            'SELECT id, username, password, full_name, role, profile_image
     FROM admin_users
     WHERE username = BINARY ?
     LIMIT 1'
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->send(false, 'Invalid username or password');
            return;
        }

        session_regenerate_id(true);
        guard_after_login_hardening();

        $_SESSION['staff'] = [
            'id'        => (int)$user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
            'avatar'    => $user['profile_image'] ?? null,
        ];

        $_SESSION['user'] = [
            'id'       => (int)$user['id'],
            'username' => $user['username'],
            'name'     => $user['full_name'],
            'role'     => $user['role'],
            'aud'      => 'staff',
        ];

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $user['full_name'];

        $token = bin2hex(random_bytes(32));
        $_SESSION['session_token'] = $token;

        $this->send(true, 'Login successful', [
            'user'          => $_SESSION['user'],
            'session_token' => $token,
            'redirect'      => '/admin/index.php'
        ]);
    }

    /* -------------------------------- Session helpers ------------------------------- */

    private function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        $this->send(true, 'Logged out successfully');
    }

    private function checkSession(): void
    {
        if (!empty($_SESSION['user'])) {
            $this->send(true, 'Session valid', [
                'who'  => $_SESSION['user']['aud'] ?? null,
                'user' => $_SESSION['user'],
            ]);
            return;
        }
        $this->send(false, 'No valid session');
    }

    /* ---------------------------------- Responder ---------------------------------- */

    private function send(bool $success, string $message, array $data = null): void
    {
        $response = [
            'success' => $success,
            'message' => $message
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$auth = new AuthAPI();
$auth->handleRequest();
