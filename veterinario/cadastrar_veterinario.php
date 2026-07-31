<?php
$mensagem_sucesso = "";
$mensagem_erro = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = 'localhost';
    $dbname = 'clinica_vet';
    $username = 'root';
    $password = '';     

    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $crmv = trim($_POST['crmv'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nome) || empty($telefone) || empty($crmv) || empty($email)) {
        $mensagem_erro = "Por favor, preencha todos os campos do formulário.";
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "INSERT INTO veterinario (nome, telefone, crmv, email) 
                    VALUES (:nome, :telefone, :crmv, :email)";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':nome' => $nome,
                ':telefone' => $telefone,
                ':crmv' => $crmv,
                ':email' => $email,
            ]);

        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao salvar no banco de dados: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Veterinário - Clinica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <h1>Clínica Veterinária</h1>
        <a href="../index.php" class="btn-navegacao">Voltar para o Início</a>
    </header>

    <main class="container-formulario">
        <div class="form-box">
            <h2>Cadastro de Novo Veterinário</h2>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem_sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <form action="cadastrar_veterinario.php" method="POST">
                <div class="form-grupo">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome ?? '') ?>">
                </div>

                <div class="form-grupo">
                    <label for="telefone">Telefone</label>
                    <input type="number" id="telefone" name="telefone" value="<?= htmlspecialchars($telefone ?? '') ?>">
                </div>

                <div class="form-grupo">
                    <label for="crmv">CRMV</label>
                    <input type="number" id="crmv" name="crmv" value="<?= htmlspecialchars($crmv ?? '') ?>">
                </div>

                <div class="form-grupo">
                    <label for="email">E-mail</label>
                    <input type="text" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
                </div>
                <br>
                <button type="submit" class="btn-enviar">Salvar Veterinário</button>
            </form>
        </div>
    </main>
</body>
</html>
