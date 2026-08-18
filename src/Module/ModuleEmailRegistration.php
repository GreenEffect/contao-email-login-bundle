<?php

declare(strict_types=1);

namespace GreenEffect\EmailLoginBundle\Module;

use Contao\ModuleRegistration;

/**
 * Module d'inscription natif dans lequel l'identifiant de connexion est l'adresse e-mail.
 *
 * Le champ "username" n'a plus à figurer dans les champs éditables du module :
 * il est dérivé de l'e-mail juste avant l'enregistrement du membre.
 */
class ModuleEmailRegistration extends ModuleRegistration
{
    use DerivesUsernameFromEmailTrait;
}
