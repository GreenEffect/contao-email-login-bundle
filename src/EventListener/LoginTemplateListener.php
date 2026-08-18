<?php

declare(strict_types=1);

namespace GreenEffect\EmailLoginBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\System;
use Contao\Template;

/**
 * Remplace le libellé "Nom d'utilisateur" du formulaire de connexion.
 *
 * ModuleLogin alimente le template avec $GLOBALS['TL_LANG']['MSC']['username'],
 * une clé partagée avec le back end : on surcharge donc la variable du template
 * plutôt que la traduction elle-même.
 */
#[AsHook('parseTemplate')]
class LoginTemplateListener
{
    public function __invoke(Template $template): void
    {
        if (!str_starts_with($template->getName(), 'mod_login')) {
            return;
        }

        System::loadLanguageFile('default');

        if (!empty($GLOBALS['TL_LANG']['MSC']['emailLogin'])) {
            $template->username = $GLOBALS['TL_LANG']['MSC']['emailLogin'];
        }
    }
}
