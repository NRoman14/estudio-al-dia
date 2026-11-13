for (const button of document.querySelectorAll('[data-password-toggle]')) {
    const password = document.getElementById(button.getAttribute('aria-controls'));

    button.addEventListener('click', () => {
        const shouldShow = password.type === 'password';
        password.type = shouldShow ? 'text' : 'password';
        button.textContent = shouldShow ? 'Ocultar contraseña' : 'Mostrar contraseña';
    });

    button.hidden = false;
}

for (const counter of document.querySelectorAll('[data-count]')) {
    const field = document.getElementById(counter.dataset.count);
    const update = () => { counter.textContent = field.value.length; };

    field.addEventListener('input', update);
    update();
}

for (const button of document.querySelectorAll('[data-dialog]')) {
    button.addEventListener('click', () => {
        document.getElementById(button.dataset.dialog).showModal();
    });
}

for (const form of document.querySelectorAll('[data-loading]')) {
    const button = form.querySelector('button');
    const label = button.querySelector('span');
    const originalLabel = label.textContent;

    form.addEventListener('submit', () => {
        button.disabled = true;
        label.textContent = 'Generando pasos…';
    });

    window.addEventListener('pageshow', () => {
        button.disabled = false;
        label.textContent = originalLabel;
    });
}
