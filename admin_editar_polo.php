<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirPerfil(["admin"]);
require_once __DIR__ . "/config/conexao.php";
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit("Polo inválido."); }
$stmt = $pdo->prepare("SELECT id, nome, cep, logradouro, bairro, cidade, estado, numero, complemento, contato FROM polos WHERE id = :id LIMIT 1");
$stmt->execute([":id" => $id]);
$polo = $stmt->fetch();
if (!$polo) { http_response_code(404); exit("Polo não encontrado."); }
if (empty($_SESSION["csrf_admin_provisionamento"])) $_SESSION["csrf_admin_provisionamento"] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar polo | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>
<main class="admin-pagina">
    <a class="admin-voltar" href="admin.php">&larr; Voltar ao painel</a>
    <section class="admin-secao">
        <h1>Editar polo</h1>
        <form class="admin-formulario admin-formulario-polo" action="actions/admin_atualizar_polo.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_admin_provisionamento"], ENT_QUOTES, "UTF-8") ?>">
            <input type="hidden" name="polo_id" value="<?= (int) $polo["id"] ?>">
            <label><span>Nome do polo</span><input name="nome" maxlength="100" value="<?= htmlspecialchars($polo["nome"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>CEP</span><input name="cep" inputmode="numeric" maxlength="9" value="<?= htmlspecialchars($polo["cep"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>Logradouro</span><input name="logradouro" maxlength="150" value="<?= htmlspecialchars($polo["logradouro"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>Número</span><input name="numero" maxlength="20" value="<?= htmlspecialchars($polo["numero"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>Complemento</span><input name="complemento" maxlength="100" value="<?= htmlspecialchars($polo["complemento"] ?? "", ENT_QUOTES, "UTF-8") ?>"></label>
            <label><span>Bairro</span><input name="bairro" maxlength="100" value="<?= htmlspecialchars($polo["bairro"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>Cidade</span><input name="cidade" maxlength="100" value="<?= htmlspecialchars($polo["cidade"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>Estado (UF)</span><input name="estado" maxlength="2" pattern="[A-Za-z]{2}" value="<?= htmlspecialchars($polo["estado"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>Contato da coordenação</span><input name="contato" maxlength="100" value="<?= htmlspecialchars($polo["contato"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <button type="submit">Salvar alterações</button>
        </form>
    </section>
</main>
<?php include __DIR__ . "/includes/rodape.php"; ?>
</body>
</html>
