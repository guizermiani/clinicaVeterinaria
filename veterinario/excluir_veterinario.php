<?php
if (isset($_GET['id_veterinario']) || isset($_POST['id_veterinario'])) {
    $id = $_GET['id_veterinario'] ?? $_POST['id_veterinario'] ?? '';

    if (!empty($id)) {
        $host = 'localhost';
        $dbname = 'clinica_vet';
        $username = 'root'; 
        $password = '';     

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("DELETE FROM veterinario WHERE id_veterinario = :id_veterinario");
            $stmt->execute([':id_veterinario' => (int)$id]);

        } catch (PDOException $e) {
            die("Erro ao excluir do banco de dados: " . $e->getMessage());
        }
    }
}

header("Location: ../consulta/consultar_veterinario.php");
exit;