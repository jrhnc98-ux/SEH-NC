<?php

declare(strict_types=1);

/**
 * Conversion PDF vers EPUB, version simplifiee (site etudiant SEHNC_JRH).
 * Le navigateur (PDF.js) extrait le texte page par page et l'envoie ici en JSON.
 * Ce script fabrique un vrai fichier .epub (ZipArchive) et le renvoie tel quel.
 * L'apercu est ensuite affiche par epub.js dans le volet de droite.
 *
 * Entree attendue (corps de la requete, JSON) :
 *   { "titre": "Nom du bulletin", "pages": [ { "paras": ["...", "..."] }, ... ] }
 */

function repondreErreur(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erreur' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    repondreErreur(405, 'Methode non autorisee, utilise POST.');
}
if (!class_exists('ZipArchive')) {
    repondreErreur(500, "L'extension ZipArchive n'est pas active sur ce serveur.");
}

$brut = file_get_contents('php://input');
$data = json_decode($brut ?: '', true);
if (!is_array($data) || empty($data['pages']) || !is_array($data['pages'])) {
    repondreErreur(400, 'Donnees de conversion absentes ou invalides.');
}

$titre = isset($data['titre']) ? trim((string) $data['titre']) : '';
if ($titre === '') {
    $titre = 'Bulletin';
}
$pages = $data['pages'];

// Familles de police imitees d'apres le PDF (fond neutre, pas la charte du site).
$familles = [
    'serif' => "Georgia, 'Times New Roman', serif",
    'sans'  => "Arial, Helvetica, sans-serif",
    'mono'  => "'Courier New', Consolas, monospace",
];
$policeDoc = (isset($data['police']) && isset($familles[$data['police']])) ? $data['police'] : 'serif';
$fontBody  = $familles[$policeDoc];

