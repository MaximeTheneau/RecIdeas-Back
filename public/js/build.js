const CryptoJS = require('crypto.js');

const triggerNextJsBuild = async () => {
    const url = `https://unetaupechezvous.fr/api/webhook`;
    const data = {
        name: 'NextJsBuild',
        project: 'unetaupechezvous.fr',
        force: true,
    };

    const calculatedSignature = CryptoJS.HmacSHA256(JSON.stringify(data), 'secret');
    const headers = {
        'Content-Type': 'application/json',
        'x-hub-signature-256': `sha256=${calculatedSignature}`,
        'x-taupe-event': 'build',
    };

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(data),
        });

        const statusCode = response.status;
        const content = await response.json(); 

        if (response.ok) {
            return content;
        } else {
            throw new Error(`Erreur de requête : ${statusCode}`);
        }
    } catch (error) {
        throw new Error(`Une erreur est survenue lors de la requête : ${error.message}`);
    }
};

const buildDiv = document.getElementById('build').innerHTML;
if (buildDiv === 'Build') {
        try {
            const response = triggerNextJsBuild();
            console.log(response);
        } catch (error) {
            console.error(error);
        }
}


