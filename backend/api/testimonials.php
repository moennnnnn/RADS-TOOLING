<?php
// /backend/api/testimonials.php - Fetch recent testimonials
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Fetch recent testimonials with customer names
    $stmt = $db->prepare("
        SELECT 
            f.rating,
            f.comment,
            f.created_at,
            c.full_name as customer_name
        FROM feedback f
        INNER JOIN customers c ON f.customer_id = c.id
        WHERE f.comment IS NOT NULL AND f.comment != ''
        ORDER BY f.created_at DESC
        LIMIT 6
    ");
    
    $stmt->execute();
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'testimonials' => $testimonials
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load testimonials'
    ]);
}