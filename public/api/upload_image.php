<?php
require_once "../../src/config/db.php";
require_once "../../src/lib/security.php";
require_once "../../src/lib/FileUploader.php";

header("Content-Type: application/json");

try {
    // Check authentication
    session_start();
    if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
        throw new Exception("Unauthorized");
    }

    if (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] !== UPLOAD_ERR_OK
    ) {
        throw new Exception("Image upload failed");
    }

    $uploader = new FileUploader();
    $imagePath = $uploader->uploadImage($_FILES["image"]);

    echo json_encode([
        "success" => true,
        "url" => $imagePath,
        "size" => $_FILES["image"]["size"],
        "message" => "Image uploaded successfully",
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage(),
    ]);
}
?>
