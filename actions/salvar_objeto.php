<?php
session_start();

require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["funcionario"]);
require_once __DIR__ . "/../config/conexao.php";

// O formulário só pode ser enviado pelo método POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecionar("../index.php");
}

// Dados do funcionário logado.
$usuarioId = $_SESSION["usuario_id"] ?? null;
$poloId = $_SESSION["polo_id"] ?? null;

// O funcionário precisa estar vinculado a um polo.
if (!$usuarioId || !$poloId) {
    exit("Não foi possível identificar o funcionário ou o polo.");
}

// Recebe os dados do formulário.
$titulo = trim($_POST["titulo"] ?? "");
$descricao = trim($_POST["descricao"] ?? "");
$categoria = trim($_POST["categoria"] ?? "");
$dataOcorrencia = $_POST["data_ocorrencia"] ?? "";

// Validação básica dos campos.
if (
    $titulo === "" ||
    $descricao === "" ||
    $categoria === "" ||
    $dataOcorrencia === ""
) {
    exit("Preencha todos os campos obrigatórios.");
}

if (mb_strlen($titulo) > 100) {
    exit("O nome do objeto deve ter no máximo 100 caracteres.");
}

// Categorias permitidas no formulário.
$categoriasPermitidas = [
    "Acessórios",
    "Documentos",
    "Eletrônicos",
    "Material escolar",
    "Roupas",
    "Outros"
];

if (!in_array($categoria, $categoriasPermitidas, true)) {
    exit("A categoria selecionada é inválida.");
}

// Valida a data.
$dataValida = DateTime::createFromFormat("Y-m-d", $dataOcorrencia);

if (
    !$dataValida ||
    $dataValida->format("Y-m-d") !== $dataOcorrencia
) {
    exit("A data informada é inválida.");
}

if ($dataOcorrencia > date("Y-m-d")) {
    exit("A data não pode ser futura.");
}

// Verifica se foram enviadas fotos.
$fotos = $_FILES["fotos"] ?? null;
$totalFotos = 0;

// Verifica se o formulário enviou algum arquivo.
if (
    $fotos !== null &&
    isset($fotos["error"]) &&
    is_array($fotos["error"])
) {
    // Código 4 significa que nenhum arquivo foi selecionado.
    if ($fotos["error"][0] !== UPLOAD_ERR_NO_FILE) {
        $totalFotos = count($fotos["error"]);
    }
}

// Limite de 3 fotos por objeto.
if ($totalFotos > 3) {
    exit("Você pode enviar no máximo 3 fotos por objeto.");
}

$fotos = $_FILES["fotos"] ?? null;
$totalFotos = 0;

// Verifica se o formulário enviou algum arquivo.
if (
    $fotos !== null &&
    isset($fotos["error"]) &&
    is_array($fotos["error"])
) {
    // Código 4 significa que nenhum arquivo foi selecionado.
    if ($fotos["error"][0] !== UPLOAD_ERR_NO_FILE) {
        $totalFotos = count($fotos["error"]);
    }
}

// Limite de 3 fotos por objeto.
if ($totalFotos > 3) {
    exit("Você pode enviar no máximo 3 fotos por objeto.");
}

// Tipos de imagem permitidos.
$tiposPermitidos = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

// Tipos de imagem permitidos.
$tiposPermitidos = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

$arquivosSalvos = [];
$diretorioFotos = __DIR__ . "/../assets/img/objetos/";

// Confere se a pasta de fotos existe.
if (!is_dir($diretorioFotos)) {
    exit("A pasta de imagens não foi encontrada.");
}

// Valida as fotos antes de salvar o objeto.
if ($fotos && $totalFotos > 0) {
    $verificador = new finfo(FILEINFO_MIME_TYPE);

    for ($i = 0; $i < $totalFotos; $i++) {
        $erroUpload = $fotos["error"][$i];

        if ($erroUpload !== UPLOAD_ERR_OK) {
            exit("Ocorreu um erro ao enviar uma das fotos.");
        }

        // Limite de 5 MB por foto.
        if ($fotos["size"][$i] > 5 * 1024 * 1024) {
            exit("Cada foto deve ter no máximo 5 MB.");
        }

        $caminhoTemporario = $fotos["tmp_name"][$i];
        $tipoMime = $verificador->file($caminhoTemporario);

        if (!isset($tiposPermitidos[$tipoMime])) {
            exit("Formato de foto inválido. Use JPG, PNG ou WEBP.");
        }

        // Confere se o arquivo realmente é uma imagem.
        if (getimagesize($caminhoTemporario) === false) {
            exit("Um dos arquivos enviados não é uma imagem válida.");
        }
    }
}

try {
    // Inicia uma transação para manter o banco consistente.
    $pdo->beginTransaction();

    // Cadastra o objeto.
    $sqlObjeto = "
        INSERT INTO objetos (
            usuario_id,
            polo_id,
            titulo,
            descricao,
            categoria,
            data_ocorrencia,
            status
        ) VALUES (
            :usuario_id,
            :polo_id,
            :titulo,
            :descricao,
            :categoria,
            :data_ocorrencia,
            'disponivel'
        )
    ";

    $stmtObjeto = $pdo->prepare($sqlObjeto);

    $stmtObjeto->execute([
        ":usuario_id" => $usuarioId,
        ":polo_id" => $poloId,
        ":titulo" => $titulo,
        ":descricao" => $descricao,
        ":categoria" => $categoria,
        ":data_ocorrencia" => $dataOcorrencia
    ]);

    // Recupera o ID do objeto recém-cadastrado.
    $objetoId = $pdo->lastInsertId();

    // Salva as fotos, caso tenham sido enviadas.
    if ($fotos && $totalFotos > 0) {
        $sqlFoto = "
            INSERT INTO fotos_objetos (
                objeto_id,
                foto_numero,
                caminho
            ) VALUES (
                :objeto_id,
                :foto_numero,
                :caminho
            )
        ";

        $stmtFoto = $pdo->prepare($sqlFoto);

        for ($i = 0; $i < $totalFotos; $i++) {
            $tipoMime = (new finfo(FILEINFO_MIME_TYPE))
                ->file($fotos["tmp_name"][$i]);

            $extensao = $tiposPermitidos[$tipoMime];

            // Nome aleatório para evitar conflitos e nomes perigosos.
            $nomeArquivo = bin2hex(random_bytes(16)) . "." . $extensao;

            $caminhoCompleto = $diretorioFotos . $nomeArquivo;
            $caminhoBanco = "assets/img/objetos/" . $nomeArquivo;

            if (
                !move_uploaded_file(
                    $fotos["tmp_name"][$i],
                    $caminhoCompleto
                )
            ) {
                throw new Exception("Não foi possível salvar uma das fotos.");
            }

            $arquivosSalvos[] = $caminhoCompleto;

            $stmtFoto->execute([
                ":objeto_id" => $objetoId,
                ":foto_numero" => $i + 1,
                ":caminho" => $caminhoBanco
            ]);
        }
    }

    // Confirma as gravações no banco.
    $pdo->commit();

    redirecionar("../index.php");

} catch (Throwable $erro) {
    // Desfaz as alterações no banco.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Remove arquivos que já tenham sido enviados para a pasta.
    foreach ($arquivosSalvos as $arquivo) {
        if (file_exists($arquivo)) {
            unlink($arquivo);
        }
    }

    error_log($erro->getMessage());

    exit("Não foi possível cadastrar o objeto. Verifique os dados e tente novamente.");
}