<?php

declare(strict_types=1);

/**
 * Énumération StatutExigence : cycle de vie d'une exigence de correction.
 */
enum StatutExigence: string
{
    case BROUILLON = 'brouillon';
    case SOUMISE   = 'soumise';
    case VALIDEE   = 'validée';
    case REJETEE   = 'rejetée';
    case APPLIQUEE = 'appliquée';
}
