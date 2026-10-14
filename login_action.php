<?php
session_start();
header('Content-Type: application/json');

// Include your database connection file
require_once 'includes/db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit();
    }

    // Query database for user
    $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            
            // Set Session Variables
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Determine redirect URL based on role
            $redirectUrl = ($user['role'] === 'admin') ? 'admin/overview.php' : 'index.php';

            echo json_encode([
                'success' => true, 
                'redirect' => $redirectUrl,
                'role' => $user['role']
            ]);
            exit();
        }
    }

    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit();
}