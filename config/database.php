<?php
$host = "localhost";
$dbname = "crm_dashboard";
$username = "root";  // Alterar se necessário
$password = "";      // Alterar se necessário

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro na conexão: " . $e->getMessage());
}
