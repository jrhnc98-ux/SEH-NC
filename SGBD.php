<?php

declare(strict_types=1);

/**
 * Classe SGBD (service externe).
 * Porte la persistance des exigences et des rapports.
 * Relations du diagramme : stocke ExigenceCorrection et RapportExigences.
 */
class SGBD
{
    private string $chaineConnexion;

    public function enregistrerExigence(ExigenceCorrection $exigence): bool { }

    public function recupererExigences(string $idMembre): array { }

    public function recupererRapport(string $id): RapportExigences { }

    public function supprimerExigence(string $id): bool { }
}
