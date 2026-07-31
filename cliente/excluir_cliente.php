<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    if (!empty($id)) {
        $host = 'localhost';
        $dbname = 'clinica_vet';
        $username = 'root'; 
        $password = '';     

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("DELETE FROM cliente WHERE id = :id");
            $stmt->execute([':id' => (int)$id]);

        } catch (PDOException $e) {
            die("Erro ao excluir do banco de dados: " . $e->getMessage());
        }
    }
}

header("Location: index.php");
exit;