<?php
header('Content-Type: application/json');

$targetDir = "images/uploadedbyUsers/";
$jsonFile = "gallery.json";

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$uploadedFiles = [];
$errors = [];

if (isset($_FILES['files'])) {
    $totalFiles = count($_FILES['files']['name']);

    for ($i = 0; $i < $totalFiles; $i++) {
        $fileName = basename($_FILES['files']['name'][$i]);
        $uniqueName = time() . '_' . rand(100, 999) . '_' . $fileName;
        $targetFilePath = $targetDir . $uniqueName;
        
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov');

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $targetFilePath)) {
                $uploadedFiles[] = $targetFilePath;
            } else {
                $errors[] = "Failed to upload " . $fileName;
            }
        } else {
            $errors[] = "Invalid file type: " . $fileName;
        }
    }

    if (count($uploadedFiles) > 0) {
        // Read existing gallery list
        $existingGallery = [];
        if (file_exists($jsonFile)) {
            $existingData = file_get_contents($jsonFile);
            $existingGallery = json_decode($existingData, true) ?? [];
        }

        // Add newly uploaded files to top of gallery
        $updatedGallery = array_merge($uploadedFiles, $existingGallery);
        file_put_contents($jsonFile, json_encode($updatedGallery, JSON_PRETTY_PRINT));

        echo json_encode(['status' => 'success', 'files' => $uploadedFiles, 'errors' => $errors]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Upload failed.', 'errors' => $errors]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No files sent.']);
}
?>
