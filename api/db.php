<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$db_file = __DIR__ . '/../database.sqlite';

try {
    $pdo = new PDO("sqlite:" . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create tables if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_code TEXT NOT NULL UNIQUE,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS subjects (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_code TEXT NOT NULL UNIQUE,
        subject_name TEXT NOT NULL,
        credits INTEGER NOT NULL DEFAULT 3
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS scores (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        subject_id INTEGER NOT NULL,
        score INTEGER NOT NULL DEFAULT 0,
        FOREIGN KEY (student_id) REFERENCES students(id),
        FOREIGN KEY (subject_id) REFERENCES subjects(id),
        UNIQUE(student_id, subject_id)
    )");

    // Insert mock data if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO students (student_code, first_name, last_name) VALUES 
            ('660101', 'สมชาย', 'ใจดี'),
            ('660102', 'สมหญิง', 'รักเรียน'),
            ('660103', 'สมศักดิ์', 'ขยันยิ่ง'),
            ('660104', 'มานะ', 'มานี'),
            ('660105', 'ปิติ', 'ชูใจ')
        ");
        
        $pdo->exec("INSERT INTO subjects (subject_code, subject_name, credits) VALUES 
            ('MATH101', 'คณิตศาสตร์พื้นฐาน', 3),
            ('SCI101', 'วิทยาศาสตร์', 3),
            ('ENG101', 'ภาษาอังกฤษ', 2),
            ('THAI101', 'ภาษาไทย', 2)
        ");
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}
?>
