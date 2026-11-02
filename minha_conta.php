<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirAcessoUsuario();

$nomeUsuario = htmlspecialchars((string) ($_SESSION["nome"] ?? ""), ENT_QUOTES, "UTF-8");
$emailUsuario = htmlspecialchars((string) ($_SESSION["email"] ?? ""), ENT_QUOTES, "UTF-8");
$perfilUsuario = htmlspecialchars((string) ($_SESSION["perfil"] ?? ""), ENT_QUOTES, "UTF-8");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha conta | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/listagem.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>
<main class="busca-pagina">
    <h1>Minha conta</h1>
    <p><strong>Nome:</strong> <?= $nomeUsuario ?></p>
    <p><strong>E-mail:</strong> <?= $emailUsuario ?></p>
    <p><strong>Perfil:</strong> <?= $perfilUsuario ?></p>
</main>
<?php include __DIR__ . "/includes/rodape.php"; ?>
</body>
</html>