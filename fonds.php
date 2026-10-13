<?php

declare(strict_types=1);

/**
 * Page Fonds : tableau de bord de l'atelier.
 * Etat des travaux en cours, productions récentes, étagère du fonds.
 * Classes mobilisées : SGBD (lecture des statuts en base),
 * RapportExigences (inventaire des sessions closes).
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'fonds';
$titre_page  = 'Fonds';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Tableau de bord de l'atelier : état des travaux en cours, productions récentes et étagère du fonds patrimonial. Point d'entrée du membre après connexion.</p>

    <div class="bloc-titre">En un coup d'oeil</div>
    <div class="stats">
      <div class="stat"><b>128</b><span>Documents au fonds</span></div>
      <div class="stat"><b>7</b><span>Traitements en cours</span></div>
      <div class="stat"><b>3</b><span>Exigences à valider</span></div>
      <div class="stat"><b>41</b><span>EPUB produits</span></div>
    </div>

    <div class="bloc-titre">Travaux en cours</div>
    <table class="tableau">
      <thead>
        <tr><th>Réf.</th><th>Bulletin</th><th>Étape</th><th>État</th></tr>
      </thead>
      <tbody>
        <tr><td class="ref">B-1908-03</td><td class="nom">Bulletin SEH n°3, 1908</td><td>Relecture assistée</td><td><span class="etat cours">en cours</span></td></tr>
        <tr><td class="ref">B-1912-07</td><td class="nom">Bulletin SEH n°7, 1912</td><td>Zones à corriger</td><td><span class="etat attente">en attente</span></td></tr>
        <tr><td class="ref">B-1920-01</td><td class="nom">Bulletin SEH n°1, 1920</td><td>Exigences soumises</td><td><span class="etat warn">à valider</span></td></tr>
        <tr><td class="ref">B-1905-02</td><td class="nom">Bulletin SEH n°2, 1905</td><td>EPUB généré</td><td><span class="etat ok">terminé</span></td></tr>
      </tbody>
    </table>

    <div class="bloc-titre">Étagère du fonds</div>
    <div class="fonds">
      <div class="shelf">
        <div class="slot"><span class="legend">1905</span></div>
        <div class="slot"><span class="legend">1908</span></div>
        <div class="slot"><span class="legend">1912</span></div>
        <div class="slot"><span class="legend">1920</span></div>
        <div class="slot"><span class="legend">1924</span></div>
      </div>
    </div>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
