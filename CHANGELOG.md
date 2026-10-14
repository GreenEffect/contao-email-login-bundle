## Changelog

### 0.9.1

- Fix: the Notification Center registration module crashed (HTTP 500) with
  Notification Center 2.7.6, whose `RegistrationController` constructor gained a
  sixth argument (`ParameterBagInterface`). The service is now autowired instead
  of listing the upstream arguments by hand, so it follows constructor changes
  and works with Notification Center 2.7.4 as well as 2.7.6. Only `$optIn` is
  bound explicitly (`contao.opt_in`), Contao exposing no alias for
  `OptInInterface`.

### 0.9.0

- Initial release: registration and login of Contao members using their
  e-mail address as identifier.
- Support for the native registration module and the Notification Center
  registration module.
- `tl_member.username` derived from and kept in sync with `email`, with
  case-insensitive comparison (`BINARY` flag removed).
- Login form label switched from "Username" to "E-mail address".
