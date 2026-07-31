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

if (isset($_GET['cliente_cadastrado']) && $_GET['cliente_cadastrado'] == 1) {
    $mensagem_sucesso = "Cliente cadastrado com sucesso! Preencha os dados abaixo para cadastrar seu pet.";
}

try {
    $stmt = $pdo->query("SELECT id_cliente, nome FROM cliente ORDER BY nome");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao buscar clientes: " . $e->getMessage();
    $clientes = [];
}

$id_cliente = $_GET['id_cliente'] ?? $_POST['id_cliente'] ?? '';
$nome = trim($_POST['nome'] ?? '');
$especie = trim($_POST['especie'] ?? '');
$idade = trim($_POST['idade'] ?? '');
$peso = trim($_POST['peso'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($id_cliente) || empty($nome) || empty($especie) || empty($idade) || empty($peso)) {
        $mensagem_erro = "Por favor, preencha todos os campos do formulário.";
    } else {
        try {
            $sql = "INSERT INTO animal (nome, especie, idade, peso, id_cliente)
                    VALUES (:nome, :especie, :idade, :peso, :id_cliente)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':especie' => $especie,
                ':idade' => (int)$idade,
                ':peso' => (float)$peso,
                ':id_cliente' => (int)$id_cliente,
            ]);

            $mensagem_sucesso = "Pet cadastrado com sucesso e vinculado ao tutor!";
            
            $id_cliente = $nome = $especie = $idade = $peso = "";

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
    <title>Cadastrar Pet - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <h1>Clínica Veterinária</h1>
        <a href="../index.php" class="btn-navegacao">Voltar para o Início</a>
    </header>

    <main class="container-formulario">
        <div class="form-box">
            <h2>Cadastro de Novo Pet</h2>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem_sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <?php if (empty($clientes)): ?>
                <div class="alerta alerta-erro">
                    Nenhum cliente cadastrado ainda. <a href="../cliente/cadastrar_cliente.php">Cadastre um tutor primeiro</a>.
                </div>
            <?php else: ?>

            <form action="cadastrar_pet.php" method="POST">
                <div class="form-grupo">
                    <label for="id_cliente">Tutor (Cliente)</label>
                    <select id="id_cliente" name="id_cliente" required>
                        <option value="">-- Selecione o tutor --</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>" <?= ($id_cliente == $c['id_cliente']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="nome">Nome do Pet</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($nome) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="especie">Espécie</label>
                    <input type="text" id="especie" name="especie" placeholder="Ex: Cão, Gato, Ave" value="<?= htmlspecialchars($especie) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="pet_idade">Idade (anos)</label>
                    <input type="number" id="idade" name="idade" value="<?= htmlspecialchars($idade) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="pet_peso">Peso (kg)</label>
                    <input type="number" step="0.1" id="peso" name="peso" value="<?= htmlspecialchars($peso) ?>" required>
                </div>
                <br>
                <button type="submit" class="btn-enviar">Salvar Pet</button>
            </form>

            <?php endif; ?>
        </div>
    </main>
</body>
</html>