<?php

declare(strict_types=1);

/**
 * Classe PanneauCorrection (orchestrateur).
 * Le panneau ouvert sur une zone : dictionnaire d'actions (mots-clés),
 * commentaire en langage naturel, assistance IA à sens unique.
 * Relations du diagramme :
 *   - produit 1 ExigenceCorrection        -> résultat de validerExigence
 *   - propose * MotCleAction              -> le dictionnaire d'actions
 */
class PanneauCorrection
{
    private ZoneCorrection $zoneActive;
    private MotCleAction $motCleSelectionne;
    private string $commentaire;
    private string $suggestionIA;

    public function afficherMotsCles(): array { }

    public function selectionnerMotCle(MotCleAction $mot): void { }

    public function saisirCommentaire(string $texte): void { }

    public function demanderAssistanceIA(string $question): string { }

    public function validerExigence(): ExigenceCorrection { }
}
