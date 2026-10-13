<?php

declare(strict_types=1);

/**
 * Énumération StatutZone : cycle de vie d'une zone sélectionnée.
 */
enum StatutZone: string
{
    case SELECTIONNEE = 'sélectionnée';
    case EN_COURS     = 'en cours';
    case VALIDEE      = 'validée';
    case ANNULEE      = 'annulée';
}
