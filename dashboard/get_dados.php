<?php
include '../config/database.php';

/**
 * 1) PARTICIPAÇÃO DE VENDAS (Gráfico de Pizza)
 * Soma as vendas por setor.
 */
$stmt1 = $pdo->query("
  SELECT 
    setor,
    COALESCE(SUM(total_vendas), 0) AS total_vendas
  FROM metricas_vendas
  GROUP BY setor
  ORDER BY setor
");
$participacaoRows = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$labelsParticipacao = [];
$valuesParticipacao = [];
foreach ($participacaoRows as $row) {
    $labelsParticipacao[] = ucfirst($row['setor']);
    $valuesParticipacao[] = floatval($row['total_vendas']);
}
$participacao = [
  "labels" => $labelsParticipacao,
  "values" => $valuesParticipacao
];

/**
 * 2) PROGRESSO DAS METAS (Gráfico de Barras)
 * Calcula o percentual atingido (total_vendas / meta_total * 100).
 */
$stmt2 = $pdo->query("
  SELECT 
    setor,
    CASE WHEN SUM(meta_total) > 0 THEN (SUM(total_vendas)/SUM(meta_total)) * 100
         ELSE 0
    END AS percentual_atingido
  FROM metricas_vendas
  GROUP BY setor
  ORDER BY setor
");
$progressoRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$labelsProgresso = [];
$valuesProgresso = [];
foreach ($progressoRows as $row) {
    $labelsProgresso[] = ucfirst($row['setor']);
    $valuesProgresso[] = floatval($row['percentual_atingido']);
}
$progresso = [
  "labels" => $labelsProgresso,
  "values" => $valuesProgresso
];

/**
 * 3) TENDÊNCIA DE CRESCIMENTO (Gráfico de Linhas)
 * Para demonstração, vamos usar os mesmos dados da participação,
 * mas você pode criar outra lógica (ex: evolução diária).
 */
$tendencia = [
  "labels" => $labelsParticipacao,
  "values" => $valuesParticipacao
];

// Montando o array final para JSON
$data = [
  "participacao" => $participacao,
  "progresso"    => $progresso,
  "tendencia"    => $tendencia
];

// Retornando como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
