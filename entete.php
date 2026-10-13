<?php
/**
 * Entête commune : logo centré + boutons de navigation verticaux.
 * Fidèle à la maquette : le logo en haut, un filet, puis les boutons empilés.
 * $page_active est défini par chaque page avant l'inclusion.
 * Ordre des boutons imposé : Fonds, Connexion, Inscription,
 * Convertisseur par document, Par lots, Catalogues, Journaux, Contact, Aide.
 */
if (!isset($page_active)) { $page_active = ''; }
$boutons = array(
    'fonds'         => array('fonds.php',         'Fonds'),
    'connexion'     => array('connexion.php',     'Connexion'),
    'inscription'   => array('inscription.php',   'Inscription'),
    'convertisseur' => array('convertisseur.php', 'Convertisseur par document'),
    'lots'          => array('lots.php',          'Par lots'),
    'catalogues'    => array('catalogues.php',    'Catalogues'),
    'journaux'      => array('journaux.php',      'Journaux'),
);
$petits = array(
    'contact' => array('contact.php', 'Contact'),
    'aide'    => array('aide.php',    'Aide'),
);
// Descriptions affichées en bulle au survol des boutons d'accueil.
$tips = array(
    'fonds'         => 'Tableau de bord : état des travaux et des productions, étagère, compteurs.',
    'connexion'     => 'Accès membre, formulaire sobre en colonne à gauche.',
    'inscription'   => 'Création de compte membre SEH-NC.',
    'convertisseur' => 'Relecture assistée : PDF et EPUB côte à côte, sélection de zone, mots-clés, IA.',
    'lots'          => 'Traitement de masse : dépôt multiple, file d\'attente, progression.',
    'catalogues'    => 'Classement Dewey, fiches éditoriales, filtres thématiques.',
    'journaux'      => 'Historique des traitements et rapports d\'exigences.',
    'contact'       => 'Formulaire et coordonnées de la SEH-NC.',
    'aide'          => 'Guide d\'usage pour les membres non informaticiens.',
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SEH-NC · Atelier de production<?php echo $titre_page ? ' · ' . $titre_page : ''; ?></title>
  <link rel="stylesheet" href="css/atelier.css?v=36">
</head>
<body>

  <!-- LOGO CENTRÉ EN HAUT (croquis : "logo") -->
  <div class="logo-header">
    <a href="index.php" style="text-decoration:none">
      <img src="seh1.png" alt="Logo SEH-NC">
      <div class="brand">
        SEH · Atelier de production
        <span>SOCIÉTÉ D'ÉTUDES HISTORIQUES · NC</span>
        <small>FONDS PATRIMONIAL CALÉDONIEN</small>
      </div>
    </a>
  </div>

  <?php if ($page_active === ''): ?>

  <!-- ACCUEIL : barre de navigation verticale (boutons empilés), inchangée -->
  <nav class="barre-nav">
    <div class="wrap">
      <?php foreach ($boutons as $cle => $b): ?>
        <a class="a-tip" href="<?php echo $b[0]; ?>"><?php echo $b[1]; ?><?php if (isset($tips[$cle])): ?><span class="bulle-tip"><?php echo $tips[$cle]; ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <?php foreach ($petits as $cle => $b): ?>
        <a class="a-tip petit" href="<?php echo $b[0]; ?>"><?php echo $b[1]; ?><?php if (isset($tips[$cle])): ?><span class="bulle-tip"><?php echo $tips[$cle]; ?></span><?php endif; ?></a>
      <?php endforeach; ?>
    </div>
  </nav>

  <script>
  // Boutons d'accueil : sélection unique + mémorisation.
  // Au clic, le bouton s'enfonce (texte creux), on mémorise le choix, puis on
  // laisse un court délai pour voir l'action avant d'ouvrir la page.
  // Au retour sur l'accueil, le dernier bouton choisi reste enfoncé.
  (function () {
    var CLE = 'seh_bouton_actif';
    var DELAI = 220; // ms
    var liens = document.querySelectorAll('.barre-nav a');

    // Restaurer l'état mémorisé
    var memorise = null;
    try { memorise = localStorage.getItem(CLE); } catch (e) {}
    if (memorise) {
      liens.forEach(function (a) {
        if (a.getAttribute('href') === memorise) { a.classList.add('enfonce'); }
      });
    }

    liens.forEach(function (a) {
      a.addEventListener('click', function (e) {
        var cible = a.getAttribute('href');
        e.preventDefault();
        liens.forEach(function (x) { x.classList.remove('enfonce'); });
        a.classList.add('enfonce');
        try { localStorage.setItem(CLE, cible); } catch (err) {}
        setTimeout(function () { window.location.href = cible; }, DELAI);
      });
    });
  })();
  </script>

  <?php if (!empty($titre_page)): ?>
  <div class="titre-atelier"><?php echo $titre_page; ?></div>
  <?php endif; ?>

  <main class="wrap">

  <?php else: ?>

  <!-- PAGES INTERNES : tiroir de navigation coulissant (hamburger tout à gauche) -->
  <input type="checkbox" id="bascule-menu" class="menu-bascule" hidden>
  <label for="bascule-menu" class="menu-ouvrir" aria-label="Ouvrir le menu" title="Menu">
    <span></span><span></span><span></span>
  </label>
  <label for="bascule-menu" class="menu-fond" aria-hidden="true"></label>

  <aside class="menu-tiroir">
    <label for="bascule-menu" class="menu-fermer" aria-label="Fermer le menu" title="Fermer">&times;</label>
    <nav class="menu-liens">
      <a href="index.php" class="menu-accueil">&#8962; Accueil</a>
      <?php foreach ($boutons as $cle => $b): ?>
        <a href="<?php echo $b[0]; ?>"<?php echo $cle === $page_active ? ' class="actif"' : ''; ?>><?php echo $b[1]; ?></a>
      <?php endforeach; ?>
      <?php foreach ($petits as $cle => $b): ?>
        <a href="<?php echo $b[0]; ?>" class="petit<?php echo $cle === $page_active ? ' actif' : ''; ?>"><?php echo $b[1]; ?></a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="wrap contenu-atelier">
    <?php if (!empty($titre_page)): ?>
    <div class="titre-atelier"><?php echo $titre_page; ?></div>
    <?php endif; ?>

  <?php endif; ?>
