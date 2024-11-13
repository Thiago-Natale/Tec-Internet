<?php
include("conexao.php");
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['nome'])) {
    header("Location: login.php"); // Redireciona para a página de login
    exit;
}

// Controle de exibição das seções (Cadastro de Usuário, Listagem e Edição)
$showForm = false;
$showList = false;
$showEdit = false;
$userToEdit = null;

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action == 'form') {
        $showForm = true;
    } elseif ($action == 'list') {
        $showList = true;
    } elseif ($action == 'edit' && isset($_GET['cpf'])) {
        $showEdit = true;
        $userToEdit = $_GET['cpf'];
    }
}

// Função de validação de senha (para ser usada no front-end)
function validarSenha($senha) {
    return preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $senha);
}

// Função para formatar CPF
function formatarCPF($cpf) {
    $cpf = preg_replace("/\D/", "", $cpf);
    if (strlen($cpf) === 11) {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "$1.$2.$3-$4", $cpf);
    }
    return $cpf;
}

// Cadastro de Usuário (salvar no banco de dados)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $showForm) {
    $cpf = preg_replace("/\D/", "", $_POST['cpf']);
    $nome = $_POST['nome'];
    $senha = $_POST['senha'];

    if (strlen($cpf) !== 11) {
        $message = "<p style='color:red;'>CPF deve ter 11 dígitos.</p>";
    } elseif (!validarSenha($senha)) {
        $message = "<p style='color:red;'>A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula, uma letra minúscula, um número e um caractere especial.</p>";
    } else {
        $sql = "INSERT INTO usuarios (cpf, nome, senha) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $cpf, $nome, $senha);

        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Cadastro realizado com sucesso!</p>";
        } else {
            $message = "<p style='color:red;'>Erro ao cadastrar: " . $conn->error . "</p>";
        }

        $stmt->close();
    }
}

// Listagem de Usuários
if ($showList) {
    $sql = "SELECT cpf, nome, senha FROM usuarios"; // Incluindo a senha
    $result = $conn->query($sql);
}

// Edição de Usuário
if ($_SERVER["REQUEST_METHOD"] == "POST" && $showEdit) {
    $novo_cpf = preg_replace("/\D/", "", $_POST['cpf']);
    $nome = $_POST['nome'];
    $senha = $_POST['senha'];

    if (strlen($novo_cpf) !== 11) {
        $message = "<p style='color:red;'>CPF deve ter 11 dígitos.</p>";
    } elseif (!validarSenha($senha)) {
        $message = "<p style='color:red;'>A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula, uma letra minúscula, um número e um caractere especial.</p>";
    } else {
        $update_sql = "UPDATE usuarios SET cpf = ?, nome = ?, senha = ? WHERE cpf = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssss", $novo_cpf, $nome, $senha, $userToEdit);

        if ($update_stmt->execute()) {
            $message = "<p style='color:green;'>Usuário atualizado com sucesso!</p>";
            if ($novo_cpf !== $userToEdit) {
                $_SESSION['cpf'] = $novo_cpf; 
            }
        } else {
            $message = "<p style='color:red;'>Erro ao atualizar: " . $conn->error . "</p>";
        }

        $update_stmt->close();
    }
}

// Busca dados do usuário para edição
$userEditData = null;
if ($showEdit) {
    $sql = "SELECT cpf, nome, senha FROM usuarios WHERE cpf = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userToEdit);
    $stmt->execute();
    $resultEdit = $stmt->get_result();
    if ($resultEdit->num_rows > 0) {
        $userEditData = $resultEdit->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro e Listagem de Usuários</title>
    <style>
        .content { display: none; }
        .active { display: block; }
    </style>
    <script>
        function formatarCPF(input) {
            var valor = input.value.replace(/\D/g, ''); // Remove tudo o que não é número
            if (valor.length <= 3) {
                input.value = valor.replace(/(\d{1,3})/, '$1');
            } else if (valor.length <= 6) {
                input.value = valor.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            } else if (valor.length <= 9) {
                input.value = valor.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else {
                input.value = valor.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            }
        }

        function validarSenha(senha) {
            var regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            return regex.test(senha); // Retorna true se a senha for válida
        }

        function verificarSenha() {
            var senha = document.getElementById("senha").value;
            if (!validarSenha(senha)) {
                alert("A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula, uma letra minúscula, um número e um caractere especial.");
                return false; // Impede o envio do formulário
            }
            return true; // Se a senha for válida, o formulário pode ser enviado
        }
    </script>
</head>
<body>
    <div style="width: 800px; margin: 0 auto;">
        <div style="min-height: 100px; width: 100%; background-color: #4CAF50;">
            <?php echo "Olá ".$_SESSION["nome"]. "!"; ?>
            <a href="sair.php" style="float: right;">SAIR</a>
        </div>
        <div id="menu" style="width: 200px; background-color: #F4F4F4; min-height: 400px; float: left;">
            <h2>Menu</h2>
            <p><a href="?action=form">Cadastro de Usuários</a></p>
            <p><a href="?action=list">Listar Usuários</a></p>
        </div>
        <div style="background-color: #ddd; min-height: 400px; width: 600px; float:left">
            <div id="form-section" class="content <?php echo $showForm ? 'active' : ''; ?>">
                <h2>Cadastro de Usuários</h2>
                <form method="POST" onsubmit="return verificarSenha()"> <!-- Validação de senha no front-end -->
                    <label for="cpf">CPF:</label>
                    <input type="text" id="cpf" name="cpf" oninput="formatarCPF(this)" maxlength="14" required><br><br>

                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required><br><br>

                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" required><br><br>

                    <input type="submit" value="Cadastrar">
                </form>
                <?php if (isset($message)) echo $message; ?>
            </div>

            <div id="list-section" class="content <?php echo $showList ? 'active' : ''; ?>">
                <h2>Lista de Usuários</h2>
                <?php if ($showList): ?>
                    <table border="1">
                        <thead>
                            <tr>
                                <th>CPF</th>
                                <th>Nome</th>
                                <th>Senha</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo formatarCPF($row['cpf']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                        <td><?php echo htmlspecialchars($row['senha']); ?></td>
                                        <td>
                                            <a href="?action=edit&cpf=<?php echo urlencode($row['cpf']); ?>">Editar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4">Nenhum usuário encontrado.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <?php if ($showEdit && $userEditData): ?>
                <div id="edit-section" class="content active">
                    <h2>Editar Usuário</h2>
                    <form method="POST" onsubmit="return verificarSenha()"> <!-- Validação de senha no front-end -->
                        <label for="edit-cpf">CPF:</label>
                        <input type="text" id="edit-cpf" name="cpf" value="<?php echo formatarCPF($userEditData['cpf']); ?>" oninput="formatarCPF(this)" maxlength="14" required><br><br>

                        <label for="edit-nome">Nome:</label>
                        <input type="text" id="edit-nome" name="nome" value="<?php echo htmlspecialchars($userEditData['nome']); ?>" required><br><br>

                        <label for="edit-senha">Senha:</label>
                        <input type="password" id="edit-senha" name="senha" required><br><br>

                        <input type="submit" value="Atualizar">
                    </form>
                    <?php if (isset($message)) echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
