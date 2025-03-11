<?php
include '../config/database.php';

// Busca os dados do banco
$stmt = $pdo->query("SELECT data, setor, meta_total, total_vendas FROM metricas_vendas ORDER BY data ASC");
$metricas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiza os dados para JSON
$dados = [];
foreach ($metricas as $metrica) {
  $dados[$metrica['setor']]['datas'][] = $metrica['data'];
  $dados[$metrica['setor']]['meta'][] = $metrica['meta_total'];
  $dados[$metrica['setor']]['vendas'][] = $metrica['total_vendas'];
}

// Retorna os dados em JSON
header('Content-Type: application/json');
echo json_encode($dados);
