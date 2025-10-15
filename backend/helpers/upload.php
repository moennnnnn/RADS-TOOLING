<?php

/**
 * Image/Video Upload Helper
 * Validates and processes file uploads with security measures
 */

/**
 * Upload and validate a file (image or video)
 * 
 * @param array $file The uploaded file from $_FILES
 * @param string $group The group/category for organizing uploads
 * @return array Result with success status and file info
 */
function processFileUpload($file, $group = 'general')
{  // CHANGED: renamed function
    // Configuration
    $maxFileSize = 10 * 1024 * 1024; // 10 MB
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov', 'avi'];
    $allowedMimes = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo'
    ];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Upload error: ' . getUploadErrorMessage($file['error'])
        ];
    }

    // Validate file size
    if ($file['size'] > $maxFileSize) {
        return [
            'success' => false,
            'message' => 'File size exceeds 10MB limit'
        ];
    }

    // Validate file exists
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return [
            'success' => false,
            'message' => 'Invalid uploaded file'
        ];
    }

    // Get file extension
    $originalName = basename($file['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Validate extension
    if (!in_array($extension, $allowedExtensions)) {
        return [
            'success' => false,
            'message' => 'Invalid file type. Only JPG, PNG, WebP, MP4, MOV, and AVI are allowed'
        ];
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        return [
            'success' => false,
            'message' => 'Invalid file MIME type: ' . $mimeType
        ];
    }

    // FIX: Calculate base directory correctly
    // We're in /backend/helpers/, need to go up 2 levels to project root
    $baseDir = dirname(dirname(__DIR__));
    $uploadDir = $baseDir . '/uploads/cms/' . sanitizeGroupName($group) . '/';

    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return [
                'success' => false,
                'message' => 'Failed to create upload directory'
            ];
        }
    }

    // Verify directory is writable
    if (!is_writable($uploadDir)) {
        return [
            'success' => false,
            'message' => 'Upload directory is not writable: ' . $uploadDir
        ];
    }

    // Generate unique filename
    $uniqueName = generateUniqueFileName($originalName, $extension);
    $filePath = $uploadDir . $uniqueName;
    $relativePath = '/RADS-TOOLING/uploads/cms/' . sanitizeGroupName($group) . '/' . $uniqueName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        return [
            'success' => false,
            'message' => 'Failed to move uploaded file'
        ];
    }

    // Get dimensions for images only
    $width = 0;
    $height = 0;
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
        $dimensions = @getimagesize($filePath);
        $width = $dimensions ? $dimensions[0] : 0;
        $height = $dimensions ? $dimensions[1] : 0;

        // Optionally create WebP version if not already WebP
        if ($extension !== 'webp' && function_exists('imagewebp')) {
            createWebPVersion($filePath, $extension);
        }
    }

    return [
        'success' => true,
        'file_path' => $relativePath,
        'file_name' => $uniqueName,
        'mime' => $mimeType,
        'width' => $width,
        'height' => $height
    ];
}

/**
 * Generate a unique filename
 */
function generateUniqueFileName($originalName, $extension)
{
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($originalName, PATHINFO_FILENAME));
    $safeName = substr($safeName, 0, 30); // Limit length

    return $safeName . '_' . $timestamp . '_' . $random . '.' . $extension;
}

/**
 * Sanitize group name for directory creation
 */
function sanitizeGroupName($group)
{
    return preg_replace('/[^a-zA-Z0-9_-]/', '', $group);
}

/**
 * Get human-readable upload error message
 */
function getUploadErrorMessage($errorCode)
{
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
    ];

    return $errors[$errorCode] ?? 'Unknown upload error';
}

/**
 * Create WebP version of an image
 */
function createWebPVersion($filePath, $extension)
{
    try {
        $image = null;

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = @imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $image = @imagecreatefrompng($filePath);
                break;
        }

        if (!$image) {
            return false;
        }

        $webpPath = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $filePath);
        $result = imagewebp($image, $webpPath, 85);
        imagedestroy($image);

        return $result;
    } catch (Exception $e) {
        return false;
    }
}
?>