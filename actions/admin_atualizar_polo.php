<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["admin"]);

function voltarAtualizarPolo(string $tipo, string $mensagem): void
{
    $_SESSION["flash_admin"] = ["tipo" => $tipo, "texto" => $mensagem];
    redirecionar("../admin.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") redirecionar("../admin.php");
$token = $_SESSION["csrf_admin_provisionamento"] ?? "";
$enviado = $_POST["csrf_token"] ?? "";
if (!is_string($token) || !is_string($enviado) || $token === "" || !hash_equals($token, $enviado)) { http_response_code(403); exit("Solicitação inválida."); }
$id = filter_var($_POST["polo_id"] ?? null, FILTER_VALIDATE_INT);
$polo = [
    "nome" => trim((string) ($_POST["nome"] ?? "")),
    "cep" => preg_replace("/\\D/", "", (string) ($_POST["cep"] ?? "")),
    "logradouro" => trim((string) ($_POST["logradouro"] ?? "")),
    "numero" => trim((string) ($_POST["numero"] ?? "")),
    "complemento" => trim((string) ($_POST["complemento"] ?? "")),
    "bairro" => trim((string) ($_POST["bairro"] ?? "")),
    "cidade" => trim((string) ($_POST["cidade"] ?? "")),
    "estado" => strtoupper(trim((string) ($_POST["estado"] ?? ""))),
    "contato" => trim((string) ($_POST["contato"] ?? ""))
];
if (!$id || $id < 1) voltarAtualizarPolo("erro", "Polo inválido.");
if ($polo["nome"] === "" || mb_strlen($polo["nome"]) > 100 || !preg_match("/^\\d{8}$/", $polo["cep"]) || $polo["logradouro"] === "" || $polo["numero"] === "" || mb_strlen($polo["numero"]) > 20 || mb_strlen($polo["complemento"]) > 100 || $polo["bairro"] === "" || mb_strlen($polo["bairro"]) > 100 || $polo["cidade"] === "" || mb_strlen($polo["cidade"]) > 100 || !preg_match("/^[A-Z]{2}$/", $polo["estado"]) || $polo["contato"] === "" || mb_strlen($polo["contato"]) > 100) {
    voltarAtualizarPolo("erro", "Confira os campos do polo e informe um CEP válido com 8 números.");
}

require_once __DIR__ . "/../config/conexao.php";
$stmt = $pdo->prepare("UPDATE polos SET nome=:nome, cep=:cep, logradouro=:logradouro, numero=:numero, complemento=:complemento, bairro=:bairro, cidade=:cidade, estado=:estado, contato=:contato WHERE id=:id");
$stmt->execute([
    ":nome" => $polo["nome"], ":cep" => $polo["cep"], ":logradouro" => $polo["logradouro"],
    ":numero" => $polo["numero"], ":complemento" => $polo["complemento"], ":bairro" => $polo["bairro"],
    ":cidade" => $polo["cidade"], ":estado" => $polo["estado"], ":contato" => $polo["contato"], ":id" => (int) $id
]);
voltarAtualizarPolo("sucesso", "Os dados do polo foram atualizados.");
