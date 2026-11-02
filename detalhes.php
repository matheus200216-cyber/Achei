<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirAcessoUsuario();
require_once __DIR__ . "/config/conexao.php";

$idObjeto = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$idObjeto) {
    http_response_code(404);
    exit("Objeto não encontrado.");
}

$sql = "SELECT o.id, o.titulo, o.descricao, o.categoria, o.data_ocorrencia,
               o.imagem, p.nome AS nome_polo, p.cidade, p.estado
        FROM objetos o
        INNER JOIN polos p ON p.id = o.polo_id
        WHERE o.id = :id AND o.status = 'disponivel'
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([":id" => $idObjeto]);
$objeto = $stmt->fetch();

if (!$objeto) {
    http_response_code(404);
    exit("Objeto não encontrado.");
}

$stmtFotos = $pdo->prepare(
    "SELECT caminho FROM fotos_objetos WHERE objeto_id = :id ORDER BY foto_numero ASC"
);
$stmtFotos->execute([":id" => $idObjeto]);
$fotos = array_column($stmtFotos->fetchAll(), "caminho");

if (empty($fotos) && !empty($objeto["imagem"])) {
    $fotos[] = $objeto["imagem"];
}
$dataFormatada = date("d/m/Y", strtotime($objeto["data_ocorrencia"]));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($objeto["titulo"]) ?> | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/detalhes.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>

<main class="detalhe-pagina">
    <section class="detalhe-galeria">
        <?php if ($fotos): ?>
            <div class="detalhe-carrossel" aria-label="Fotos do objeto">
                <img class="detalhe-foto-principal" id="fotoPrincipal"
                     src="<?= htmlspecialchars($fotos[0]) ?>"
                     alt="<?= htmlspecialchars($objeto["titulo"]) ?>">
                <?php if (count($fotos) > 1): ?>
                    <button type="button" class="detalhe-seta detalhe-seta-anterior" id="fotoAnterior"
                            aria-label="Ver foto anterior">&#10094;</button>
                    <button type="button" class="detalhe-seta detalhe-seta-proxima" id="fotoProxima"
                            aria-label="Ver pr�xima foto">&#10095;</button>
                    <span class="detalhe-contador" id="fotoContador" aria-live="polite">1 / <?= count($fotos) ?></span>
                <?php endif; ?>
            </div>
            <?php if (count($fotos) > 1): ?>
                <script>
                    (() => {
                        const fotos = <?= json_encode($fotos, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                        const imagem = document.getElementById("fotoPrincipal");
                        const contador = document.getElementById("fotoContador");
                        let fotoAtual = 0;

                        function mostrarFoto(indice) {
                            fotoAtual = (indice + fotos.length) % fotos.length;
                            imagem.src = fotos[fotoAtual];
                            contador.textContent = `${fotoAtual + 1} / ${fotos.length}`;
                        }

                        document.getElementById("fotoAnterior").addEventListener("click", () => mostrarFoto(fotoAtual - 1));
                        document.getElementById("fotoProxima").addEventListener("click", () => mostrarFoto(fotoAtual + 1));
                    })();
                </script>
            <?php endif; ?>
        <?php else: ?>
            <div class="detalhe-sem-foto">Sem imagem dispon�vel</div>
        <?php endif; ?>
    </section>

    <section class="detalhe-informacoes">
        <a class="botao-voltar-detalhe" href="index.php" onclick="if (document.referrer && new URL(document.referrer).origin === location.origin) { history.back(); return false; }">&larr; Voltar</a>
        <h1><?= htmlspecialchars($objeto["titulo"]) ?></h1>
        <div class="detalhe-dado"><span>📍</span> Encontrada e armazenada no polo: <?= htmlspecialchars($objeto["nome_polo"]) ?></div>
        <div class="detalhe-dado"><span>📍</span> Informações do local: <?= htmlspecialchars($objeto["descricao"]) ?></div>
        <div class="detalhe-dado"><span>▦</span> Data de encontro do objeto: <?= $dataFormatada ?></div>
        <h2>Informações adicionais:</h2>
        <p class="detalhe-descricao"><?= nl2br(htmlspecialchars($objeto["descricao"])) ?></p>
        <p class="detalhe-aviso">
            Para a retirada do item, compareça à secretaria do polo com alguma forma
            de comprovação de posse do objeto, como nota fiscal, imagem da compra,
            foto com o item ou semelhante.
        </p>
    </section>
</main>

<?php include __DIR__ . "/includes/rodape.php"; ?>
</body>
</html>
