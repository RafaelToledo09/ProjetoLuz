<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../conexao.php'; 
$erro = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM Cliente WHERE email = :email AND senha = :senha";
    $stmt = $pdo->prepare($sql);
    
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':senha', $senha);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION['usuario_logado'] = true;
        $_SESSION['id_usuario'] = $usuario['id_cliente']; 
        $_SESSION['nome_usuario'] = $usuario['nome']; 
        $_SESSION['nivel'] = $usuario['nivel'];

        header("Location: ../index.php");
        exit;
    } else {
        $erro = "E-mail ou senha incorretos. Tente novamente.";
    }
}
?>


<?php include '../header.php'; ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Luz Arte e Cultura</title>
    <style>
    body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; }
    .login-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 350px; text-align: center; }
    .login-container h2 { margin-bottom: 20px; color: #333; }
    .form-group { margin-bottom: 15px; text-align: left; }
    .form-group label { display: block; margin-bottom: 5px; color: #666; }
    .form-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    .btn-entrar { width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
    .btn-entrar:hover { background-color: #218838; }
    .erro-msg { color: red; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
<div style="min-height: 75vh; display: flex; justify-content: center; align-items: center; padding: 20px;">
    <div class="login-container">
        <h2>Acessar Conta</h2>
        
        <?php if ($erro != ""): ?>
            <div class="erro-msg"><?php echo $erro; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" required placeholder="Digite seu e-mail">
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required placeholder="Digite sua senha">
            </div>
            <button type="submit" class="btn-entrar">Entrar</button>
        </form>
    </div> 
</div> 
<?php include '../footer.php'; ?>
</body>
</html>