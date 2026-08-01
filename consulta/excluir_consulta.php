<?php
if (isset($_GET['id_consulta']) || isset($_POST['id_consulta'])) {
    $id = $_GET['id_consulta'] ?? $_POST['id_consulta'] ?? '';

    if (!empty($id)) {
        $host = 'localhost';
        $dbname = 'clinica_vet';
        $username = 'root'; 
        $password = '';     

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("DELETE FROM consulta WHERE id_consulta = :id_consulta");
            $stmt->execute([':id_consulta' => (int)$id]);

        } catch (PDOException $e) {
            die("Erro ao excluir pet do banco de dados: " . $e->getMessage());
        }
    }
}

header("Location
-=: ../pet/historico_pet.php");
exit;
