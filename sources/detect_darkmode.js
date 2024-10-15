document.addEventListener('DOMContentLoaded', () => {
    const temaGuardado = localStorage.getItem('tema') || 'light';
    document.body.classList.add(temaGuardado);
});
