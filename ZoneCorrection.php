<?php

declare(strict_types=1);

/**
 * Classe ZoneCorrection (domaine métier).
 * Zone désignée par le membre sur le document : repérée par sa page
 * et ses coordonnées (le cadre glissant de la maquette).
 * Relation du diagramme : a 1 StatutZone -> attribut simple.
 */
class ZoneCorrection
{
    private string $id;
    private int $page;
    private int $x1;
    private int $y1;
    private int $x2;
    private int $y2;
    private int $largeur;
    private int $hauteur;

    // Relation "a" : le statut de la zone
    private StatutZone $statut;

    public function getCoordonnees(): string { }

    public function redimensionner(int $nouveauX, int $nouveauY): void { }

    public function annuler(): void { }
}
