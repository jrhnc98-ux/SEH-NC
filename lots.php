<?php

declare(strict_types=1);

/**
 * Convertisseur par lots : traitement de masse du fonds.
 * La même chaîne que le convertisseur par document, appliquée en boucle
 * sur un ensemble de PDF déposés.
 * Classes mobilisées : AtelierProduction (en boucle), DocumentPDF,
 * DocumentEPUB, SGBD.
 */

require_once __DIR__ . '/includes/chargeur.php';

$page_active = 'lots';
$titre_page  = 'Convertisseur par lots';
require __DIR__ . '/includes/entete.php';
?>

    <p class="page-intro">Traitement de masse : dépôt de plusieurs bulletins d'un coup, file d'attente et suivi de progression. Chaque PDF est converti en EPUB de base et déposé dans le dossier d'attente. La relecture fine se fait ensuite bulletin par bulletin, dans le convertisseur par document.</p>

    <form method="post" action="#" enctype="multipart/form-data">
      <div class="depot" id="zone-depot">
        <b>Déposer des fichiers PDF ici</b>
        <span>ou glisser un dossier entier, ou cliquer pour parcourir &middot; plusieurs fichiers acceptés</span><br>
        <small>PDF &middot; 50 Mo max par fichier</small>
        <div class="depot-boutons">
          <button type="button" class="bouton-clair" id="btn-fichiers">Choisir des fichiers</button>
          <button type="button" class="bouton-clair" id="btn-dossier">Choisir un dossier</button>
        </div>
        <input type="file" id="pdfs" name="pdfs[]" accept=".pdf" multiple hidden>
        <input type="file" id="pdfdir" webkitdirectory multiple hidden>
      </div>

      <div class="bloc-titre">File d'attente <span class="ref" id="compte-lots"></span></div>
      <table class="tableau">
        <thead>
          <tr><th>Fichier</th><th>Taille</th><th>Progression</th><th>État</th></tr>
        </thead>
        <tbody id="file-lots"></tbody>
      </table>

      <div class="actions-ligne">
        <button class="bouton-plein" type="submit">Lancer la conversion</button>
        <button class="bouton-clair" type="reset">Vider la file</button>
      </div>
    </form>

    <script>
    (function () {
      var zone   = document.getElementById('zone-depot');
      var inF    = document.getElementById('pdfs');
      var inD    = document.getElementById('pdfdir');
      var tbody  = document.getElementById('file-lots');
      var compte = document.getElementById('compte-lots');
      var MAX    = 50 * 1024 * 1024;
      var items  = [];

      function fmt(o) { return (o / 1048576).toFixed(o >= 1048576 ? 1 : 2) + ' Mo'; }
      function isPdf(f) { return /\.pdf$/i.test(f.name); }
      function existe(f) {
        return items.some(function (x) { return x.name === f.name && x.size === f.size; });
      }

      function add(files) {
        for (var i = 0; i < files.length; i++) {
          if (isPdf(files[i]) && !existe(files[i])) { items.push(files[i]); }
        }
        render();
      }

      function render() {
        tbody.innerHTML = '';
        if (!items.length) {
          compte.textContent = '';
          tbody.innerHTML = '<tr><td colspan="4" class="ref">Aucun fichier PDF sélectionné pour le moment.</td></tr>';
          return;
        }
        compte.textContent = '· ' + items.length + ' fichier' + (items.length > 1 ? 's' : '');
        items.forEach(function (f) {
          var trop = f.size > MAX;
          var tr = document.createElement('tr');
          tr.innerHTML =
            '<td class="nom"></td>' +
            '<td class="ref">' + fmt(f.size) + '</td>' +
            '<td><div class="barre-prog"><i style="width:0%"></i></div></td>' +
            '<td><span class="etat attente">' + (trop ? 'trop lourd' : 'en attente') + '</span></td>';
          tr.querySelector('.nom').textContent = f.name;
          tbody.appendChild(tr);
        });
      }

      document.getElementById('btn-fichiers').onclick = function () { inF.click(); };
      document.getElementById('btn-dossier').onclick  = function () { inD.click(); };
      inF.onchange = function () { add(inF.files); };
      inD.onchange = function () { add(inD.files); };
      zone.addEventListener('click', function (e) {
        if (e.target.tagName !== 'BUTTON') { inF.click(); }
      });

      ['dragenter', 'dragover'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('survol'); });
      });
      ['dragleave', 'drop'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('survol'); });
      });

      function parcourir(entry) {
        return new Promise(function (res) {
          if (entry.isFile) {
            entry.file(function (f) { res([f]); });
          } else if (entry.isDirectory) {
            var reader = entry.createReader();
            var tout = [];
            var lire = function () {
              reader.readEntries(function (es) {
                if (!es.length) {
                  Promise.all(tout.map(parcourir)).then(function (listes) {
                    res([].concat.apply([], listes));
                  });
                } else { tout = tout.concat(es); lire(); }
              });
            };
            lire();
          } else { res([]); }
        });
      }

      zone.addEventListener('drop', function (e) {
        var its = e.dataTransfer.items;
        if (its && its.length && its[0].webkitGetAsEntry) {
          var racines = [];
          for (var i = 0; i < its.length; i++) {
            var en = its[i].webkitGetAsEntry();
            if (en) { racines.push(en); }
          }
          Promise.all(racines.map(parcourir)).then(function (listes) {
            add([].concat.apply([], listes));
          });
        } else {
          add(e.dataTransfer.files);
        }
      });

      render();
    })();
    </script>

<?php require __DIR__ . '/includes/piedpage.php'; ?>
