<?php

declare(strict_types=1);

/**
 * Aide : guide d'utilisation de l'Atelier.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'aide';
$titre_page  = 'Aide';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Guide d'usage pour les membres, pensé pour des utilisateurs non informaticiens. Chaque étape est décrite simplement.</p>

    <div class="aide-layout">
      <nav class="sommaire">
        <div class="etiquette">Sommaire</div>
        <a href="#demarrer">Démarrer</a>
        <a href="#relire">Relire un bulletin</a>
        <a href="#zone">Désigner une zone</a>
        <a href="#action">Choisir une action</a>
        <a href="#ia">La proposition IA</a>
        <a href="#lots">Traiter par lots</a>
      </nav>

      <div class="aide-contenu">
        <h3 id="demarrer">Démarrer</h3>
        <p>Connecte-toi avec ton identifiant de membre. Tu arrives sur la page Fonds, qui montre les travaux en cours et les bulletins disponibles. Choisis un bulletin pour commencer.</p>

        <h3 id="relire">Relire un bulletin</h3>
        <p>Ouvre le Convertisseur par document. Le PDF d'origine s'affiche à gauche, l'aperçu du livre numérique (EPUB) à droite. Tu compares les deux page à page grâce aux flèches en haut.</p>

        <h3 id="zone">Désigner une zone</h3>
        <p>Sur le PDF, maintiens le clic et trace un rectangle autour du passage à corriger. Un cadre apparaît : c'est la zone que tu prescris à l'atelier. Tu n'as rien à coder, tu montres simplement l'endroit.</p>

        <h3 id="action">Choisir une action</h3>
        <p>Dans le panneau de correction, choisis une action dans la liste de mots-clés : corriger, effacer, déplacer, légender, et ainsi de suite. Ajoute une consigne en une phrase pour préciser ce que tu attends.</p>

        <h3 id="ia">La proposition IA</h3>
        <p>L'assistant propose une correction en une seule passe. Tu la relis, puis tu valides pour l'appliquer, ou tu la modifies. Rien n'est changé sans ta validation : tu restes le prescripteur.</p>

        <h3 id="lots">Traiter par lots</h3>
        <p>Pour convertir plusieurs bulletins d'un coup sans relecture fine, utilise la page Par lots : dépose les fichiers, lance la conversion et suis la progression.</p>
      </div>
    </div>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
