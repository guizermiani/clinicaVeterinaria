<?php
require_once '../conexao.php';

$mensagem_sucesso = "";
$mensagem_erro = "";
$pet_selecionado = null;
$prontuario = null;
$consultas = [];
$resultados_busca = [];
$busca_realizada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'salvar_prontuario') {
    $id_animal_prontuario = (int)($_POST['id_animal'] ?? 0);
    $alergias = trim($_POST['alergias'] ?? '');
    $observacoes = trim($_POST['observacoes'] ?? '');

    if ($id_animal_prontuario <= 0) {
        $mensagem_erro = "Identificação do animal inválida.";
    } else {
        try {
            $stmt_check = $pdo->prepare("SELECT id_protuario FROM prontuario WHERE id_animal = :id_animal");
            $stmt_check->execute([':id_animal' => $id_animal_prontuario]);
            $existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                $stmt_update = $pdo->prepare("UPDATE prontuario SET alergias = :alergias, observacoes = :observacoes WHERE id_animal = :id_animal");
                $stmt_update->execute([
                    ':alergias' => substr($alergias, 0, 255),
                    ':observacoes' => substr($observacoes, 0, 255),
                    ':id_animal' => $id_animal_prontuario
                ]);
                $mensagem_sucesso = "Prontuário médico atualizado com sucesso!";
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO prontuario (alergias, observacoes, id_animal) VALUES (:alergias, :observacoes, :id_animal)");
                $stmt_insert->execute([
                    ':alergias' => substr($alergias, 0, 255),
                    ':observacoes' => substr($observacoes, 0, 255),
                    ':id_animal' => $id_animal_prontuario
                ]);
                $mensagem_sucesso = "Prontuário médico criado com sucesso!";
            }

            $_GET['id_animal'] = $id_animal_prontuario;

        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao salvar o prontuário: " . $e->getMessage();
        }
    }
}

$termo_busca = trim($_GET['busca'] ?? $_POST['busca'] ?? '');
if (!empty($termo_busca)) {
    $busca_realizada = true;
    try {
        $query_busca = "
            SELECT a.id_animal, a.nome AS pet_nome, a.especie, c.nome AS tutor_nome 
            FROM animal a 
            JOIN cliente c ON a.id_cliente = c.id_cliente
            WHERE a.id_animal = :busca_id OR a.nome LIKE :busca_nome
            ORDER BY a.nome
        ";
        
        $stmt_busca = $pdo->prepare($query_busca);
        $stmt_busca->execute([
            ':busca_id' => is_numeric($termo_busca) ? (int)$termo_busca : -1,
            ':busca_nome' => '%' . $termo_busca . '%'
        ]);
        
        $resultados_busca = $stmt_busca->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultados_busca) === 1) {
            $_GET['id_animal'] = $resultados_busca[0]['id_animal'];
        }
    } catch (PDOException $e) {
        $mensagem_erro = "Erro na pesquisa: " . $e->getMessage();
    }
}

