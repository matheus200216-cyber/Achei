
<?php
$usuarioEstaLogado = isset($_SESSION['usuario_id']);
$perfilUsuario = $_SESSION['perfil'] ?? null;
$funcionarioProvisionado = !empty($_SESSION['provisionado_por']);
?>

<header class="navbar">
    <a href="index.php" class="navbar-logo">
        <span class="logo-nome">ACHEI!</span>
        <span class="logo-descricao">Achados e perdidos</span>
    </a>

    <form action="buscar.php" method="GET" class="navbar-busca">
        <input
            type="search"
            name="q"
            value="<?= htmlspecialchars(isset($_GET["q"]) && is_string($_GET["q"]) ? trim($_GET["q"]) : "", ENT_QUOTES, "UTF-8") ?>"
            placeholder="Pesquise um objeto encontrado..."
            aria-label="Pesquisar objetos encontrados"
        >

        <button type="submit" aria-label="Pesquisar">
            <span>⌕</span>
        </button>
    </form>

    <nav class="navbar-acoes">
        <?php if (!$usuarioEstaLogado): ?>

            <a href="login.php" class="botao-entrar">
                Entrar
            </a>

            <a href="cadastro.php" class="botao-cadastro">
                Cadastro
            </a>

        <?php else: ?>

            <?php if ($perfilUsuario === 'funcionario' && $funcionarioProvisionado): ?>
                <a href="gerenciar_objetos.php">
                    Gerenciar objetos
                </a>
                <a href="cadastrar_objeto.php">
                    Cadastrar objeto
                </a>
            <?php endif; ?>

            <?php if ($perfilUsuario === 'admin'): ?>
                <a href="gerenciar_objetos.php">
                    Gerenciar objetos
                </a>
                <a href="admin.php">
                    Administração
                </a>
            <?php endif; ?>

            <a href="minha_conta.php">
                Minha conta
            </a>

            <a href="actions/sair.php">
                Sair
            </a>

        <?php endif; ?>
    </nav>
</header>

