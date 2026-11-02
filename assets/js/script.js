(() => {
    document.querySelectorAll(".busca-filtros select").forEach((campo) => {
        campo.addEventListener("change", () => campo.form.requestSubmit());
    });
})();