$id_animal = (int)($_GET['id_animal'] ?? $_POST['id_animal'] ?? 0);
if ($id_animal > 0) {
    try {
        $stmt_pet = $pdo->prepare("
            SELECT a.id_animal, a.nome AS pet_nome, a.especie, a.idade, a.peso, a.id_cliente,
                   c.nome AS tutor_nome, c.telefone AS tutor_telefone, c.email AS tutor_email
            FROM animal a 
            JOIN cliente c ON a.id_cliente = c.id_cliente 
            WHERE a.id_animal = :id_animal
        ");
        $stmt_pet->execute([':id_animal' => $id_animal]);
        $pet_selecionado = $stmt_pet->fetch(PDO::FETCH_ASSOC);

        if ($pet_selecionado) {
            $stmt_pront = $pdo->prepare("SELECT id_protuario, alergias, observacoes FROM prontuario WHERE id_animal = :id_animal");
            $stmt_pront->execute([':id_animal' => $id_animal]);
            $prontuario = $stmt_pront->fetch(PDO::FETCH_ASSOC);

            $stmt_consultas = $pdo->prepare("
                SELECT c.data_consulta, c.diagnostico, c.valor, v.nome AS vet_nome, v.crmv AS vet_crmv
                FROM consulta c
                JOIN veterinario v ON c.id_veterinario = v.id_veterinario
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
    $stmt_lista = $pdo->query("
        SELECT a.id_animal, a.nome AS pet_nome, a.especie, c.nome AS tutor_nome 
        FROM animal a 
        JOIN cliente c ON a.id_cliente = c.id_cliente 
        ORDER BY a.nome
    ");
    $lista_pets = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lista_pets = [];
}
?>

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

    <main class="container-formulario-wide">
        <div class="form-box">
            <h2>Histórico Médico do Pet</h2>

            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alerta alerta-sucesso"><?= htmlspecialchars($mensagem_sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($mensagem_erro)): ?>
                <div class="alerta alerta-erro"><?= htmlspecialchars($mensagem_erro) ?></div>
            <?php endif; ?>

            <div class="busca-container">
                <div class="busca-coluna">
                    <form action="historico_pet.php" method="GET">
                        <div class="form-grupo" style="margin-bottom: 0;">
                            <label for="id_animal_select">Selecionar Pet Cadastrado</label>
                            <select id="id_animal_select" name="id_animal" onchange="this.form.submit()">
                                <option value="">Escolha um pet na lista</option>
                                <?php foreach ($lista_pets as $p): ?>
                                    <option value="<?= $p['id_animal'] ?>" <?= ($id_animal === (int)$p['id_animal']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['pet_nome']) ?> (<?= htmlspecialchars($p['especie']) ?>) - Tutor: <?= htmlspecialchars($p['tutor_nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>


            <?php if ($busca_realizada && count($resultados_busca) > 1 && !$pet_selecionado): ?>
                <div style="margin-bottom: 2rem;">
                    <h3 style="color: var(--primary); font-size: 1.1rem; margin-bottom: 1rem;">Múltiplos pets encontrados. Selecione um abaixo:</h3>
                    <table class="tabela-dados">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome do Pet</th>
                                <th>Espécie</th>
                                <th>Tutor (Responsável)</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados_busca as $res): ?>
                                <tr>
                                    <td><?= htmlspecialchars($res['id_animal']) ?></td>
                                    <td><strong><?= htmlspecialchars($res['pet_nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($res['especie']) ?></td>
                                    <td><?= htmlspecialchars($res['tutor_nome']) ?></td>
                                    <td>
                                        <a href="historico_pet.php?id_animal=<?= $res['id_animal'] ?>" class="btn-navegacao" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">Selecionar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($busca_realizada && empty($resultados_busca) && !$pet_selecionado): ?>
                <div class="alerta alerta-erro">Nenhum pet encontrado para o termo "<?= htmlspecialchars($termo_busca) ?>".</div>
            <?php endif; ?>

            <?php if ($pet_selecionado): ?>
                <hr style="border: 0; border-top: 1px solid var(--border); margin: 2rem 0;">

                <div class="secoes-f
                    <div class="secao-coluna">
                        <h3>Ficha Cadastral</h3>
                        
                        <div class="prontuario-card">
                            <h4 style="margin-bottom: 0.75rem; color: var(--text-main); font-size: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.25rem;">Dados do Tutor</h4>
                            <ul class="info-list">
                                <li>
                                    <span class="rotulo">Nome:</span>
                                    <span class="valor"><strong><?= htmlspecialchars($pet_selecionado['tutor_nome']) ?></strong></span>
                                </li>
                                <li>
                                    <span class="rotulo">Telefone:</span>
                                    <span class="valor"><?= htmlspecialchars($pet_selecionado['tutor_telefone']) ?></span>
                                </li>
                                <li>
                                    <span class="rotulo">E-mail:</span>
                                    <span class="valor"><?= htmlspecialchars($pet_selecionado['tutor_email']) ?></span>
                                </li>
                            </ul>
                        </div>

                        <div class="prontuario-card">
                            <h4 style="margin-bottom: 0.75rem; color: var(--text-main); font-size: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.25rem;">Dados do Animal</h4>
                            <ul class="info-list">
                                <li>
                                    <span class="rotulo">ID do Pet:</span>
                                    <span class="valor">#<?= htmlspecialchars($pet_selecionado['id_animal']) ?></span>
                                </li>
                                <li>
                                    <span class="rotulo">Nome:</span>
                                    <span class="valor"><strong><?= htmlspecialchars($pet_selecionado['pet_nome']) ?></strong></span>
                                </li>
                                <li>
                                    <span class="rotulo">Espécie:</span>
                                    <span class="valor"><?= htmlspecialchars($pet_selecionado['especie']) ?></span>
                                </li>
                                <li>
                                    <span class="rotulo">Idade:</span>
                                    <span class="valor"><?= htmlspecialchars($pet_selecionado['idade']) ?> anos</span>
                                </li>
                                <li>
                                    <span class="rotulo">Peso:</span>
                                    <span class="valor"><?= htmlspecialchars($pet_selecionado['peso']) ?> kg</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="secao-coluna">
                        <h3>Prontuário Clínico</h3>
                        <div id="prontuario-exibicao" class="prontuario-card" style="display: block;">
                            <?php if ($prontuario): ?>
                                <span class="badge-prontuario badge-existente">✓ Prontuário Ativo</span>
                                <div style="margin-bottom: 1.25rem;">
                                    <strong style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">ALERGIAS</strong>
                                    <p style="background-color: var(--surface); border: 1px solid var(--border); padding: 0.75rem; border-radius: var(--radius-sm); white-space: pre-wrap; font-size: 0.95rem;"><?= !empty($prontuario['alergias']) ? htmlspecialchars($prontuario['alergias']) : 'Nenhuma alergia relatada.' ?></p>
                                </div>
                                <div style="margin-bottom: 1.5rem;">
                                    <strong style="color: var(--text-muted); font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">OBSERVAÇÕES GERAIS</strong>
                                    <p style="background-color: var(--surface); border: 1px solid var(--border); padding: 0.75rem; border-radius: var(--radius-sm); white-space: pre-wrap; font-size: 0.95rem;"><?= !empty($prontuario['observacoes']) ? htmlspecialchars($prontuario['observacoes']) : 'Nenhuma observação registrada.' ?></p>
                                </div>
                                <button type="button" onclick="toggleProntuarioForm()" class="btn-navegacao" style="background-color: var(--primary); color: white; border: none; width: 100%;">Editar Prontuário</button>
                            <?php else: ?>
                                <span class="badge-prontuario badge-ausente">⚠ Sem Prontuário</span>
                                <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.9rem;">Este animal ainda não possui um prontuário médico registrado contendo suas alergias ou observações de saúde.</p>
                                <button type="button" onclick="toggleProntuarioForm()" class="btn-enviar">Cadastrar Prontuário</button>
                            <?php endif; ?>
                        </div>

                        <div id="prontuario-formulario" class="prontuario-card" style="display: none;">
                            <h4 style="margin-bottom: 1rem; color: var(--primary); font-size: 1rem;"><?= $prontuario ? 'Editar Prontuário' : 'Novo Prontuário' ?></h4>
                            <form action="historico_pet.php" method="POST">
                                <input type="hidden" name="action" value="salvar_prontuario">
                                <input type="hidden" name="id_animal" value="<?= $pet_selecionado['id_animal'] ?>">
                                
                                <div class="form-grupo">
                                    <label for="alergias">Alergias</label>
                                    <textarea id="alergias" name="alergias" rows="3" maxlength="255" placeholder="Informe alergias a medicamentos, alimentos, etc. (máx. 255 caracteres)"><?= htmlspecialchars($prontuario['alergias'] ?? '') ?></textarea>
                                </div>

                                <div class="form-grupo">
                                    <label for="observacoes">Observações Gerais</label>
                                    <textarea id="observacoes" name="observacoes" rows="4" maxlength="255" placeholder="Insira outras observações sobre o comportamento ou cuidados médicos do animal..."><?= htmlspecialchars($prontuario['observacoes'] ?? '') ?></textarea>
                                </div>

                                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <button type="submit" class="btn-enviar" style="flex: 1;">Salvar</button>
                                    <button type="button" onclick="toggleProntuarioForm()" class="btn-navegacao" style="flex: 1;">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 3rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">
                        <h3 style="margin-bottom: 0; color: var(--text-main);">Consultas Realizadas</h3>
                        <a href="../consulta/agendar_consulta.php?id_animal=<?= $pet_selecionado['id_animal'] ?>" class="btn-navegacao" style="background-color: var(--secondary); color: white; border: none; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600;">+ Agendar Consulta</a>
                    </div>

                    <?php if (empty($consultas)): ?>
                        <p style="color: var(--text-muted); font-style: italic;">Nenhuma consulta foi realizada para este animal até o momento.</p>
                    <?php else: ?>
                        <table class="tabela-dados">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Data</th>
                                    <th style="width: 25%;">Veterinário Responsável</th>
                                    <th style="width: 15%;">CRMV</th>
                                    <th style="width: 30%;">Diagnóstico</th>
                                    <th style="width: 15%;">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consultas as $c): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($c['data_consulta'])) ?></td>
                                        <td><strong><?= htmlspecialchars($c['vet_nome']) ?></strong></td>
                                        <td><code style="background-color: var(--background); padding: 0.2rem 0.4rem; border-radius: var(--radius-sm); font-size: 0.85rem;"><?= htmlspecialchars($c['vet_crmv']) ?></code></td>
                                        <td><?= htmlspecialchars($c['diagnostico']) ?></td>
                                        <td><strong>R$ <?= number_format($c['valor'], 2, ',', '.') ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div style="text-align: center; margin: 4rem 0; color: var(--text-muted);">
                    <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">Nenhum animal selecionado.</p>
                    <p style="font-size: 0.9rem;">Escolha um pet no menu ou faça uma pesquisa acima para visualizar seu histórico completo.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleProntuarioForm() {
            var displayDiv = document.getElementById('prontuario-exibicao');
            var formDiv = document.getElementById('prontuario-formulario');
            if (displayDiv.style.display === 'none') {
                displayDiv.style.display = 'block';
                formDiv.style.display = 'none';
            } else {
                displayDiv.style.display = 'none';
                formDiv.style.display = 'block';
            }
        }
    </script>
</body>
</html>
