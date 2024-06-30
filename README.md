# SÉCURITÉ TP1 - LOGIN SÉCURISÉ

## A propos

Ce projet propose une implémentation orientée service d'une API gérant l'inscription, la connexion et les sessions d'utilisateurs de manière sécurisée et robuste.

Conformément aux consignes, ce projet est basé sur le [TP2 - DBAL & API](https://github.com/RathGate/corbel_b2_php_tp2). 

### Spécifications techniques

**Attention** : La totalité de l'API a été réécrite et upgradée de PHP 7.3 à **PHP 8.2**.

**Versions** :
- PHP : **8.2.12**
- Serveur web : **XAMPP 8.2.12**
- Base de données : **MySQL 10.4.32-MariaDB**

Client API recommandé : **Postman**

## Installation 

Pour récupérer le projet depuis GitHub :
```
git clone https://github.com/RathGate/corbel_b2_securite_tp1
```

L'API est divisée en deux dossiers : `/src/www/` et `/src/credentials` :
- le contenu du dossier `/src/www/` doit se trouver dans le dossier du serveur web ;
- le dossier `/src/credentials/` doit se trouver un niveau au-dessus du dossier du serveur web à des fins de sécurité.

Il revient à l'utilisateur de modifier les valeurs d'environnement contenues dans les fichiers de `/src/credentials/` avant de tester le bon fonctionnement de l'API.
## Documentation API

**Format** :
- Le `body` des requêtes `POST` et `PUT` doit être au format `x-www-form-urlencoded`.

### Inscription d'un nouvel utilisateur
```http
  POST /api/sign_up
```

| Paramètre | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `email` | `string` | **Requis**. Email de l'utilisateur |
| `password` | `string` | **Requis**. Mot de passe de l'utilisateur |

Si la création de l'utilisateur réussit, un OTP de confirmation du compte est généré et envoyé par mail à l'adresse mail fournie.

<details>
  <summary>### hello</summary>                                               
  test test
</details>

### Vérification d'un compte utilisateur

#### Génération d'un OTP de validation

```http
  GET /api/verify_account
```

| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email`      | `string` | **Requis**. Email de l'utilisateur |
| `password`      | `string` | **Requis**. Mot de passe de l'utilisateur |

L'OTP de confirmation est envoyé par mail à l'adresse mail de l'utilisateur.

#### Vérification du compte avec l'OTP

```http
  POST /api/verify_account
```

| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email`      | `string` | **Requis**. Email de l'utilisateur |
| `otp`      | `string` | **Requis**. OTP reçu par email |

L'OTP de confirmation est envoyé par email à l'adresse de l'utilisateur.

### Connexion de l'utilisateur

```http
  POST /api/sign_in
```

| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email`      | `string` | **Requis**. Email de l'utilisateur |
| `password`      | `string` | **Requis**. Mot de passe de l'utilisateur |

Un token de session est retourné à l'utilisateur, qui doit être placé dans les `headers` des futures requêtes sous la forme `Autorization: Bearer {token}`.

### Vérification de la session d'un utilisateur

```http
  GET /api/signed_in
```
**Paramètres de `Headers`** :

| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Autorization`      | `string` | **Requis**. Token de l'utilisateur sous le format `Bearer {token}` |

**Paramètres de la requête** :
| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Webservice`      | `string` | **Optionnel**. Nom du webservice à accéder |


### Déconnexion de l'utilisateur

```http
  POST /api/sign_out
```

**Paramètres de `Headers`** :

| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `Autorization`      | `string` | **Optionnel**. Token de l'utilisateur sous le format `Bearer {token}` |

**Paramètres de la requête** :
| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `all`      | `null` | **Optionnel**. Force la suppression de toutes les sessions |

### Modification du mot de passe

#### Génération d'un OTP de validation
```http
  GET /api/modify_password
```

**Paramètres de la requête** :
| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email`      | `string` | **Requis**. Email de l'utilisateur |

L'OTP de confirmation est envoyé par email à l'adresse de l'utilisateur.

#### Modification du mot de passe avec l'OTP 
```http
  PUT /api/modify_password
```

**Paramètres de la requête** :
| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email`      | `string` | **Requis**. Email de l'utilisateur |
| `new_password`      | `string` | **Requis**. Nouveau mot de passe |
| `otp`      | `string` | **Requis**. OTP reçu par email |


### Suppression du compte

#### Génération d'un OTP de validation
```http
  GET /api/delete_account
```

**Paramètres de la requête** :
| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email`      | `string` | **Requis**. Email de l'utilisateur |
| `password`      | `string` | **Requis**. Mot de passe de l'utilisateur |

L'OTP de confirmation est envoyé par email à l'adresse de l'utilisateur.

#### Modification du mot de passe avec l'OTP 
```http
  DELETE /api/delete_account
```

**Paramètres de la requête** :
| Paramètre | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email`      | `string` | **Requis**. Email de l'utilisateur |
| `otp`      | `string` | **Requis**. OTP reçu par email |

