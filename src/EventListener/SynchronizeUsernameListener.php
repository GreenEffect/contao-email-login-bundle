<?php

declare(strict_types=1);

namespace GreenEffect\EmailLoginBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\User;
use Doctrine\DBAL\Connection;
use GreenEffect\EmailLoginBundle\Member\EmailIdentifier;

/**
 * Réaligne tl_member.username sur tl_member.email après chaque enregistrement.
 *
 * Sans cela, un changement d'adresse e-mail laisserait l'ancien identifiant en
 * base et le membre ne pourrait plus se connecter avec sa nouvelle adresse.
 *
 * Le callback "config.onsubmit" couvre les deux points d'entrée :
 *  - back end        : DC_Table appelle $callback($dc) ;
 *  - données perso   : ModulePersonalData appelle $callback($user, $module).
 */
#[AsCallback(table: 'tl_member', target: 'config.onsubmit')]
class SynchronizeUsernameListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function __invoke(mixed $subject = null): void
    {
        $id = $this->extractMemberId($subject);

        if (null === $id) {
            return;
        }

        $member = $this->connection->fetchAssociative(
            'SELECT username, email FROM tl_member WHERE id = ?',
            [$id],
        );

        if (false === $member || '' === (string) $member['email']) {
            return;
        }

        $username = EmailIdentifier::fromEmail((string) $member['email']);

        if ($username === $member['username']) {
            return;
        }

        // L'unicité de l'e-mail étant validée en amont par le DCA, une violation de
        // l'index unique sur username signalerait une incohérence réelle des données :
        // on laisse volontairement l'exception remonter plutôt que de désynchroniser.
        $this->connection->update('tl_member', ['username' => $username], ['id' => $id]);
    }

    private function extractMemberId(mixed $subject): int|null
    {
        if ($subject instanceof DataContainer) {
            return (int) $subject->id ?: null;
        }

        // ModulePersonalData transmet l'utilisateur connecté à la place du DataContainer
        if ($subject instanceof User) {
            return (int) $subject->id ?: null;
        }

        return null;
    }
}
