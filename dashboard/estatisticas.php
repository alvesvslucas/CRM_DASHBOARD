<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

include '../config/database.php';

// Inicializando variáveis para evitar "Undefined variable"
$total_vendas_atual = 0;
$total_vendas_anterior = 0;
$meta_total_mes = 1; // Evita divisão por zero
$meta_atingida = 0;
$variacao_percentual = 0;
$estimativa_fechamento = 0;

// Consulta para calcular estatísticas por setor
$stmt = $pdo->query("
  SELECT 
    setor,
    COALESCE(SUM(total_vendas), 0) AS total_vendas,
    COALESCE(SUM(meta_total), 0) AS total_meta,
    CASE 
      WHEN SUM(meta_total) > 0 THEN (SUM(total_vendas) / SUM(meta_total)) * 100
      ELSE 0 
    END AS percentual_atingido
  FROM metricas_vendas
  GROUP BY setor
  ORDER BY percentual_atingido ASC
");

$estatisticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para calcular KPIs e metas
$kpiStmt = $pdo->query("
  SELECT 
    (SELECT setor FROM metricas_vendas 
     GROUP BY setor 
     ORDER BY SUM(total_vendas) DESC LIMIT 1) AS setor_mais_produtivo,

    (SELECT COALESCE(SUM(total_vendas), 0) FROM metricas_vendas 
     WHERE MONTH(data) = MONTH(CURRENT_DATE()) AND YEAR(data) = YEAR(CURRENT_DATE())) AS total_vendas_mes_atual,

    (SELECT COALESCE(SUM(total_vendas), 0) FROM metricas_vendas 
     WHERE MONTH(data) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(data) = YEAR(CURRENT_DATE())) AS total_vendas_mes_anterior,

    (SELECT COALESCE(SUM(meta_total), 1) FROM metricas_vendas 
     WHERE MONTH(data) = MONTH(CURRENT_DATE()) AND YEAR(data) = YEAR(CURRENT_DATE())) AS meta_total_mes
");

$kpi = $kpiStmt->fetch(PDO::FETCH_ASSOC);

$total_vendas_atual = $kpi['total_vendas_mes_atual'] ?? 0;
$total_vendas_anterior = $kpi['total_vendas_mes_anterior'] ?? 0;
$meta_total_mes = $kpi['meta_total_mes'] ?? 1;

$meta_atingida = ($total_vendas_atual / $meta_total_mes) * 100;
$variacao_percentual = ($total_vendas_anterior > 0) ? (($total_vendas_atual - $total_vendas_anterior) / $total_vendas_anterior) * 100 : 0;

// Estimativa de Fechamento do Mês
$dias_corridos = date('j');
$dias_no_mes = date('t');
$estimativa_fechamento = ($total_vendas_atual / max($dias_corridos, 1)) * $dias_no_mes; // Evita divisão por zero

// Consulta para setores abaixo da meta (menos de 80% atingido)
$alertaStmt = $pdo->query("
  SELECT 
    setor,
    CASE 
      WHEN SUM(meta_total) > 0 THEN (SUM(total_vendas) / SUM(meta_total)) * 100
      ELSE 0 
    END AS percentual_atingido
  FROM metricas_vendas
  GROUP BY setor
  HAVING percentual_atingido < 80
");
$setores_abaixo_meta = $alertaStmt->fetchAll(PDO::FETCH_ASSOC);

// Média diária do mês
$mediaDiariaStmt = $pdo->query("
  SELECT AVG(vendas) AS media_diaria
  FROM (
    SELECT SUM(total_vendas) AS vendas
    FROM metricas_vendas
    WHERE MONTH(data) = MONTH(CURRENT_DATE()) 
    GROUP BY data
  ) AS vendas_diarias
");

$media_diaria = $mediaDiariaStmt->fetch(PDO::FETCH_ASSOC)['media_diaria'] ?? 0;

// Identificar dias com vendas abaixo da média
$diasBaixosStmt = $pdo->query("
  SELECT DATE_FORMAT(data, '%d/%m') AS dia, SUM(total_vendas) AS vendas
  FROM metricas_vendas
  WHERE MONTH(data) = MONTH(CURRENT_DATE()) 
  GROUP BY data
  HAVING SUM(total_vendas) < $media_diaria
  ORDER BY data ASC
");

$dias_abaixo_media = $diasBaixosStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Estatísticas - CRM</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Limitar a largura do container para evitar que o site se estique */
    .container {
      max-width: 1200px;
    }
    .chart-container {
      width: 100%;
      max-width: 450px;
      height: 280px;
      margin: auto;
    }
    .kpi-card {
      text-align: center;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .alert {
      border-radius: 10px;
      padding: 15px;
    }
    .alert-warning {
      background-color: #fff3cd;
      border-left: 5px solid #ff9800;
    }
    .alert-danger {
      background-color: #f8d7da;
      border-left: 5px solid #dc3545;
    }
    .alert-info {
      background-color: #d1ecf1;
      border-left: 5px solid #17a2b8;
    }
  </style>
</head>

<body>
  <div class="container mt-4">
    <h2 class="text-center">Painel de Estatísticas</h2>

    <div class="d-flex justify-content-between mb-3">
      <a href="index.php" class="btn btn-secondary">Voltar</a>
      <a href="../auth/logout.php" class="btn btn-danger">Sair</a>
    </div>
    <!-- Alertas de Desempenho -->
    <div class="row text-center mb-4">
      <div class="col-md-12">
        <div class="alert alert-warning">
          <h5><strong>⚠️ Alertas de Desempenho</strong></h5>
        </div>
      </div>
    </div>

    <!-- Setores abaixo da meta -->
    <?php if (!empty($setores_abaixo_meta)): ?>
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="alert alert-danger">
            <h6><strong>Setores abaixo da meta (menos de 80% atingido):</strong></h6>
            <ul>
              <?php foreach ($setores_abaixo_meta as $setor): ?>
                <li><?= ucfirst($setor['setor']) ?> → <?= number_format($setor['percentual_atingido'], 2, ',', '.') ?>% da meta</li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Dias com vendas abaixo da média -->
    <?php if (!empty($dias_abaixo_media)): ?>
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="alert alert-warning">
            <h6><strong>Dias com vendas abaixo da média (<?= number_format($media_diaria, 2, ',', '.') ?> R$):</strong></h6>
            <ul>
              <?php foreach ($dias_abaixo_media as $dia): ?>
                <li><?= $dia['dia'] ?> → R$ <?= number_format($dia['vendas'], 2, ',', '.') ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endif; ?>
    <?php if (!empty($setores_abaixo_meta) || !empty($dias_abaixo_media)): ?>
      <div class="row mb-4">
        <div class="col-md-12">
          <div class="alert alert-info">
            <h6><strong>📢 Recomendações para Melhorar o Desempenho:</strong></h6>
            <ul>
              <?php if (!empty($setores_abaixo_meta)): ?>
                <li>Incentivar campanhas de marketing para os setores abaixo da meta.</li>
                <li>Reforçar treinamento para os consultores dos setores com baixa performance.</li>
              <?php endif; ?>
              <?php if (!empty($dias_abaixo_media)): ?>
                <li>Identificar fatores que afetaram as vendas nos dias de baixo desempenho.</li>
                <li>Oferecer promoções em dias estratégicos para aumentar as vendas.</li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Painel de KPIs -->
    <div class="row text-center mb-4">
      <div class="col-md-4">
        <div class="kpi-card bg-light">
          <h5>% da Meta Atingida</h5>
          <h3 class="text-primary"><?= number_format($meta_atingida, 2, ',', '.') ?>%</h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-card bg-light">
          <h5>Estimativa de Fechamento do Mês</h5>
          <h3 class="text-success">R$ <?= number_format($estimativa_fechamento, 2, ',', '.') ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-card bg-light">
          <h5>Variação em Relação ao Mês Anterior</h5>
          <h3 class="<?= $variacao_percentual >= 0 ? 'text-success' : 'text-danger' ?>">
            <?= number_format($variacao_percentual, 2, ',', '.') ?>%
          </h3>
        </div>
      </div>
    </div>

    <!-- Painel de Setor Mais Produtivo -->
    <div class="row text-center mb-4">
      <div class="col-md-12">
        <div class="kpi-card bg-light">
          <h5>Setor Mais Produtivo</h5>
          <h3 class="text-primary"><?= ucfirst($kpi['setor_mais_produtivo'] ?? 'Nenhum') ?></h3>
        </div>
      </div>
    </div>

    <!-- Gráficos Responsivos -->
    <div class="row mt-4">
      <div class="col-md-6 d-flex justify-content-center mb-4">
        <div class="chart-container">
          <h5 class="text-center">Participação de Vendas</h5>
          <canvas id="graficoPizza"></canvas>
        </div>
      </div>
      <div class="col-md-6 d-flex justify-content-center mb-4">
        <div class="chart-container">
          <h5 class="text-center">Progresso das Metas (%)</h5>
          <canvas id="graficoProgresso"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    let setores = <?= json_encode(array_column($estatisticas, "setor")) ?>;
    let vendas = <?= json_encode(array_map(fn($v) => floatval($v['total_vendas']), $estatisticas)) ?>;
    let progresso = <?= json_encode(array_map(fn($v) => floatval($v['percentual_atingido']), $estatisticas)) ?>;

    // Gráfico de Pizza
    new Chart(document.getElementById("graficoPizza").getContext("2d"), {
      type: "pie",
      data: {
        labels: setores,
        datasets: [{
          data: vendas,
          backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0"]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });

    // Gráfico de Barras para Progresso das Metas
    new Chart(document.getElementById("graficoProgresso").getContext("2d"), {
      type: "bar",
      data: {
        labels: setores,
        datasets: [{
          label: "Progresso (%)",
          data: progresso,
          backgroundColor: "#36A2EB"
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100
          }
        }
      }
    });
  </script>
</body>

</html>
    