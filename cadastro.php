<?php
session_start();

require_once __DIR__ . "/config/conexao.php";
require_once __DIR__ . "/includes/funcoes.php";

if (usuarioLogado()) {
    redirecionar("index.php");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar conta | Achei!</title>

    <link rel="stylesheet" href="assets/css/autenticacao.css">
    <link rel="stylesheet" href="assets/css/senha.css">
</head>
<body class="tela-login">

    <main class="login-layout">

        <!-- LADO ESQUERDO -->
        <section class="login-lateral">

            <div class="login-marca">
                <h1>ACHEI!</h1>
                <p>Achados e perdidos</p>
            </div>

            <div class="login-parceria">
                <p>Em parceria com</p>

                <div class="login-unisa">
                    <span>UNiSA</span>
                    <small>Universidade Santo Amaro</small>
                </div>
            </div>

        </section>

        <!-- LADO DIREITO -->
        <section class="login-conteudo">

            <div class="login-cartao cadastro-cartao">

                <h2>Criar conta</h2>

                <div class="login-formulario-borda">

                    <form action="actions/salvar_usuario.php" method="POST" autocomplete="on">
                        <div id="etapaCredenciais" class="cadastro-etapa">
                        <p class="cadastro-progresso">Dados da conta</p>

                        <div class="login-campo">
                            <label for="nome">Nome</label>

                            <input
                                type="text"
                                id="nome"
                                name="nome"
                                autocomplete="given-name"
                                maxlength="60"
                                placeholder="Digite seu nome"
                                required
                            >
                        </div>

                        <div class="login-campo">
                            <label for="sobrenome">Sobrenome</label>
                            <input
                                type="text"
                                id="sobrenome"
                                name="sobrenome"
                                autocomplete="family-name"
                                maxlength="60"
                                placeholder="Digite seu sobrenome"
                                required
                            >
                        </div>

                        <div class="login-campo">
                            <label for="email">E-mail institucional</label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                autocomplete="username"
                                maxlength="150"
                                placeholder="Digite seu e-mail institucional"
                                required
                            >
                            <small>Alunos: @estudante.unisa.br · Funcionários: @funcionario.unisa.br</small>
                        </div>

                        <div class="login-campo">
                            <label for="senha">Senha</label>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                autocomplete="new-password"
                                minlength="8"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}"
                                title="Use no mínimo 8 caracteres, com uma letra maiúscula, uma minúscula e um número."
                                placeholder="Crie uma senha"
                                required
                            >
                            <small class="ajuda-senha">Mínimo de 8 caracteres, com letra maiúscula, minúscula e número.</small>
                        </div>

                        <div class="login-campo">
                            <label for="confirmar_senha">
                                Confirmar senha
                            </label>

                            <input
                                type="password"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                autocomplete="new-password"
                                minlength="8"
                                pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}"
                                title="Use no mínimo 8 caracteres, com uma letra maiúscula, uma minúscula e um número."
                                placeholder="Digite a senha novamente"
                                required
                            >
                        </div>

                        <button type="button" class="login-botao" id="proximoCadastro">
                            Próximo
                        </button>
                        <p class="login-cadastro">
                            Já possui uma conta? <a href="login.php">Entrar</a>
                        </p>
                        </div>

                        <fieldset class="cadastro-dados-polo" id="dadosPolo" hidden disabled>
            <legend>Etapa 2 de 2 · Dados do polo</legend>
                            <p>O endereço será preenchido pelo CEP. Sua conta ficará aguardando aprovação administrativa.</p>
                            <div class="login-campo"><label for="polo_nome">Nome do polo</label><input id="polo_nome" name="polo_nome" maxlength="120" disabled></div>
                            <div class="login-campo"><label for="polo_cep">CEP</label><input id="polo_cep" name="polo_cep" inputmode="numeric" autocomplete="postal-code" maxlength="9" placeholder="00000-000" disabled><small id="statusCep" role="status" aria-live="polite"></small></div>
                            <div class="login-campo"><label for="polo_logradouro">Logradouro</label><input id="polo_logradouro" name="polo_logradouro" maxlength="150" disabled></div>
                            <div class="login-campo"><label for="polo_numero">Número</label><input id="polo_numero" name="polo_numero" maxlength="20" disabled></div>
                            <div class="login-campo"><label for="polo_complemento">Complemento</label><input id="polo_complemento" name="polo_complemento" maxlength="100" disabled></div>
                            <div class="login-campo"><label for="polo_bairro">Bairro</label><input id="polo_bairro" name="polo_bairro" maxlength="100" disabled></div>
                            <div class="login-campo"><label for="polo_cidade">Cidade</label><input id="polo_cidade" name="polo_cidade" maxlength="100" disabled></div>
                            <div class="login-campo"><label for="polo_estado">UF</label><input id="polo_estado" name="polo_estado" maxlength="2" disabled></div>
                            <div class="login-campo"><label for="polo_contato">Contato da coordenação</label><input id="polo_contato" name="polo_contato" maxlength="100" placeholder="Telefone ou e-mail" disabled></div>
                            <button type="button" class="cadastro-voltar" id="voltarCredenciais">Voltar</button>
                            <button type="submit" class="login-botao">Criar conta</button>
                        </fieldset>

                    </form>

                </div>

            </div>

        </section>

    </main>

    <script>
    (() => {
        const form = document.querySelector("form[action='actions/salvar_usuario.php']");
        const etapaCredenciais = document.getElementById("etapaCredenciais");
        const email = document.getElementById("email");
        const grupo = document.getElementById("dadosPolo");
        const cep = document.getElementById("polo_cep");
        const status = document.getElementById("statusCep");
        const proximo = document.getElementById("proximoCadastro");
        const voltar = document.getElementById("voltarCredenciais");
        const senha = document.getElementById("senha");
        const confirmarSenha = document.getElementById("confirmar_senha");
        const obrigatorios = ["polo_nome", "polo_cep", "polo_logradouro", "polo_numero", "polo_bairro", "polo_cidade", "polo_estado", "polo_contato"];
        function atualizarTextoBotao() {
            proximo.textContent = email.value.trim().toLowerCase().endsWith("@funcionario.unisa.br")
                ? "Próximo: dados do polo"
                : "Criar conta";
        }
        function abrirEtapaPolo() {
            confirmarSenha.setCustomValidity(senha.value === confirmarSenha.value ? "" : "As senhas não conferem.");
            if (!form.reportValidity()) return;
            if (!email.value.trim().toLowerCase().endsWith("@funcionario.unisa.br")) {
                form.requestSubmit();
                return;
            }
            etapaCredenciais.hidden = true;
            grupo.hidden = false;
            grupo.disabled = false;
            grupo.querySelectorAll("input").forEach(campo => {
                campo.disabled = false;
                campo.required = obrigatorios.includes(campo.id);
            });
            document.getElementById("polo_nome").focus();
        }
        function voltarParaCredenciais() {
            grupo.hidden = true;
            grupo.disabled = true;
            grupo.querySelectorAll("input").forEach(campo => { campo.disabled = true; campo.required = false; });
            etapaCredenciais.hidden = false;
            email.focus();
        }
        async function consultarCep() {
            const numero = cep.value.replace(/\D/g, "");
            if (numero.length !== 8) { status.textContent = numero ? "Digite um CEP com 8 números." : ""; return; }
            status.textContent = "Consultando CEP…";
            try {
                const resposta = await fetch("https://viacep.com.br/ws/" + numero + "/json/");
                if (!resposta.ok) throw new Error();
                const endereco = await resposta.json();
                if (endereco.erro) { status.textContent = "CEP não encontrado. Confira e preencha o endereço."; return; }
                document.getElementById("polo_logradouro").value = endereco.logradouro || "";
                document.getElementById("polo_bairro").value = endereco.bairro || "";
                document.getElementById("polo_cidade").value = endereco.localidade || "";
                document.getElementById("polo_estado").value = endereco.uf || "";
                status.textContent = "Endereço preenchido. Confira os dados e complete os campos restantes.";
            } catch { status.textContent = "Consulta indisponível. Preencha o endereço manualmente."; }
        }
        proximo.addEventListener("click", abrirEtapaPolo);
        voltar.addEventListener("click", voltarParaCredenciais);
        email.addEventListener("input", atualizarTextoBotao);
        confirmarSenha.addEventListener("input", () => confirmarSenha.setCustomValidity(""));
        cep.addEventListener("blur", consultarCep);
        cep.addEventListener("input", () => { const n = cep.value.replace(/\D/g, "").slice(0, 8); cep.value = n.length > 5 ? n.slice(0, 5) + "-" + n.slice(5) : n; });
        atualizarTextoBotao();
    })();
    </script>
    <script src="assets/js/alternar-senha.js" defer></script>
</body>
</html>
