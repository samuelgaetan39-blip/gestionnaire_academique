# gestionnaire _academique

## **Université Épiscopale d'Haïti (UNEPH) | Business and Technology Institute (BTI)**  
### 8, rue du Quai, HT8110 Les Cayes, Haïti

Ce projet (un site web de gestion académique) constitue l'examen final du cours de développement Web (niveau 2) de la Faculté des Sciences Informatiques, présenté au professeur Elso POINT-DU-JOUR, par l'étudiant Samuel Kensley GAËTAN (de code : 2450-C).  

Le site prévoit des comptes administratifs pré-intégrés (pour chaque rôle) et permet à seulement un administrateur ou une administratrice de créer un autre compte (pour étudiant·e ou enseignant·e). C'est pourquoi, dans le fichier « database.sql », aux lignes 81, 85 et 91, il peut être nécessaire de remplacer la partie associée au mot de passe (juste après l'e-mail) par le code qui sera généré en lançant d'abord le fichier « generate_hash.php » dans le navigateur. En effet, une fois le serveur (WAMP ou autre démarré), « localhost/gestionnaire_academique/generate_hash.php » (par exemple) produira le hash qui doit être copié dans « database.sql » pour le mot de passe.

Ainsi, lors de la connexion, l'administrateur ou l'administratrice devra entrer comme mot de passe, non le hash qui a été copié, mais simplement : 12345678. Ce mot de passe, auquel correspond le hash précédemment copié, sera valable pour tous les comptes administratifs, quel que soit leur rôle.

Quatre cours sont aussi pré-intégrés, à titre d'exemples.

*Décembre 2025*
