<?php
$host = 'localhost';
$dbname = 'clinica_vet';
$username = 'root'; 
$password = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id_cliente, id_animal, nome, especie, idade, peso FROM animal ORDER BY id_cliente DESC");
    $pet = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Pets - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
 
    <header>
        <h1>Clínica Veterinária</h1>
        <a href="../index.php" class="btn-navegacao">Voltar para o Início</a>
    </header>
 
    <main class="container-formulario">
        <div class="form-box">
            <h2>Pets Cadastrados</h2>
 
            <?php if (empty($pet)): ?>
                <p>Nenhum pet cadastrado ainda.</p>
            <?php else: ?>
 
            <table class="tabela-dados">
                <thead>
                    <tr>
                        <th>ID Tutor</th>
                        <th>Nome</th>
                        <th>Espécie</th>
                        <th>Idade</th>
                        <th>Peso</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pet as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id_cliente']) ?></td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td><?= htmlspecialchars($p['especie']) ?></td>
                        <td><?= htmlspecialchars($p['idade']) ?></td>
                        <td><?= htmlspecialchars($p['peso']) ?></td>
                        <td>
                            <a href="../pet/editar_pet.php?id_animal=<?= $p['id_animal'] ?>">Editar</a>
                        </td>
                        <td>
                            <a href="../pet/excluir_pet.php?id_animal=<?= $p['id_animal'] ?>">Excluir</a>
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