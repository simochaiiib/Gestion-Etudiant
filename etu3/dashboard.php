<?php
include 'connect.php';
session_start();

function esc($v): string {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['student_id'])) {
  header("Location: login.php");
  exit;
}

$student_id = (int)$_SESSION['student_id'];

$st = $pdo->prepare("SELECT id, full_name, email, phone, major, created_at FROM students WHERE id = ?");
$st->execute([$student_id]);
$s = $st->fetch(PDO::FETCH_ASSOC);

if (!$s) {
  $_SESSION = [];
  session_destroy();
  header("Location: login.php");
  exit;
}

// Notes
$g = $pdo->prepare("SELECT subject, grade, semester FROM grades WHERE student_id = ? ORDER BY id DESC");
$g->execute([$student_id]);
$grades = $g->fetchAll(PDO::FETCH_ASSOC);

$gradesTable = '<p class="muted">Aucune note</p>';
if ($grades) {
  $gradesTable = '<div class="table-wrap"><table><thead><tr>
    <th>Matière</th><th>Note</th><th>Semestre</th><th>Résultat</th>
  </tr></thead><tbody>';

  foreach ($grades as $r) {
    $note = (float)$r['grade'];
    $result = ($note > 10)
      ? '<span style="color:lime;font-weight:bold;">Validé</span>'
      : '<span style="color:#ff5b6a;font-weight:bold;">Non validé</span>';

    $gradesTable .= '<tr>
      <td>' . esc($r['subject']) . '</td>
      <td>' . esc($note) . '/20</td>
      <td>' . esc($r['semester'] ?? '-') . '</td>
      <td>' . $result . '</td>
    </tr>';
  }

  $gradesTable .= '</tbody></table></div>';
}

// Image emploi du temps
$imgStmt = $pdo->prepare("
  SELECT image_path
  FROM timetable_image
  WHERE student_id = ?
  ORDER BY uploaded_at DESC
  LIMIT 1
");
$imgStmt->execute([$student_id]);
$img = $imgStmt->fetch(PDO::FETCH_ASSOC);

$timetableImageHtml = '<p class="muted">Aucune image d’emploi du temps.</p>';
if ($img && !empty($img['image_path'])) {
  $src = esc($img['image_path']);
  $timetableImageHtml = '<img src="' . $src . '" class="timetable-img" alt="Emploi du temps">';
}

// Charger template
$html = file_get_contents(__DIR__ . '/dashboard.html');

$repl = [
  '{{STUDENT_NAME}}'    => esc($s['full_name']),
  '{{FULL_NAME}}'       => esc($s['full_name']),
  '{{EMAIL}}'           => esc($s['email']),
  '{{PHONE}}'           => esc($s['phone'] ?? '-'),
  '{{MAJOR}}'           => esc($s['major'] ?? '-'),
  '{{CREATED_AT}}'      => esc($s['created_at'] ?? '-'),
  '{{GRADES_TABLE}}'    => $gradesTable,
  '{{TIMETABLE_IMAGE}}' => $timetableImageHtml,
];

echo str_replace(array_keys($repl), array_values($repl), $html);