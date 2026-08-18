<p align="center">
  <img src="./logo.svg" alt="Contao Email Login" width="140" height="140">
</p>

# greeneffect/contao-email-login-bundle

*[Version française](./README.fr.md)*

Lets Contao members register and log in using their e-mail address.

## Principle

Contao can only authenticate a member through `tl_member.username`
(`Contao\User::loadUserByIdentifier()` runs a `SELECT ... WHERE username = ?`).
Rather than decorating the user provider — which would touch the security
layer — this bundle **fills `username` with the e-mail address**.
Authentication, the session, "remember me" and 2FA therefore keep working
with the native code, without any override.

## What the bundle does

| File | Role |
| --- | --- |
| `src/Module/DerivesUsernameFromEmailTrait.php` | Overrides `ModuleRegistration::createNewUser()` to inject `username = email` before the member is saved. |
| `src/Module/ModuleEmailRegistration.php` | Applies the trait to the native registration module. |
| `contao/config/config.php` | Replaces `$GLOBALS['FE_MOD']['user']['registration']` with this module. |
| `src/Module/EmailRegistrationController.php` | Applies the trait to the Notification Center registration module (see below). |
| `contao/dca/tl_member.php` | `username` becomes `varchar(255) NULL` (without `BINARY`) and optional. |
| `src/EventListener/SynchronizeUsernameListener.php` | Realigns `username` with `email` on every save (back end and "Personal data" module). |
| `src/EventListener/LoginTemplateListener.php` | Replaces the "Username" label with "E-mail address" on the login form. |

Removing the `BINARY` flag is essential: it made the SQL comparison
case-sensitive, so a member typing `Jean@Exemple.fr` would not have been able
to log in. The identifier is also normalized to lowercase on write
(`EmailIdentifier::fromEmail()`).

## Notification Center

The "Registration (Notification Center)" module is not an override of the
native module but a **distinct module type** (`registrationNotificationCenter`),
registered as a fragment via `#[AsFrontendModule]`. Replacing
`$GLOBALS['FE_MOD']['user']['registration']` therefore has no effect on it: it
needs its own override.

`EmailRegistrationController` extends `RegistrationController` and declares
the same fragment type with a **higher priority**. Contao only keeps one
service per fragment type, the one with the highest priority
(`RegisterFragmentsPass`) — so the override does not depend on bundle
load order.

The dependency is optional: `GreenEffectEmailLoginBundle::loadExtension()`
only imports `config/services_notification_center.yaml` if the upstream class
exists. Without the Notification Center, the bundle works identically on the
native module alone.

The service arguments mirror those of the Notification Center's
`config/modules.php`. If a future version changes its constructor, container
compilation will fail explicitly — this is intentional.

## Registration module configuration

In the back end, on the registration module — native or Notification Center —
only check **`email`** and **`password`** in the editable fields. The
`username` field must not appear there: it would be shown to the visitor even
though it is derived from the e-mail address.

The "Allow login" option (`reg_allowLogin`) must stay enabled, otherwise
newly created accounts will not be able to log in.

## Installation

```bash
composer require greeneffect/contao-email-login-bundle
vendor/bin/contao-console contao:migrate
vendor/bin/contao-console cache:clear
```

The migration alters the `tl_member.username` column. **Review the proposed
SQL before confirming**: if existing members have identifiers that only
differ by case, the case-insensitive collation will make the unique index
fail — duplicates need to be fixed beforehand.

## Behaviors to know

- **Existing members**: their current `username` is kept until their next
  save (back end or "Personal data" module), at which point it is aligned
  with their e-mail. To migrate everyone at once:
  `UPDATE tl_member SET username = LOWER(email) WHERE email != '';`
  (run after the migration, and after checking that no e-mail is duplicated:
  `SELECT LOWER(email), COUNT(*) FROM tl_member GROUP BY 1 HAVING COUNT(*) > 1;`).
- **Member changes their e-mail**: the identifier changes with it, which
  invalidates the session token — the member is logged out and must log back
  in with their new address. This is Contao's native behavior when a
  username changes.
- **Back end**: the "Username" field stays visible but any value entered is
  overwritten by the e-mail on save (a note was added to its help text).
- **Forgot password**: the native module already looks up by e-mail, nothing
  to do.

## License

[CC BY-SA 4.0](./LICENSE)
