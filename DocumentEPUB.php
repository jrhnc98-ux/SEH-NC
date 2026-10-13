<?php

declare(strict_types=1);

/**
 * Classe DocumentEPUB (service externe).
 * Le document converti, celui que le membre relit et annote.
 */
class DocumentEPUB
{
    private string $chemin;
    private string $contenu;

    public function afficher(): void { }

    public function getContenu(): string { }
}
