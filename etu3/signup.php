<?php
include 'connect.php';   
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $phone     = trim($_POST['phone'] ?? '');
  $major     = trim($_POST['major'] ?? '');
  $password  = (string)($_POST['password'] ?? '');
  $confirm   = (string)($_POST['confirm'] ?? '');

  if ($full_name === '') $errors[] = "Nom complet obligatoire.";
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
  if (strlen($password) < 6) $errors[] = "Mot de passe minimum 6 caractères.";
  if ($password !== $confirm) $errors[] = "Confirmation incorrecte.";

  if (!$errors) {
    $st = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $st->execute([$email]);

    if ($st->fetch()) {
      $errors[] = "Cet email existe déjà.";
    } else {
      $hash = password_hash($password, PASSWORD_BCRYPT);

      $ins = $pdo->prepare("
        INSERT INTO students(full_name, email, password_hash, phone, major)
        VALUES(?,?,?,?,?)
      ");
      $ins->execute([$full_name, $email, $hash, $phone ?: null, $major ?: null]);

      $_SESSION['student_id'] = (int)$pdo->lastInsertId();
      header("Location: dashboard.php");
      exit;
    }
  }
}

$html = file_get_contents(__DIR__ . '/signup.html');

$errHtml = '';
if ($errors) {
  $errHtml = '<div class="alert"><ul>';
  foreach ($errors as $e) $errHtml .= '<li>' . htmlspecialchars($e) . '</li>';
  $errHtml .= '</ul></div>';
}

echo str_replace('{{ERRORS}}', $errHtml, $html);