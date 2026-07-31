<?php
if (isset($_GET['id_cliente']) || isset($_POST['id_cliente'])) {
    $id = $_GET['id_cliente'] ?? $_POST['id_cliente'] ?? '';

    if (!empty($id)) {
        $host = 'localhost';
        $dbname = 'clinica_vet';
        $username = 'root'; 
        $password = '';     

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("DELETE FROM cliente WHERE id_cliente = :id_cliente");
            $stmt->execute([':id_cliente' => (int)$id]);

        } catch (PDOException $e) {
            die("Erro ao excluir do banco de dados: " . $e->getMessage());
        }
    }
}

header("Location: ../consulta_dados/consultar_cliente.php");
exit;