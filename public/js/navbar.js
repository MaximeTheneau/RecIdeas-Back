toggleDivWithButton('.navbar__menu', '.navbar__menu__list');
toggleDivWithButton('.navbar__menu__add', '.add__posts');



function toggleDivWithButton(buttonId, divId) {
    const button = document.querySelector(buttonId);
    const div = document.querySelector(divId);
    button.addEventListener('click', () => {
        div.classList.toggle('hidden');
    });
  }
