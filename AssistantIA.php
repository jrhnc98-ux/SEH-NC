<?php

declare(strict_types=1);

/**
 * Classe AssistantIA (service externe).
 * Le chat à sens unique : le membre pose une question, l'IA propose
 * une suggestion, le membre l'accepte ou la retouche. Un seul tour.
 * En production : appel HTTP à l'API (cURL), clé lue via getenv,
 * jamais en dur dans le code.
 */
class AssistantIA
{
    private string $modele;
    private float $temperature;

    public function genererSuggestion(ZoneCorrection $zone, MotCleAction $motCle, string $question): string { }

    public function analyserContexte(ZoneCorrection $zone): string { }

    public function proposerReformulation(string $texte): string { }
}
