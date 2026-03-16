<?php
include_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM students ORDER BY student_code ASC");
        $students = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $students]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
// For POST (add new student)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['student_code']) || !isset($data['first_name']) || !isset($data['last_name'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO students (student_code, first_name, last_name) VALUES (?, ?, ?)");
        $stmt->execute([$data['student_code'], $data['first_name'], $data['last_name']]);
        echo json_encode(['success' => true, 'message' => 'Student added successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
