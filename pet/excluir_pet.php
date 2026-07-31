<?php
if (isset($_GET['id_animal']) || isset($_POST['id_animal'])) {
    $id = $_GET['id_animal'] ?? $_POST['id_animal'] ?? '';

    if (!empty($id)) {
        $host = 'localhost';
        $dbname = 'clinica_vet';
        $username = 'root'; 
        $password = '';     

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("DELETE FROM animal WHERE id_animal = :id_animal");
            $stmt->execute([':id_animal' => (int)$id]);

        } catch (PDOException $e) {
            die("Erro ao excluir pet do banco de dados: " . $e->getMessage());
        }
    }
}

header("Location: ../consulta_dados/consultar_pet.php");
exit;
