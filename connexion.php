<?php

declare(strict_types=1);

/**
 * Connexion : identification du membre de la SEH-NC.
 * Classes mobilisées : Membre, SGBD.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'connexion';
$titre_page  = 'Connexion';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Accès réservé aux membres de la SEH-NC. Formulaire sobre, en colonne à gauche.</p>

    <form class="form-bloc" method="post" action="#">
      <div class="champ">
        <label for="email">Identifiant ou courriel</label>
        <input type="email" id="email" name="email" placeholder="prenom.nom@seh-nc.fr" required>
      </div>
      <div class="champ">
        <label for="mdp">Mot de passe</label>
        <input type="password" id="mdp" name="mdp" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;" required>
      </div>
      <button class="bouton-plein" type="submit">Se connecter</button>
      <span class="aide-lien">Pas encore de compte ? <a href="inscription.php">Créer un compte membre</a></span>
      <span class="aide-lien">Mot de passe oublié ? <a href="contact.php">Contacter la SEH-NC</a></span>
    </form>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
