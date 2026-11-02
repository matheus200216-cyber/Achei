<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["admin"]);

function voltarExclusaoPolo(string $tipo, string $mensagem): void
{
    $_SESSION["flash_admin"] = ["tipo" => $tipo, "texto" => $mensagem];
    redirecionar("../admin.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") redirecionar("../admin.php");
$token = $_SESSION["csrf_admin_provisionamento"] ?? "";
$enviado = $_POST["csrf_token"] ?? "";
if (!is_string($token) || !is_string($enviado) || $token === "" || !hash_equals($token, $enviado)) { http_response_code(403); exit("Solicitação inválida."); }
$id = filter_var($_POST["polo_id"] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) voltarExclusaoPolo("erro", "Polo inválido.");

require_once __DIR__ . "/../config/conexao.php";
$stmtPolo = $pdo->prepare("SELECT id FROM polos WHERE id = :id LIMIT 1");
$stmtPolo->execute([":id" => (int) $id]);
if (!$stmtPolo->fetch()) voltarExclusaoPolo("erro", "Polo não encontrado.");
$stmtUsuarios = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE polo_id = :id");
$stmtUsuarios->execute([":id" => (int) $id]);
$stmtObjetos = $pdo->prepare("SELECT COUNT(*) FROM objetos WHERE polo_id = :id");
$stmtObjetos->execute([":id" => (int) $id]);
if ((int) $stmtUsuarios->fetchColumn() > 0 || (int) $stmtObjetos->fetchColumn() > 0) {
    voltarExclusaoPolo("erro", "Este polo ainda está vinculado a usuários ou objetos. Realoque os vínculos antes de excluí-lo.");
}

$stmtDelete = $pdo->prepare("DELETE FROM polos WHERE id = :id");
try {
    $stmtDelete->execute([":id" => (int) $id]);
} catch (Throwable $erro) {
    error_log($erro->getMessage());
    voltarExclusaoPolo("erro", "O polo possui vínculos que impedem a exclusão. Os registros associados foram preservados.");
}
voltarExclusaoPolo("sucesso", "O polo foi excluído.");
