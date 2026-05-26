<?php
// backend.php

// 1. CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// 2. Handle Preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit();
}

// 3. Database Connection
require_once 'db.php';

// 4. API Logic
$input = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? trim($input['password']) : '';
    $username = isset($input['username']) ? trim($input['username']) : '';

    // ==========================================
    // SIGNUP ENDPOINT (CREATE = INSERT)
    // ==========================================
    if ($action === 'signup') {
        if (empty($email) || empty($password) || empty($username)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request: Username, Email and Password are required.']);
            exit();
        }

        try {
            // Parameterized Query to prevent SQL Injection
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            $newId = $db->lastInsertId();
            http_response_code(201); // 201 Created
            echo json_encode(['message' => 'User registered successfully!', 'userId' => $newId]);
            
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation (UNIQUE)
                http_response_code(400); // 400 Bad Request
                echo json_encode(['error' => 'Username or Email is already registered.']);
            } else {
                http_response_code(500); // 500 Internal Error
                echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
            }
        }
    } 
    // ==========================================
    // LOGIN ENDPOINT (READ = SELECT)
    // ==========================================
    elseif ($action === 'login') {
        // Basic Validation
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request: Email and Password are required.']);
            exit();
        }

        try {
            // Parameterized Query
            $stmt = $db->prepare("SELECT id, username FROM users WHERE email = :email AND password = :password");
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                http_response_code(200); // 200 OK
                echo json_encode(['message' => 'Login successful!', 'userId' => $user['id'], 'username' => $user['username']]);
            } else {
                http_response_code(401); // 401 Unauthorized
                echo json_encode(['error' => '401 Unauthorized: Invalid email or password.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server Error: ' . $e->getMessage()]);
        }
    } 
    else {
        http_response_code(404);
        echo json_encode(['error' => '404 Not Found: Action not recognized.']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => '404 Not Found. Only POST requests allowed for this endpoint.']);
}
?>
