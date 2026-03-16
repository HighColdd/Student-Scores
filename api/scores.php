<?php
include_once 'db.php';
header('Content-Type: application/json');

function calculateGrade($score) {
    if ($score >= 80) return '4';
    if ($score >= 75) return '3.5';
    if ($score >= 70) return '3';
    if ($score >= 65) return '2.5';
    if ($score >= 60) return '2';
    if ($score >= 55) return '1.5';
    if ($score >= 50) return '1';
    return '0';
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
    
    if ($subject_id === 0) {
        echo json_encode(['success' => false, 'error' => 'Missing subject_id']);
        exit;
    }

    try {
        // Get all students and left join with scores for the specific subject
        $sql = "
            SELECT 
                st.id as student_id,
                st.student_code,
                st.first_name,
                st.last_name,
                COALESCE(sc.score, 0) as score
            FROM students st
            LEFT JOIN scores sc ON st.id = sc.student_id AND sc.subject_id = ?
            ORDER BY st.student_code ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$subject_id]);
        $results = $stmt->fetchAll();
        
        $data_with_grades = [];
        foreach ($results as $row) {
            $row['grade'] = calculateGrade($row['score']);
            $data_with_grades[] = $row;
        }

        echo json_encode(['success' => true, 'data' => $data_with_grades]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
// For POST (save multiple scores at once)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['subject_id']) || !isset($data['scores']) || !is_array($data['scores'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid data format']);
        exit;
    }
    
    $subject_id = (int)$data['subject_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Prepare statement for Insert or Replace
        $stmt = $pdo->prepare("
            INSERT INTO scores (student_id, subject_id, score) 
            VALUES (?, ?, ?)
            ON CONFLICT(student_id, subject_id) 
            DO UPDATE SET score = excluded.score
        ");
        
        foreach ($data['scores'] as $item) {
            $student_id = (int)$item['student_id'];
            $score = (int)$item['score'];
            // Ensure score is within valid bounds (0-100)
            if ($score < 0) $score = 0;
            if ($score > 100) $score = 100;
            
            $stmt->execute([$student_id, $subject_id, $score]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Scores saved successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
