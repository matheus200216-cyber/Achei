<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirPerfil(["funcionario", "admin"]);
require_once __DIR__ . "/config/conexao.php";

$objetoId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$usuarioEhAdmin = ($_SESSION["perfil"] ?? null) === "admin";
$poloId = (int) ($_SESSION["polo_id"] ?? 0);
if (!$objetoId || (!$usuarioEhAdmin && $poloId < 1)) {
    http_response_code(400);
    exit("Objeto inválido.");
}

if ($usuarioEhAdmin) {
    $stmt = $pdo->prepare("SELECT id, titulo, descricao, categoria, data_ocorrencia, status FROM objetos WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $objetoId]);
} else {
    $stmt = $pdo->prepare("SELECT id, titulo, descricao, categoria, data_ocorrencia, status FROM objetos WHERE id = :id AND polo_id = :polo_id LIMIT 1");
    $stmt->execute([":id" => $objetoId, ":polo_id" => $poloId]);
}
$objeto = $stmt->fetch();
if (!$objeto) {
    http_response_code(404);
    exit("Objeto não encontrado neste polo.");
}

$stmtFotos = $pdo->prepare("SELECT caminho FROM fotos_objetos WHERE objeto_id = :id ORDER BY foto_numero");
$stmtFotos->execute([":id" => $objetoId]);
$fotos = $stmtFotos->fetchAll();
if (empty($_SESSION["csrf_gerenciar_objetos"])) {
    $_SESSION["csrf_gerenciar_objetos"] = bin2hex(random_bytes(32));
}
$categorias = ["Acessórios", "Documentos", "Eletrônicos", "Material escolar", "Roupas", "Outros"];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar objeto | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cadastro-objeto.css">
    <link rel="stylesheet" href="assets/css/gerenciar-objetos.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>
<main class="editar-objeto-pagina">
    <a class="gerenciar-link-voltar" href="gerenciar_objetos.php">&larr; Voltar aos objetos</a>
    <section class="formulario-objeto">
        <h1>Editar objeto</h1>
        <p>Atualize as informações do objeto do seu polo.</p>
        <form action="actions/atualizar_objeto.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_gerenciar_objetos"], ENT_QUOTES, "UTF-8") ?>">
            <input type="hidden" name="objeto_id" value="<?= (int) $objeto["id"] ?>">
            <div class="campo">
                <label for="titulo">Nome do objeto</label>
                <input type="text" id="titulo" name="titulo" maxlength="100" value="<?= htmlspecialchars($objeto["titulo"], ENT_QUOTES, "UTF-8") ?>" required>
            </div>
            <div class="campo">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="4" required><?= htmlspecialchars($objeto["descricao"], ENT_QUOTES, "UTF-8") ?></textarea>
            </div>
            <div class="campo">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" required>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= htmlspecialchars($categoria, ENT_QUOTES, "UTF-8") ?>" <?= $objeto["categoria"] === $categoria ? "selected" : "" ?>><?= htmlspecialchars($categoria, ENT_QUOTES, "UTF-8") ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label for="data_ocorrencia">Data em que foi encontrado</label>
                <input type="date" id="data_ocorrencia" name="data_ocorrencia" value="<?= htmlspecialchars($objeto["data_ocorrencia"], ENT_QUOTES, "UTF-8") ?>" required>
            </div>
            <div class="campo">
                <label for="status">Situação</label>
                <select id="status" name="status" required>
                    <option value="disponivel" <?= $objeto["status"] === "disponivel" ? "selected" : "" ?>>Disponível</option>
                    <option value="retirado" <?= $objeto["status"] === "retirado" ? "selected" : "" ?>>Retirado</option>
                </select>
            </div>

            <?php if ($fotos): ?>
                <div class="campo">
                    <span class="editar-fotos-titulo">Fotos cadastradas</span>
                    <div class="editar-fotos-lista">
                        <?php foreach ($fotos as $indice => $foto): ?>
                            <img src="<?= htmlspecialchars($foto["caminho"], ENT_QUOTES, "UTF-8") ?>" alt="Foto <?= $indice + 1 ?> de <?= htmlspecialchars($objeto["titulo"], ENT_QUOTES, "UTF-8") ?>" loading="lazy">
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <button type="submit" class="botao-principal">Salvar alterações</button>
        </form>
    </section>
</main>
<?php include __DIR__ . "/includes/rodape.php"; ?>
</body>
</html>
