<?php
$host = 'localhost';
$dbname = 'clinica_vet';
$username = 'root'; 
$password = '';     

$mensagem_sucesso = "";
$mensagem_erro = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1) {
    $mensagem_sucesso = "Dados atualizados com sucesso!";
}

$id = $_GET['id_cliente'] ?? $_POST['id_cliente'] ?? '';

if (empty($id)) {
    header("Location: ../consulta/consultar_cliente.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM cliente WHERE id_cliente = :id_cliente");
$stmt->execute([':id_cliente' => $id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Cliente não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nome) || empty($cpf) || empty($telefone) || empty($email)) {
        $mensagem_erro = "Por favor, preencha todos os campos do formulário.";
    } else {
        try {
            $sql = "UPDATE cliente SET 
                        nome = :nome, 
                        cpf = :cpf, 
                        telefone = :telefone, 
                        email = :email 
                    WHERE id_cliente = :id_cliente";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':cpf' => $cpf,
                ':telefone' => $telefone,
                ':email' => $email,
                ':id_cliente' => $id,
            ]);

            header("Location: editar_cliente.php?id_cliente=" . $id . "&sucesso=1");
            exit;

        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao atualizar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Cliente - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <h1>Editar Cliente</h1>
        <a href="../consulta_dados/consultar_cliente.php" class="btn-navegacao">Voltar para o Início</a>
    </header>

    <main class="container-formulario">
        <div class="form-box">
            <h2>Alterar Dados: <?= htmlspecialchars($cliente['nome']) ?></h2>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem_sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <form action="editar_cliente.php" method="POST">
                <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">

                <div class="form-grupo">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="cpf">CPF</label>
                    <input type="number" id="cpf" name="cpf" value="<?= htmlspecialchars($cliente['cpf']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="telefone">Telefone</label>
                    <input type="number" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="email">E-mail</label>
                    <input type="text" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required>
                </div>
                <br>
                <button type="submit" class="btn-enviar">Salvar Alterações</button>
            </form>
        </div>
    </main>

</body>
</html>