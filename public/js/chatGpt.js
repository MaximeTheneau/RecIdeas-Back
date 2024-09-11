    function generateResponse(title, content) {
        // event.preventDefault();
        // Récupérer le contenu du paragraphe
        const paragraphContent = document.getElementById('paragraph').innerText;
        console.log('Contenu du paragraphe:', title, content);
        // Appeler l'API GPT avec le contenu du paragraphe
        // fetch('/votre-url-d-api-gpt', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //     },
        //     body: JSON.stringify({ prompt: paragraphContent }),
        // })
        // .then(response => response.json())
        // .then(data => {
        //     // Mettre la réponse de GPT dans le paragraphe
        //     console.log('Réponse de GPT:', data.generated_text);
        //     // document.getElementById('paragraph').innerText = data.generated_text;
        // })
        // .catch(error => {
        //     console.error('Erreur lors de la récupération de la réponse de GPT:', error);
        // });
    }