<?php

declare(strict_types=1);

namespace GreenEffect\EmailLoginBundle\Module;

use GreenEffect\EmailLoginBundle\Member\EmailIdentifier;

/**
 * Injecte l'identifiant de connexion dérivé de l'e-mail dans les données du membre.
 *
 * Le trait s'applique à toute descendance de ModuleRegistration : le module natif
 * comme celui du Notification Center, qui en hérite également.
 *
 * L'injection a lieu ici plutôt que dans le hook "createNewUser", car ce dernier
 * n'est appelé qu'après $objNewUser->save(), donc après l'envoi de la notification
 * d'activation, la création du répertoire personnel et la version initiale.
 */
trait DerivesUsernameFromEmailTrait
{
    protected function createNewUser($arrData)
    {
        if (!empty($arrData['email'])) {
            $arrData['username'] = EmailIdentifier::fromEmail($arrData['email']);
        }

        parent::createNewUser($arrData);
    }
}
