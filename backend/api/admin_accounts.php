<?php
// Account management API with role-based permissions
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
}

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../../includes/guard.php';

class AdminAccountsAPI
{
    private PDO $conn;
    private array $currentUser;
    private string $currentRole;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Throwable $e) {
            $this->send(false, 'Database connection failed');
        }

        // Check if user is authenticated staff
        if (empty($_SESSION['staff'])) {
            $this->send(false, 'Unauthorized access', null, 401);
        }

        $this->currentUser = $_SESSION['staff'];
        $this->currentRole = $this->currentUser['role'];
    }   

    private function resetPassword(): void
    {
        // Only Owner can reset passwords
        if ($this->currentRole !== 'Owner') {
            $this->send(false, 'You do not have permission to reset passwords');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id']) || empty($input['new_password'])) {
            $this->send(false, 'User ID and new password are required');
            return;
        }

        $userId = (int)$input['id'];
        $newPassword = trim((string)$input['new_password']);

        // Validate password
        if (strlen($newPassword) < 6) {
            $this->send(false, 'Password must be at least 6 characters long');
            return;
        }

        try {
            // Check if user exists
            $stmt = $this->conn->prepare('SELECT id, username FROM admin_users WHERE id = ? LIMIT 1');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->send(false, 'User not found');
                return;
            }

            // Reset password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare('UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$hashedPassword, $userId]);

            $this->send(true, 'Password reset successfully');
        } catch (Throwable $e) {
            error_log("Reset password error: " . $e->getMessage());
            $this->send(false, 'Failed to reset password');
        }
    }

    public function handleRequest(): void
    {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'list':
                $this->listUsers();
                break;
            case 'create':
                $this->createUser();
                break;
            case 'update':
                $this->updateUser();
                break;
            case 'delete':
                $this->deleteUser();
                break;
            case 'reset_password':
                $this->resetPassword();
                break;
            default:
                $this->send(false, 'Invalid action');
        }
    }

    private function listUsers(): void
    {
        try {
            // Determine what users this role can see
            $whereClause = '';
            $params = [];

            if ($this->currentRole === 'Secretary') {
                // Secretary can only see other secretaries and their own account
                $whereClause = 'WHERE role = ? OR id = ?';
                $params = ['Secretary', $this->currentUser['id']];
            } elseif ($this->currentRole === 'Admin') {
                // Admin can see Admins and Secretaries, but NOT Owners
                $whereClause = 'WHERE role IN (?, ?)';
                $params = ['Admin', 'Secretary'];
            }
            // Owner can see all users (no WHERE clause needed)

            $sql = "SELECT id, username, full_name, role, created_at, 
                           CASE WHEN id = ? THEN 0 ELSE 1 END as can_modify
                    FROM admin_users $whereClause
                    ORDER BY 
                        CASE role 
                            WHEN 'Owner' THEN 1 
                            WHEN 'Admin' THEN 2 
                            WHEN 'Secretary' THEN 3 
                        END,
                        full_name";

            $stmt = $this->conn->prepare($sql);

            if ($whereClause) {
                $stmt->execute([...$params, $this->currentUser['id']]);
            } else {
                $stmt->execute([$this->currentUser['id']]);
            }

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add permission flags for frontend
            foreach ($users as &$user) {
                $user['can_edit'] = $this->canModifyUser($user);
                $user['can_delete'] = $this->canDeleteUser($user);
                $user['can_modify'] = (bool)$user['can_modify'];
            }

            $this->send(true, 'Users retrieved successfully', [
                'users' => $users,
                'current_user_role' => $this->currentRole,
                'permissions' => $this->getUserPermissions()
            ]);
        } catch (Throwable $e) {
            error_log("List users error: " . $e->getMessage());
            $this->send(false, 'Failed to retrieve users');
        }
    }

    private function createUser(): void
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

        $username = trim((string)($input['username'] ?? ''));
        $fullName = trim((string)($input['full_name'] ?? ''));
        $role = trim((string)($input['role'] ?? ''));
        $password = trim((string)($input['password'] ?? ''));

        // Validate required fields
        if (empty($username) || empty($fullName) || empty($role) || empty($password)) {
            $this->send(false, 'All fields are required');
            return;
        }

        // Check if user has permission to create this role
        if (!$this->canCreateRole($role)) {
            $this->send(false, 'You do not have permission to create this role');
            return;
        }

        // Validate role
        if (!in_array($role, ['Owner', 'Admin', 'Secretary'])) {
            $this->send(false, 'Invalid role');
            return;
        }

        // Validate username
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $this->send(false, 'Username must be 3-20 characters (letters, numbers, underscore only)');
            return;
        }

        // Validate password
        if (strlen($password) < 6) {
            $this->send(false, 'Password must be at least 6 characters long');
            return;
        }

        try {
            // Check if username already exists
            $stmt = $this->conn->prepare('SELECT id FROM admin_users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $this->send(false, 'Username already exists');
                return;
            }

            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare('
                INSERT INTO admin_users (username, password, full_name, role, created_at) 
                VALUES (?, ?, ?, ?, NOW())
            ');
            $stmt->execute([$username, $hashedPassword, $fullName, $role]);

            $userId = $this->conn->lastInsertId();

            $this->send(true, 'User created successfully', [
                'user_id' => (int)$userId,
                'username' => $username,
                'full_name' => $fullName,
                'role' => $role
            ]);
        } catch (Throwable $e) {
            error_log("Create user error: " . $e->getMessage());
            if (isset($e->errorInfo) && $e->errorInfo[1] === 1062) {
                $this->send(false, 'Username already exists');
            } else {
                $this->send(false, 'Failed to create user');
            }
        }
    }

    private function updateUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            $this->send(false, 'User ID is required');
            return;
        }

        $userId = (int)$input['id'];
        $username = trim((string)($input['username'] ?? ''));
        $fullName = trim((string)($input['full_name'] ?? ''));
        $role = trim((string)($input['role'] ?? ''));

        // Get target user
        $stmt = $this->conn->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            $this->send(false, 'User not found');
            return;
        }

        // Check permissions
        if (!$this->canModifyUser($targetUser)) {
            $this->send(false, 'You do not have permission to modify this user');
            return;
        }

        if (!empty($role) && $role !== $targetUser['role'] && !$this->canChangeRole($targetUser['role'], $role)) {
            $this->send(false, 'You do not have permission to change this user\'s role');
            return;
        }

        try {
            // Build update query dynamically
            $updates = [];
            $params = [];

            if (!empty($username) && $username !== $targetUser['username']) {
                // Check if username is taken
                $stmt = $this->conn->prepare('SELECT id FROM admin_users WHERE username = ? AND id != ? LIMIT 1');
                $stmt->execute([$username, $userId]);
                if ($stmt->fetch()) {
                    $this->send(false, 'Username already exists');
                    return;
                }
                $updates[] = 'username = ?';
                $params[] = $username;
            }

            if (!empty($fullName) && $fullName !== $targetUser['full_name']) {
                $updates[] = 'full_name = ?';
                $params[] = $fullName;
            }

            if (!empty($role) && $role !== $targetUser['role']) {
                $updates[] = 'role = ?';
                $params[] = $role;
            }

            if (empty($updates)) {
                $this->send(false, 'No changes to update');
                return;
            }

            $updates[] = 'updated_at = NOW()';
            $params[] = $userId;

            $sql = 'UPDATE admin_users SET ' . implode(', ', $updates) . ' WHERE id = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            // Update session if user updated their own account
            if ($userId === $this->currentUser['id']) {
                if (!empty($username)) $_SESSION['staff']['username'] = $username;
                if (!empty($fullName)) {
                    $_SESSION['staff']['full_name'] = $fullName;
                    $_SESSION['admin_name'] = $fullName;
                }
                if (!empty($role)) $_SESSION['staff']['role'] = $role;
            }

            $this->send(true, 'User updated successfully');
        } catch (Throwable $e) {
            error_log("Update user error: " . $e->getMessage());
            $this->send(false, 'Failed to update user');
        }
    }

    private function deleteUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->send(false, 'Method not allowed');
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['id'])) {
            $this->send(false, 'User ID is required');
            return;
        }

        $userId = (int)$input['id'];

        // Can't delete self
        if ($userId === $this->currentUser['id']) {
            $this->send(false, 'You cannot delete your own account');
            return;
        }

        // Get target user
        $stmt = $this->conn->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            $this->send(false, 'User not found');
            return;
        }

        // Check permissions
        if (!$this->canDeleteUser($targetUser)) {
            $this->send(false, 'You do not have permission to delete this user');
            return;
        }

        try {
            // Delete user
            $stmt = $this->conn->prepare('DELETE FROM admin_users WHERE id = ?');
            $stmt->execute([$userId]);

            $this->send(true, 'User deleted successfully');
        } catch (Throwable $e) {
            error_log("Delete user error: " . $e->getMessage());
            $this->send(false, 'Failed to delete user');
        }
    }

    // Permission check methods
    private function canCreateRole(string $role): bool
    {
        switch ($this->currentRole) {
            case 'Owner':
                return true; // Owner can create any role
            case 'Admin':
                return in_array($role, ['Admin', 'Secretary']); // Admin can create Admin or Secretary
            case 'Secretary':
                return false; // Secretary cannot create users
            default:
                return false;
        }
    }

    private function canModifyUser(array $targetUser): bool
    {
        $targetRole = $targetUser['role'];
        $targetId = (int)$targetUser['id'];

        // Can always modify self (limited fields)
        if ($targetId === $this->currentUser['id']) {
            return true;
        }

        switch ($this->currentRole) {
            case 'Owner':
                return true; // Owner can modify anyone
            case 'Admin':
                return in_array($targetRole, ['Admin', 'Secretary']); // Admin can modify Admin or Secretary
            case 'Secretary':
                return false; // Secretary can only modify self (handled above)
            default:
                return false;
        }
    }

    private function canDeleteUser(array $targetUser): bool
    {
        $targetRole = $targetUser['role'];
        $targetId = (int)$targetUser['id'];

        // Cannot delete self
        if ($targetId === $this->currentUser['id']) {
            return false;
        }

        switch ($this->currentRole) {
            case 'Owner':
                // Owner can delete anyone except themselves, but check if deleting another owner would leave system without owners
                if ($targetRole === 'Owner') {
                    return !$this->isLastOwner();
                }
                return true;
            case 'Admin':
                return $targetRole === 'Secretary'; // Admin can only delete Secretary
            case 'Secretary':
                return false; // Secretary cannot delete users
            default:
                return false;
        }
    }

    private function canChangeRole(string $fromRole, string $toRole): bool
    {
        switch ($this->currentRole) {
            case 'Owner':
                return true; // Owner can change any role
            case 'Admin':
                return $fromRole !== 'Owner' && $toRole !== 'Owner'; // Admin cannot touch Owner role
            case 'Secretary':
                return false; // Secretary cannot change roles
            default:
                return false;
        }
    }

    private function isLastOwner(): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM admin_users WHERE role = ?');
            $stmt->execute(['Owner']);
            return $stmt->fetchColumn() <= 1;
        } catch (Throwable $e) {
            return true; // Err on the side of caution
        }
    }

    private function getUserPermissions(): array
    {
        return [
            'can_create_owner' => $this->currentRole === 'Owner',
            'can_create_admin' => in_array($this->currentRole, ['Owner', 'Admin']),
            'can_create_secretary' => in_array($this->currentRole, ['Owner', 'Admin']),
            'can_modify_others' => $this->currentRole !== 'Secretary',
            'can_delete_users' => $this->currentRole !== 'Secretary'
        ];
    }

    private function send(bool $success, string $message, array|object|null $data = null, ?int $code = null): void
    {
        if ($code !== null) {
            http_response_code($code);
        }

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

$api = new AdminAccountsAPI();
$api->handleRequest();
