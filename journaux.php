<?php

declare(strict_types=1);

/**
 * Journaux : les 218 bulletins trimestriels de la SEH-NC.
 * Classes mobilisées : DocumentPDF, DocumentEPUB, SGBD, RapportExigences.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'journaux';
$titre_page  = 'Journaux';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Historique des traitements et rapports d'exigences. Trace ce qui a été demandé, appliqué ou rejeté, pour chaque bulletin.</p>

    <div class="barre-outils">
      <span class="filtre on">Tout</span>
      <span class="filtre">Exigences</span>
      <span class="filtre">Conversions</span>
      <span class="filtre">Connexions</span>
      <input class="recherche" type="search" placeholder="Filtrer par bulletin ou par membre&hellip;">
    </div>

    <div class="fil">
      <div class="fil-item">
        <div class="quand">06/07/2026 &middot; 09:14</div>
        <div class="quoi">Exigence appliquée &middot; Bulletin 1908 n&deg;3</div>
        <div class="detail">Action «&nbsp;Corriger&nbsp;» p.12 validée par J. Robert-Hénaff. 3 coquilles corrigées.</div>
      </div>
      <div class="fil-item">
        <div class="quand">06/07/2026 &middot; 08:58</div>
        <div class="quoi">Exigence soumise &middot; Bulletin 1908 n&deg;3</div>
        <div class="detail">Action «&nbsp;Effacer&nbsp;» p.15 : retrait du filigrane de numérisation. En attente.</div>
      </div>
      <div class="fil-item">
        <div class="quand">05/07/2026 &middot; 16:32</div>
        <div class="quoi">EPUB généré &middot; Bulletin 1905 n&deg;2</div>
        <div class="detail">Conversion terminée. 96 pages, 4 illustrations, 12 notes.</div>
      </div>
      <div class="fil-item">
        <div class="quand">05/07/2026 &middot; 15:07</div>
        <div class="quoi">Exigence rejetée &middot; Bulletin 1912 n&deg;7</div>
        <div class="detail">Action «&nbsp;Réorganiser&nbsp;» p.4 : refusée, l'ordre d'origine est conservé.</div>
      </div>
      <div class="fil-item">
        <div class="quand">05/07/2026 &middot; 14:20</div>
        <div class="quoi">Connexion &middot; J. Robert-Hénaff</div>
        <div class="detail">Ouverture de session depuis Nouméa.</div>
      </div>
    </div>

    <div class="bloc-titre">Rapport d'exigences &middot; Bulletin 1908 n&deg;3</div>
    <table class="tableau">
      <thead>
        <tr><th>Page</th><th>Action</th><th>Auteur</th><th>État</th></tr>
      </thead>
      <tbody>
        <tr><td class="ref">p. 8</td><td class="nom">Légender</td><td>J. Robert-Hénaff</td><td><span class="etat ok">appliquée</span></td></tr>
        <tr><td class="ref">p. 10</td><td class="nom">Réorganiser</td><td>J. Robert-Hénaff</td><td><span class="etat ok">validée</span></td></tr>
        <tr><td class="ref">p. 12</td><td class="nom">Corriger</td><td>J. Robert-Hénaff</td><td><span class="etat ok">appliquée</span></td></tr>
        <tr><td class="ref">p. 15</td><td class="nom">Effacer</td><td>J. Robert-Hénaff</td><td><span class="etat warn">soumise</span></td></tr>
      </tbody>
    </table>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
