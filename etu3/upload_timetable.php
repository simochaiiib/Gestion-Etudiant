<?php
include 'connect.php';
session_start();

if (empty($_SESSION['student_id'])) {
  header("Location: login.php");
  exit;
}

if (!isset($_FILES['timetable_image']) || $_FILES['timetable_image']['error'] !== UPLOAD_ERR_OK) {
  die("Erreur upload image.");
}

$student_id = (int)$_SESSION['student_id'];

$tmp  = $_FILES['timetable_image']['tmp_name'];
$name = $_FILES['timetable_image']['name'];

$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png'];

if (!in_array($ext, $allowed, true)) {
  die("Format interdit. Utilise JPG/PNG.");
}

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

$newName = 'emploi_' . $student_id . '_' . time() . '.' . $ext;
$relativePath = 'uploads/' . $newName;

if (!move_uploaded_file($tmp, __DIR__ . '/' . $relativePath)) {
  die("Impossible de déplacer l'image.");
}

// supprimer ancienne image (1 seule)
$old = $pdo->prepare("SELECT image_path FROM timetable_image WHERE student_id=? ORDER BY uploaded_at DESC LIMIT 1");
$old->execute([$student_id]);
$oldRow = $old->fetch(PDO::FETCH_ASSOC);

if ($oldRow && !empty($oldRow['image_path'])) {
  $oldFile = __DIR__ . '/' . $oldRow['image_path'];
  if (is_file($oldFile)) @unlink($oldFile);
  $pdo->prepare("DELETE FROM timetable_image WHERE student_id=?")->execute([$student_id]);
}

$ins = $pdo->prepare("INSERT INTO timetable_image(student_id, image_path) VALUES(?, ?)");
$ins->execute([$student_id, $relativePath]);

header("Location: dashboard.php");
exit;