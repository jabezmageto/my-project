<?php
include 'config.php';
include 'session.php';

// Handle property upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_property'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image = $_FILES['image']['name'];
    
    // Move uploaded image to uploads directory
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);

    // Insert property into the properties table
    $sql_insert = "INSERT INTO properties (admin_id, title, description, image, price) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param('isssd', $_SESSION['user_id'], $title, $description, $image, $price);
    $stmt_insert->execute();
    $stmt_insert->close();

    // Update the property count in the users table
    $sql_update_count = "UPDATE users SET property_count = property_count + 1 WHERE id = ?";
    $stmt_update_count = $conn->prepare($sql_update_count);
    $stmt_update_count->bind_param('i', $_SESSION['user_id']);
    $stmt_update_count->execute();
    $stmt_update_count->close();
    
    // Redirect to the properties count page
    header("Location: view.php");
    exit();
}
?>
