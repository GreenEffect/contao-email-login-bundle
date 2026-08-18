<?php

declare(strict_types=1);

namespace GreenEffect\EmailLoginBundle\Member;

/**
 * Dérive l'identifiant de connexion (tl_member.username) depuis une adresse e-mail.
 *
 * La normalisation en minuscules est indispensable : sans elle, deux membres
 * pourraient exister avec la même adresse écrite différemment, et l'unicité
 * de tl_member.username ne serait plus alignée sur celle de tl_member.email.
 */
final class EmailIdentifier
{
    private function __construct()
    {
    }

    public static function fromEmail(string $email): string
    {
        return mb_strtolower(trim($email), 'UTF-8');
    }
}
