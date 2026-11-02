<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirAcessoUsuario();
require_once __DIR__ . "/config/conexao.php";

$entradaBusca = $_GET["q"] ?? "";
$termo = is_string($entradaBusca) ? trim($entradaBusca) : "";
$poloSelecionado = filter_input(INPUT_GET, "polo", FILTER_VALIDATE_INT) ?: 0;
$entradaCategoria = $_GET["tipo"] ?? "";
$categoriaSelecionada = is_string($entradaCategoria) ? trim($entradaCategoria) : "";

$polos = $pdo->query("SELECT id, nome FROM polos ORDER BY nome")->fetchAll();
$categorias = $pdo->query("SELECT DISTINCT categoria FROM objetos WHERE status = 'disponivel' ORDER BY categoria")->fetchAll();

$sql = "SELECT o.id, o.titulo, o.descricao, o.categoria, o.data_ocorrencia,
               p.nome AS nome_polo, p.cidade, p.estado,
               COALESCE(
                   (SELECT f.caminho FROM fotos_objetos f
                    WHERE f.objeto_id = o.id ORDER BY f.foto_numero ASC LIMIT 1),
                   o.imagem
               ) AS foto
        FROM objetos o
        INNER JOIN polos p ON p.id = o.polo_id
        WHERE o.status = 'disponivel'";
$params = [];

if ($termo !== "") {
    $sql .= " AND (o.titulo LIKE :termo_titulo OR o.descricao LIKE :termo_descricao OR o.categoria LIKE :termo_categoria OR p.nome LIKE :termo_polo)";
    $params[":termo_titulo"] = "%" . $termo . "%";
    $params[":termo_descricao"] = "%" . $termo . "%";
    $params[":termo_categoria"] = "%" . $termo . "%";
    $params[":termo_polo"] = "%" . $termo . "%";
}
if ($poloSelecionado > 0) {
    $sql .= " AND o.polo_id = :polo";
    $params[":polo"] = $poloSelecionado;
}
if ($categoriaSelecionada !== "") {
    $sql .= " AND o.categoria = :categoria";
    $params[":categoria"] = $categoriaSelecionada;
}
$sql .= " ORDER BY o.data_ocorrencia DESC, o.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$objetos = $stmt->fetchAll();

function dataFormatada(string $data): string {
    return date("d/m/Y", strtotime($data));
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar objetos | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/listagem.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>

<main class="busca-pagina">
    <div class="busca-cabecalho">
        <h1>Itens encontrados</h1>
        <form class="busca-filtros" method="GET" action="buscar.php">
            <label>
                <span>Polo:</span>
                <select name="polo">
                    <option value="">Todos</option>
                    <?php foreach ($polos as $polo): ?>
                        <option value="<?= (int)$polo["id"] ?>" <?= $poloSelecionado === (int)$polo["id"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($polo["nome"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span>Tipo:</span>
                <select name="tipo">
                    <option value="">Todos</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= htmlspecialchars($categoria["categoria"]) ?>" <?= $categoriaSelecionada === $categoria["categoria"] ? "selected" : "" ?>>
                            <?= htmlspecialchars($categoria["categoria"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <input type="hidden" name="q" value="<?= htmlspecialchars($termo) ?>">
            <button type="submit" class="busca-aplicar-filtros">Filtrar</button>
            <a class="busca-limpar" href="buscar.php">Limpar</a>
        </form>
    </div>

    <?php if ($objetos): ?>
        <section class="objetos-grade" aria-label="Objetos encontrados">
            <?php foreach ($objetos as $objeto): ?>
                <article class="objeto-card">
                    <?php if (!empty($objeto["foto"])): ?>
                        <img src="<?= htmlspecialchars($objeto["foto"]) ?>" alt="<?= htmlspecialchars($objeto["titulo"]) ?>">
                    <?php else: ?>
                        <div class="objeto-card-sem-foto">Sem imagem</div>
                    <?php endif; ?>
                    <div class="objeto-card-conteudo">
                        <h2><?= htmlspecialchars($objeto["titulo"]) ?></h2>
                        <p><strong>Polo:</strong> <?= htmlspecialchars($objeto["nome_polo"]) ?></p>
                        <p><strong>Encontrado em:</strong> <?= htmlspecialchars($objeto["descricao"]) ?></p>
                        <p class="objeto-card-data">Data: <?= dataFormatada($objeto["data_ocorrencia"]) ?></p>
                        <a class="botao-detalhes-card" href="detalhes.php?id=<?= (int) $objeto["id"] ?>">Ver detalhes</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <p class="busca-vazia">Nenhum objeto encontrado. <a href="buscar.php">Limpar pesquisa e filtros</a>.</p>
    <?php endif; ?>
</main>

<?php include __DIR__ . "/includes/rodape.php"; ?>
    <script src="assets/js/script.js" defer></script>
</body>
</html>