<?php

/* conexao com o bd */
$host = 'localhost';
$dbname = 'clinica_vet';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id_animal FROM animal WHERE id_animal = 0");
    
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}


$mensagem_sucesso = "";
$mensagem_erro = "";
$pet_selecionado = null;
$prontuario = null;
$consultas = [];


/* salvar prontuario medico */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'salvar_prontuario') {
    $id_animal_prontuario = (int)($_POST['id_animal'] ?? 0);
    $alergias = substr(trim($_POST['alergias'] ?? ''), 0, 255);
    $observacoes = substr(trim($_POST['observacoes'] ?? ''), 0, 255);

    if ($id_animal_prontuario <= 0) {
        $mensagem_erro = "Identificação do animal inválida.";
    } else {
        try {
            $stmt_check = $pdo->prepare("SELECT id_prontuario FROM prontuario 
                               WHERE id_animal = :id_animal");
            $stmt_check->execute([':id_animal' => $id_animal_prontuario]);

            if ($stmt_check->fetch()) {
                $stmt = $pdo->prepare("UPDATE prontuario SET alergias = :alergias, observacoes = :observacoes 
                                          WHERE id_animal = :id_animal");
                $mensagem_sucesso = "Prontuário médico atualizado com sucesso!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO prontuario (alergias, observacoes, id_animal) 
                                         VALUES (:alergias, :observacoes, :id_animal)");
                $mensagem_sucesso = "Prontuário médico criado com sucesso!";
            }
            $stmt->execute([':alergias' => $alergias, ':observacoes' => $observacoes, ':id_animal' => $id_animal_prontuario]);
            $_GET['id_animal'] = $id_animal_prontuario;
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao salvar o prontuário: " . $e->getMessage();
        }
    }
}

/* exibir a ficha cadastral completa */
$id_animal = (int)($_GET['id_animal'] ?? $_POST['id_animal'] ?? 0);
if ($id_animal > 0) {
    try {
        $stmt_pet = $pdo->prepare("
            SELECT a.id_animal, a.nome AS pet_nome, a.especie, a.idade, a.peso,
                   c.nome AS tutor_nome, c.telefone AS tutor_telefone, c.email AS tutor_email
            FROM animal a JOIN cliente c ON a.id_cliente = c.id_cliente
            WHERE a.id_animal = :id_animal
        ");
        $stmt_pet->execute([':id_animal' => $id_animal]);
        $pet_selecionado = $stmt_pet->fetch(PDO::FETCH_ASSOC);

        if ($pet_selecionado) {
            $stmt_pront = $pdo->prepare("SELECT alergias, observacoes FROM prontuario WHERE id_animal = :id_animal");
            $stmt_pront->execute([':id_animal' => $id_animal]);
            $prontuario = $stmt_pront->fetch(PDO::FETCH_ASSOC);

            $stmt_consultas = $pdo->prepare("
                SELECT c.id_consulta, c.data_consulta, c.diagnostico, c.valor, v.nome AS vet_nome, v.crmv AS vet_crmv
                FROM consulta c JOIN veterinario v ON c.id_veterinario = v.id_veterinario
                WHERE c.id_animal = :id_animal
                ORDER BY c.data_consulta DESC, c.id_consulta DESC
            ");
            $stmt_consultas->execute([':id_animal' => $id_animal]);
            $consultas = $stmt_consultas->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $mensagem_erro = "Pet não encontrado no banco de dados.";
        }
    } catch (PDOException $e) {
        $mensagem_erro = "Erro ao buscar dados do pet: " . $e->getMessage();
    }
}

try {
    $lista_pets = $pdo->query("
        SELECT a.id_animal, a.nome AS pet_nome, a.especie, c.nome AS tutor_nome
        FROM animal a JOIN cliente c ON a.id_cliente = c.id_cliente
        ORDER BY a.nome
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lista_pets = [];
}
?>



<!-- html para o histórico de pet -->

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico Médico - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <h1>Clínica Veterinária</h1>
        <a href="../index.php" class="btn-navegacao">Voltar para o Início</a>
    </header>
 
    <main class="container-formulario container-largo">
        <div class="form-box">
            <h2>Histórico Médico do Pet</h2>
 
            <?php if ($mensagem_sucesso): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem_sucesso) ?></div><?php endif; ?>
            <?php if ($mensagem_erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div><?php endif; ?>
 
            <form action="historico_pet.php" method="GET" class="form-grupo">
                <label for="id_animal_select">Selecionar Pet Cadastrado</label>
                <select id="id_animal_select" name="id_animal" onchange="this.form.submit()">
                    <option value="">Escolha um pet na lista</option>
                    <?php foreach ($lista_pets as $p): ?>
                        <option value="<?= $p['id_animal'] ?>" <?= $id_animal === (int)$p['id_animal'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['pet_nome']) ?> (<?= htmlspecialchars($p['especie']) ?>) - Tutor: <?= htmlspecialchars($p['tutor_nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
 
            <?php if ($pet_selecionado): ?>
                <hr class="divisor">
 
                <div class="secoes-form">
                    <div class="secao-coluna">


                    <!-- ficha cadastral com dados do tutor e do animal -->
                        <h3>Ficha Cadastral</h3>
                        <div class="prontuario-card">
                            <h4>Dados do Tutor</h4>
                            <ul class="info-list">
                                <li><span class="rotulo">Nome:</span> <span class="valor"><strong><?= htmlspecialchars($pet_selecionado['tutor_nome']) ?></strong></span></li>
                                <li><span class="rotulo">Telefone:</span> <span class="valor"><?= htmlspecialchars($pet_selecionado['tutor_telefone']) ?></span></li>
                                <li><span class="rotulo">E-mail:</span> <span class="valor"><?= htmlspecialchars($pet_selecionado['tutor_email']) ?></span></li>
                            </ul>
                        </div>
 
                        <div class="prontuario-card">
                            <h4>Dados do Animal</h4>
                            <ul class="info-list">
                                <li><span class="rotulo">ID do Pet:</span> <span class="valor"><?= $pet_selecionado['id_animal'] ?></span></li>
                                <li><span class="rotulo">Nome:</span> <span class="valor"><strong><?= htmlspecialchars($pet_selecionado['pet_nome']) ?></strong></span></li>
                                <li><span class="rotulo">Espécie:</span> <span class="valor"><?= htmlspecialchars($pet_selecionado['especie']) ?></span></li>
                                <li><span class="rotulo">Idade:</span> <span class="valor"><?= htmlspecialchars($pet_selecionado['idade']) ?> anos</span></li>
                                <li><span class="rotulo">Peso:</span> <span class="valor"><?= htmlspecialchars($pet_selecionado['peso']) ?> kg</span></li>
                            </ul>
                        </div>
                    </div>

                    <!-- prontuario para cadastro de prontuario do animal (nao vai aparecer durante o cadastro do pet) -->
                    <div class="secao-coluna">
                        <h3>Prontuário Clínico</h3>
 
                        <div id="prontuario-exibicao" class="prontuario-card">
                            <?php if ($prontuario): ?>
                                <span class="badge-prontuario badge-existente">Prontuário Ativo</span>
                                <div class="prontuario-campo">
                                    <strong class="prontuario-campo-titulo">ALERGIAS</strong>
                                    <p class="prontuario-campo-texto"><?= $prontuario['alergias'] ? htmlspecialchars($prontuario['alergias']) : 'Nenhuma alergia relatada.' ?></p>
                                </div>
                                <div class="prontuario-campo">
                                    <strong class="prontuario-campo-titulo">OBSERVAÇÕES GERAIS</strong>
                                    <p class="prontuario-campo-texto"><?= $prontuario['observacoes'] ? htmlspecialchars($prontuario['observacoes']) : 'Nenhuma observação registrada.' ?></p>
                                </div>
                                <button type="button" onclick="toggleProntuarioForm()" class="btn-navegacao btn-editar-prontuario">Editar Prontuário</button>
                            <?php else: ?>
                                <span class="badge-prontuario badge-ausente">Sem Prontuário</span>
                                <button type="button" onclick="toggleProntuarioForm()" class="btn-enviar">Cadastrar Prontuário</button>
                            <?php endif; ?>
                        </div>
 
                        <div id="prontuario-formulario" class="prontuario-card prontuario-formulario-oculto">
                            <h4 class="prontuario-formulario-titulo"><?= $prontuario ? 'Editar Prontuário' : 'Novo Prontuário' ?></h4>
                            <form action="historico_pet.php" method="POST">
                                <input type="hidden" name="action" value="salvar_prontuario">
                                <input type="hidden" name="id_animal" value="<?= $pet_selecionado['id_animal'] ?>">
 
                                <div class="form-grupo">
                                    <label for="alergias">Alergias</label>
                                    <textarea id="alergias" name="alergias" rows="3" maxlength="255" class="campo-textarea" placeholder="Informe alergias a medicamentos, alimentos, etc. (máx. 255 caracteres)"><?= htmlspecialchars($prontuario['alergias'] ?? '') ?></textarea>
                                </div>
 
                                <div class="form-grupo">
                                    <label for="observacoes">Observações Gerais</label>
                                    <textarea id="observacoes" name="observacoes" rows="4" maxlength="255" class="campo-textarea" placeholder="Insira outras observações sobre o comportamento ou cuidados médicos do animal..."><?= htmlspecialchars($prontuario['observacoes'] ?? '') ?></textarea>
                                </div>
 
                                <div class="acoes-form">
                                    <button type="submit" class="btn-enviar acao-flex">Salvar</button>
                                    <button type="button" onclick="toggleProntuarioForm()" class="btn-navegacao acao-flex">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                
                <!-- consultas que o pet realizou -->
                <div class="consultas-header">
                    <h3>Consultas Realizadas</h3>
                    <a href="../consulta/agendar_consulta.php?id_animal=<?= $pet_selecionado['id_animal'] ?>" class="btn-navegacao btn-agendar">Agendar Consulta</a>
                </div>
 
                <?php if (empty($consultas)): ?>
                    <p class="texto-vazio">Nenhuma consulta foi realizada para este animal até o momento.</p>
                <?php else: ?>
                    <table class="tabela-dados">
                        <thead>
                            <tr><th>Data</th>
                            <th>Veterinário Responsável</th>
                            <th>CRMV</th>
                            <th>Diagnóstico</th>
                            <th>Valor</th></tr>
                        </thead>

                       
                        <!-- retorna os valores do banco de dados de acordo com a tabela acima -->
                        <tbody>
                            <?php foreach ($consultas as $c): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($c['data_consulta'])) ?></td>
                                    <td><strong><?= htmlspecialchars($c['vet_nome']) ?></strong></td>
                                    <td><code class="crmv-code"><?= htmlspecialchars($c['vet_crmv']) ?></code></td>
                                    <td><?= htmlspecialchars($c['diagnostico']) ?></td>
                                    <td><strong>R$ <?= number_format($c['valor'], 2, ',', '.') ?></strong></td>

                                    <td>
                                <a href="../consulta/excluir_consulta.php?id_consulta=<?= $c['id_consulta']?>">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                        </tbody>

                        

                    </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>