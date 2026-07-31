<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle - Clínica Veterinária</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <h1>Clínica Veterinária</h1>
    </header>

    <main class="container-formulario">
        <div class="form-box" style="text-align: center;">
            <h2 style="display: inline-block; border-left: none; padding-left: 0; margin-bottom: 2rem;">Sistema de Controle Integrado</h2>
            <p style="color: var(--text-muted); margin-bottom: 3rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Bem-vindo ao sistema de gerenciamento da clínica veterinária. Selecione uma das ações abaixo para gerenciar os cadastros de clientes e seus respectivos pets.
            </p>

            <div class="dashboard-grid">
                <a href="cliente/cadastrar_cliente.php" class="card-opcao">
                    <h3>Cadastrar Cliente</h3>
                    <p>Adicionar novo tutor no banco de dados com CPF, telefone e email.</p>
                </a>

                <a href="consulta/consultar_cliente.php" class="card-opcao">
                    <h3>Consultar Clientes</h3>
                    <p>Visualizar, alterar dados ou remover tutores cadastrados.</p>
                </a>

                <a href="pet/cadastrar_pet.php" class="card-opcao">
                    <h3>Cadastrar Pet</h3>
                    <p>Registrar um novo animal de estimação e vinculá-lo a um tutor cadastrado.</p>
                </a>

                <a href="consulta/consultar_pet.php" class="card-opcao">
                    <h3>Consultar Pets</h3>
                    <p>Visualizar, alterar dados ou remover pets cadastrados.</p>
                </a>

                <a href="veterinario/cadastrar_veterinario.php" class="card-opcao">
                    <h3>Cadastrar Veterinário</h3>
                    <p>Registrar um novo veterinário.</p>
                </a>

                <a href="consulta/consultar_veterinario.php" class="card-opcao">
                    <h3>Consultar Veterinários</h3>
                    <p>Visualizar, alterar dados ou remover veterinários cadastrados.</p>
                </a>
            </div>
        </div>
    </main>

</body>
</html>
