<?php
include("conexao.php");
session_start();

// Verifique se o usuário está logado
if (!isset($_SESSION['nome'])) {
    header("Location: login.php"); // Redireciona para a página de login
    exit;
}

// Verifica se o CPF foi passado como parâmetro na URL
if (!isset($_GET['cpf'])) {
    header("Location: index.php"); // Redireciona para a página principal
    exit;
}

$cpf = preg_replace("/\D/", "", $_GET['cpf']);  // Remove tudo o que não for número

// Busca os dados do usuário
$sql = "SELECT cpf, nome, senha FROM usuarios WHERE cpf = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php"); // Redireciona se o usuário não for encontrado
    exit;
}

$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_cpf = preg_replace("/\D/", "", $_POST['cpf']);  // Remove tudo o que não for número
    $nome = $_POST['nome'];
    $senha = $_POST['senha'];

    // Valida CPF
    if (strlen($novo_cpf) !== 11) {
        $message = "<p style='color:red;'>CPF deve ter 11 dígitos.</p>";
    } else {
        // Verifica a senha com a expressão regular no PHP (para evitar envio de dados inválidos)
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $senha)) {
            $message = "<p style='color:red;'>A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula, uma letra minúscula, um número e um caractere especial.</p>";
        } else {
            // Atualiza os dados do usuário
            $update_sql = "UPDATE usuarios SET cpf = ?, nome = ?, senha = ? WHERE cpf = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssss", $novo_cpf, $nome, $senha, $cpf);

            if ($update_stmt->execute()) {
                $message = "<p style='color:green;'>Usuário atualizado com sucesso!</p>";

                // Atualiza a sessão com o novo CPF
                if ($novo_cpf !== $cpf) {
                    $_SESSION['cpf'] = $novo_cpf; 
                }
            } else {
                $message = "<p style='color:red;'>Erro ao atualizar: " . $conn->error . "</p>";
            }

            $update_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
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
            return regex.test(senha);
        }

        function verificarSenha() {
            var senha = document.getElementById("senha").value;
            if (!validarSenha(senha)) {
                alert("A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula, uma letra minúscula, um número e um caractere especial.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div style="width: 800px; margin: 0 auto;">
        <h2>Editar Usuário</h2>
        <?php if (isset($message)) echo $message; ?>
        <form method="POST" onsubmit="return verificarSenha()"> <!-- Chama a função de verificação de senha -->
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" value="<?php echo formatarCPF($user['cpf']); ?>" oninput="formatarCPF(this)" maxlength="14" required><br><br>

            <label for="nome">Nome:</label>
            <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required><br><br>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required><br><br>

            <input type="submit" value="Atualizar">
        </form>
    </div>
</body>
</html>
