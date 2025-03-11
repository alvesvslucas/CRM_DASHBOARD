<?php
include '../config/database.php';

// Consulta para identificar alertas
$alertas = [];

// Verifica setores abaixo de 80% da meta
$stmt = $pdo->query("
  SELECT setor, 
         SUM(total_vendas) AS total_vendas, 
         SUM(meta_total) AS meta_total,
         CASE 
           WHEN SUM(meta_total) > 0 THEN (SUM(total_vendas) / SUM(meta_total)) * 100
           ELSE 0 
         END AS percentual_atingido
  FROM metricas_vendas
  WHERE MONTH(data) = MONTH(CURRENT_DATE()) 
  GROUP BY setor
  HAVING percentual_atingido < 80
  ORDER BY percentual_atingido ASC
");
$setores_abaixo_meta = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($setores_abaixo_meta as $setor) {
    $alertas[] = [
        "tipo" => "alerta",  // ou "warning"
        "mensagem" => "⚠️ Setor " . ucfirst($setor['setor']) . " abaixo da meta: " 
            . number_format($setor['percentual_atingido'], 2, ',', '.') . "%"
    ];
}

// Verifica metas atingidas (≥ 100%)
$stmt = $pdo->query("
  SELECT setor, 
         SUM(total_vendas) AS total_vendas, 
         SUM(meta_total) AS meta_total,
         CASE 
           WHEN SUM(meta_total) > 0 THEN (SUM(total_vendas) / SUM(meta_total)) * 100
           ELSE 0 
         END AS percentual_atingido
  FROM metricas_vendas
  WHERE MONTH(data) = MONTH(CURRENT_DATE()) 
  GROUP BY setor
  HAVING percentual_atingido >= 100
  ORDER BY percentual_atingido DESC
");
$setores_meta_atingida = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($setores_meta_atingida as $setor) {
    $alertas[] = [
        "tipo" => "sucesso",  // ou "success"
        "mensagem" => "🎉 Parabéns! Setor " . ucfirst($setor['setor']) 
            . " atingiu " . number_format($setor['percentual_atingido'], 2, ',', '.') . "% da meta!"
    ];
}

// Retorna os alertas em formato JSON
header('Content-Type: application/json');
echo json_encode($alertas);
?>
