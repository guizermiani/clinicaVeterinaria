<?php
$host = 'localhost';
$dbname = 'clinica_vet';
$username = 'root'; 
$password = '';     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id_cliente, nome, cpf, telefone, email FROM cliente ORDER BY id_cliente DESC");
    $cliente = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Clientes - Clínica Veterinária</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
 
    <header>
        <h1>Clínica Veterinária</h1>
        <a href="../index.php" class="btn-navegacao">Voltar para o Início</a>
    </header>
 
    <main class="container-formulario">
        <div class="form-box">
            <h2>Clientes Cadastrados</h2>
 
            <?php if (empty($cliente)): ?>
                <p>Nenhum cliente cadastrado ainda.</p>
            <?php else: ?>
 
            <table class="tabela-dados">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cliente as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nome']) ?></td>
                        <td><?= htmlspecialchars($c['cpf']) ?></td>
                        <td><?= htmlspecialchars($c['telefone']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td>
                            <a href="../cliente/editar_cliente.php?id_cliente=<?= $c['id_cliente'] ?>">Editar</a>
                        </td>
                        <td>
                            <a href="../cliente/excluir_cliente.php?id_cliente=<?= $c['id_cliente'] ?>">Excluir</a>
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