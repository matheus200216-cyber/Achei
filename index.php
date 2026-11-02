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
$pagina = max(1, filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT) ?: 1);
$porPagina = 8;

$polos = $pdo->query("SELECT id, nome FROM polos ORDER BY nome")->fetchAll();
$categorias = $pdo->query("SELECT DISTINCT categoria FROM objetos WHERE status = 'disponivel' ORDER BY categoria")->fetchAll();

$where = ["o.status = 'disponivel'"];
$params = [];
if ($termo !== "") {
    $where[] = "(o.titulo LIKE :termo_titulo OR o.descricao LIKE :termo_descricao OR o.categoria LIKE :termo_categoria OR p.nome LIKE :termo_polo)";
    $params[":termo_titulo"] = "%" . $termo . "%";
    $params[":termo_descricao"] = "%" . $termo . "%";
    $params[":termo_categoria"] = "%" . $termo . "%";
    $params[":termo_polo"] = "%" . $termo . "%";
}
if ($poloSelecionado > 0) {
    $where[] = "o.polo_id = :polo";
    $params[":polo"] = $poloSelecionado;
}
if ($categoriaSelecionada !== "") {
    $where[] = "o.categoria = :categoria";
    $params[":categoria"] = $categoriaSelecionada;
}
$whereSql = implode(" AND ", $where);

$stmtTotal = $pdo->prepare("SELECT COUNT(*) FROM objetos o INNER JOIN polos p ON p.id = o.polo_id WHERE $whereSql");
$stmtTotal->execute($params);
$totalObjetos = (int) $stmtTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalObjetos / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

$sql = "SELECT o.id, o.titulo, o.descricao, o.categoria, o.data_ocorrencia,
               p.nome AS nome_polo, p.cidade, p.estado,
               COALESCE((SELECT f.caminho FROM fotos_objetos f
                         WHERE f.objeto_id = o.id ORDER BY f.foto_numero ASC LIMIT 1), o.imagem) AS foto
        FROM objetos o
        INNER JOIN polos p ON p.id = o.polo_id
        WHERE $whereSql
        ORDER BY o.data_ocorrencia DESC, o.id DESC
        LIMIT :limite OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $chave => $valor) {
    $stmt->bindValue($chave, $valor, PDO::PARAM_STR);
}
$stmt->bindValue(":limite", $porPagina, PDO::PARAM_INT);
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->execute();
$objetos = $stmt->fetchAll();

function dataInicialFormatada(string $data): string {
    return date("d/m/Y", strtotime($data));
}

function linkPagina(int $pagina, string $termo, int $polo, string $tipo): string {
    return "index.php?" . http_build_query(["q" => $termo, "polo" => $polo ?: "", "tipo" => $tipo, "pagina" => $pagina]);
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achei! | Itens encontrados</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/listagem.css">
</head>
<body class="pagina-com-rodape">
    <?php include __DIR__ . "/includes/menu.php"; ?>

    <main class="busca-pagina pagina-inicial-listagem">
        <div class="busca-cabecalho">
            <h1>Itens encontrados</h1>
            <form class="busca-filtros" method="GET" action="index.php">
                <label>
                    <span>Polo:</span>
                    <select name="polo">
                        <option value="">Todos</option>
                        <?php foreach ($polos as $polo): ?>
                            <option value="<?= (int) $polo["id"] ?>" <?= $poloSelecionado === (int) $polo["id"] ? "selected" : "" ?>>
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
                <a class="busca-limpar" href="index.php">Limpar</a>
            </form>
        </div>

        <?php if ($objetos): ?>
            <section class="objetos-grade" aria-label="Itens encontrados">
                <?php foreach ($objetos as $objeto): ?>
                    <article class="objeto-card">
                        <?php if (!empty($objeto["foto"])): ?>
                            <img src="<?= htmlspecialchars($objeto["foto"]) ?>" alt="<?= htmlspecialchars($objeto["titulo"]) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="objeto-card-sem-foto">Sem imagem</div>
                        <?php endif; ?>
                        <div class="objeto-card-conteudo">
                            <h2><?= htmlspecialchars($objeto["titulo"]) ?></h2>
                            <p class="objeto-card-local"><strong>Polo:</strong> <?= htmlspecialchars($objeto["nome_polo"]) ?></p>
                            <p class="objeto-card-descricao"><strong>Achado em:</strong> <?= htmlspecialchars($objeto["descricao"]) ?></p>
                            <p class="objeto-card-data">Data: <?= dataInicialFormatada($objeto["data_ocorrencia"]) ?></p>
                            <a class="botao-detalhes-card" href="detalhes.php?id=<?= (int) $objeto["id"] ?>">Ver detalhes</a>
                            
                        </div>
                        
                    </article>
                <?php endforeach; ?>
            </section>

            <?php if ($totalPaginas > 1): ?>
                <nav class="paginacao-objetos" aria-label="Paginação">
                    <?php $primeiraVisivel = max(1, $pagina - 2); $ultimaVisivel = min($totalPaginas, $pagina + 2); ?>
                    <?php if ($pagina > 1): ?>
                        <a class="paginacao-controle" href="<?= htmlspecialchars(linkPagina($pagina - 1, $termo, $poloSelecionado, $categoriaSelecionada)) ?>" aria-label="Página anterior">&larr; <span>Anterior</span></a>
                    <?php endif; ?>
                    <?php if ($primeiraVisivel > 1): ?>
                        <a href="<?= htmlspecialchars(linkPagina(1, $termo, $poloSelecionado, $categoriaSelecionada)) ?>" aria-label="Página 1">1</a>
                        <?php if ($primeiraVisivel > 2): ?><span class="paginacao-reticencias" aria-hidden="true">…</span><?php endif; ?>
                    <?php endif; ?>
                    <?php for ($i = $primeiraVisivel; $i <= $ultimaVisivel; $i++): ?>
                        <a class="<?= $i === $pagina ? "pagina-atual" : "" ?>" href="<?= htmlspecialchars(linkPagina($i, $termo, $poloSelecionado, $categoriaSelecionada)) ?>" <?= $i === $pagina ? 'aria-current="page"' : "" ?> aria-label="Página <?= $i ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($ultimaVisivel < $totalPaginas): ?>
                        <?php if ($ultimaVisivel < $totalPaginas - 1): ?><span class="paginacao-reticencias" aria-hidden="true">…</span><?php endif; ?>
                        <a href="<?= htmlspecialchars(linkPagina($totalPaginas, $termo, $poloSelecionado, $categoriaSelecionada)) ?>" aria-label="Página <?= $totalPaginas ?>"><?= $totalPaginas ?></a>
                    <?php endif; ?>
                    <?php if ($pagina < $totalPaginas): ?>
                        <a class="paginacao-controle" href="<?= htmlspecialchars(linkPagina($pagina + 1, $termo, $poloSelecionado, $categoriaSelecionada)) ?>" aria-label="Próxima página"><span>Próxima</span> &rarr;</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <p class="busca-vazia">Nenhum item encontrado. <a href="index.php">Limpar pesquisa e filtros</a>.</p>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . "/includes/rodape.php"; ?>
    <script src="assets/js/script.js" defer></script>
</body>
</html>
