<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

include '../config/database.php';

// Verifica se o ID foi passado
if (!isset($_GET['id'])) {
  header("Location: index.php");
  exit();
}

$id = $_GET['id'];

// Exclui a métrica
$stmt = $pdo->prepare("DELETE FROM metricas_vendas WHERE id = :id");
$stmt->execute(['id' => $id]);

header("Location: index.php");
exit();
