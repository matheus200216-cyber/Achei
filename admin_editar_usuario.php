<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirPerfil(["admin"]);
require_once __DIR__ . "/config/conexao.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit("Usuário inválido."); }
$stmt = $pdo->prepare("SELECT id, nome, email, perfil, polo_id FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute([":id" => $id]);
$usuario = $stmt->fetch();
if (!$usuario) { http_response_code(404); exit("Usuário não encontrado."); }
$polos = $pdo->query("SELECT id, nome FROM polos ORDER BY nome")->fetchAll();
$mensagemAdmin = $_SESSION["flash_admin"] ?? null;
unset($_SESSION["flash_admin"]);
if (empty($_SESSION["csrf_admin_provisionamento"])) $_SESSION["csrf_admin_provisionamento"] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar usuário | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>
<main class="admin-pagina">
    <a class="admin-voltar" href="admin.php">&larr; Voltar ao painel</a>
    <section class="admin-secao">
        <h1>Editar usuário</h1>
        <?php if (is_array($mensagemAdmin)): ?>
            <p class="admin-mensagem admin-mensagem-<?= htmlspecialchars($mensagemAdmin["tipo"], ENT_QUOTES, "UTF-8") ?>"><?= htmlspecialchars($mensagemAdmin["texto"], ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>
        <p>Atualize os dados da conta e o perfil. Funcionários precisam estar vinculados a um polo.</p>
        <form class="admin-formulario admin-formulario-edicao" action="actions/admin_atualizar_usuario.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_admin_provisionamento"], ENT_QUOTES, "UTF-8") ?>">
            <input type="hidden" name="usuario_id" value="<?= (int) $usuario["id"] ?>">
            <label><span>Nome completo</span><input type="text" name="nome" maxlength="100" value="<?= htmlspecialchars($usuario["nome"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>E-mail</span><input type="email" name="email" maxlength="150" value="<?= htmlspecialchars($usuario["email"], ENT_QUOTES, "UTF-8") ?>" required></label>
            <label><span>Perfil</span>
                <select name="perfil" id="perfilUsuario" required>
                    <option value="aluno" <?= $usuario["perfil"] === "aluno" ? "selected" : "" ?>>Aluno</option>
                    <option value="funcionario" <?= $usuario["perfil"] === "funcionario" ? "selected" : "" ?>>Funcionário</option>
                    <option value="admin" <?= $usuario["perfil"] === "admin" ? "selected" : "" ?>>Administrador</option>
                </select>
            </label>
            <label id="campoPoloUsuario" <?= $usuario["perfil"] !== "funcionario" ? "hidden" : "" ?>><span>Polo vinculado</span>
                    <select name="polo_id" id="poloUsuario" <?= $usuario["perfil"] === "funcionario" ? "required" : "disabled" ?>>
                        <option value="">Selecione um polo</option>
                        <?php foreach ($polos as $polo): ?>
                            <option value="<?= (int) $polo["id"] ?>" <?= (int) $usuario["polo_id"] === (int) $polo["id"] ? "selected" : "" ?>><?= htmlspecialchars($polo["nome"], ENT_QUOTES, "UTF-8") ?></option>
                        <?php endforeach; ?>
                    </select>
            </label>
            <button type="submit">Salvar alterações</button>
        </form>
    </section>
</main>
<?php include __DIR__ . "/includes/rodape.php"; ?>
<script>
(() => {
    const perfil = document.getElementById("perfilUsuario");
    const campoPolo = document.getElementById("campoPoloUsuario");
    const polo = document.getElementById("poloUsuario");
    function atualizarPolo() {
        const funcionario = perfil.value === "funcionario";
        campoPolo.hidden = !funcionario;
        polo.disabled = !funcionario;
        polo.required = funcionario;
    }
    perfil.addEventListener("change", atualizarPolo);
    atualizarPolo();
})();
</script>
</body>
</html>
