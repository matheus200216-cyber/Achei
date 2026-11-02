<?php
session_start();
require_once __DIR__ . "/../includes/funcoes.php";
exigirPerfil(["funcionario", "admin"]);

function voltarEdicaoObjeto(string $tipo, string $mensagem, int $id = 0): void
{
    $_SESSION["flash_objetos"] = ["tipo" => $tipo, "texto" => $mensagem];
    redirecionar($id > 0 ? "../editar_objeto.php?id=" . $id : "../gerenciar_objetos.php");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") redirecionar("../gerenciar_objetos.php");
$tokenSessao = $_SESSION["csrf_gerenciar_objetos"] ?? "";
$tokenEnviado = $_POST["csrf_token"] ?? "";
if (!is_string($tokenSessao) || !is_string($tokenEnviado) || $tokenSessao === "" || !hash_equals($tokenSessao, $tokenEnviado)) {
    http_response_code(403);
    exit("Solicitação inválida. Volte e tente novamente.");
}

$id = filter_var($_POST["objeto_id"] ?? null, FILTER_VALIDATE_INT);
$titulo = trim((string) ($_POST["titulo"] ?? ""));
$descricao = trim((string) ($_POST["descricao"] ?? ""));
$categoria = trim((string) ($_POST["categoria"] ?? ""));
$data = trim((string) ($_POST["data_ocorrencia"] ?? ""));
$status = (string) ($_POST["status"] ?? "");
$usuarioEhAdmin = ($_SESSION["perfil"] ?? null) === "admin";
$poloId = (int) ($_SESSION["polo_id"] ?? 0);
if (!$id || (!$usuarioEhAdmin && $poloId < 1)) voltarEdicaoObjeto("erro", "Objeto inválido.");
if ($titulo === "" || mb_strlen($titulo) > 100 || $descricao === "") voltarEdicaoObjeto("erro", "Informe um título e uma descrição válidos.", (int) $id);
if (!in_array($categoria, ["Acessórios", "Documentos", "Eletrônicos", "Material escolar", "Roupas", "Outros"], true)) voltarEdicaoObjeto("erro", "A categoria selecionada é inválida.", (int) $id);
if (!in_array($status, ["disponivel", "retirado"], true)) voltarEdicaoObjeto("erro", "A situação selecionada é inválida.", (int) $id);
$dataValida = DateTime::createFromFormat("Y-m-d", $data);
if (!$dataValida || $dataValida->format("Y-m-d") !== $data || $data > date("Y-m-d")) voltarEdicaoObjeto("erro", "Informe uma data válida que não seja futura.", (int) $id);

require_once __DIR__ . "/../config/conexao.php";
$sqlUpdate = "UPDATE objetos
              SET titulo = :titulo, descricao = :descricao, categoria = :categoria,
                  data_ocorrencia = :data, status = :status
              WHERE id = :id" . ($usuarioEhAdmin ? "" : " AND polo_id = :polo_id");
$parametros = [
    ":titulo" => $titulo,
    ":descricao" => $descricao,
    ":categoria" => $categoria,
    ":data" => $data,
    ":status" => $status,
    ":id" => (int) $id
];
if (!$usuarioEhAdmin) $parametros[":polo_id"] = $poloId;
$stmt = $pdo->prepare($sqlUpdate);
$stmt->execute($parametros);
if ($stmt->rowCount() === 0) {
    $sqlChecar = "SELECT id FROM objetos WHERE id = :id" . ($usuarioEhAdmin ? "" : " AND polo_id = :polo_id") . " LIMIT 1";
    $parametrosChecagem = [":id" => (int) $id];
    if (!$usuarioEhAdmin) $parametrosChecagem[":polo_id"] = $poloId;
    $checar = $pdo->prepare($sqlChecar);
    $checar->execute($parametrosChecagem);
    if (!$checar->fetch()) voltarEdicaoObjeto("erro", "Objeto não encontrado neste polo.");
}
voltarEdicaoObjeto("sucesso", "As alterações do objeto foram salvas.");
