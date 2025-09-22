# 🌱 EcoRide - Plateforme de Covoiturage Écologique

EcoRide est une application de covoiturage orientée écologie qui permet aux utilisateurs de partager leurs trajets, réduire leur empreinte carbone et gagner des crédits écologiques.

## 🚀 Fonctionnalités

### Backend (PHP)
- **Authentification JWT** : Inscription, connexion, vérification email
- **Gestion des trajets** : Création, recherche, modification, annulation
- **Système de participation** : Réservation de places, gestion des statuts
- **Évaluations et avis** : Système de notation entre conducteurs et passagers
- **Crédits écologiques** : Système de points basé sur le type de véhicule
- **Gestion des véhicules** : CRUD complet avec types de carburant
- **Emails automatiques** : Vérification, récupération de mot de passe
- **API RESTful** : Documentation complète des endpoints

### Frontend (React)
- **Interface moderne** : Design responsive avec Tailwind CSS
- **Authentification** : Pages de connexion et inscription
- **Recherche de trajets** : Filtres avancés et pagination
- **Tableau de bord** : Vue d'ensemble des trajets et statistiques
- **Profil utilisateur** : Gestion des informations personnelles
- **Détails des trajets** : Informations complètes et réservation
- **Crédits écologiques** : Suivi des points et récompenses

## 🛠️ Technologies

### Backend
- **PHP 8.1+** avec architecture MVC
- **MySQL 8.0** pour la base de données
- **JWT** pour l'authentification
- **PHPMailer** pour les emails
- **PHPUnit** pour les tests
- **Docker** pour la containerisation

### Frontend
- **React 18** avec hooks modernes
- **Tailwind CSS** pour le design
- **React Router** pour la navigation
- **Axios** pour les requêtes API
- **Context API** pour la gestion d'état

### Infrastructure
- **Docker Compose** : Orchestration des services
- **Apache** : Serveur web
- **MySQL** : Base de données relationnelle

## 📋 Installation

### Prérequis
- Docker et Docker Compose
- Git

### 1. Cloner le projet
```bash
git clone https://github.com/kriscab94/ecoride1.git
cd ecoride1
```

### 2. Configuration de l'environnement
Copiez et modifiez le fichier d'environnement :
```bash
cp backend/.env.example backend/.env
```

Éditez `backend/.env` avec vos paramètres :
```env
# Database
DB_HOST=mysql
DB_NAME=ecoride
DB_USER=ecoride_user
DB_PASS=ecoride_pass

# JWT
JWT_SECRET=your-super-secret-jwt-key-change-in-production
JWT_EXPIRE=3600

# Email (Gmail SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_EMAIL=noreply@ecoride.com
MAIL_FROM_NAME="EcoRide"

# URLs
APP_URL=http://localhost:8080
FRONTEND_URL=http://localhost:3000
```

### 3. Lancer l'application
```bash
docker-compose up -d
```

Cette commande va :
- Construire et démarrer MySQL (port 3306)
- Construire et démarrer le backend PHP (port 8080)
- Construire et démarrer le frontend React (port 3000)
- Créer la base de données avec des données d'exemple

### 4. Accéder à l'application
- **Frontend** : http://localhost:3000
- **API Backend** : http://localhost:8080/api
- **Base de données** : localhost:3306

### 5. Comptes de test
```
Administrateur:
Email: admin@ecoride.com
Mot de passe: password

Conducteur:
Email: driver@ecoride.com  
Mot de passe: password

Passager:
Email: passenger@ecoride.com
Mot de passe: password
```

## 🗄️ Structure de la base de données

### Tables principales
- **users** : Utilisateurs avec crédits écologiques
- **vehicles** : Véhicules avec type de carburant
- **trips** : Trajets avec statuts et points éco
- **participations** : Réservations de places
- **reviews** : Évaluations et commentaires
- **eco_transactions** : Historique des crédits
- **messages** : Communication entre utilisateurs

## 🔧 Développement

### Backend
```bash
# Installer les dépendances
cd backend
composer install

# Lancer les tests
composer test

# Tests avec couverture
composer test-coverage
```

### Frontend
```bash
# Installer les dépendances
cd frontend
npm install

# Démarrer en mode développement
npm start

# Construire pour la production
npm run build

# Lancer les tests
npm test
```

### Base de données
```bash
# Accéder à MySQL
docker exec -it ecoride_mysql mysql -u ecoride_user -p ecoride

# Réinitialiser la base
docker-compose down -v
docker-compose up -d
```

## 📡 API Endpoints

### Authentification
- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion
- `POST /api/auth/verify-email` - Vérification email
- `POST /api/auth/forgot-password` - Mot de passe oublié
- `POST /api/auth/reset-password` - Réinitialisation

### Trajets
- `GET /api/trips` - Rechercher des trajets
- `POST /api/trips` - Créer un trajet
- `GET /api/trips/{id}` - Détails d'un trajet
- `PUT /api/trips/{id}` - Modifier un trajet
- `DELETE /api/trips/{id}` - Annuler un trajet

### Participations
- `POST /api/trips/{id}/participate` - Rejoindre un trajet
- `PUT /api/participations/{id}/status` - Modifier le statut
- `GET /api/participations/{id}` - Détails participation

### Profil utilisateur
- `GET /api/user/profile` - Profil utilisateur
- `PUT /api/user/profile` - Modifier le profil
- `GET /api/user/credits` - Crédits écologiques

## 🌱 Système de Crédits Écologiques

### Attribution des points
- **Véhicule électrique** : 3 points par km
- **Véhicule hybride** : 2 points par km  
- **Véhicule diesel** : 1.2 points par km
- **Véhicule essence** : 1 point par km

### Utilisation des crédits
- Réductions sur les trajets
- Badges écologiques
- Avantages exclusifs

## 🧪 Tests

### Backend (PHPUnit)
```bash
cd backend
vendor/bin/phpunit
```

### Frontend (Jest)
```bash
cd frontend
npm test
```

## 🚀 Déploiement

### Production avec Docker
```bash
# Variables d'environnement de production
export ENVIRONMENT=production

# Construire les images
docker-compose -f docker-compose.prod.yml build

# Déployer
docker-compose -f docker-compose.prod.yml up -d
```

### Optimisations
- Compression Gzip activée
- Cache des assets statiques
- Minification CSS/JS
- Optimisation des images

## 🤝 Contribution

1. Fork du projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit des changements (`git commit -m 'Ajout nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 🆘 Support

Pour obtenir de l'aide :
1. Consultez la documentation
2. Vérifiez les issues existantes
3. Créez une nouvelle issue avec le template approprié

## 🔮 Roadmap

### Version 2.0
- [ ] Application mobile React Native
- [ ] Intégration cartes GPS en temps réel
- [ ] Système de messagerie instantanée
- [ ] Paiements intégrés
- [ ] Notifications push
- [ ] Mode hors ligne

### Version 1.5
- [ ] Géolocalisation avancée
- [ ] Partage sur réseaux sociaux
- [ ] Système de parrainage
- [ ] Dashboard administrateur
- [ ] Statistiques détaillées

---

Fait avec 💚 pour une mobilité plus durable !