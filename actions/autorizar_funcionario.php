<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["admin"]);

function voltarAprovacaoFuncionario(string $tipo, string $texto): void
{
    $_SESSION["flash_admin"] = ["tipo" => $tipo, "texto" => $texto];
    redirecionar("../admin.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecionar("../admin.php");
}

$tokenSessao = $_SESSION["csrf_admin_provisionamento"] ?? "";
$tokenEnviado = $_POST["csrf_token"] ?? "";
if (!is_string($tokenSessao) || !is_string($tokenEnviado) || $tokenSessao === "" || !hash_equals($tokenSessao, $tokenEnviado)) {
    http_response_code(403);
    exit("Solicitação inválida. Atualize a página e tente novamente.");
}

$usuarioId = filter_var($_POST["usuario_id"] ?? null, FILTER_VALIDATE_INT);
$poloId = null;
if ($usuarioId === false || $usuarioId === null || $usuarioId < 1) {
    voltarAprovacaoFuncionario("erro", "Selecione um funcionário válido.");
}

require_once __DIR__ . "/../config/conexao.php";
if (!$pdo->query("SHOW COLUMNS FROM usuarios LIKE 'provisionado_por'")->fetch()) {
    voltarAprovacaoFuncionario("erro", "Aplique a migração de autorização antes de aprovar contas.");
}

$stmtAprovar = $pdo->prepare(
    "UPDATE usuarios
     SET provisionado_por = :admin_id
     WHERE id = :usuario_id AND perfil = 'funcionario'
       AND polo_id IS NOT NULL AND provisionado_por IS NULL"
);
$stmtAprovar->execute([
    ":admin_id" => (int) $_SESSION["usuario_id"],
    ":usuario_id" => $usuarioId
]);

if ($stmtAprovar->rowCount() !== 1) {
    voltarAprovacaoFuncionario("erro", "A conta não existe, já foi aprovada ou não tem perfil de funcionário.");
}

voltarAprovacaoFuncionario("sucesso", "Funcionário aprovado e vinculado ao polo.");
