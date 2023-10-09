# Gestion de matériel API 

L'API gestion de matériel contient des ressources API pour ajouter, modifier, supprimer et afficher des équipements pour un logiciel de gestion de matériel confié aux employés d'une entreprise dans le cadre de leur fonction.
Bien sûr, voici un exemple de section "Dépendances" que vous pourriez ajouter à votre fichier README.md pour un projet Symfony :

## Dépendances

Ce projet Symfony dépend de plusieurs composants et bibliothèques pour fonctionner correctement. Assurez-vous de les installer avant d'exécuter l'application.

- **PHP:** Ce projet nécessite PHP 7.2 ou une version ultérieure. Consultez [le site officiel de PHP](https://www.php.net/) pour les instructions d'installation.

- **Composer:** Composer est un gestionnaire de dépendances pour PHP. Si vous ne l'avez pas déjà installé, suivez les instructions sur [getcomposer.org](https://getcomposer.org/) pour l'installer.

- **Symfony CLI (facultatif) :** Symfony CLI est un ensemble d'outils en ligne de commande pour Symfony. Il facilite le développement, le débogage et le déploiement des applications Symfony. Installez-le avec les instructions sur [symfony.com/download](https://symfony.com/download).

## Installation 

1. Clonez le repository : `git clone https://github.com/elirabalison/API_Gestion_Materiel_Symfony.git`
2. Installez les dépendances : `composer install`
3. Copiez le fichier `.env` : `cp .env.dist .env`
4. Configurez votre base de données dans le fichier `.env`
5. Effectuez les migrations de base de données : `php bin/console doctrine:migrations:migrate`


## Utilisation

### 1. Ajouter un Équipement (POST)

**URL :** `http://votre-domaine.com/api/equipments`

**Méthode :** `POST`

**Headers :** 
```
Content-Type: application/json
```

**Body :** Sélectionnez l'option `raw` et entrez votre JSON de demande :
```json
{
    "name": "iPhone X 128GB",
    "category": "Téléphone",
    "number": "1234567890",
    "description": "Cet iPhone est en parfait état, avec une batterie fiable."
}
```

### 2. Mettre à Jour un Équipement (PUT)

**URL :** `http://votre-domaine.com/api/equipments/{id}`

Remplacez `{id}` par l'ID réel de l'équipement que vous voulez mettre à jour.

**Méthode :** `PUT`

**Headers :**
```
Content-Type: application/json
```

**Body :** Sélectionnez l'option `raw` et entrez votre JSON de demande pour la mise à jour :
```json
{
    "name": "Nouveau Nom",
    "category": "Nouvelle Catégorie",
    "number": "Nouveau Numéro",
    "description": "Nouvelle Description"
}
```

### 3. Supprimer un Équipement (DELETE)

**URL :** `http://votre-domaine.com/api/equipments/{id}`

Remplacez `{id}` par l'ID réel de l'équipement que vous voulez supprimer.

**Méthode :** `DELETE`

**Aucun Body ou Headers spéciaux nécessaires.**

### 4. Afficher Tous les Équipements (GET)

**URL :** `http://votre-domaine.com/api/equipments`

**Méthode :** `GET`

**Aucun Body ou Headers spéciaux nécessaires.**

