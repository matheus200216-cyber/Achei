<?php
session_start();

require_once __DIR__ . "/includes/funcoes.php";

exigirPerfil(["funcionario"]);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastrar objeto | Achei!</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/cadastro-objeto.css">
</head>
<body>

    <?php include __DIR__ . "/includes/menu.php"; ?>

    <main class="pagina-inicial">

        <section class="apresentacao">
            <div class="apresentacao-conteudo">

                <span class="etiqueta">ÁREA DO FUNCIONÁRIO</span>

                <h1>
                    Cadastrar
                    <span>objeto encontrado</span>
                </h1>

                <p>
                    Registre um objeto encontrado no seu polo
                    para que os alunos possam consultá-lo no catálogo.
                </p>

            </div>
        </section>

        <section class="formulario-objeto">

            <h2>Informações do objeto</h2>

            <p>
                Preencha os dados abaixo para registrar o objeto encontrado.
            </p>

            <form
                action="actions/salvar_objeto.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="campo">
                    <label for="titulo">Nome do objeto</label>

                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="descricao">Descrição</label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        rows="4"
                        required
                    ></textarea>
                </div>

                <div class="campo">
                    <label for="categoria">Categoria</label>

                    <select
                        id="categoria"
                        name="categoria"
                        required
                    >
                        <option value="">Selecione uma categoria</option>
                        <option value="Acessórios">Acessórios</option>
                        <option value="Documentos">Documentos</option>
                        <option value="Eletrônicos">Eletrônicos</option>
                        <option value="Material escolar">Material escolar</option>
                        <option value="Roupas">Roupas</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="data_ocorrencia">
                        Data em que foi encontrado
                    </label>

                    <input
                        type="date"
                        id="data_ocorrencia"
                        name="data_ocorrencia"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="fotos">
                        Fotos do objeto (máximo 3)
                    </label>

                    <input
                        type="file"
                        id="fotos"
                        name="fotos[]"
                        accept="image/jpeg,image/png,image/webp"
                        multiple
                    >

                    <p class="ajuda-formulario">
                        Você pode selecionar até 3 imagens JPG, PNG ou WEBP.
                    </p>
                    <p class="estado-previsualizacao" id="estadoPrevisualizacao" aria-live="polite"></p>
                    <div class="previsualizacao-fotos" id="previsualizacaoFotos" aria-label="Pré-visualização das fotos selecionadas"></div>
                </div>

                <button type="submit" class="botao-principal">
                    Cadastrar objeto
                </button>

            </form>

        </section>

    </main>

    <script src="assets/js/cadastro-objeto.js" defer></script>

</body>
</html>
