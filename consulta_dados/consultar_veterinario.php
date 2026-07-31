<?php
$host = 'localhost';
$dbname = 'clinica_vet';
$username = 'root'; 
$password = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id_veterinario, nome, telefone, crmv, email FROM veterinario ORDER BY id_veterinario DESC");
    $veterinario = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Veterinários - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
 
    <header>
        <h1>Clínica Veterinária</h1>
        <a href="../index.php" class="btn-navegacao">Voltar para o Início</a>
    </header>
 
    <main class="container-formulario">
        <div class="form-box">
            <h2>Veterinários Cadastrados</h2>
 
            <?php if (empty($veterinario)): ?>
                <p>Nenhum veterinário cadastrado ainda.</p>
            <?php else: ?>
 
            <table class="tabela-dados">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>CRMV</th>
                        <th>E-mail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($veterinario as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['nome']) ?></td>
                        <td><?= htmlspecialchars($v['telefone']) ?></td>
                        <td><?= htmlspecialchars($v['crmv']) ?></td>
                        <td><?= htmlspecialchars($v['email']) ?></td>
                        <td>
                            <a href="../veterinario/editar_veterinario.php?id_veterinario=<?= $v['id_veterinario'] ?>">Editar</a>
                        </td>
                        <td>
                            <a href="../veterinario/excluir_veterinario.php?id_veterinario=<?= $v['id_veterinario'] ?>">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
 
            <?php endif; ?>
        </div>
    </main>
</body>
</html>