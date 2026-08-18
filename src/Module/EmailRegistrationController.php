<?php

declare(strict_types=1);

namespace GreenEffect\EmailLoginBundle\Module;

use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Terminal42\NotificationCenterBundle\Controller\FrontendModule\RegistrationController;

/**
 * Même traitement que ModuleEmailRegistration, pour le module d'inscription du
 * Notification Center (type "registrationNotificationCenter").
 *
 * Le type et le template reprennent ceux de RegistrationController : à type de
 * fragment identique, Contao ne conserve qu'un seul service, celui de plus haute
 * priorité (RegisterFragmentsPass). La priorité positive garantit donc que cette
 * classe l'emporte, indépendamment de l'ordre de chargement des bundles.
 */
#[AsFrontendModule(type: 'registrationNotificationCenter', category: 'user', template: 'member_default', priority: 10)]
class EmailRegistrationController extends RegistrationController
{
    use DerivesUsernameFromEmailTrait;
}
