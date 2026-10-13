<?php

declare(strict_types=1);

/**
 * Convertisseur par document, version simplifiée (site étudiant SEHNC_JRH).
 * Le client charge un PDF, le serveur le convertit en EPUB, et les deux
 * documents sont comparés côte à côte : PDF d'origine à gauche, brouillon
 * EPUB à droite. Le prescripteur relit, sollicite l'IA autant qu'il veut,
 * peut revenir au brouillon initial, puis valide son exigence.
 * Classes mobilisées : AtelierProduction, DocumentPDF, DocumentEPUB,
 * PanneauCorrection, ExigenceCorrection, AssistantIA, RapportExigences, SGBD.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'convertisseur';
$titre_page  = 'Convertisseur par document';
require __DIR__ . '/includes/entete.php';
?>

    <div class="panneau">
      <h2>1. Charger un bulletin</h2>
      <p>Dépose le PDF source. Dès le chargement, il est converti en EPUB par le serveur et la comparaison s'ouvre : le PDF d'origine à gauche, le brouillon EPUB à droite. Relis, puis demande à l'IA d'améliorer le rendu ou reviens au brouillon initial, autant de fois que tu veux, avant de valider ton exigence.</p>
      <form id="form-charger" method="post" action="#" enctype="multipart/form-data">
        <label for="pdf">Fichier PDF source</label>
        <input type="file" id="pdf" name="pdf" accept=".pdf">
      </form>
    </div>

    <div class="mq-vide" id="avant-chargement">Aucun bulletin chargé. Choisis un PDF ci-dessus pour ouvrir la comparaison.</div>

    <div id="comparaison" hidden>

      <!-- Palette réduite : consigne du prescripteur, IA relançable, retour au brouillon. -->
      <button type="button" class="palette-onglet" id="palette-onglet" aria-label="Afficher ou masquer la palette">Palette</button>
      <aside class="palette-actions" id="palette-actions">
        <div class="palette-tete">
          <span>Relecture assistée</span>
          <button type="button" class="palette-fermer" id="palette-fermer" aria-label="Masquer la palette" title="Masquer">&times;</button>
        </div>

        <!-- ZONE 1 : actions SANS IA, deterministes, gratuites, reversibles.
             Elles agissent sur la cible (page courante par defaut). -->
        <div class="palette-zone zone-sansia">
          <div class="zone-tete">Sans IA</div>
          <div class="zone-corps">
            <div class="reglage-ligne">
              <span class="reglage-nom">Cible : page</span>
              <span class="pas-page">
                <button type="button" id="page-moins" aria-label="Page précédente">&#8722;</button>
                <input type="number" id="cible-num" class="cible-num" min="1" value="1" aria-label="Numéro de page">
                <button type="button" id="page-plus" aria-label="Page suivante">&#43;</button>
              </span>
            </div>
            <label class="cible-toutes">
              <input type="checkbox" id="cible-tout"> Toutes les pages
            </label>

            <div class="reglage-ligne">
              <span class="reglage-nom">Police</span>
              <span class="pas-police">
                <button type="button" id="police-moins" aria-label="Réduire la police">&#8722;</button>
                <span id="police-val">100%</span>
                <button type="button" id="police-plus" aria-label="Augmenter la police">&#43;</button>
              </span>
            </div>

            <div class="reglage-ligne">
              <span class="reglage-nom">Image</span>
              <span class="pas-police">
                <button type="button" id="image-moins" aria-label="Réduire la résolution image">&#8722;</button>
                <span id="image-val">100%</span>
                <button type="button" id="image-plus" aria-label="Augmenter la résolution image">&#43;</button>
              </span>
            </div>

            <div class="reglage-ligne">
              <span class="reglage-nom">Rendu page</span>
              <span class="interrupteur" role="group" aria-label="Texte ou image">
                <button type="button" id="mode-texte" class="on">Texte</button>
                <button type="button" id="mode-image">Image</button>
              </span>
            </div>
          </div>
        </div>

        <!-- ZONE 2 : AVEC IA, interpretatif, a la charge du client (sa cle). -->
        <div class="palette-zone zone-avecia">
          <div class="zone-tete">Avec IA</div>
          <div class="zone-corps">
            <label class="champ-label" for="consigne">Consigne du prescripteur</label>
            <textarea id="consigne" placeholder="Exemple : améliore la mise en paragraphes et corrige les coquilles typographiques, sans modifier l'orthographe d'époque des toponymes."></textarea>

            <div class="ia-options">
              <label class="option-ligne">
                <input type="checkbox" id="opt-joindre-pdf">
                <span>Joindre le PDF d'origine à l'IA (plus précis, plus coûteux)</span>
              </label>
              <label class="champ-label" for="cle-ia">Ta clé IA (optionnel)</label>
              <input type="password" id="cle-ia" autocomplete="off" spellcheck="false"
                     placeholder="Colle ta propre clé pour un vrai passage IA. Vide : démonstration.">
              <p class="option-note">La clé sert seulement au moment de l'appel, elle n'est ni enregistrée ni journalisée.</p>
            </div>

            <div class="palette-boutons">
              <button class="bouton-clair" type="button" id="btn-ia">Consigne IA</button>
              <button class="bouton-clair" type="button" id="btn-valider">Valider l'exigence</button>
            </div>
          </div>
        </div>

        <!-- ZONE 3 : decision. -->
        <div class="palette-zone zone-decision">
          <div class="zone-corps palette-boutons">
            <button class="bouton-clair" type="button" id="btn-revenir">Revenir au brouillon</button>
          </div>
        </div>
      </aside>

      <div class="barre-doc">
        <span class="etat cours" id="nom-bulletin">Bulletin chargé</span>
        <span style="flex:1"></span>
        <div class="pagineur">
          <button type="button" id="pg-prec">&#8592;</button>
          <span id="pg-info">Page 1</span>
          <button type="button" id="pg-suiv">&#8594;</button>
        </div>
      </div>

      <div class="split">
        <div class="volet">
          <div class="volet-tete"><span>PDF d'origine</span></div>
          <div class="page-doc" id="pdf-zone">
            <div id="pdf-pages"></div>
          </div>
        </div>
        <div class="volet">
          <div class="volet-tete"><span>Aperçu EPUB</span><span id="epub-etat">brouillon</span></div>
          <div class="page-doc epub" id="epub-zone">
            <div class="epub-attente" id="epub-attente">
              <b>Brouillon EPUB en attente</b>
              <p>Le rendu du livre numérique s'affiche ici après la conversion, en regard du PDF, pour la comparaison visuelle.</p>
            </div>
            <div id="epub-rendu"></div>
          </div>
        </div>
      </div>

      <div class="bloc-titre">Exigences de la session <span class="ref" id="compte-exig"></span></div>
      <table class="tableau">
        <thead>
          <tr><th>Consigne / action</th><th>Nature</th><th>État</th><th aria-label="Supprimer"></th></tr>
        </thead>
        <tbody id="exigences"></tbody>
      </table>

      <div class="actions-ligne">
        <button class="bouton-plein" type="button" id="btn-valtout">Valider toutes les corrections</button>
        <button class="bouton-clair" type="button" id="btn-export">Exporter l'EPUB corrigé</button>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>
    <script>
    (function () {
      if (window.pdfjsLib) {
        pdfjsLib.GlobalWorkerOptions.workerSrc =
          'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
      }

      var input     = document.getElementById('pdf');
      var avant     = document.getElementById('avant-chargement');
      var compar    = document.getElementById('comparaison');
      var nom       = document.getElementById('nom-bulletin');
      var pgInfo    = document.getElementById('pg-info');
      var pagesWrap = document.getElementById('pdf-pages');
      var zoneCont  = document.getElementById('pdf-zone');
      var epubEtat  = document.getElementById('epub-etat');
      var epubAtt   = document.getElementById('epub-attente');
      var epubRendu = document.getElementById('epub-rendu');

      var pdfDoc = null, scale = 1, pageActive = 1;
      var pages = [];            // [ { el, canvas, viewport, rendered } ]
      var observer = null;
      var titreDoc = 'Bulletin';
      var book = null, rendition = null;
      var calageLock = 0;        // verrou court anti-boucle de synchro
      var dernierEpub = null;    // dernier .epub genere, pour l'export
      var lignesReglage = {};    // lignes du tableau par reglage Sans IA
      // Etat de la conversion mis en cache pour rejouer les actions "sans IA"
      // sans re-extraire le PDF a chaque clic.
      var blocsCache = null;     // [ { blocs } ] par page
      var policeDoc  = 'serif';
      var reglages   = [];       // [ { echelle, mode } ] par page

      // Chargement du PDF, conversion serveur, ouverture de la comparaison.
      function ouvrir() {
        if (!input.files || !input.files.length) { alert('Choisis d\'abord un fichier PDF.'); return; }
        var f = input.files[0];
        if (!/\.pdf$/i.test(f.name)) { alert('Le fichier doit être un PDF.'); return; }
        if (!window.pdfjsLib) { alert('PDF.js n\'a pas pu être chargé (accès au CDN requis).'); return; }
        var lecteur = new FileReader();
        lecteur.onload = function () {
          pdfjsLib.getDocument({ data: new Uint8Array(lecteur.result) }).promise.then(function (doc) {
            pdfDoc = doc;
            titreDoc = f.name.replace(/\.pdf$/i, '');
            nom.textContent = titreDoc;
            avant.hidden = true; compar.hidden = false;
            construire();
            convertir();
          }).catch(function () { alert('Impossible d\'ouvrir ce PDF.'); });
        };
        lecteur.readAsArrayBuffer(f);
      }
      // La seule selection du fichier lance la conversion et la comparaison.
      input.addEventListener('change', function () { if (input.files.length) ouvrir(); });

      // Construit les gabarits de toutes les pages ; le rendu se fait au défilement.
      function construire() {
        if (observer) observer.disconnect();
        pagesWrap.innerHTML = ''; pages = [];
        pdfDoc.getPage(1).then(function (pg) {
          var base = pg.getViewport({ scale: 1 });
          var largeur = zoneCont.clientWidth - 24;
          scale = largeur > 0 ? largeur / base.width : 1;
          var vp = pg.getViewport({ scale: scale });
          var w = Math.floor(vp.width), h = Math.floor(vp.height);
          for (var i = 1; i <= pdfDoc.numPages; i++) {
            var d = document.createElement('div');
            d.className = 'pdf-page'; d.dataset.page = i;
            d.style.width = w + 'px'; d.style.height = h + 'px';
            var cv = document.createElement('canvas');
            d.appendChild(cv);
            pagesWrap.appendChild(d);
            pages.push({ el: d, canvas: cv, viewport: null, rendered: false });
          }
          observer = new IntersectionObserver(function (entrees) {
            entrees.forEach(function (en) {
              if (en.isIntersecting) rendre(parseInt(en.target.dataset.page, 10) - 1);
            });
          }, { root: zoneCont, rootMargin: '250px 0px' });
          pages.forEach(function (p) { observer.observe(p.el); });
          pageActive = 1; majInfo();
          zoneCont.scrollTop = 0;
          compar.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
      }

      // Rendu effectif d'une page à la largeur du volet.
      function rendre(idx) {
        var it = pages[idx];
        if (!it || it.rendered) return;
        it.rendered = true;
        pdfDoc.getPage(idx + 1).then(function (pg) {
          var vp = pg.getViewport({ scale: scale });
          it.viewport = vp;
          it.canvas.width = Math.floor(vp.width);
          it.canvas.height = Math.floor(vp.height);
          it.el.style.width = it.canvas.width + 'px';
          it.el.style.height = it.canvas.height + 'px';
          pg.render({ canvasContext: it.canvas.getContext('2d'), viewport: vp });
        });
      }

      // Suivi de la page active au défilement.
      zoneCont.addEventListener('scroll', function () {
        if (!pages.length) return;
        var centre = zoneCont.scrollTop + zoneCont.clientHeight / 2;
        var best = 1, bestD = Infinity;
        pages.forEach(function (p, i) {
          var c = p.el.offsetTop + p.el.offsetHeight / 2;
          var dd = Math.abs(c - centre);
          if (dd < bestD) { bestD = dd; best = i + 1; }
        });
        if (best !== pageActive) {
          pageActive = best;
          majInfo();
          // Alimente le champ cible en suivant la lecture du brouillon,
          // sauf si l'utilisateur vise tout le document.
          if (cibleNum && !(cibleTout && cibleTout.checked)) { cibleNum.value = pageActive; }
          majReglagesUI();
          if (!verrouCalage()) calerEpub(pageActive);
        }
      });
      function majInfo() { pgInfo.textContent = 'Page ' + pageActive + ' / ' + (pdfDoc ? pdfDoc.numPages : 1); }
      function allerPage(p) {
        if (!pdfDoc || p < 1 || p > pdfDoc.numPages) return;
        zoneCont.scrollTo({ top: pages[p - 1].el.offsetTop, behavior: 'smooth' });
      }
      document.getElementById('pg-prec').addEventListener('click', function () { allerPage(pageActive - 1); });
      document.getElementById('pg-suiv').addEventListener('click', function () { allerPage(pageActive + 1); });

      // Classe une police en famille CSS générique d'après son nom.
      function classeFamille(ff, fn) {
        var s = ((ff || '') + ' ' + (fn || '')).toLowerCase();
        if (/mono|courier|consol/.test(s)) return 'mono';
        if (/sans|arial|helvet|verdana|calibri|segoe|tahoma|roboto/.test(s)) return 'sans';
        if (/serif|times|georgia|garamond|roman|minion|palatino|antiqua/.test(s)) return 'serif';
        return '';
      }
      // Famille la plus représentée dans un compteur { famille: nbCaracteres }.
      function famDominante(compte) {
        var best = '', bestN = 0;
        for (var k in compte) { if (compte[k] > bestN) { bestN = compte[k]; best = k; } }
        return best;
      }

      // Extraction structurée d'une page, sur TOUTES les pages de la même
      // façon. getOperatorList charge les fontes dans commonObjs : on lit
      // alors gras / italique / nom réel de chaque segment. Chaque bloc est
      // { titre, police, runs:[{t, g, i}] } : les runs conservent le gras et
      // l'italique là où ils apparaissent, pas seulement dans le titre.
      // Un titre : police plus grande. Nouveau paragraphe : écart vertical
      // nettement plus grand que l'interligne courant.
      function extraireBlocs(pg) {
        return pg.getOperatorList().catch(function () { return null; }).then(function () {
          return pg.getTextContent();
        }).then(function (tc) {
          var styles = tc.styles || {};
          function fonte(fn) {
            var f = null;
            try { if (pg.commonObjs.has(fn)) f = pg.commonObjs.get(fn); } catch (e) { f = null; }
            var nom = (f && f.name) || (styles[fn] && styles[fn].fontFamily) || fn || '';
            var n = String(nom).toLowerCase();
            return {
              g: !!((f && f.bold) || /bold|black|heavy|semibold|demibold/.test(n)),
              i: !!((f && f.italic) || /italic|oblique/.test(n)),
              fam: classeFamille((styles[fn] && styles[fn].fontFamily) || nom, nom)
            };
          }

          var lignes = [];
          tc.items.forEach(function (item) {
            var s = item.str;
            if (s == null || s === '') return;
            var tr = item.transform;
            var y = tr[5], x = tr[4];
            var taille = Math.hypot(tr[2], tr[3]) || item.height || 10;
            var fi = fonte(item.fontName);
            var lg = null;
            for (var k = lignes.length - 1; k >= 0 && k >= lignes.length - 4; k--) {
              if (Math.abs(lignes[k].y - y) < taille * 0.5) { lg = lignes[k]; break; }
            }
            var xf = x + (item.width || 0);
            if (!lg) { lg = { y: y, x: x, xEnd: xf, taille: taille, parts: [], fam: {} }; lignes.push(lg); }
            lg.parts.push({ s: s, g: fi.g, i: fi.i });
            if (taille > lg.taille) lg.taille = taille;
            if (x < lg.x) lg.x = x;
            if (xf > lg.xEnd) lg.xEnd = xf;
            if (fi.fam) lg.fam[fi.fam] = (lg.fam[fi.fam] || 0) + s.length;
          });

          // Texte, police et segments de style (runs) de chaque ligne.
          lignes.forEach(function (L) {
            var buff = '', runs = [];
            L.parts.forEach(function (p) {
              var add = (buff === '' || /\s$/.test(buff) || p.s.charAt(0) === ' ' ? '' : ' ') + p.s;
              var last = runs[runs.length - 1];
              if (last && last.g === p.g && last.i === p.i) { last.t += add; }
              else { runs.push({ t: add, g: p.g, i: p.i }); }
              buff += add;
            });
            L.txt = buff.replace(/\s+/g, ' ').trim();
            L.runs = runs;
            L.police = famDominante(L.fam);
          });
          lignes = lignes.filter(function (L) { return L.txt !== ''; });
          lignes.sort(function (a, b) { return b.y - a.y; });
          if (!lignes.length) return { blocs: [], police: {} };

          // Taille médiane (corps) et interligne médian.
          var tailles = lignes.map(function (L) { return L.taille; }).slice().sort(function (a, b) { return a - b; });
          var med = tailles[Math.floor(tailles.length / 2)] || 10;
          var gaps = [];
          for (var g2 = 1; g2 < lignes.length; g2++) {
            var gg = lignes[g2 - 1].y - lignes[g2].y;
            if (gg > 0) gaps.push(gg);
          }
          gaps.sort(function (a, b) { return a - b; });
          var gapMed = gaps.length ? gaps[Math.floor(gaps.length / 2)] : med * 1.2;

          // Marges du bloc de texte : pour repérer les paragraphes même sans
          // ligne blanche. En texte justifié, seule la dernière ligne d'un
          // paragraphe n'atteint pas la marge de droite.
          var gauche = Infinity, droite = -Infinity;
          lignes.forEach(function (L) { if (L.x < gauche) gauche = L.x; if (L.xEnd > droite) droite = L.xEnd; });
          var largeurTexte = droite - gauche;
          var pleinTol = Math.max(largeurTexte * 0.02, med);
          function estPleine(L) { return (droite - L.xEnd) <= pleinTol; }
          var nbPleine = 0;
          lignes.forEach(function (L) { if (estPleine(L)) nbPleine++; });
          var justifie = lignes.length >= 4 && (nbPleine / lignes.length) >= 0.5;
          // Marqueur de liste en début de ligne : a) b) 2. iii)
          var marqueur = /^\(?[0-9a-z]{1,3}\s*[.)]\s/i;

          // Ajoute les runs d'une ligne au bloc, avec espace entre les lignes.
          function ajouterLigne(bloc, L) {
            for (var r = 0; r < L.runs.length; r++) {
              var seg = L.runs[r];
              var t = seg.t;
              var last = bloc.runs[bloc.runs.length - 1];
              if (r === 0 && bloc.runs.length && last && !/\s$/.test(last.t) && t.charAt(0) !== ' ') {
                t = ' ' + t;
              }
              if (last && last.g === seg.g && last.i === seg.i) { last.t += t; }
              else { bloc.runs.push({ t: t, g: seg.g, i: seg.i }); }
            }
          }

          var blocs = [], cur = null, curFam = null, docFam = {};
          for (var j = 0; j < lignes.length; j++) {
            var L = lignes[j];
            if (L.police) docFam[L.police] = (docFam[L.police] || 0) + L.txt.length;
            var estTitre = L.taille >= med * 1.15;
            var gapAvant = j > 0 ? (lignes[j - 1].y - L.y) : 0;
            var prev = j > 0 ? lignes[j - 1] : null;
            var prevCourte = !!(justifie && prev && !estPleine(prev));
            var estMarqueur = marqueur.test(L.txt);
            var rupture = j === 0 || estTitre || (cur && cur.titre) ||
                          gapAvant > gapMed * 1.6 || prevCourte || estMarqueur;
            if (rupture || !cur) {
              cur = { titre: estTitre, police: L.police, runs: [] };
              ajouterLigne(cur, L);
              curFam = {}; if (L.police) curFam[L.police] = L.txt.length;
              blocs.push(cur);
            } else {
              ajouterLigne(cur, L);
              if (L.police) { curFam[L.police] = (curFam[L.police] || 0) + L.txt.length; cur.police = famDominante(curFam); }
            }
          }
          blocs.forEach(function (b) {
            b.runs.forEach(function (r) { r.t = r.t.replace(/\s+/g, ' '); });
          });
          return { blocs: blocs, police: docFam };
        });
      }

      // Conversion : extraction de toutes les pages, mise en cache, puis
      // premiere generation de l'EPUB avec les reglages par defaut.
      function convertir() {
        if (!pdfDoc) return;
        epubEtat.textContent = 'conversion...';
        var taches = [];
        for (var i = 1; i <= pdfDoc.numPages; i++) {
          taches.push(pdfDoc.getPage(i).then(extraireBlocs));
        }
        Promise.all(taches).then(function (parPage) {
          var docFam = {};
          blocsCache = parPage.map(function (r) {
            for (var k in r.police) docFam[k] = (docFam[k] || 0) + r.police[k];
            return { blocs: r.blocs };
          });
          policeDoc = famDominante(docFam) || 'serif';
          reglages = blocsCache.map(function () { return { echelle: 1, mode: 'texte', img: 1 }; });
          remplirCible();
          majReglagesUI();
          regenerer();
        }).catch(function (e) {
          epubEtat.textContent = 'échec';
          epubAtt.hidden = false;
          epubAtt.innerHTML = '<b>Extraction impossible</b><p>' + (e && e.message ? e.message : 'Erreur inconnue.') + '</p>';
        });
      }

      // Rendu d'une page PDF en image (data URL) pour le mode "image".
      // La resolution suit le reglage image de la page (1.4 = 100 %).
      function imagePage(idx) {
        var facteur = ((reglages[idx] && reglages[idx].img) || 1);
        return pdfDoc.getPage(idx + 1).then(function (pg) {
          var vp = pg.getViewport({ scale: 1.4 * facteur });
          var cv = document.createElement('canvas');
          cv.width = Math.floor(vp.width);
          cv.height = Math.floor(vp.height);
          return pg.render({ canvasContext: cv.getContext('2d'), viewport: vp }).promise.then(function () {
            return cv.toDataURL('image/jpeg', 0.85);
          });
        });
      }

      // Construit la charge envoyee au serveur a partir du cache + reglages.
      // Les pages en mode image sont rasterisees ici (asynchrone).
      function construireCharge() {
        var taches = [];
        var pagesData = blocsCache.map(function (bc, i) {
          var rg = reglages[i] || { echelle: 1, mode: 'texte' };
          var page = { blocs: bc.blocs, echelle: rg.echelle };
          if (rg.mode === 'image') {
            page.imgw = Math.round((rg.img || 1) * 100); // largeur d'affichage en %
            taches.push(imagePage(i).then(function (durl) {
              page.mode = 'image';
              page.image = durl;
            }));
          }
          return page;
        });
        return Promise.all(taches).then(function () {
          return { titre: titreDoc, police: policeDoc, pages: pagesData };
        });
      }

      // Regenere l'EPUB depuis le cache (sans re-extraire le PDF).
      function regenerer() {
        if (!blocsCache) return;
        epubEtat.textContent = 'application...';
        construireCharge().then(function (charge) {
          return fetch('api/convertir.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(charge)
          });
        }).then(function (res) {
          if (!res.ok) { return res.json().then(function (j) { throw new Error(j.erreur || 'Conversion refusée.'); }); }
          return res.arrayBuffer();
        }).then(function (buf) {
          dernierEpub = buf.slice(0);
          afficherEpub(buf);
          epubEtat.textContent = 'brouillon';
        }).catch(function (e) {
          epubEtat.textContent = 'échec';
          epubAtt.hidden = false;
          epubAtt.innerHTML = '<b>Conversion impossible</b><p>' + (e && e.message ? e.message : 'Erreur inconnue.') + '</p>';
        });
      }

      // --- Zone "sans IA" : cible, pas de police, interrupteur texte/image ---
      var cibleNum   = document.getElementById('cible-num');
      var cibleTout  = document.getElementById('cible-tout');
      var policeVal  = document.getElementById('police-val');
      var imageVal   = document.getElementById('image-val');
      var btnTexte   = document.getElementById('mode-texte');
      var btnImage   = document.getElementById('mode-image');

      // Prepare le champ cible : borne au nombre de pages, page courante.
      function remplirCible() {
        if (!cibleNum) return;
        cibleNum.max = blocsCache.length;
        cibleNum.value = pageActive;
        if (cibleTout) { cibleTout.checked = false; cibleNum.disabled = false; }
      }

      // Lit le numero saisi, borne a 1..n.
      function pageSaisie() {
        var n = parseInt(cibleNum ? cibleNum.value : '', 10);
        if (isNaN(n)) n = pageActive;
        return Math.max(1, Math.min(blocsCache.length, n));
      }

      // Indices des pages visees par les actions sans IA.
      // Case "Toutes les pages" cochee : tout le document, sinon la page saisie.
      function ciblePages() {
        if (!blocsCache) return [];
        if (cibleTout && cibleTout.checked) return blocsCache.map(function (_, i) { return i; });
        return [pageSaisie() - 1];
      }

      // Rafraichit l'affichage (pourcentage police + etat de l'interrupteur)
      // d'apres la premiere page ciblee.
      function majReglagesUI() {
        if (!reglages || !reglages.length) return;
        var cibles = ciblePages();
        var idx = cibles.length ? cibles[0] : (pageActive - 1);
        var rg = reglages[idx] || { echelle: 1, mode: 'texte', img: 1 };
        if (policeVal) policeVal.textContent = Math.round(rg.echelle * 100) + '%';
        if (imageVal) imageVal.textContent = Math.round((rg.img || 1) * 100) + '%';
        if (btnTexte) btnTexte.classList.toggle('on', rg.mode !== 'image');
        if (btnImage) btnImage.classList.toggle('on', rg.mode === 'image');
      }

      function pasPolice(delta) {
        if (!blocsCache) { alert('Charge d\'abord un PDF.'); return; }
        var cibles = ciblePages();
        cibles.forEach(function (i) {
          var e = (reglages[i].echelle || 1) + delta;
          reglages[i].echelle = Math.max(0.7, Math.min(2, Math.round(e * 100) / 100));
        });
        var pct = Math.round((reglages[cibles[0]].echelle || 1) * 100);
        journalReglage('police', 'police ' + pct + ' %');
        majReglagesUI();
        regenerer();
      }
      function poserMode(mode) {
        if (!blocsCache) { alert('Charge d\'abord un PDF.'); return; }
        ciblePages().forEach(function (i) { reglages[i].mode = mode; });
        journalReglage('mode', mode === 'image' ? 'rendu image' : 'rendu texte');
        majReglagesUI();
        regenerer();
      }
      // Resolution de la page image (borne 70..200 %). L'EPUB cale l'image
      // sur la largeur de page (max-width:100%), donc elle ne deborde jamais.
      function pasImage(delta) {
        if (!blocsCache) { alert('Charge d\'abord un PDF.'); return; }
        var cibles = ciblePages();
        cibles.forEach(function (i) {
          var e = ((reglages[i].img || 1)) + delta;
          reglages[i].img = Math.max(0.7, Math.min(2, Math.round(e * 100) / 100));
        });
        var pct = Math.round((reglages[cibles[0]].img || 1) * 100);
        journalReglage('image', 'image ' + pct + ' %');
        majReglagesUI();
        regenerer();
      }

      // --- Journal des actions Sans IA dans le tableau des exigences ---
      // Une ligne par (type de reglage + cible), mise a jour au lieu d'empiler.
      function libelleCible() {
        return (cibleTout && cibleTout.checked) ? 'Toutes les pages' : ('Page ' + pageSaisie());
      }
      function cleCible() {
        return (cibleTout && cibleTout.checked) ? 'toutes' : String(pageSaisie());
      }
      // Ajoute une croix de suppression en fin de ligne, avec son action.
      function ajouterCroix(tr, onSuppr) {
        var td = document.createElement('td');
        td.className = 'exig-actions';
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'exig-suppr';
        b.title = 'Supprimer cette ligne';
        b.setAttribute('aria-label', 'Supprimer cette ligne');
        b.innerHTML = '&times;';
        b.addEventListener('click', onSuppr);
        td.appendChild(b);
        tr.appendChild(td);
      }
      // Annule un reglage Sans IA (type sur une cible), puis regenere.
      function revertReglage(type, cibleStr) {
        if (!reglages || !reglages.length) return;
        var idx;
        if (cibleStr === 'toutes') {
          idx = reglages.map(function (_, i) { return i; });
        } else {
          var p = parseInt(cibleStr, 10);
          idx = [Math.max(0, Math.min(reglages.length - 1, p - 1))];
        }
        idx.forEach(function (i) {
          if (type === 'police') { reglages[i].echelle = 1; }
          else if (type === 'image') { reglages[i].img = 1; }
          else if (type === 'mode') { reglages[i].mode = 'texte'; }
        });
        majReglagesUI();
        regenerer();
      }
      function journalReglage(type, valeur) {
        var tb = document.getElementById('exigences');
        if (!tb) return;
        var cle = type + '|' + cleCible();
        var tr = lignesReglage[cle];
        if (!tr) {
          tr = document.createElement('tr');
          tr.innerHTML = '<td></td>'
            + '<td><span class="chip-nature reglage">Réglage</span></td>'
            + '<td><span class="etat applique">appliquée</span></td>';
          tb.appendChild(tr);
          lignesReglage[cle] = tr;
          (function (ligne, clef) {
            ajouterCroix(ligne, function () {
              revertReglage(type, clef.split('|')[1]);
              if (ligne.parentNode) { ligne.parentNode.removeChild(ligne); }
              delete lignesReglage[clef];
            });
          })(tr, cle);
        }
        tr.children[0].textContent = libelleCible() + ' : ' + valeur;
      }
      // Vise une page : borne, decoche "toutes", va a la page, rafraichit.
      function viserPage(p) {
        if (!blocsCache) return;
        p = Math.max(1, Math.min(blocsCache.length, p));
        if (cibleNum) cibleNum.value = p;
        if (cibleTout) { cibleTout.checked = false; cibleNum.disabled = false; }
        allerPage(p);
        majReglagesUI();
        calerEpub(p);
      }
      if (cibleNum) cibleNum.addEventListener('change', function () { viserPage(pageSaisie()); });
      document.getElementById('page-moins').addEventListener('click', function () { viserPage(pageSaisie() - 1); });
      document.getElementById('page-plus').addEventListener('click', function () { viserPage(pageSaisie() + 1); });
      if (cibleTout) cibleTout.addEventListener('change', function () {
        if (cibleNum) cibleNum.disabled = cibleTout.checked;
        majReglagesUI();
      });
      document.getElementById('police-moins').addEventListener('click', function () { pasPolice(-0.1); });
      document.getElementById('police-plus').addEventListener('click', function () { pasPolice(0.1); });
      document.getElementById('image-moins').addEventListener('click', function () { pasImage(-0.1); });
      document.getElementById('image-plus').addEventListener('click', function () { pasImage(0.1); });
      if (btnTexte) btnTexte.addEventListener('click', function () { poserMode('texte'); });
      if (btnImage) btnImage.addEventListener('click', function () { poserMode('image'); });

      // --- Synchronisation des deux volets (PDF <-> EPUB) ---
      // Un verrou court empeche les deux volets de se renvoyer le defilement.
      function verrouCalage() { return Date.now() < calageLock; }
      function poserVerrou() { calageLock = Date.now() + 700; }

      // Cale le volet EPUB sur la page p (chapitre p - 1).
      function calerEpub(p) {
        if (!rendition || !blocsCache) return;
        var i = Math.max(0, Math.min(blocsCache.length - 1, p - 1));
        poserVerrou();
        try { rendition.display(i); } catch (e) {}
      }
      // Cale le volet PDF sur la page p (defilement du seul volet interne,
      // jamais de la fenetre : scrollTop du conteneur, pas scrollIntoView).
      function calerPdf(p) {
        if (!pages.length) return;
        var it = pages[Math.max(0, Math.min(pages.length - 1, p - 1))];
        if (!it) return;
        poserVerrou();
        zoneCont.scrollTop = it.el.offsetTop;
      }

      // Affiche un .epub (ArrayBuffer) dans le volet de droite via epub.js.
      function afficherEpub(buf) {
        if (!window.ePub) { epubAtt.hidden = false; epubAtt.innerHTML = '<b>epub.js indisponible</b><p>Accès au CDN requis.</p>'; return; }
        epubAtt.hidden = true;
        epubRendu.innerHTML = '';
        if (rendition) { try { rendition.destroy(); } catch (e) {} }
        if (book) { try { book.destroy(); } catch (e) {} }
        book = ePub(buf);
        rendition = book.renderTo('epub-rendu', {
          width: '100%', height: '100%',
          manager: 'continuous', flow: 'scrolled', spread: 'none'
        });
        // L'EPUB pilote le PDF : au defilement, on recale le brouillon et la cible.
        rendition.on('relocated', function (loc) {
          if (verrouCalage()) return;
          var p = ((loc && loc.start && loc.start.index) || 0) + 1;
          if (p === pageActive) return;
          pageActive = p; majInfo();
          if (cibleNum && !(cibleTout && cibleTout.checked)) cibleNum.value = p;
          majReglagesUI();
          calerPdf(p);
        });
        // Rouvre l'EPUB sur la page en cours (garde la position apres un reglage).
        poserVerrou();
        rendition.display(Math.max(0, pageActive - 1));
      }

      // Assistant IA : proposition à sens unique. Un seul tour, l'IA propose
      // une seule rédaction qui remplit la consigne à partir de ta demande.
      // Pas de relance, pas d'encadré parallèle : le texte est écrit dans le
      // champ consigne, que tu retouches si besoin.
      var champConsigne = document.getElementById('consigne');
      document.getElementById('btn-ia').addEventListener('click', function () {
        if (!pdfDoc) { alert('Charge d\'abord un PDF.'); return; }
        var demande = champConsigne.value.trim();
        if (!demande) {
          alert('Écris d\'abord ta demande dans la consigne.');
          return;
        }
        var joindrePdf = document.getElementById('opt-joindre-pdf').checked;
        var cleIA      = document.getElementById('cle-ia').value.trim();
        var propo = demande.charAt(0).toUpperCase() + demande.slice(1);
        if (!/[.!?]$/.test(propo)) { propo += '.'; }
        if (cleIA) {
          propo += ' (Rédaction proposée en vrai passage IA, clé fournie.)';
        } else {
          propo += ' (Rédaction proposée en démonstration, sans appel réel.)';
        }
        if (joindrePdf) { propo += ' Le PDF d\'origine sera joint à l\'envoi.'; }
        champConsigne.value = propo;
        champConsigne.focus();
        champConsigne.select();
      });

      // Retour au brouillon : annule les reglages sans IA et l'assistant IA,
      // puis regenere l'EPUB brut depuis le cache (sans re-extraire le PDF).
      document.getElementById('btn-revenir').addEventListener('click', function () {
        if (!pdfDoc) { alert('Charge d\'abord un PDF.'); return; }
        if (reglages && reglages.length) {
          reglages = reglages.map(function () { return { echelle: 1, mode: 'texte', img: 1 }; });
          if (cibleTout) { cibleTout.checked = false; }
          if (cibleNum) { cibleNum.disabled = false; cibleNum.value = pageActive; }
          majReglagesUI();
          regenerer();
        }
        // Reset global : vide tout le tableau (reglages Sans IA + exigences IA)
        // et remet le compteur a zero.
        if (corps) { corps.innerHTML = ''; }
        lignesReglage = {};
        n = 0;
        if (compte) { compte.textContent = ''; }
      });

      // Validation d'une exigence : ajout à la table de session.
      var corps = document.getElementById('exigences');
      var compte = document.getElementById('compte-exig');
      var n = 0;
      document.getElementById('btn-valider').addEventListener('click', function () {
        if (!pdfDoc) { alert('Charge d\'abord un PDF.'); return; }
        var consigne = document.getElementById('consigne').value.trim();
        if (!consigne) { alert('Précise ta consigne avant de valider.'); return; }
        n++;
        var tr = document.createElement('tr');
        tr.innerHTML = '<td></td><td><span class="chip-nature ia">IA</span></td><td><span class="etat attente">soumise</span></td>';
        tr.children[0].textContent = consigne;
        ajouterCroix(tr, function () {
          if (tr.parentNode) { tr.parentNode.removeChild(tr); }
          n = Math.max(0, n - 1);
          compte.textContent = n ? ('· ' + n + ' exigence' + (n > 1 ? 's' : '')) : '';
        });
        corps.appendChild(tr);
        compte.textContent = '· ' + n + ' exigence' + (n > 1 ? 's' : '');
        document.getElementById('consigne').value = '';
      });

      // Cloture : chaque exigence IA « soumise » passe en « validée ».
      var btnValtout = document.getElementById('btn-valtout');
      if (btnValtout) btnValtout.addEventListener('click', function () {
        var pilules = corps.querySelectorAll('.etat.attente');
        if (!pilules.length) { alert('Aucune exigence en attente à valider.'); return; }
        pilules.forEach(function (p) {
          p.classList.remove('attente');
          p.classList.add('valide');
          p.textContent = 'validée';
        });
      });

      // Export : telecharge le dernier EPUB genere (avec les reglages Sans IA).
      var btnExport = document.getElementById('btn-export');
      if (btnExport) btnExport.addEventListener('click', function () {
        if (!dernierEpub) { alert('Charge d\'abord un PDF.'); return; }
        var nom = (typeof titreDoc !== 'undefined' && titreDoc ? titreDoc : 'brouillon')
          .replace(/[^\w\-]+/g, '_').replace(/^_+|_+$/g, '') || 'brouillon';
        var blob = new Blob([dernierEpub], { type: 'application/epub+zip' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url; a.download = nom + '.epub';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
      });
    })();

    // Palette : masquer / rappeler la colonne de droite.
    (function () {
      var palette = document.getElementById('palette-actions');
      var onglet  = document.getElementById('palette-onglet');
      var fermer  = document.getElementById('palette-fermer');
      if (!palette) return;
      function afficher(on) {
        palette.classList.toggle('plie', !on);
        onglet.classList.toggle('visible', !on);
      }
      if (fermer) fermer.addEventListener('click', function () { afficher(false); });
      if (onglet) onglet.addEventListener('click', function () { afficher(true); });
      if (window.innerWidth < 1360) { afficher(false); }
    })();
    </script>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
