<?php
include_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM subjects ORDER BY subject_code ASC");
        $subjects = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $subjects]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
// For POST (add new subject)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['subject_code']) || !isset($data['subject_name'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    $credits = isset($data['credits']) ? (int)$data['credits'] : 3;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, credits) VALUES (?, ?, ?)");
        $stmt->execute([$data['subject_code'], $data['subject_name'], $credits]);
        echo json_encode(['success' => true, 'message' => 'Subject added successfully', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
