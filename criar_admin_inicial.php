<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require_once __DIR__ . "/config/conexao.php";

$totalAdmins = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE perfil = 'admin'")->fetchColumn();
if ($totalAdmins > 0) {
    fwrite(STDERR, "Já existe uma conta administradora. Use a área administrativa para provisionar outras contas.\n");
    exit(1);
}

$ler = static function (string $pergunta): string {
    fwrite(STDOUT, $pergunta);
    return trim((string) fgets(STDIN));
};

$nome = $ler("Nome do administrador inicial: ");
$email = strtolower($ler("E-mail do administrador inicial: "));
if ($nome === "" || mb_strlen($nome) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Informe nome e e-mail válidos.\n");
    exit(1);
}

$senhaInicial = chr(random_int(65, 90))
    . chr(random_int(97, 122))
    . random_int(0, 9)
    . bin2hex(random_bytes(15));
$senhaHash = password_hash($senhaInicial, PASSWORD_DEFAULT);
$stmt = $pdo->prepare(
    "INSERT INTO usuarios (nome, email, senha, perfil, polo_id)
     VALUES (:nome, :email, :senha, 'admin', NULL)"
);
$stmt->execute([
    ":nome" => $nome,
    ":email" => $email,
    ":senha" => $senhaHash
]);

fwrite(STDOUT, "Administrador inicial criado. Guarde esta senha e entregue-a por canal seguro:\n");
fwrite(STDOUT, $senhaInicial . "\n");
