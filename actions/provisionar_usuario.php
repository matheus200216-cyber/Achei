<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["admin"]);

function voltarParaAdmin(string $tipo, string $texto): void
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

$nomeEntrada = $_POST["nome"] ?? "";
$emailEntrada = $_POST["email"] ?? "";
$perfilEntrada = $_POST["perfil"] ?? "";
$senha = $_POST["senha"] ?? "";
$confirmarSenha = $_POST["confirmar_senha"] ?? "";
$nome = is_string($nomeEntrada) ? trim($nomeEntrada) : "";
$email = is_string($emailEntrada) ? strtolower(trim($emailEntrada)) : "";
$perfil = is_string($perfilEntrada) ? $perfilEntrada : "";

if ($nome === "" || mb_strlen($nome) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarParaAdmin("erro", "Informe um nome e um e-mail válidos.");
}
if ($perfil !== "admin") {
    voltarParaAdmin("erro", "O perfil selecionado não pode ser provisionado por este formulário.");
}
if (!is_string($senha) || !senhaCumpreRequisitos($senha, 12) || !is_string($confirmarSenha) || $senha !== $confirmarSenha) {
    voltarParaAdmin("erro", "A senha deve ter pelo menos 12 caracteres, uma letra maiúscula, uma minúscula e um número; as duas senhas também devem coincidir.");
}

$poloId = null;
if ($perfil === "funcionario") {
    $poloEntrada = $_POST["polo_id"] ?? "";
    $poloId = filter_var($poloEntrada, FILTER_VALIDATE_INT);
    if ($poloId === false || $poloId === null || $poloId < 1) {
        voltarParaAdmin("erro", "Selecione um polo para o funcionário.");
    }
}

require_once __DIR__ . "/../config/conexao.php";

$colunaProvisionamento = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'provisionado_por'")->fetch();
if (!$colunaProvisionamento) {
    voltarParaAdmin("erro", "Aplique a migração de autorização antes de provisionar contas.");
}

$stmtExistente = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
$stmtExistente->execute([":email" => $email]);
if ($stmtExistente->fetch()) {
    voltarParaAdmin("erro", "Já existe uma conta com esse e-mail.");
}

if ($perfil === "funcionario") {
    $stmtPolo = $pdo->prepare("SELECT id FROM polos WHERE id = :id LIMIT 1");
    $stmtPolo->execute([":id" => $poloId]);
    if (!$stmtPolo->fetch()) {
        voltarParaAdmin("erro", "O polo selecionado não existe.");
    }
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$stmtCriar = $pdo->prepare(
    "INSERT INTO usuarios (nome, email, senha, perfil, polo_id, provisionado_por)
     VALUES (:nome, :email, :senha, :perfil, :polo_id, :provisionado_por)"
);
$stmtCriar->execute([
    ":nome" => $nome,
    ":email" => $email,
    ":senha" => $senhaHash,
    ":perfil" => $perfil,
    ":polo_id" => $poloId,
    ":provisionado_por" => (int) $_SESSION["usuario_id"]
]);

unset($_SESSION["csrf_admin_provisionamento"]);
voltarParaAdmin("sucesso", "A conta foi criada com o perfil e o polo selecionados.");
