<?php

declare(strict_types=1);

namespace GreenEffect\EmailLoginBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Terminal42\NotificationCenterBundle\Controller\FrontendModule\RegistrationController;

class GreenEffectEmailLoginBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $configurator->import('../config/services.yaml');

        // Intégration optionnelle : le module d'inscription du Notification Center.
        // Sans ce test, EmailRegistrationController serait chargé alors que sa classe
        // parente n'existe pas.
        if (class_exists(RegistrationController::class)) {
            $configurator->import('../config/services_notification_center.yaml');
        }
    }
}
