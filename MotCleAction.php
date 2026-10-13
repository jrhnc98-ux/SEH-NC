<?php

declare(strict_types=1);

/**
 * Énumération MotCleAction : dictionnaire d'actions proposé au membre
 * pour pré-orienter chaque exigence de correction.
 */
enum MotCleAction: string
{
    case EFFACER         = 'effacer';
    case DEPLACER        = 'déplacer';
    case REORGANISER     = 'réorganiser';
    case CORRIGER        = 'corriger';
    case AJOUTER         = 'ajouter';
    case FUSIONNER       = 'fusionner';
    case SEPARER         = 'séparer';
    case METTRE_EN_FORME = 'mettre en forme';
    case LEGENDER        = 'légender';
    case REFERENCER      = 'référencer';
}
