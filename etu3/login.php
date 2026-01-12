<?php
include 'connect.php';
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = (string)($_POST['password'] ?? '');

  $st = $pdo->prepare("SELECT id, password_hash FROM students WHERE email = ?");
  $st->execute([$email]);
  $user = $st->fetch(PDO::FETCH_ASSOC);

  if (!$user || !password_verify($password, $user['password_hash'])) {
    $errors[] = "Email ou mot de passe incorrect.";
  } else {
    $_SESSION['student_id'] = (int)$user['id'];
    header("Location: dashboard.php");
    exit;
  }
}

$html = file_get_contents(__DIR__ . '/login.html');

$errHtml = '';
if ($errors) {
  $errHtml = '<div class="alert"><ul>';
  foreach ($errors as $e) $errHtml .= '<li>' . htmlspecialchars($e) . '</li>';
  $errHtml .= '</ul></div>';
}

echo str_replace('{{ERRORS}}', $errHtml, $html);