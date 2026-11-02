<?php

function redirecionar(string $pagina): void
{
    header("Location: " . $pagina);
    exit;
}

function usuarioLogado(): bool
{
    return isset($_SESSION["usuario_id"]);
}

function senhaCumpreRequisitos(string $senha, int $minimo = 8): bool
{
    return mb_strlen($senha, "UTF-8") >= $minimo
        && preg_match("/[a-z]/", $senha) === 1
        && preg_match("/[A-Z]/", $senha) === 1
        && preg_match("/[0-9]/", $senha) === 1;
}

function exigirLogin(): void
{
    if (usuarioLogado()) {
        return;
    }

    $diretorioScript = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "/"));

    if (basename($diretorioScript) === "actions") {
        $diretorioScript = dirname($diretorioScript);
    }

    $baseAplicacao = rtrim($diretorioScript, "/");
    $paginaLogin = ($baseAplicacao === "" ? "" : $baseAplicacao) . "/login.php";

    header("Location: " . $paginaLogin);
    exit;
}

function exigirPerfil(array $perfisPermitidos): void
{
    exigirLogin();

    $perfil = $_SESSION["perfil"] ?? null;
    if (!in_array($perfil, $perfisPermitidos, true)) {
        http_response_code(403);
        exit("Acesso não autorizado.");
    }

    if ($perfil === "funcionario" && in_array("funcionario", $perfisPermitidos, true) && empty($_SESSION["provisionado_por"])) {
        http_response_code(403);
        exit("O acesso de funcionário aguarda autorização administrativa.");
    }
}

function exigirAcessoUsuario(): void
{
    exigirLogin();

    if (!in_array($_SESSION["perfil"] ?? null, ["aluno", "funcionario", "admin"], true)) {
        http_response_code(403);
        exit("Acesso não autorizado.");
    }

    if (($_SESSION["perfil"] ?? null) === "funcionario" && empty($_SESSION["provisionado_por"])) {
        http_response_code(403);
        exit("Sua conta de funcionário aguarda aprovação administrativa.");
    }
}
