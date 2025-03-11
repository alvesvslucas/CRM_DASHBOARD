<?php
include '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $data = $_POST['data'];
  $setor = $_POST['setor'];
  $meta_total = $_POST['meta_total'];
  $meta_diaria = $_POST['meta_diaria'];
  $total_vendas = $_POST['total_vendas'];

  $percentual_vendas = ($total_vendas / $meta_total) * 100;
  $deficit = $meta_total - $total_vendas;
  $nova_meta_diaria = $deficit / 24;  // Ajustar dinamicamente

  $stmt = $pdo->prepare("INSERT INTO metricas_vendas (data, setor, meta_total, meta_diaria, total_vendas, percentual_vendas, deficit, nova_meta_diaria) 
                          VALUES (:data, :setor, :meta_total, :meta_diaria, :total_vendas, :percentual_vendas, :deficit, :nova_meta_diaria)");

  $stmt->execute([
    'data' => $data,
    'setor' => $setor,
    'meta_total' => $meta_total,
    'meta_diaria' => $meta_diaria,
    'total_vendas' => $total_vendas,
    'percentual_vendas' => $percentual_vendas,
    'deficit' => $deficit,
    'nova_meta_diaria' => $nova_meta_diaria
  ]);

  header("Location: index.php");
  exit();
}
