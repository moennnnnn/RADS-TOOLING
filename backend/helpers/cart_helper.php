<?php
/**
 * Cart Helper Functions
 * Handles cart persistence and session/DB synchronization
 */

if (!function_exists('merge_session_cart_to_db')) {
    /**
     * Merge session cart items into database cart for logged-in user
     *
     * @param PDO $pdo Database connection
     * @param int $userId Customer ID
     * @return void
     */
    function merge_session_cart_to_db($pdo, $userId) {
        try {
            // Check if there's a session cart
            if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
                return; // No session cart to merge
            }

            $sessionCart = $_SESSION['cart'];

            foreach ($sessionCart as $cartItem) {
                // Extract cart item data
                $productId = (int)($cartItem['id'] ?? 0);
                $quantity = (int)($cartItem['quantity'] ?? 1);
                $isCustomized = (bool)($cartItem['isCustomized'] ?? false);

                if ($productId <= 0) {
                    continue; // Skip invalid items
                }

                // Check if this product already exists in user's cart
                $checkStmt = $pdo->prepare("
                    SELECT id, quantity
                    FROM cart_items
                    WHERE customer_id = ? AND product_id = ? AND item_type = 'product'
                    LIMIT 1
                ");
                $checkStmt->execute([$userId, $productId]);
                $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Update quantity (add session quantity to existing)
                    $newQuantity = (int)$existing['quantity'] + $quantity;
                    $updateStmt = $pdo->prepare("
                        UPDATE cart_items
                        SET quantity = ?
                        WHERE id = ?
                    ");
                    $updateStmt->execute([$newQuantity, $existing['id']]);
                } else {
                    // Insert new cart item
                    $insertStmt = $pdo->prepare("
                        INSERT INTO cart_items
                        (customer_id, item_type, product_id, quantity, added_at)
                        VALUES (?, 'product', ?, ?, NOW())
                    ");
                    $insertStmt->execute([$userId, $productId, $quantity]);
                }
            }

            // Clear session cart after successful merge
            unset($_SESSION['cart']);

        } catch (Exception $e) {
            error_log("Cart merge error: " . $e->getMessage());
            // Don't throw - cart merge failure shouldn't break login
        }
    }
}

if (!function_exists('get_user_cart_from_db')) {
    /**
     * Get user's cart items from database
     *
     * @param PDO $pdo Database connection
     * @param int $userId Customer ID
     * @return array Cart items
     */
    function get_user_cart_from_db($pdo, $userId) {
        try {
            $stmt = $pdo->prepare("
                SELECT
                    ci.*,
                    p.name as product_name,
                    p.price as product_price,
                    p.image as product_image
                FROM cart_items ci
                LEFT JOIN products p ON ci.product_id = p.id
                WHERE ci.customer_id = ?
                ORDER BY ci.added_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Cart retrieval error: " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('clear_user_cart')) {
    /**
     * Clear all items from user's cart in database
     *
     * @param PDO $pdo Database connection
     * @param int $userId Customer ID
     * @return bool Success status
     */
    function clear_user_cart($pdo, $userId) {
        try {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE customer_id = ?");
            $stmt->execute([$userId]);
            return true;
        } catch (Exception $e) {
            error_log("Cart clear error: " . $e->getMessage());
            return false;
        }
    }
}
