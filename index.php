<?php

declare(strict_types=1);

/**
 * Page d'accueil de l'Atelier de Production (maquette Jean Robert-Hénaff).
 * Elle précède l'ancienne page d'accueil, devenue la page Fonds.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = '';
$titre_page  = '';
require __DIR__ . '/includes/entete.php';
?>

    <div class="titre-atelier" style="margin-top:18px">SEH &middot; Atelier de production</div>

    <div class="accueil-zone">
      <p class="devise">&laquo; Un peuple qui ignore son histoire ressemble à un arbre sans racines : il pousse, certes, mais le premier vent l'emporte. &raquo;</p>
      <a class="entrer" href="fonds.php">Entrer dans l'atelier &rarr;</a>
    </div>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
