<?php
session_start();

require_once __DIR__ . "/config/conexao.php";
require_once __DIR__ . "/includes/funcoes.php";

if (usuarioLogado()) {
    redirecionar("index.php");
}

$mensagemErro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {
        $mensagemErro = "Informe seu e-mail e sua senha.";
    } else {
        $colunaProvisionamento = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'provisionado_por'")->fetch() ? "provisionado_por" : "NULL AS provisionado_por";

$sql = "
            SELECT
                id,
                nome,
                email,
                senha,
                perfil,
                polo_id,
                $colunaProvisionamento
            FROM usuarios
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":email" => $email
        ]);

        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario["senha"]) && $usuario["perfil"] === "funcionario" && empty($usuario["provisionado_por"])) {
            $mensagemErro = "Sua conta de funcionário aguarda aprovação do administrador.";
        } elseif ($usuario && password_verify($senha, $usuario["senha"])) {
            session_regenerate_id(true);

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["nome"] = $usuario["nome"];
            $_SESSION["email"] = $usuario["email"];
            $_SESSION["perfil"] = $usuario["perfil"];
            $_SESSION["polo_id"] = $usuario["polo_id"];
            $_SESSION["provisionado_por"] = $usuario["provisionado_por"] ?? null;

            redirecionar("index.php");
        } else {
            $mensagemErro = "E-mail ou senha incorretos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Achei!</title>

    <link rel="stylesheet" href="assets/css/autenticacao.css">
    <link rel="stylesheet" href="assets/css/senha.css">
    <link rel="stylesheet" href="assets/css/senha.css">
</head>
<body class="tela-login">

    <main class="login-layout">

        <!-- LADO ESQUERDO -->
        <section class="login-lateral">

            <div class="login-marca">
                <h1>ACHEI!</h1>
                <p>Achados e perdidos</p>
            </div>

            <div class="login-parceria">
                <p>Em parceria com</p>

                <div class="login-unisa">
                    <span>UNiSA</span>
                    <small>Universidade Santo Amaro</small>
                </div>
            </div>

        </section>

        <!-- LADO DIREITO -->
        <section class="login-conteudo">

            <div class="login-cartao">

                <h2>Login</h2>

                <div class="login-formulario-borda">

                    <?php if ($mensagemErro !== ""): ?>
                        <p class="mensagem-erro">
                            <?= htmlspecialchars($mensagemErro) ?>
                        </p>
                    <?php endif; ?>

                    <form action="login.php" method="POST" autocomplete="on">

                        <div class="login-campo">
                            <label for="email">Email</label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                autocomplete="username"
                                autocapitalize="none"
                                spellcheck="false"
                                maxlength="150"
                                placeholder="Digite o seu e-mail institucional"
                                required
                            >
                        </div>

                        <div class="login-campo">
                            <label for="senha">Senha</label>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                autocomplete="current-password"
                                placeholder="Digite a sua senha"
                                required
                            >
                        </div>

                        <button type="submit" class="login-botao">
                            Login
                        </button>

                        <p class="login-cadastro">
                            Não tem uma conta?
                            <a href="cadastro.php">Cadastre-se aqui!</a>
                        </p>

                    </form>

                </div>

            </div>

        </section>

    </main>

    <script src="assets/js/alternar-senha.js" defer></script>
</body>
</html>
