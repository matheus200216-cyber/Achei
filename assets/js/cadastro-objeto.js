(() => {
    const campoFotos = document.getElementById("fotos");
    const galeria = document.getElementById("previsualizacaoFotos");
    const mensagem = document.getElementById("estadoPrevisualizacao");
    const formulario = campoFotos?.form;

    if (!campoFotos || !galeria || !mensagem || !formulario) return;

    let urlsTemporarias = [];

    function limparPrevisualizacoes() {
        urlsTemporarias.forEach((url) => URL.revokeObjectURL(url));
        urlsTemporarias = [];
        galeria.replaceChildren();
    }

    campoFotos.addEventListener("change", () => {
        limparPrevisualizacoes();
        const arquivos = Array.from(campoFotos.files || []);

        if (arquivos.length === 0) {
            mensagem.textContent = "";
            return;
        }

        if (arquivos.length > 3) {
            mensagem.textContent = "Selecione no máximo 3 fotos. Remova o excesso para continuar.";
            mensagem.classList.add("estado-previsualizacao-erro");
        } else {
            mensagem.textContent = `${arquivos.length} ${arquivos.length === 1 ? "foto selecionada" : "fotos selecionadas"}.`;
            mensagem.classList.remove("estado-previsualizacao-erro");
        }

        arquivos.slice(0, 3).forEach((arquivo, indice) => {
            const url = URL.createObjectURL(arquivo);
            urlsTemporarias.push(url);

            const item = document.createElement("figure");
            item.className = "miniatura-foto";

            const imagem = document.createElement("img");
            imagem.src = url;
            imagem.alt = `Prévia da foto ${indice + 1}: ${arquivo.name}`;

            const legenda = document.createElement("figcaption");
            legenda.textContent = arquivo.name;

            item.append(imagem, legenda);
            galeria.append(item);
        });
    });

    formulario.addEventListener("submit", (evento) => {
        if (campoFotos.files.length > 3) {
            evento.preventDefault();
            mensagem.textContent = "O cadastro aceita até 3 fotos. Remova o excesso para continuar.";
            mensagem.classList.add("estado-previsualizacao-erro");
            campoFotos.focus();
        }
    });

    window.addEventListener("beforeunload", limparPrevisualizacoes);
})();
