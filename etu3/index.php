<?php
include 'connect.php';
session_start();

if (!empty($_SESSION['student_id'])) {
  header("Location: dashboard.php");
  exit;
}

header("Location: login.php");
exit;