<?php
session_start();
include("conexao.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber e limpar os dados do formulário
    $cpf = preg_replace("/\D/", "", $_POST['cpf']);  // Remove tudo que não for número
    $senha = $_POST['senha'];

    // Validar CPF
    if (strlen($cpf) !== 11) {
        $message = "<p style='color:red;'>CPF deve ter 11 dígitos.</p>";
    } else {
        // Validar a senha
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $senha)) {
            $message = "<p style='color:red;'>A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula, uma letra minúscula, um número e um caractere especial.</p>";
        } else {
            // Verificar se o CPF e a senha estão corretos no banco de dados
            $sql = "SELECT * FROM usuarios WHERE cpf = ? AND senha = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $cpf, $senha);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Login bem-sucedido
                $user = $result->fetch_assoc();
                $_SESSION["cpf"] = $user["cpf"];
                $_SESSION["nome"] = $user["nome"];
                header("Location: principal.php");
                exit();
            } else {
                $message = "<p style='color:red;'>Usuário ou senha inválidos.</p>";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
    <div style="width: 400px; margin: 0 auto; padding: 20px;">
        <h2>Login</h2>
        <?php if ($message): ?>
            <div><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" onsubmit="return verificarSenha()">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" oninput="formatarCPF(this)" maxlength="14" required><br><br>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required><br><br>

            <input type="submit" value="Entrar">
        </form>
    </div>
</body>
</html>
