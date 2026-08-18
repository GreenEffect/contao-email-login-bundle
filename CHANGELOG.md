## Changelog

### 1.0.0

- Initial release: registration and login of Contao members using their
  e-mail address as identifier.
- Support for the native registration module and the Notification Center
  registration module.
- `tl_member.username` derived from and kept in sync with `email`, with
  case-insensitive comparison (`BINARY` flag removed).
- Login form label switched from "Username" to "E-mail address".
