<?php
session_start();
include '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['setor'] = $user['setor'];
        header("Location: ../dashboard/index.php");
        exit();
    } else {
        $erro = "E-mail ou senha incorretos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRM Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">

    <div class="card p-4 shadow" style="width: 350px;">
        <h4 class="text-center mb-3">Acesso ao Sistema</h4>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">E-mail:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha:</label>
                <input type="password" name="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>

</body>
</html>
