(() => {
    const iconeOlho = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
    const iconeOculto = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"></path><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15.8 15.8 0 0 1-3.1 3.8M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7a10 10 0 0 0 3-.5"></path></svg>';

    document.querySelectorAll('input[type="password"]').forEach((campo) => {
        const contenedor = document.createElement("div");
        contenedor.className = "campo-com-senha";
        campo.parentNode.insertBefore(contenedor, campo);
        contenedor.appendChild(campo);
        campo.classList.add("campo-senha-com-olho");

        const botao = document.createElement("button");
        botao.type = "button";
        botao.className = "botao-alternar-senha";
        botao.setAttribute("aria-label", "Mostrar senha");
        botao.setAttribute("aria-pressed", "false");
        botao.title = "Mostrar senha";
        botao.innerHTML = iconeOlho;
        contenedor.appendChild(botao);

        botao.addEventListener("click", () => {
            const mostrar = campo.type === "password";
            campo.type = mostrar ? "text" : "password";
            botao.setAttribute("aria-label", mostrar ? "Ocultar senha" : "Mostrar senha");
            botao.setAttribute("aria-pressed", String(mostrar));
            botao.title = mostrar ? "Ocultar senha" : "Mostrar senha";
            botao.innerHTML = mostrar ? iconeOculto : iconeOlho;
            campo.focus({ preventScroll: true });
        });
    });
})();
