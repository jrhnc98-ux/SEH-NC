<?php

declare(strict_types=1);

/**
 * Classe ExigenceCorrection (domaine métier).
 * La demande de modification exprimée par le membre sur une zone,
 * pré-orientée par un mot-clé du dictionnaire d'actions et enrichie
 * par la suggestion de l'IA.
 * Relations du diagramme :
 *   - référence 1 ZoneCorrection  -> attribut simple
 *   - utilise 1 MotCleAction      -> attribut simple
 *   - a 1 StatutExigence          -> attribut simple
 */
class ExigenceCorrection
{
    private string $id;

    // Relation "référence" : la zone visée
    private ZoneCorrection $zone;

    // Relation "utilise" : le mot-clé du dictionnaire d'actions
    private MotCleAction $motCle;

    private string $commentaire;
    private string $suggestionIA;
    private DateTime $dateCreation;

    // Relation "a" : le statut de l'exigence
    private StatutExigence $statut;

    public function toJSON(): string { }

    public function appliquer(): void { }
}
