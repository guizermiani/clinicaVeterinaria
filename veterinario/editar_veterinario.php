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

$id = $_GET['id_veterinario'] ?? $_POST['id_veterinario'] ?? '';

if (empty($id)) {
    header("Location: ../consulta/consultar_veterinario.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM veterinario WHERE id_veterinario = :id_veterinario");
$stmt->execute([':id_veterinario' => $id]);
$veterinario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$veterinario) {
    die("Veterinário não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $crmv = trim($_POST['crmv'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($nome) || empty($telefone) || empty($crmv) || empty($email)) {
        $mensagem_erro = "Por favor, preencha todos os campos do formulário.";
    } else {
        try {
            $sql = "UPDATE veterinario SET 
                        nome = :nome, 
                        telefone = :telefone, 
                        crmv = :crmv, 
                        email = :email 
                    WHERE id_veterinario = :id_veterinario";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':telefone' => $telefone,
                ':crmv' => $crmv,
                ':email' => $email,
                ':id_veterinario' => $id,
            ]);

            header("Location: ../veterinario/editar_veterinario.php?id_veterinario=" . $id . "&sucesso=1");
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
    <title>Alterar Veterinário - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <h1>Editar Veterinário</h1>
        <a href="../consulta/consultar_veterinario.php" class="btn-navegacao">Voltar para o Início</a>
    </header>

    <main class="container-formulario">
        <div class="form-box">
            <h2>Alterar Dados: <?= htmlspecialchars($veterinario['nome']) ?></h2>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem_sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <form action="editar_veterinario.php" method="POST">
                <input type="hidden" name="id_veterinario" value="<?= $veterinario['id_veterinario'] ?>">

                <div class="form-grupo">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($veterinario['nome']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="telefone">Telefone</label>
                    <input type="number" id="telefone" name="telefone" value="<?= htmlspecialchars($veterinario['telefone']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="crmv">CRMV</label>
                    <input type="number" id="crmv" name="crmv" value="<?= htmlspecialchars($veterinario['crmv']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="email">E-mail</label>
                    <input type="text" id="email" name="email" value="<?= htmlspecialchars($veterinario['email']) ?>" required>
                </div>
                <br>
                <button type="submit" class="btn-enviar">Salvar Alterações</button>
            </form>
        </div>
    </main>
</body>
</html>