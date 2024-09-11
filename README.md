# Une Taupe Chez Vous - Back-office 

Ce référentiel contient un projet Symfony. Avant de lancer le projet, assurez-vous de remplacer toutes les mentions du fichier .env et .env.local par les valeurs appropriées en fonction de votre environnement et de votre configuration.

## Variables d'environnement

Dans tous les environnements, l'application Symfony charge les variables d'environnement à partir de différents fichiers. Suivez les instructions ci-dessous pour configurer les variables d'environnement nécessaires.

### Fichier .env.local

Le fichier `.env.local` est utilisé pour les surcharges locales et n'est pas inclus dans le référentiel. Mettez à jour les variables suivantes :
```
APP_SECRET=votre_secret_d_application
DATABASE_URL="mysql://votre_utilisateur_bdd:votre_mot_de_passe_bdd@votre_hote_bdd:votre_port_bdd/votre_nom_bdd?serverVersion=8&charset=utf8mb4"
CORS_ALLOW_ORIGIN='^https?://(localhost|127.0.0.1)(:[0-9]+)?$'
APP_PROJECTDIR='http://votre_domaine_projet/public/'
```

## Configuration Symfony/Mailer

Pour la configuration de Symfony Mailer, mettez à jour les variables suivantes :

```
MAILER_TO=destinataire_email@example.com
MAILER_TO_WEBMASTER=webmaster_email@example.com
MAILER_DSN=smtp://votre_utilisateur_smtp:votre_mot_de_passe_smtp@smtp.hostinger.com:465
```

## Configuration Symfony/Cloudinary

Pour la configuration de Symfony Cloudinary, mettez à jour les variables suivantes :

```
CLOUD_NAME=votre_nom_cloud
CLOUD_API_KEY=votre_cle_api
CLOUD_API_SECRET=votre_secret_api
```

N'oubliez pas que ce README est juste un modèle avec des espaces réservés. Remplacez ces espaces réservés par vos valeurs réelles avant de déployer l'application.

## Réalisé par Theneau Maxime

Back office développé avec Symfony. Elle est conçue pour gérer diverses opérations liées à une base de données MySQL.

Gestion des fichiers prise en charge part le service CDN Cloudinary.

Pour plus d'informations sur les bonnes pratiques Symfony et la configuration des variables d'environnement, consultez la documentation officielle de Symfony :

- [Symfony Bonnes Pratiques](https://symfony.com/doc/current/best_practices.html)
- [Symfony Variables d'Environnement](https://symfony.com/doc/current/configuration/environments.html)

N'hésitez pas à explorer le code source et à fournir des commentaires ou des suggestions d'amélioration. Vos retours seront grandement appréciés !

Bon développement ! 🚀



{
	"id": 9,
    	"createdAt": "2024-09-10T18:21:16+00:00",
	"updatedAt": null,
    "fr": {
	"heading": "Exercitationem aperiam eum quibusdam magni non harum",
	"title": "Test1",
	"metaDescription": "Eos molestias ut rem quis voluptas facere corporis cillum alias sint maiores praesentium",
	"slug": "Test1",
	"contents": "hgggggggggggggg\n",
    	"formattedDate": "Publié le 10 septembre 2024",

    }
    "en":{
	"heading": "enExercitationem aperiam eum quibusdam magni non harum",
	"title": "enTest1",
	"metaDescription": "enEos molestias ut rem quis voluptas facere corporis cillum alias sint maiores praesentium",
	"slug": "en Teest1",
	"contents": "enhgggggggggggggg\n",
	"formattedDate": "en Publié le 10 septembre 2024",

    }
}