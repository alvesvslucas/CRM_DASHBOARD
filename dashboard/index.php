<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

include '../config/database.php';

// Configuração de filtros
$where = [];
$params = [];

if (!empty($_GET['data_inicial']) && !empty($_GET['data_final'])) {
  $where[] = "data BETWEEN :data_inicial AND :data_final";
  $params['data_inicial'] = $_GET['data_inicial'];
  $params['data_final'] = $_GET['data_final'];
}

if (!empty($_GET['setor'])) {
  $where[] = "setor = :setor";
  $params['setor'] = $_GET['setor'];
}

$sql = "SELECT * FROM metricas_vendas";
if ($where) {
  $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$metricas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - CRM</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-4">
    <h2 class="text-center">Painel de Alimentação de Dados</h2>

    <div class="d-flex justify-content-between mb-3">
      <a href="../auth/logout.php" class="btn btn-danger">Sair</a>
      <a href="estatisticas.php" class="btn btn-info">Ver Estatísticas</a>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMetricModal">Adicionar Métrica</button>
    </div>

    <!-- Formulário de Filtros -->
    <form method="GET" class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Data Inicial:</label>
        <input type="date" name="data_inicial" class="form-control" value="<?= $_GET['data_inicial'] ?? '' ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Data Final:</label>
        <input type="date" name="data_final" class="form-control" value="<?= $_GET['data_final'] ?? '' ?>">
      </div>

      <div class="col-md-3">
        <label class="form-label">Setor:</label>
        <select name="setor" class="form-control">
          <option value="">Todos</option>
          <option value="cartão" <?= ($_GET['setor'] ?? '') === 'cartão' ? 'selected' : '' ?>>Cartão</option>
          <option value="consignado" <?= ($_GET['setor'] ?? '') === 'consignado' ? 'selected' : '' ?>>Consignado</option>
          <option value="energia" <?= ($_GET['setor'] ?? '') === 'energia' ? 'selected' : '' ?>>Energia</option>
          <option value="fgts" <?= ($_GET['setor'] ?? '') === 'fgts' ? 'selected' : '' ?>>FGTS</option>
        </select>
      </div>

      <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
      </div>
    </form>

    <div class="mt-3">
      <a href="exportar.php?formato=csv" class="btn btn-warning">Exportar para CSV</a>
      <a href="exportar.php?formato=excel" class="btn btn-success">Exportar para Excel</a>
    </div>

    <!-- Tabela Responsiva -->
    <div class="table-responsive mt-3">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Data</th>
            <th>Setor</th>
            <th>Meta Total</th>
            <th>Meta Diária</th>
            <th>Total Vendas</th>
            <th>% Vendas</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($metricas as $metrica): ?>
            <tr>
              <td><?= date('d/m/Y', strtotime($metrica['data'])) ?></td>
              <td><?= ucfirst($metrica['setor']) ?></td>
              <td>R$ <?= number_format($metrica['meta_total'], 2, ',', '.') ?></td>
              <td>R$ <?= number_format($metrica['meta_diaria'], 2, ',', '.') ?></td>
              <td>R$ <?= number_format($metrica['total_vendas'], 2, ',', '.') ?></td>
              <td><?= $metrica['percentual_vendas'] ?>%</td>
              <td>
                <a href="editar.php?id=<?= $metrica['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                <a href="deletar.php?id=<?= $metrica['id'] ?>" class="btn btn-danger btn-sm">Excluir</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal de Adição -->
  <div class="modal fade" id="addMetricModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Adicionar Nova Métrica</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form action="salvar.php" method="POST">
            <div class="mb-3">
              <label class="form-label">Data</label>
              <input type="date" name="data" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Setor</label>
              <select name="setor" class="form-control" required>
                <option value="cartão">Cartão</option>
                <option value="consignado">Consignado</option>
                <option value="energia">Energia</option>
                <option value="fgts">FGTS</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Meta Total</label>
              <input type="number" step="0.01" name="meta_total" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Meta Diária</label>
              <input type="number" step="0.01" name="meta_diaria" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Total Vendas</label>
              <input type="number" step="0.01" name="total_vendas" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Salvar</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>