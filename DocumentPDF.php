<?php

declare(strict_types=1);

/**
 * Classe DocumentPDF (service externe).
 * Le document source, affiché en regard de l'EPUB pour la comparaison.
 */
class DocumentPDF
{
    private string $chemin;
    private int $pages;

    public function afficher(): void { }

    public function getPage(int $page): string { }
}
