<?php

declare(strict_types=1);

/**
 * Contact : joindre l'équipe SEH-NC.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'contact';
$titre_page  = 'Contact';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Écrire à la Société d'études historiques de la Nouvelle-Calédonie : question sur un bulletin, demande d'accès, signalement.</p>

    <div class="coordonnees">
      <b>Société d'études historiques de la Nouvelle-Calédonie</b><br>
      Fonds patrimonial calédonien &middot; Nouméa<br>
      Courriel : <b>contact@seh-nc.fr</b>
    </div>

    <form class="form-bloc large" method="post" action="#">
      <div class="deux-colonnes">
        <div class="champ">
          <label for="nom">Nom</label>
          <input id="nom" name="nom" type="text" placeholder="Votre nom">
        </div>
        <div class="champ">
          <label for="courriel">Courriel</label>
          <input id="courriel" name="courriel" type="email" placeholder="vous@exemple.fr">
        </div>
      </div>
      <div class="champ">
        <label for="sujet">Sujet</label>
        <select id="sujet" name="sujet">
          <option>Question sur un bulletin</option>
          <option>Demande d'accès membre</option>
          <option>Signaler une erreur</option>
          <option>Autre</option>
        </select>
      </div>
      <div class="champ">
        <label for="msg">Message</label>
        <textarea id="msg" name="msg" placeholder="Votre message&hellip;"></textarea>
      </div>
      <button class="bouton-plein" type="submit">Envoyer</button>
    </form>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
