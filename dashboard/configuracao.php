<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
  header("Location: ../auth/login.php");
  exit();
}

include '../config/database.php';

// Busca configurações existentes
$stmt = $pdo->query("SELECT * FROM configuracoes LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// Processa o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $tempo_troca_slides = $_POST['tempo_troca_slides'];
  $tempo_atualizacao = $_POST['tempo_atualizacao'];

  if ($config) {
    $stmt = $pdo->prepare("UPDATE configuracoes SET tempo_troca_slides = :tempo_troca, tempo_atualizacao = :tempo_atual");
  } else {
    $stmt = $pdo->prepare("INSERT INTO configuracoes (tempo_troca_slides, tempo_atualizacao) VALUES (:tempo_troca, :tempo_atual)");
  }

  $stmt->execute([
    'tempo_troca' => $tempo_troca_slides,
    'tempo_atual' => $tempo_atualizacao
  ]);

  header("Location: configuracao.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuração do Sistema</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-4">
    <h2 class="text-center">Configuração do Sistema</h2>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Tempo de troca de slides (segundos):</label>
        <input type="number" name="tempo_troca_slides" class="form-control" value="<?= $config['tempo_troca_slides'] ?? 10 ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Frequência de atualização dos dados (segundos):</label>
        <input type="number" name="tempo_atualizacao" class="form-control" value="<?= $config['tempo_atualizacao'] ?? 30 ?>" required>
      </div>

      <button type="submit" class="btn btn-primary">Salvar Configurações</button>
      <a href="index.php" class="btn btn-secondary">Voltar</a>
    </form>
  </div>

</body>

</html>