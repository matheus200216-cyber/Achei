<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["admin"]);

function voltarExclusaoUsuario(string $tipo, string $mensagem): void
{
    $_SESSION["flash_admin"] = ["tipo" => $tipo, "texto" => $mensagem];
    redirecionar("../admin.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") redirecionar("../admin.php");
$token = $_SESSION["csrf_admin_provisionamento"] ?? "";
$enviado = $_POST["csrf_token"] ?? "";
if (!is_string($token) || !is_string($enviado) || $token === "" || !hash_equals($token, $enviado)) { http_response_code(403); exit("Solicitação inválida."); }
$id = filter_var($_POST["usuario_id"] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) voltarExclusaoUsuario("erro", "Usuário inválido.");
if ((int) $id === (int) $_SESSION["usuario_id"]) voltarExclusaoUsuario("erro", "Você não pode excluir a própria conta administradora.");

require_once __DIR__ . "/../config/conexao.php";
$stmtUser = $pdo->prepare("SELECT id, perfil FROM usuarios WHERE id = :id LIMIT 1");
$stmtUser->execute([":id" => (int) $id]);
$usuario = $stmtUser->fetch();
if (!$usuario) voltarExclusaoUsuario("erro", "Usuário não encontrado.");
if ($usuario["perfil"] === "admin" && (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin'")->fetchColumn() <= 1) {
    voltarExclusaoUsuario("erro", "Não é possível excluir o último administrador do sistema.");
}
if ($usuario["perfil"] === "admin" && $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'provisionado_por'")->fetch()) {
    $stmtProvisionados = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE provisionado_por = :id");
    $stmtProvisionados->execute([":id" => (int) $id]);
    if ((int) $stmtProvisionados->fetchColumn() > 0) voltarExclusaoUsuario("erro", "Este administrador autorizou contas de funcionários. Não é possível excluí-lo enquanto essas autorizações estiverem vinculadas a ele.");
}

$stmtObjetos = $pdo->prepare("SELECT COUNT(*) FROM objetos WHERE usuario_id = :id");
$stmtObjetos->execute([":id" => (int) $id]);
$stmtSolicitacoes = $pdo->prepare("SELECT COUNT(*) FROM solicitacoes WHERE usuario_id = :id");
$stmtSolicitacoes->execute([":id" => (int) $id]);
if ((int) $stmtObjetos->fetchColumn() > 0 || (int) $stmtSolicitacoes->fetchColumn() > 0) {
    voltarExclusaoUsuario("erro", "Este usuário possui objetos ou solicitações vinculadas. Para preservar o histórico, não é possível excluir esta conta.");
}

$stmtDelete = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
try {
    $stmtDelete->execute([":id" => (int) $id]);
} catch (Throwable $erro) {
    error_log($erro->getMessage());
    voltarExclusaoUsuario("erro", "A conta possui vínculos que impedem a exclusão. Os registros relacionados foram preservados.");
}
voltarExclusaoUsuario("sucesso", "A conta do usuário foi excluída.");
