<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["admin"]);

function voltarAdminEdicaoUsuario(string $tipo, string $texto, int $id = 0): void
{
    $_SESSION["flash_admin"] = ["tipo" => $tipo, "texto" => $texto];
    redirecionar($id > 0 ? "../admin_editar_usuario.php?id=" . $id : "../admin.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") redirecionar("../admin.php");
$token = $_SESSION["csrf_admin_provisionamento"] ?? "";
$enviado = $_POST["csrf_token"] ?? "";
if (!is_string($token) || !is_string($enviado) || $token === "" || !hash_equals($token, $enviado)) { http_response_code(403); exit("Solicitação inválida."); }
$id = filter_var($_POST["usuario_id"] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) voltarAdminEdicaoUsuario("erro", "Usuário inválido.");
$nome = trim((string) ($_POST["nome"] ?? ""));
$email = strtolower(trim((string) ($_POST["email"] ?? "")));
$novoPerfil = (string) ($_POST["perfil"] ?? "");
if ($nome === "" || mb_strlen($nome) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) voltarAdminEdicaoUsuario("erro", "Informe um nome e e-mail válidos.", (int) $id);
if (!in_array($novoPerfil, ["aluno", "funcionario", "admin"], true)) voltarAdminEdicaoUsuario("erro", "Selecione um perfil válido.", (int) $id);

require_once __DIR__ . "/../config/conexao.php";
$temColunaProvisionamento = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'provisionado_por'")->fetch();
$campoProvisionamento = $temColunaProvisionamento ? "provisionado_por" : "NULL AS provisionado_por";
$stmtAtual = $pdo->prepare("SELECT perfil, $campoProvisionamento FROM usuarios WHERE id = :id LIMIT 1");
$stmtAtual->execute([":id" => (int) $id]);
$usuarioAtual = $stmtAtual->fetch();
if (!$usuarioAtual) voltarAdminEdicaoUsuario("erro", "Usuário não encontrado.");
$perfil = $usuarioAtual["perfil"];
if ((int) $id === (int) $_SESSION["usuario_id"] && $novoPerfil !== "admin") voltarAdminEdicaoUsuario("erro", "Você não pode remover seu próprio acesso de administrador.", (int) $id);
if ($novoPerfil === "aluno" && !str_ends_with($email, "@estudante.unisa.br")) voltarAdminEdicaoUsuario("erro", "O e-mail do aluno precisa terminar em @estudante.unisa.br.", (int) $id);
if ($novoPerfil === "funcionario" && !str_ends_with($email, "@funcionario.unisa.br")) voltarAdminEdicaoUsuario("erro", "O e-mail do funcionário precisa terminar em @funcionario.unisa.br.", (int) $id);

$stmtDuplicado = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email AND id <> :id LIMIT 1");
$stmtDuplicado->execute([":email" => $email, ":id" => (int) $id]);
if ($stmtDuplicado->fetch()) voltarAdminEdicaoUsuario("erro", "Esse e-mail já está em uso por outra conta.", (int) $id);

$poloId = null;
$provisionadoPor = null;
if ($novoPerfil === "funcionario") {
    $poloId = filter_var($_POST["polo_id"] ?? null, FILTER_VALIDATE_INT);
    if (!$poloId || $poloId < 1) voltarAdminEdicaoUsuario("erro", "Selecione um polo para o funcionário.", (int) $id);
    $stmtPolo = $pdo->prepare("SELECT id FROM polos WHERE id = :id LIMIT 1");
    $stmtPolo->execute([":id" => $poloId]);
    if (!$stmtPolo->fetch()) voltarAdminEdicaoUsuario("erro", "O polo selecionado não existe.", (int) $id);
    if (!$temColunaProvisionamento) voltarAdminEdicaoUsuario("erro", "A migração de autorização precisa estar aplicada.", (int) $id);
    if ($perfil === "funcionario") $provisionadoPor = $usuarioAtual["provisionado_por"];
}
if ($perfil === "admin" && $novoPerfil !== "admin" && (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin'")->fetchColumn() <= 1) {
    voltarAdminEdicaoUsuario("erro", "Não é possível remover o último administrador do sistema.", (int) $id);
}
if ($perfil === "admin" && $novoPerfil !== "admin" && $temColunaProvisionamento) {
    $stmtProvisionados = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE provisionado_por = :id");
    $stmtProvisionados->execute([":id" => (int) $id]);
    if ((int) $stmtProvisionados->fetchColumn() > 0) voltarAdminEdicaoUsuario("erro", "Este administrador autorizou contas de funcionários e não pode perder o perfil enquanto essas autorizações estiverem vinculadas.", (int) $id);
}

$stmtUpdate = $pdo->prepare("UPDATE usuarios SET nome = :nome, email = :email, perfil = :perfil, polo_id = :polo_id, provisionado_por = :provisionado_por WHERE id = :id");
$stmtUpdate->execute([":nome" => $nome, ":email" => $email, ":perfil" => $novoPerfil, ":polo_id" => $poloId, ":provisionado_por" => $provisionadoPor, ":id" => (int) $id]);
voltarAdminEdicaoUsuario("sucesso", "Os dados do usuário foram atualizados.");
