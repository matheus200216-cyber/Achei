<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["funcionario", "admin"]);

function voltarListaObjetos(string $tipo, string $mensagem): void
{
    $_SESSION["flash_objetos"] = ["tipo" => $tipo, "texto" => $mensagem];
    redirecionar("../gerenciar_objetos.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") redirecionar("../gerenciar_objetos.php");
$tokenSessao = $_SESSION["csrf_gerenciar_objetos"] ?? "";
$tokenEnviado = $_POST["csrf_token"] ?? "";
if (!is_string($tokenSessao) || !is_string($tokenEnviado) || $tokenSessao === "" || !hash_equals($tokenSessao, $tokenEnviado)) {
    http_response_code(403);
    exit("Solicitação inválida. Atualize a página e tente novamente.");
}
$id = filter_var($_POST["objeto_id"] ?? null, FILTER_VALIDATE_INT);
$usuarioEhAdmin = ($_SESSION["perfil"] ?? null) === "admin";
$poloId = (int) ($_SESSION["polo_id"] ?? 0);
if (!$id || (!$usuarioEhAdmin && $poloId < 1)) voltarListaObjetos("erro", "Objeto inválido.");

require_once __DIR__ . "/../config/conexao.php";
$sqlObjeto = "SELECT imagem FROM objetos WHERE id = :id" . ($usuarioEhAdmin ? "" : " AND polo_id = :polo_id") . " LIMIT 1";
$parametrosObjeto = [":id" => (int) $id];
if (!$usuarioEhAdmin) $parametrosObjeto[":polo_id"] = $poloId;
$stmt = $pdo->prepare($sqlObjeto);
$stmt->execute($parametrosObjeto);
$objeto = $stmt->fetch();
if (!$objeto) voltarListaObjetos("erro", "Objeto não encontrado neste polo.");

$stmtSolicitacoes = $pdo->prepare("SELECT COUNT(*) FROM solicitacoes WHERE objeto_id = :id");
$stmtSolicitacoes->execute([":id" => (int) $id]);
if ((int) $stmtSolicitacoes->fetchColumn() > 0) {
    voltarListaObjetos("erro", "Este objeto tem registros antigos de solicitação e não pode ser excluído. Você pode marcá-lo como retirado pela opção Editar.");
}

$stmtFotos = $pdo->prepare("SELECT caminho FROM fotos_objetos WHERE objeto_id = :id");
$stmtFotos->execute([":id" => (int) $id]);
$caminhos = array_column($stmtFotos->fetchAll(), "caminho");
if (!empty($objeto["imagem"])) $caminhos[] = $objeto["imagem"];

try {
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM fotos_objetos WHERE objeto_id = :id")->execute([":id" => (int) $id]);
    $sqlExcluir = "DELETE FROM objetos WHERE id = :id" . ($usuarioEhAdmin ? "" : " AND polo_id = :polo_id");
    $parametrosExcluir = [":id" => (int) $id];
    if (!$usuarioEhAdmin) $parametrosExcluir[":polo_id"] = $poloId;
    $stmtExcluir = $pdo->prepare($sqlExcluir);
    $stmtExcluir->execute($parametrosExcluir);
    if ($stmtExcluir->rowCount() !== 1) throw new RuntimeException("Objeto não foi excluído.");
    $pdo->commit();
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log($erro->getMessage());
    voltarListaObjetos("erro", "Não foi possível excluir o objeto. Tente novamente.");
}

$pastaImagens = realpath(__DIR__ . "/../assets/img/objetos");
if ($pastaImagens !== false) {
    foreach (array_unique($caminhos) as $caminho) {
        if (!is_string($caminho) || !str_starts_with(str_replace("\\", "/", $caminho), "assets/img/objetos/")) continue;
        $arquivo = realpath(__DIR__ . "/../" . str_replace(["/", "\\"], DIRECTORY_SEPARATOR, $caminho));
        if ($arquivo !== false && str_starts_with($arquivo, $pastaImagens . DIRECTORY_SEPARATOR) && is_file($arquivo)) {
            unlink($arquivo);
        }
    }
}
voltarListaObjetos("sucesso", "O objeto e as fotos cadastradas foram excluídos.");
