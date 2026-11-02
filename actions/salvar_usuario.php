<?php

session_start();

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/../includes/funcoes.php";

// Aceita somente envio pelo formulário
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirecionar("../cadastro.php");
}

// Recebe os dados
$primeiroNome = trim((string) ($_POST["nome"] ?? ""));
$sobrenome = trim((string) ($_POST["sobrenome"] ?? ""));
$nome = trim($primeiroNome . " " . $sobrenome);
$email = strtolower(trim($_POST["email"] ?? ""));
$senha = $_POST["senha"] ?? "";
$confirmarSenha = $_POST["confirmar_senha"] ?? "";

// Valida os campos obrigatórios
if (
    $nome === "" ||
    $email === "" ||
    $senha === "" ||
    $confirmarSenha === ""
) {
    exit("Preencha todos os campos.");
}

// Valida nome e sobrenome
if ($primeiroNome === "" || $sobrenome === "" || mb_strlen($nome) > 100) {
    exit("Informe nome e sobrenome (máximo de 100 caracteres no total).");
}

// Valida o formato do e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Informe um e-mail válido.");
}

if (str_ends_with($email, "@estudante.unisa.br")) {
    $perfil = "aluno";
} elseif (str_ends_with($email, "@funcionario.unisa.br")) {
    $perfil = "funcionario";
} else {
    exit("Use o e-mail institucional correspondente ao seu perfil.");
}

if (!is_string($senha) || !senhaCumpreRequisitos($senha)) {
    exit("A senha precisa ter no mínimo 8 caracteres, uma letra maiúscula, uma minúscula e um número.");
}
if (!is_string($confirmarSenha) || $senha !== $confirmarSenha) {
    exit("As senhas não são iguais.");
}
$stmtExistente = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
$stmtExistente->execute([":email" => $email]);
if ($stmtExistente->fetch()) {
    exit("Este e-mail já está cadastrado.");
}

$poloId = null;
if ($perfil === "funcionario") {
    $polo = [
        "nome" => trim((string) ($_POST["polo_nome"] ?? "")),
        "cep" => preg_replace("/\\D/", "", (string) ($_POST["polo_cep"] ?? "")),
        "logradouro" => trim((string) ($_POST["polo_logradouro"] ?? "")),
        "bairro" => trim((string) ($_POST["polo_bairro"] ?? "")),
        "cidade" => trim((string) ($_POST["polo_cidade"] ?? "")),
        "estado" => strtoupper(trim((string) ($_POST["polo_estado"] ?? ""))),
        "numero" => trim((string) ($_POST["polo_numero"] ?? "")),
        "complemento" => trim((string) ($_POST["polo_complemento"] ?? "")),
        "contato" => trim((string) ($_POST["polo_contato"] ?? ""))
    ];
    if ($polo["nome"] === "" || !preg_match("/^\\d{8}$/", $polo["cep"]) || $polo["logradouro"] === "" || $polo["bairro"] === "" || $polo["cidade"] === "" || !preg_match("/^[A-Z]{2}$/", $polo["estado"]) || $polo["numero"] === "" || $polo["contato"] === "") {
        exit("Preencha os dados do polo. O CEP deve conter 8 números.");
    }
    if (!$pdo->query("SHOW COLUMNS FROM usuarios LIKE 'provisionado_por'")->fetch()) {
        exit("A migração de autorização precisa estar aplicada para cadastrar funcionários.");
    }
    $stmtPolo = $pdo->prepare("SELECT id FROM polos WHERE nome = :nome AND cep = :cep AND numero = :numero LIMIT 1");
    $stmtPolo->execute([":nome" => $polo["nome"], ":cep" => $polo["cep"], ":numero" => $polo["numero"]]);
    $poloExistente = $stmtPolo->fetch();
    if ($poloExistente) {
        $poloId = (int) $poloExistente["id"];
    } else {
        $stmtPolo = $pdo->prepare("INSERT INTO polos (nome, cep, logradouro, bairro, cidade, estado, numero, complemento, contato) VALUES (:nome, :cep, :logradouro, :bairro, :cidade, :estado, :numero, :complemento, :contato)");
        $stmtPolo->execute([":nome" => $polo["nome"], ":cep" => $polo["cep"], ":logradouro" => $polo["logradouro"], ":bairro" => $polo["bairro"], ":cidade" => $polo["cidade"], ":estado" => $polo["estado"], ":numero" => $polo["numero"], ":complemento" => $polo["complemento"], ":contato" => $polo["contato"]]);
        $poloId = (int) $pdo->lastInsertId();
    }
}
// Protege a senha
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// Insere o usuário no banco
$sql = $perfil === "funcionario"
    ? "INSERT INTO usuarios (nome, email, senha, perfil, polo_id, provisionado_por) VALUES (:nome, :email, :senha, :perfil, :polo_id, NULL)"
    : "INSERT INTO usuarios (nome, email, senha, perfil) VALUES (:nome, :email, :senha, :perfil)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":nome" => $nome,
    ":email" => $email,
    ":senha" => $senhaHash,
    ":perfil" => $perfil
] + ($perfil === "funcionario" ? [":polo_id" => $poloId] : []));

if ($perfil === "funcionario") {
    exit("Cadastro enviado. Sua conta ficará liberada depois da aprovação administrativa. <a href='../login.php'>Voltar ao login</a>");
}
echo "Cadastro realizado com sucesso!";
echo '<p><a href="../login.php">Ir para o login</a></p>';
