<p align="center">
  <img src="./logo.svg" alt="Contao Email Login" width="140" height="140">
</p>

# greeneffect/contao-email-login-bundle

*[English version](./README.md)*

Permet aux membres Contao de s'inscrire et de se connecter avec leur adresse e-mail.

## Principe

Contao ne sait authentifier un membre que par `tl_member.username`
(`Contao\User::loadUserByIdentifier()` fait un `SELECT ... WHERE username = ?`).
Plutôt que de décorer le fournisseur d'utilisateurs — ce qui toucherait à la
couche de sécurité — ce bundle **remplit `username` avec l'adresse e-mail**.
L'authentification, la session, le « se souvenir de moi » et la 2FA continuent
donc de fonctionner avec le code natif, sans surcharge.

## Ce que fait le bundle

| Fichier | Rôle |
| --- | --- |
| `src/Module/DerivesUsernameFromEmailTrait.php` | Surcharge `ModuleRegistration::createNewUser()` pour injecter `username = e-mail` avant l'enregistrement du membre. |
| `src/Module/ModuleEmailRegistration.php` | Applique le trait au module d'inscription natif. |
| `contao/config/config.php` | Remplace `$GLOBALS['FE_MOD']['user']['registration']` par ce module. |
| `src/Module/EmailRegistrationController.php` | Applique le trait au module d'inscription du Notification Center (voir plus bas). |
| `contao/dca/tl_member.php` | `username` passe en `varchar(255) NULL` (sans `BINARY`) et devient facultatif. |
| `src/EventListener/SynchronizeUsernameListener.php` | Réaligne `username` sur `email` à chaque enregistrement (back end et module « Données personnelles »). |
| `src/EventListener/LoginTemplateListener.php` | Remplace le libellé « Nom d'utilisateur » par « Adresse e-mail » dans le formulaire de connexion. |

Le retrait du flag `BINARY` est essentiel : il rendait la comparaison SQL
sensible à la casse, un membre saisissant `Jean@Exemple.fr` n'aurait pas pu se
connecter. L'identifiant est par ailleurs normalisé en minuscules à l'écriture
(`EmailIdentifier::fromEmail()`).

## Notification Center

Le module « Inscription (Notification Center) » n'est pas une surcharge du module
natif mais un **type de module distinct** (`registrationNotificationCenter`),
enregistré comme fragment par `#[AsFrontendModule]`. Remplacer
`$GLOBALS['FE_MOD']['user']['registration']` ne l'affecte donc pas : il lui faut
sa propre surcharge.

`EmailRegistrationController` étend `RegistrationController` et déclare le même
type de fragment avec une **priorité supérieure**. Contao ne conserve qu'un
service par type de fragment, celui de plus haute priorité
(`RegisterFragmentsPass`) : la surcharge ne dépend donc pas de l'ordre de
chargement des bundles.

La dépendance est optionnelle : `GreenEffectEmailLoginBundle::loadExtension()`
n'importe `config/services_notification_center.yaml` que si la classe amont
existe. Sans le Notification Center, le bundle fonctionne à l'identique sur le
seul module natif.

Les arguments du service reprennent ceux de `config/modules.php` du Notification
Center. Si une future version en change le constructeur, la compilation du
conteneur échouera explicitement — c'est voulu.

## Configuration du module d'inscription

Dans le back end, sur le module d'inscription — natif ou Notification Center —
ne cocher que **`email`** et **`password`** dans les champs éditables. Le champ `username` ne doit pas y
figurer : il serait affiché au visiteur alors qu'il est dérivé de l'e-mail.

L'option « Autoriser la connexion » (`reg_allowLogin`) doit rester activée, sinon
les comptes créés ne pourront pas se connecter.

## Installation

```bash
composer require greeneffect/contao-email-login-bundle
vendor/bin/contao-console contao:migrate
vendor/bin/contao-console cache:clear
```

La migration modifie la colonne `tl_member.username`. **Vérifier le SQL proposé
avant de valider** : si des membres existants possèdent des identifiants ne
différant que par la casse, la collation insensible à la casse fera échouer
l'index unique — il faut alors corriger ces doublons au préalable.

## Comportements à connaître

- **Membres existants** : leur `username` actuel est conservé jusqu'à leur
  prochain enregistrement (back end ou données personnelles), où il sera aligné
  sur leur e-mail. Pour tout basculer d'un coup :
  `UPDATE tl_member SET username = LOWER(email) WHERE email != '';`
  (à lancer après la migration, et après avoir vérifié qu'aucun e-mail n'est en
  double : `SELECT LOWER(email), COUNT(*) FROM tl_member GROUP BY 1 HAVING COUNT(*) > 1;`).
- **Changement d'e-mail par le membre** : l'identifiant change avec lui, ce qui
  invalide le jeton de session — le membre est déconnecté et doit se reconnecter
  avec sa nouvelle adresse. C'est le comportement natif de Contao lors d'un
  changement de nom d'utilisateur.
- **Back end** : le champ « Nom d'utilisateur » reste visible mais toute valeur
  saisie est écrasée par l'e-mail à l'enregistrement (mention ajoutée dans son
  libellé d'aide).
- **Mot de passe oublié** : le module natif cherche déjà par e-mail, rien à faire.

## Licence

[CC BY-SA 4.0](./LICENSE)
