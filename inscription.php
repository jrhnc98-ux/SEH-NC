<?php

declare(strict_types=1);

/**
 * Inscription : création d'un compte membre SEH-NC.
 * Classes mobilisées : Membre, SGBD.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'inscription';
$titre_page  = 'Inscription';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Création d'un compte membre SEH-NC. Le membre devient prescripteur : il désigne les corrections, l'atelier les applique. La demande est validée par l'administrateur.</p>

    <form class="form-bloc large" method="post" action="#">
      <div class="deux-colonnes">
        <div class="champ">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" required>
        </div>
        <div class="champ">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" required>
        </div>
      </div>
      <div class="champ">
        <label for="email">Courriel</label>
        <input type="email" id="email" name="email" placeholder="prenom.nom@seh-nc.fr" required>
      </div>
      <div class="champ">
        <label for="role">Rôle au sein de la SEH-NC</label>
        <select id="role" name="role">
          <option>Membre relecteur</option>
          <option>Éditeur</option>
          <option>Administrateur</option>
        </select>
      </div>
      <div class="deux-colonnes">
        <div class="champ">
          <label for="mdp1">Mot de passe</label>
          <input type="password" id="mdp1" name="mdp1" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
        </div>
        <div class="champ">
          <label for="mdp2">Confirmation</label>
          <input type="password" id="mdp2" name="mdp2" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
        </div>
      </div>
      <button class="bouton-plein" type="submit">Créer mon compte</button>
      <span class="aide-lien">Déjà membre ? <a href="connexion.php">Se connecter</a></span>
    </form>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
