<?php
session_start();
require_once __DIR__ . "/includes/funcoes.php";
exigirPerfil(["admin"]);
require_once __DIR__ . "/config/conexao.php";

$colunaProvisionamento = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'provisionado_por'")->fetch();
$migracaoProvisionamentoDisponivel = (bool) $colunaProvisionamento;
$campoProvisionamento = $migracaoProvisionamentoDisponivel ? "u.provisionado_por" : "NULL AS provisionado_por";

if (empty($_SESSION["csrf_admin_provisionamento"])) {
    $_SESSION["csrf_admin_provisionamento"] = bin2hex(random_bytes(32));
}

$stmtUsuarios = $pdo->query(
    "SELECT u.id, u.nome, u.email, u.perfil, u.polo_id, $campoProvisionamento,
            p.nome AS nome_polo, p.cep, p.logradouro, p.numero, p.bairro,
            p.cidade, p.estado, p.contato
     FROM usuarios u
     LEFT JOIN polos p ON p.id = u.polo_id
     ORDER BY u.perfil, u.nome"
);
$usuariosGerenciados = $stmtUsuarios->fetchAll();
$polosGerenciados = $pdo->query("SELECT id, nome, cep, logradouro, numero, bairro, cidade, estado, contato FROM polos ORDER BY nome")->fetchAll();
$mensagemAdmin = $_SESSION["flash_admin"] ?? null;
unset($_SESSION["flash_admin"]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração | Achei!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/listagem.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="assets/css/senha.css">
</head>
<body class="pagina-com-rodape">
<?php include __DIR__ . "/includes/menu.php"; ?>
<main class="admin-pagina">
    <header class="admin-cabecalho">
        <p class="admin-etiqueta">CONTROLE DE ACESSO</p>
        <h1>Administração</h1>
        <p class="admin-descricao">Revise os dados enviados pelos funcionários e aprove as contas. Você também pode provisionar outros administradores.</p>
        <a class="admin-link-gerenciar-objetos" href="gerenciar_objetos.php">Gerenciar objetos de todos os polos</a>
    </header>

    <?php if (is_array($mensagemAdmin)): ?>
        <p class="admin-mensagem admin-mensagem-<?= htmlspecialchars($mensagemAdmin["tipo"], ENT_QUOTES, "UTF-8") ?>">
            <?= htmlspecialchars($mensagemAdmin["texto"], ENT_QUOTES, "UTF-8") ?>
        </p>
    <?php endif; ?>

    <?php if (!$migracaoProvisionamentoDisponivel): ?>
        <p class="admin-mensagem admin-mensagem-erro">
            A migração de autorização ainda não foi aplicada. A criação e aprovação de funcionários está bloqueada até aplicá-la.
        </p>
    <?php else: ?>
        <section class="admin-secao" aria-labelledby="titulo-provisionamento">
            <h2 id="titulo-provisionamento">Criar conta de administrador</h2>
            <form class="admin-formulario" action="actions/provisionar_usuario.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_admin_provisionamento"], ENT_QUOTES, "UTF-8") ?>">

                <label>
                    <span>Nome completo</span>
                    <input type="text" name="nome" maxlength="100" autocomplete="name" required>
                </label>
                <label>
                    <span>E-mail</span>
                    <input type="email" name="email" maxlength="150" autocomplete="email" required>
                </label>
                <input type="hidden" name="perfil" value="admin">
                <label>
                    <span>Senha inicial (mínimo de 12 caracteres)</span>
                    <input type="password" name="senha" minlength="12" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{12,}" title="Use pelo menos 12 caracteres, com letra maiúscula, minúscula e número." autocomplete="new-password" required>
                    <small class="admin-ajuda-senha">Use pelo menos 12 caracteres, com letra maiúscula, minúscula e número.</small>
                </label>
                <label>
                    <span>Confirme a senha inicial</span>
                    <input type="password" name="confirmar_senha" minlength="12" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{12,}" title="Use pelo menos 12 caracteres, com letra maiúscula, minúscula e número." autocomplete="new-password" required>
                </label>
                <button type="submit">Criar conta</button>
            </form>
        </section>
    <?php endif; ?>

    <section class="admin-secao" aria-labelledby="titulo-usuarios-gerenciados">
        <h2 id="titulo-usuarios-gerenciados">Usuários</h2>
        <p class="admin-aviso-legado">Contas antigas de funcionário ficam sem acesso às funções de funcionário até serem aprovadas por um administrador.</p>
        <?php if ($usuariosGerenciados): ?>
            <div class="admin-tabela-conteiner">
                <table class="admin-tabela">
                    <thead>
                        <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Polo</th><th>Acesso</th><th>Ações</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuariosGerenciados as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario["nome"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($usuario["email"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= ["admin" => "Administrador", "funcionario" => "Funcionário", "aluno" => "Aluno"][$usuario["perfil"]] ?? "—" ?></td>
                                <td>
                                    <?php if (!empty($usuario["nome_polo"])): ?>
                                        <strong><?= htmlspecialchars($usuario["nome_polo"], ENT_QUOTES, "UTF-8") ?></strong><br>
                                        <?= htmlspecialchars($usuario["logradouro"] . ", " . $usuario["numero"] . " — " . $usuario["bairro"], ENT_QUOTES, "UTF-8") ?><br>
                                        <?= htmlspecialchars($usuario["cidade"] . "/" . $usuario["estado"] . " · CEP " . $usuario["cep"], ENT_QUOTES, "UTF-8") ?><br>
                                        Contato: <?= htmlspecialchars($usuario["contato"], ENT_QUOTES, "UTF-8") ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($usuario["perfil"] === "admin"): ?>
                                        Administrador
                                    <?php elseif ($usuario["perfil"] === "aluno"): ?>
                                        Ativo
                                    <?php elseif (!empty($usuario["provisionado_por"])): ?>
                                        Provisionado
                                    <?php elseif ($migracaoProvisionamentoDisponivel): ?>
                                        <form class="admin-aprovar-formulario" action="actions/autorizar_funcionario.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_admin_provisionamento"], ENT_QUOTES, "UTF-8") ?>">
                                            <input type="hidden" name="usuario_id" value="<?= (int) $usuario["id"] ?>">
                                            <button type="submit">Aprovar conta</button>
                                        </form>
                                    <?php else: ?>
                                        Migração pendente
                                    <?php endif; ?>
                                </td>
                                <td class="admin-acoes-celula">
                                    <a class="admin-acao-editar" href="admin_editar_usuario.php?id=<?= (int) $usuario["id"] ?>">Editar</a>
                                    <?php if ((int) $usuario["id"] !== (int) $_SESSION["usuario_id"]): ?>
                                        <form action="actions/admin_excluir_usuario.php" method="POST" onsubmit="return confirm('Confirma a exclusão deste usuário? Contas com objetos ou registros vinculados não poderão ser removidas.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_admin_provisionamento"], ENT_QUOTES, "UTF-8") ?>">
                                            <input type="hidden" name="usuario_id" value="<?= (int) $usuario["id"] ?>">
                                            <button class="admin-acao-excluir" type="submit">Excluir</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="admin-sem-exclusao">Sua conta</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Ainda não há usuários cadastrados.</p>
        <?php endif; ?>
    </section>

    <section class="admin-secao" aria-labelledby="titulo-polos-gerenciados">
        <h2 id="titulo-polos-gerenciados">Polos cadastrados</h2>
        <?php if ($polosGerenciados): ?>
            <div class="admin-tabela-conteiner">
                <table class="admin-tabela admin-tabela-polos">
                    <thead><tr><th>Polo</th><th>Endereço</th><th>Contato</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php foreach ($polosGerenciados as $polo): ?>
                            <tr>
                                <td><?= htmlspecialchars($polo["nome"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($polo["logradouro"] . ", " . $polo["numero"] . " — " . $polo["bairro"] . ", " . $polo["cidade"] . "/" . $polo["estado"] . " · CEP " . $polo["cep"], ENT_QUOTES, "UTF-8") ?></td>
                                <td><?= htmlspecialchars($polo["contato"], ENT_QUOTES, "UTF-8") ?></td>
                                <td class="admin-acoes-celula">
                                    <a class="admin-acao-editar" href="admin_editar_polo.php?id=<?= (int) $polo["id"] ?>">Editar</a>
                                    <form action="actions/admin_excluir_polo.php" method="POST" onsubmit="return confirm('Confirma a exclusão deste polo? Polos vinculados a usuários ou objetos não podem ser excluídos.');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["csrf_admin_provisionamento"], ENT_QUOTES, "UTF-8") ?>">
                                        <input type="hidden" name="polo_id" value="<?= (int) $polo["id"] ?>">
                                        <button class="admin-acao-excluir" type="submit">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>Nenhum polo cadastrado.</p>
        <?php endif; ?>
    </section>
</main>
<?php include __DIR__ . "/includes/rodape.php"; ?>
<script src="assets/js/alternar-senha.js" defer></script>
</body>
</html>
