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

// Busca os dados da métrica
$stmt = $pdo->prepare("SELECT * FROM metricas_vendas WHERE id = :id");
$stmt->execute(['id' => $id]);
$metrica = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrar, redireciona
if (!$metrica) {
  header("Location: index.php");
  exit();
}

// Processando a atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $meta_total = $_POST['meta_total'];
  $meta_diaria = $_POST['meta_diaria'];
  $total_vendas = $_POST['total_vendas'];

  $percentual_vendas = ($total_vendas / $meta_total) * 100;
  $deficit = $meta_total - $total_vendas;
  $nova_meta_diaria = $deficit / 24; // Ajuste conforme necessário

  $stmt = $pdo->prepare("UPDATE metricas_vendas SET meta_total = :meta_total, meta_diaria = :meta_diaria, total_vendas = :total_vendas, percentual_vendas = :percentual_vendas, deficit = :deficit, nova_meta_diaria = :nova_meta_diaria WHERE id = :id");

  $stmt->execute([
    'meta_total' => $meta_total,
    'meta_diaria' => $meta_diaria,
    'total_vendas' => $total_vendas,
    'percentual_vendas' => $percentual_vendas,
    'deficit' => $deficit,
    'nova_meta_diaria' => $nova_meta_diaria,
    'id' => $id
  ]);

  header("Location: index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Métrica</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-4">
    <h2 class="text-center">Editar Métrica</h2>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Meta Total</label>
        <input type="number" step="0.01" name="meta_total" class="form-control" value="<?= $metrica['meta_total'] ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Meta Diária</label>
        <input type="number" step="0.01" name="meta_diaria" class="form-control" value="<?= $metrica['meta_diaria'] ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Total Vendas</label>
        <input type="number" step="0.01" name="total_vendas" class="form-control" value="<?= $metrica['total_vendas'] ?>" required>
      </div>
      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
      <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>