function xmlEchappe(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function uuidV4(): string
{
    $o = random_bytes(16);
    $o[6] = chr((ord($o[6]) & 0x0f) | 0x40);
    $o[8] = chr((ord($o[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($o), 4));
}

// Un chapitre XHTML par page du PDF.
$chapitres = [];
$images    = []; // Pages rendues en image : [ id, href, media, data ].
foreach ($pages as $i => $pg) {
    $num = $i + 1;

    // Echelle de police (action "sans IA"), bornee cote serveur.
    $echelle = isset($pg['echelle']) ? (float) $pg['echelle'] : 1.0;
    if ($echelle < 0.7) { $echelle = 0.7; }
    if ($echelle > 2.0) { $echelle = 2.0; }
    $styleBody = '';
    if (abs($echelle - 1.0) > 0.001) {
        $ech = rtrim(rtrim(number_format($echelle, 2, '.', ''), '0'), '.');
        $styleBody = ' style="font-size: ' . $ech . 'em"';
    }

    $corps = '';

    // Mode image : la page est gardee telle quelle (bitmap), pas de texte.
    $estImage = (isset($pg['mode']) && $pg['mode'] === 'image'
        && isset($pg['image']) && is_string($pg['image']));
    if ($estImage
        && preg_match('#^data:(image/(?:jpeg|png));base64,(.+)$#s', $pg['image'], $m)) {
        $media  = $m[1];
        $donnee = base64_decode($m[2], true);
        if ($donnee !== false && $donnee !== '') {
            $ext  = ($media === 'image/png') ? 'png' : 'jpg';
            $href = 'img/page' . $num . '.' . $ext;
            $images[] = ['id' => 'img' . $num, 'href' => $href, 'media' => $media, 'data' => $donnee];
            // Largeur d'affichage (100 = calee sur la page). Au-dela, l'image
            // deborde et la liseuse ajoute un defilement horizontal.
            $larg = isset($pg['imgw']) ? (int) $pg['imgw'] : 100;
            if ($larg < 50)  { $larg = 50; }
            if ($larg > 200) { $larg = 200; }
            $style = ($larg === 100) ? '' : " style=\"max-width:none;width:{$larg}%\"";
            $corps = "    <p class=\"page-image\"><img src=\"{$href}\" alt=\"Page {$num}\"{$style}/></p>\n";
        }
    }

    if ($corps === '') {
        // Nouveau format : blocs [ { titre: bool, texte: string } ].
        // Ancien format (compat) : paras [ "..." ].
        $blocs = (isset($pg['blocs']) && is_array($pg['blocs'])) ? $pg['blocs'] : null;
        if ($blocs === null) {
            $paras = (isset($pg['paras']) && is_array($pg['paras'])) ? $pg['paras'] : [];
            $blocs = array_map(static function ($p) {
                return ['titre' => false, 'texte' => (string) $p];
            }, $paras);
        }
        foreach ($blocs as $b) {
            if (!is_array($b)) {
                $b = ['titre' => false, 'texte' => (string) $b];
            }
            // Contenu interne : soit des runs {t,g,i} (gras / italique conserves),
            // soit l'ancien format texte simple.
            $inner = '';
            if (isset($b['runs']) && is_array($b['runs'])) {
                foreach ($b['runs'] as $run) {
                    if (!is_array($run)) {
                        continue;
                    }
                    $t = (string) ($run['t'] ?? '');
                    if ($t === '') {
                        continue;
                    }
                    $frag = xmlEchappe($t);
                    if (!empty($run['g'])) {
                        $frag = '<strong>' . $frag . '</strong>';
                    }
                    if (!empty($run['i'])) {
                        $frag = '<em>' . $frag . '</em>';
                    }
                    $inner .= $frag;
                }
            } else {
                $inner = xmlEchappe(trim((string) ($b['texte'] ?? '')));
            }
            $inner = trim($inner);
            if ($inner === '') {
                continue;
            }
            $estTitre = !empty($b['titre']);
            $balise   = $estTitre ? 'h3' : 'p';
            $pol      = isset($b['police']) ? (string) $b['police'] : '';
            $cls      = ($pol !== '' && isset($familles[$pol]) && $pol !== $policeDoc)
                ? ' class="f-' . $pol . '"' : '';
            $corps   .= "    <{$balise}{$cls}>" . $inner . "</{$balise}>\n";
        }
    }

    if ($corps === '') {
        $corps = "    <p class=\"vide\">[Page sans texte extrait, sans doute une image.]</p>\n";
    }
    $xhtml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
        . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"fr\" lang=\"fr\">\n"
        . "<head>\n"
        . "  <meta charset=\"UTF-8\"/>\n"
        . "  <title>Page {$num}</title>\n"
        . "  <link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\"/>\n"
        . "</head>\n"
        . "<body{$styleBody}>\n"
        . "  <section>\n"
        . "    <h2>Page {$num}</h2>\n"
        . $corps
        . "  </section>\n"
        . "</body>\n"
        . "</html>\n";
    $chapitres[] = [
        'id'    => 'page' . $num,
        'href'  => 'page' . $num . '.xhtml',
        'titre' => 'Page ' . $num,
        'xhtml' => $xhtml,
    ];
}

// Feuille de style du livre : lecture sobre, proche de la charte parchemin.
$css = "body { background: #ffffff; color: #111111; font-family: {$fontBody}; "
    . "line-height: 1.5; margin: 1.2em 1.4em; }\n"
    . "h2 { font-size: .78em; letter-spacing: .08em; text-transform: uppercase; "
    . "color: #888888; border-bottom: 1px solid #dddddd; "
    . "padding-bottom: .3em; margin: 1.8em 0 .9em; font-weight: normal; }\n"
    . "h3 { font-size: 1.15em; font-weight: bold; color: #111111; "
    . "margin: 1.2em 0 .5em; }\n"
    . "p { margin: 0 0 .7em; text-align: justify; }\n"
    . "p.vide { color: #999999; font-style: italic; }\n"
    . "p.page-image { margin: 0; text-align: center; }\n"
    . "p.page-image img { max-width: 100%; height: auto; }\n"
    . ".f-serif { font-family: Georgia, 'Times New Roman', serif; }\n"
    . ".f-sans { font-family: Arial, Helvetica, sans-serif; }\n"
    . ".f-mono { font-family: 'Courier New', Consolas, monospace; }\n";

$container = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
    . "<container version=\"1.0\" xmlns=\"urn:oasis:names:tc:opendocument:xmlns:container\">\n"
    . "  <rootfiles>\n"
    . "    <rootfile full-path=\"OEBPS/content.opf\" media-type=\"application/oebps-package+xml\"/>\n"
    . "  </rootfiles>\n"
    . "</container>\n";

// Sommaire de navigation (EPUB 3).
$navItems = '';
foreach ($chapitres as $c) {
    $navItems .= '      <li><a href="' . $c['href'] . '">' . xmlEchappe($c['titre']) . "</a></li>\n";
}
$nav = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
    . "<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:epub=\"http://www.idpf.org/2007/ops\" xml:lang=\"fr\" lang=\"fr\">\n"
    . "<head>\n  <meta charset=\"UTF-8\"/>\n  <title>Sommaire</title>\n</head>\n"
    . "<body>\n  <nav epub:type=\"toc\" id=\"toc\">\n    <h1>Sommaire</h1>\n    <ol>\n"
    . $navItems
    . "    </ol>\n  </nav>\n</body>\n</html>\n";

// Paquet OPF : metadonnees, manifeste, colonne de lecture.
$uuid     = 'urn:uuid:' . uuidV4();
$modifie  = gmdate('Y-m-d\TH:i:s\Z');
$manifest = "    <item id=\"nav\" href=\"nav.xhtml\" media-type=\"application/xhtml+xml\" properties=\"nav\"/>\n"
    . "    <item id=\"css\" href=\"style.css\" media-type=\"text/css\"/>\n";
$spine = '';
foreach ($chapitres as $c) {
    $manifest .= '    <item id="' . $c['id'] . '" href="' . $c['href']
        . "\" media-type=\"application/xhtml+xml\"/>\n";
    $spine .= '    <itemref idref="' . $c['id'] . "\"/>\n";
}
foreach ($images as $img) {
    $manifest .= '    <item id="' . $img['id'] . '" href="' . $img['href']
        . '" media-type="' . $img['media'] . "\"/>\n";
}
$opf = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
    . "<package xmlns=\"http://www.idpf.org/2007/opf\" version=\"3.0\" unique-identifier=\"bookid\" xml:lang=\"fr\">\n"
    . "  <metadata xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\n"
    . "    <dc:identifier id=\"bookid\">" . xmlEchappe($uuid) . "</dc:identifier>\n"
    . "    <dc:title>" . xmlEchappe($titre) . "</dc:title>\n"
    . "    <dc:language>fr</dc:language>\n"
    . "    <dc:creator>Atelier SEH-NC</dc:creator>\n"
    . "    <meta property=\"dcterms:modified\">" . $modifie . "</meta>\n"
    . "  </metadata>\n"
    . "  <manifest>\n" . $manifest . "  </manifest>\n"
    . "  <spine>\n" . $spine . "  </spine>\n"
    . "</package>\n";

// Fabrication du .epub : le fichier mimetype doit venir en premier, non compresse.
$tmp = tempnam(sys_get_temp_dir(), 'epub');
if ($tmp === false) {
    repondreErreur(500, 'Impossible de creer un fichier temporaire.');
}
$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
    @unlink($tmp);
    repondreErreur(500, "Impossible d'initialiser l'archive EPUB.");
}
$zip->addFromString('mimetype', 'application/epub+zip');
if (method_exists($zip, 'setCompressionName')) {
    $zip->setCompressionName('mimetype', ZipArchive::CM_STORE);
}
$zip->addFromString('META-INF/container.xml', $container);
$zip->addFromString('OEBPS/style.css', $css);
$zip->addFromString('OEBPS/nav.xhtml', $nav);
$zip->addFromString('OEBPS/content.opf', $opf);
foreach ($chapitres as $c) {
    $zip->addFromString('OEBPS/' . $c['href'], $c['xhtml']);
}
foreach ($images as $img) {
    $zip->addFromString('OEBPS/' . $img['href'], $img['data']);
}
$zip->close();

$octets = file_get_contents($tmp);
@unlink($tmp);
if ($octets === false) {
    repondreErreur(500, 'Lecture du fichier EPUB impossible.');
}

header('Content-Type: application/epub+zip');
header('Content-Disposition: inline; filename="brouillon.epub"');
header('Content-Length: ' . strlen($octets));
echo $octets;
