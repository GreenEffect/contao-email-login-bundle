<?php

declare(strict_types=1);

/*
 * L'identifiant de connexion est dérivé de l'adresse e-mail :
 *
 *  - la colonne passe de varchar(64) à varchar(255), longueur maximale d'un e-mail
 *    dans Contao (tl_member.email) ;
 *  - le flag BINARY est retiré : il rend la comparaison SQL sensible à la casse,
 *    donc un membre saisissant "Jean@Exemple.fr" au lieu de "jean@exemple.fr" ne
 *    pourrait pas se connecter. Sans BINARY, la collation par défaut de la table
 *    (insensible à la casse) s'applique ;
 *  - le champ n'est plus obligatoire côté back end, puisqu'il est rempli
 *    automatiquement par SynchronizeUsernameListener.
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['sql'] = 'varchar(255) NULL';
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['maxlength'] = 255;
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['mandatory'] = false;
