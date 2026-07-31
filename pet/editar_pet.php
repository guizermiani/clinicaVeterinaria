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
    $mensagem_sucesso = "Dados do pet atualizados com sucesso!";
}

$id_animal = $_GET['id_animal'] ?? $_POST['id_animal'] ?? '';

if (empty($id_animal)) {
    header("Location: ../consulta/consultar_pet.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM animal WHERE id_animal = :id_animal");
$stmt->execute([':id_animal' => $id_animal]);
$pet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pet) {
    die("Pet não encontrado.");
}

try {
    $stmt_clientes = $pdo->query("SELECT id_cliente, nome FROM cliente ORDER BY nome");
    $clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clientes = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $especie = trim($_POST['especie'] ?? '');
    $idade = trim($_POST['idade'] ?? '');
    $peso = trim($_POST['peso'] ?? '');
    $id_cliente = $_POST['id_cliente'] ?? '';

    if (empty($nome) || empty($especie) || empty($idade) || empty($peso) || empty($id_cliente)) {
        $mensagem_erro = "Por favor, preencha todos os campos do formulário.";
    } else {
        try {
            $sql = "UPDATE animal SET 
                        nome = :nome, 
                        especie = :especie, 
                        idade = :idade, 
                        peso = :peso,
                        id_cliente = :id_cliente 
                    WHERE id_animal = :id_animal";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':especie' => $especie,
                ':idade' => (int)$idade,
                ':peso' => (float)$peso,
                ':id_cliente' => (int)$id_cliente,
                ':id_animal' => $id_animal
            ]);

            header("Location: editar_pet.php?id_animal=" . $id_animal . "&sucesso=1");
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
    <title>Alterar Pet - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <h1>Editar Pet</h1>
        <a href="../consulta_dados/consultar_pet.php" class="btn-navegacao">Voltar para a Consulta</a>
    </header>

    <main class="container-formulario">
        <div class="form-box">
            <h2>Alterar Dados: <?= htmlspecialchars($pet['nome']) ?></h2>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem_sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <form action="editar_pet.php" method="POST">
                <input type="hidden" name="id_animal" value="<?= $pet['id_animal'] ?>">

                <div class="form-grupo">
                    <label for="id_cliente">Tutor</label>
                    <select id="id_cliente" name="id_cliente" required>
                        <option value="">Selecione o tutor</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>" <?= ($pet['id_cliente'] == $c['id_cliente']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="nome">Nome do Pet</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($pet['nome']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="especie">Espécie</label>
                    <input type="text" id="especie" name="especie" value="<?= htmlspecialchars($pet['especie']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="idade">Idade (anos)</label>
                    <input type="number" id="idade" name="idade" value="<?= htmlspecialchars($pet['idade']) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="peso">Peso (kg)</label>
                    <input type="number" step="0.1" id="peso" name="peso" value="<?= htmlspecialchars($pet['peso']) ?>" required>
                </div>
                <br>
                <button type="submit" class="btn-enviar">Salvar Alterações</button>
            </form>
        </div>
    </main>

</body>
</html>