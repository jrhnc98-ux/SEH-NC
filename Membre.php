<?php

declare(strict_types=1);

/**
 * Classe Membre (domaine métier).
 * Le membre de la SEH-NC connecté à l'Atelier.
 */
class Membre
{
    private string $id;
    private string $nom;
    private string $email;
    private string $role;

    public function getNom(): string { return $this->nom; }

    public function getId(): string { return $this->id; }
}
