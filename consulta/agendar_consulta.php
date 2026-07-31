<?php
require_once '../conexao.php';

$mensagem_sucesso = "";
$mensagem_erro = "";

// Buscar pets cadastrados com seus respectivos tutores
try {
    $stmt_pets = $pdo->query("
        SELECT a.id_animal, a.nome AS pet_nome, a.especie, c.nome AS tutor_nome 
        FROM animal a 
        JOIN cliente c ON a.id_cliente = c.id_cliente 
        ORDER BY a.nome
    ");
    $pets = $stmt_pets->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar os animais: " . $e->getMessage();
    $pets = [];
}

// Buscar veterinários cadastrados
try {
    $stmt_vets = $pdo->query("SELECT id_veterinario, nome, crmv FROM veterinario ORDER BY nome");
    $veterinarios = $stmt_vets->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensagem_erro = "Erro ao carregar os veterinários: " . $e->getMessage();
    $veterinarios = [];
}

$id_animal = $_POST['id_animal'] ?? $_GET['id_animal'] ?? '';
$id_veterinario = $_POST['id_veterinario'] ?? '';
$data_consulta = $_POST['data_consulta'] ?? date('Y-m-d');
$diagnostico = trim($_POST['diagnostico'] ?? '');
$valor = $_POST['valor'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($id_animal) || empty($id_veterinario) || empty($data_consulta) || empty($diagnostico) || $valor === '') {
        $mensagem_erro = "Por favor, preencha todos os campos do formulário.";
    } else {
        try {
            $sql = "INSERT INTO consulta (data_consulta, diagnostico, valor, id_animal, id_veterinario) 
                    VALUES (:data_consulta, :diagnostico, :valor, :id_animal, :id_veterinario)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':data_consulta' => $data_consulta,
                ':diagnostico' => $diagnostico,
                ':valor' => (float)$valor,
                ':id_animal' => (int)$id_animal,
                ':id_veterinario' => (int)$id_veterinario
            ]);

            $mensagem_sucesso = "Consulta agendada e registrada com sucesso!";
            
            // Limpar campos após sucesso, guardando o id_animal para o link do histórico
            $sucesso_id_animal = $id_animal;
            $id_animal = $id_veterinario = $diagnostico = $valor = "";
            $data_consulta = date('Y-m-d');

        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao salvar a consulta: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Consulta - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <header>
        <h1>Clínica Veterinária</h1>
        <a href="../index.php" class="btn-navegacao">Voltar para o Início</a>
    </header>

    <main class="container-formulario">
        <div class="form-box">
            <h2>Agendar Nova Consulta</h2>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alerta alerta-sucesso">
                    <?= htmlspecialchars($mensagem_sucesso) ?>
                    <?php if (isset($sucesso_id_animal)): ?>
                        <br><br>
                        <a href="../pet/historico_pet.php?id_animal=<?= $sucesso_id_animal ?>" class="btn-navegacao" style="background-color: var(--success); color: white; border: none;">Ver Histórico do Pet</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <?php if (empty($pets)): ?>
                <div class="alerta alerta-erro">
                    Nenhum pet cadastrado no sistema. <a href="../pet/cadastrar_pet.php">Cadastre um pet primeiro</a>.
                </div>
            <?php elseif (empty($veterinarios)): ?>
                <div class="alerta alerta-erro">
                    Nenhum veterinário cadastrado no sistema. <a href="../veterinario/cadastrar_veterinario.php">Cadastre um veterinário primeiro</a>.
                </div>
            <?php else: ?>

            <form action="agendar_consulta.php" method="POST">
                <div class="form-grupo">
                    <label for="id_animal">Selecione o Animal (Pet)</label>
                    <select id="id_animal" name="id_animal" required>
                        <option value="">-- Selecione o animal --</option>
                        <?php foreach ($pets as $p): ?>
                            <option value="<?= $p['id_animal'] ?>" <?= ($id_animal == $p['id_animal']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['pet_nome']) ?> (<?= htmlspecialchars($p['especie']) ?>) - Tutor: <?= htmlspecialchars($p['tutor_nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="id_veterinario">Selecione o Veterinário</label>
                    <select id="id_veterinario" name="id_veterinario" required>
                        <option value="">-- Selecione o veterinário --</option>
                        <?php foreach ($veterinarios as $v): ?>
                            <option value="<?= $v['id_veterinario'] ?>" <?= ($id_veterinario == $v['id_veterinario']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['nome']) ?> (CRMV: <?= htmlspecialchars($v['crmv']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="data_consulta">Data da Consulta</label>
                    <input type="date" id="data_consulta" name="data_consulta" value="<?= htmlspecialchars($data_consulta) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="valor">Valor da Consulta (R$)</label>
                    <input type="number" step="0.01" min="0" id="valor" name="valor" placeholder="0.00" value="<?= htmlspecialchars($valor) ?>" required>
                </div>

                <div class="form-grupo">
                    <label for="diagnostico">Diagnóstico</label>
                    <textarea id="diagnostico" name="diagnostico" rows="4" maxlength="250" placeholder="Insira o diagnóstico clínico (máx. 250 caracteres)..." required><?= htmlspecialchars($diagnostico) ?></textarea>
                </div>
                <br>
                <button type="submit" class="btn-enviar">Registrar Consulta</button>
            </form>

            <?php endif; ?>
        </div>
    </main>
</body>
</html>
