<?php

declare(strict_types=1);

/**
 * Catalogues : les ouvrages EPUB validés, classés par cote et indice Dewey.
 * Classes mobilisées : DocumentEPUB, SGBD.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'catalogues';
$titre_page  = 'Catalogues';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Classement du fonds selon la classification Dewey. Fiches éditoriales des ouvrages et bulletins validés, prêts pour la Vitrine, avec filtres thématiques.</p>

    <div class="barre-outils">
      <span class="filtre on">Tout</span>
      <span class="filtre">900 &middot; Histoire</span>
      <span class="filtre">990 &middot; Océanie</span>
      <span class="filtre">550 &middot; Géologie</span>
      <span class="filtre">390 &middot; Coutumes</span>
      <input class="recherche" type="search" placeholder="Rechercher un ouvrage, une cote, un thème&hellip;">
    </div>

    <div class="catalogue">
      <div class="fiche">
        <div class="couv">La Foa, notes d'histoire</div>
        <div class="meta"><span class="t">SEH-NC/O-017</span>Chroniques et récits de la commune.<span class="dewey">Dewey 995.97</span></div>
      </div>
      <div class="fiche">
        <div class="couv">La découverte de la Nouvelle-Calédonie</div>
        <div class="meta"><span class="t">SEH-NC/O-003</span>Premiers contacts et cartographie.<span class="dewey">Dewey 995.9</span></div>
      </div>
      <div class="fiche">
        <div class="couv">Histoire maritime du Pacifique Sud</div>
        <div class="meta"><span class="t">SEH-NC/O-024</span>Routes, navires et comptoirs.<span class="dewey">Dewey 387.5</span></div>
      </div>
      <div class="fiche">
        <div class="couv">Bulletin SEH n°7, 1912</div>
        <div class="meta"><span class="t">SEH-NC/B-1912-07</span>Géologie du nickel, prospection minière.<span class="dewey">Dewey 553.4</span></div>
      </div>
      <div class="fiche">
        <div class="couv">Bulletin SEH n°1, 1920</div>
        <div class="meta"><span class="t">SEH-NC/B-1920-01</span>Coutumes, récits oraux, langues kanak.<span class="dewey">Dewey 390.9</span></div>
      </div>
      <div class="fiche">
        <div class="couv">Bulletin SEH n°5, 1928</div>
        <div class="meta"><span class="t">SEH-NC/B-1928-05</span>Faune endémique, inventaires illustrés.<span class="dewey">Dewey 591.9</span></div>
      </div>
    </div>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
