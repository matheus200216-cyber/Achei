<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirPerfil(["funcionario", "admin"]);
require_once __DIR__ . "/config/conexao.php";

$usuarioEhAdmin = ($_SESSION["perfil"] ?? null) === "admin";
$poloId = (int) ($_SESSION["polo_id"] ?? 0);
if (!$usuarioEhAdmin && $poloId < 1) {
    http_response_code(403);
    exit("Sua conta não está vinculada a um polo.");
}

if (empty($_SESSION["csrf_gerenciar_objetos"])) {
    $_SESSION["csrf_gerenciar_objetos"] = bin2hex(random_bytes(32));
}
$mensagem = $_SESSION["flash_objetos"] ?? null;
unset($_SESSION["flash_objetos"]);

$sqlObjetos = "SELECT o.id, o.titulo, o.descricao, o.categoria, o.data_ocorrencia, o.status,
                      COALESCE((SELECT f.caminho FROM fotos_objetos f WHERE f.objeto_id = o.id ORDER BY f.foto_numero LIMIT 1), o.imagem) AS foto,
                      p.nome AS nome_polo
               FROM objetos o
               LEFT JOIN polos p ON p.id = o.polo_id";
if ($usuarioEhAdmin) {
    $stmt = $pdo->query($sqlObjetos . " ORDER BY o.criado_em DESC, o.id DESC");
} else {
    $stmt = $pdo->prepare($sqlObjetos . " WHERE o.polo_id = :polo_id ORDER BY o.criado_em DESC, o.id DESC");
    $stmt->execute([":polo_id" => $poloId]);
}
$objetos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar objetos | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/gerenciar-objetos.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>
<main class="gerenciar-objetos-pagina">
    <header class="gerenciar-objetos-cabecalho">
        <div>
            <p class="gerenciar-objetos-etiqueta"><?= $usuarioEhAdmin ? "ADMINISTRAÇÃO" : "ÁREA DO FUNCIONÁRIO" ?></p>
            <h1>Gerenciar objetos</h1>
            <p><?= $usuarioEhAdmin ? "Edite ou remova objetos de qualquer polo." : "Edite ou remova os objetos cadastrados no seu polo." ?></p>
        </div>
        <?php if (!$usuarioEhAdmin): ?>
            <a class="gerenciar-voltar-cadastro" href="cadastrar_objeto.php">Cadastrar objeto</a>
        <?php endif; ?>
    </header>

    <?php if (is_array($mensagem)): ?>
        <p class="gerenciar-mensagem gerenciar-mensagem-<?= htmlspecialchars($mensagem["tipo"], ENT_QUOTES, "UTF-8") ?>">
            <?= htmlspecialchars($mensagem["texto"], ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <?php if ($objetos): ?>
        <section class="gerenciar-objetos-lista" aria-label="Objetos do seu polo">
            <?php foreach ($objetos as $objeto): ?>
                <article class="gerenciar-objeto-card">
                    <?php if (!empty($objeto["foto"])): ?>
                        <img class="gerenciar-objeto-foto" src="<?= htmlspecialchars($objeto["foto"], ENT_QUOTES, "UTF-8") ?>" alt="Foto de <?= htmlspecialchars($objeto["titulo"], ENT_QUOTES, "UTF-8") ?>" loading="lazy">
                    <?php else: ?>
                        <div class="gerenciar-objeto-sem-foto">Sem foto</div>
                    <?php endif; ?>
                    <div class="gerenciar-objeto-conteudo">
                        <div class="gerenciar-objeto-titulo-linha">
                            <h2><?= htmlspecialchars($objeto["titulo"], ENT_QUOTES, "UTF-8") ?></h2>
                            <span class="gerenciar-status gerenciar-status-<?= $objeto["status"] === "retirado" ? "retirado" : "disponivel" ?>">
                                <?= $objeto["status"] === "retirado" ? "Retirado" : "Disponível" ?>
                            </span>
                        </div>
                        <p class="gerenciar-objeto-detalhe"><strong>Categoria:</strong> <?= htmlspecialchars($objeto["categoria"], ENT_QUOTES, "UTF-8") ?></p>
                        <?php if ($usuarioEhAdmin): ?><p class="gerenciar-objeto-detalhe"><strong>Polo:</strong> <?= htmlspecialchars($objeto["nome_polo"] ?? "—", ENT_QUOTES, "UTF-8") ?></p><?php endif; ?>
                        <p class="gerenciar-objeto-descricao"><?= htmlspecialchars($objeto["descricao"], ENT_QUOTES, "UTF-8") ?></p>
                        <div class="gerenciar-objeto-acoes">
                            <a class="gerenciar-editar" href="editar_objeto.php?id=<?= (int) $objeto["id"] ?>">Editar</a>
                            <form action="actions/excluir_objeto.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este objeto? Esta ação não pode ser desfeita.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_gerenciar_objetos"], ENT_QUOTES, "UTF-8") ?>">
                                <input type="hidden" name="objeto_id" value="<?= (int) $objeto["id"] ?>">
                                <button class="gerenciar-excluir" type="submit">Excluir</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <div class="gerenciar-objetos-vazio">
            <h2><?= $usuarioEhAdmin ? "Nenhum objeto cadastrado" : "Nenhum objeto cadastrado neste polo" ?></h2>
            <p><?= $usuarioEhAdmin ? "Os itens cadastrados aparecerão nesta página." : "Quando cadastrar um objeto, você poderá editá-lo ou excluí-lo por aqui." ?></p>
            <?php if (!$usuarioEhAdmin): ?><a class="gerenciar-voltar-cadastro" href="cadastrar_objeto.php">Cadastrar primeiro objeto</a><?php endif; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . "/includes/rodape.php"; ?>
</body>
</html>
