<?php

declare(strict_types=1);

/**
 * Classe AtelierProduction (orchestrateur).
 * Point d'entrée de l'Atelier : charge les deux documents, affiche la
 * comparaison, recueille les zones sélectionnées et pilote la session
 * de correction jusqu'au rapport d'exigences.
 * Relations du diagramme :
 *   - utilise 1 Membre                    -> attribut simple
 *   - charge 1 DocumentPDF                -> attribut simple
 *   - charge 1 DocumentEPUB               -> attribut simple
 *   - contient * ZoneCorrection           -> tableau
 *   - possède 1 PanneauCorrection         -> attribut simple
 *   - interroge 1 SGBD                    -> attribut simple
 *   - sollicite 1 AssistantIA             -> attribut simple
 */
class AtelierProduction
{
    // Relation "utilise" : le membre connecté
    private Membre $membreActuel;

    // Relation "charge" : le PDF source
    private DocumentPDF $pdfSource;

    // Relation "charge" : l'EPUB converti
    private DocumentEPUB $epubConverti;

    // Relation "contient *" : un tableau de ZoneCorrection
    private array $zonesSelectionnees = array();

    // Relation "possède" : le panneau de correction
    private PanneauCorrection $panneauCorrection;

    // Relation "interroge" : une référence au SGBD
    private SGBD $sgbd;

    // Relation "sollicite" : une référence à l'assistant IA
    private AssistantIA $assistantIA;

    public function chargerDocuments(string $pdf, string $epub): void { }

    public function afficherComparaison(): void { }

    public function selectionnerZone(int $x1, int $y1, int $x2, int $y2): ZoneCorrection { }

    public function ouvrirPanneauCorrection(ZoneCorrection $zone): void { }

    public function validerToutesCorrections(): RapportExigences { }

    public function genererRapport(): string { }
}
