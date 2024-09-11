
toggleDivWithButton('.button__altImg', '.add__altImg');
toggleDivWithButton('.button__link', '.add__link');

function toggleDivWithButton(buttonId, divId) {
    const button = document.querySelector(buttonId);
    const div = document.querySelector(divId);
  
    button.addEventListener('click', () => {
        div.classList.toggle('hidden');
    });
  }


// // Add list 
// const addTagLink = document.querySelector('');
// const collectionHolder = document.querySelector('.');
// const prototype = collectionHolder.dataset.prototype;
// let index = collectionHolder.dataset.index;

// addTagLink.addEventListener('click', function(e) {
// e.preventDefault();
// const button = document.querySelector('.tags');
// addTagLink.textContent = 'Ajouter un element à la liste';
// console.log(addTagLink.textContent);
// const ul = document.querySelector('.tags');
// ul.classList.remove('none');
// const newForm = prototype.replace(/__name__/g, index);
// index++;
// const newLi = document.createElement('li');
// newLi.innerHTML = newForm;
// collectionHolder.appendChild(newLi);
// });