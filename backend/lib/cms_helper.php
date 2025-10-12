<?php
/**
 * CMS Helper Functions
 */

function getCMSContent($pageKey)
{
    // Check preview mode
    if (isset($GLOBALS['cms_preview_content'])) {
        return $GLOBALS['cms_preview_content'];
    }

    global $pdo;

    $stmt = $pdo->prepare("
        SELECT content_data 
        FROM rt_cms_pages 
        WHERE page_key = ? AND status = 'published'
        ORDER BY updated_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$pageKey]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return json_decode($result['content_data'], true);
    }

    return ['content' => '<p>Content not available</p>'];
}

function isPreviewMode()
{
    return isset($GLOBALS['cms_preview_content']);
}