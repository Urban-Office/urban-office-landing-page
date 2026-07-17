<?php
/**
 * Admin CKEditor Image Upload Handler
 */

require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['upload'])) {
    $file = $_FILES['upload'];
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    if ($fileError === UPLOAD_ERR_OK) {
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Allowed extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // Generate unique filename
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            
            // Upload directory
            $uploadDir = dirname(dirname(__FILE__)) . '/assets/images/blog/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $destPath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $url = BASE_URL . 'assets/images/blog/' . $newFileName;
                
                // Return success JSON for CKEditor
                echo json_encode([
                    'uploaded' => true,
                    'url' => $url
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'uploaded' => false,
                'error' => [
                    'message' => 'Jenis file tidak didukung. Hanya mendukung JPG, JPEG, PNG, GIF, dan WEBP.'
                ]
            ]);
            exit;
        }
    }
}

echo json_encode([
    'uploaded' => false,
    'error' => [
        'message' => 'Terjadi kesalahan saat mengunggah file.'
    ]
]);
exit;
