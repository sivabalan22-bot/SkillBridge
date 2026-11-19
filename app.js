(() => {
    const menuButton = document.querySelector('.menu-toggle');
    const navigation = document.querySelector('.primary-nav');

    if (!menuButton || !navigation) {
        return;
    }

    menuButton.addEventListener('click', () => {
        const expanded = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', String(!expanded));
        navigation.classList.toggle('is-open', !expanded);
    });

    navigation.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('a')) {
            menuButton.setAttribute('aria-expanded', 'false');
            navigation.classList.remove('is-open');
        }
    });
})();
