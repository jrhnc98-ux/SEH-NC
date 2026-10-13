<?php

declare(strict_types=1);

/**
 * Classe RapportExigences (domaine métier).
 * Le récapitulatif des exigences d'une session de correction.
 * Relations du diagramme :
 *   - appartient à 1 Membre               -> attribut simple
 *   - contient * ExigenceCorrection       -> tableau
 */
class RapportExigences
{
    private string $id;

    // Relation "appartient à" : le membre auteur du rapport
    private Membre $membre;

    // Relation "contient *" : un tableau d'ExigenceCorrection
    private array $exigences = array();

    private DateTime $dateGeneration;
    private int $totalExigences;

    public function exporterHTML(): string { }

    public function exporterPDF(): string { }

    public function resumer(): string { }
}
