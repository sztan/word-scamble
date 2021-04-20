<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
include('env.php');
// vérifications recaptcha
$recaptchaSuccess=false;
include('checkRecaptcha.php');
if(!$recaptchaSuccess) {
    return;
}

// traitement des données $_POST
//remplacer les ; par des virgules
$_POST['mots'] = str_replace(';', ',', $_POST['mots']);
//remplacer les 'new line' par des virgules
$_POST['mots'] = preg_replace("/\n/m", ",", $_POST['mots']);
//remplacer les whitespaces par des virgules
$_POST['mots'] = preg_replace("/\s/m", ",", $_POST['mots']);
// dé-x-blonner les ,
$_POST['mots'] = preg_replace('/,+/', ',', $_POST['mots']);

//remplacer les ; par des virgules
$_POST['motsSecrets'] = str_replace(';', ',', $_POST['motsSecrets']);
//remplacer les 'new line' par des virgules
$_POST['motsSecrets'] = preg_replace("/\n/m", ",", $_POST['motsSecrets']);
//remplacer les whitespaces par des virgules
$_POST['motsSecrets'] = preg_replace("/\s/m", ",", $_POST['motsSecrets']);
// dé-x-blonner les ,
$_POST['motsSecrets'] = preg_replace('/,+/', ',', $_POST['motsSecrets']);

$motsAuHasard = [];
if ($_POST['auHasard'] === "true") {
    $csvMots = [];
    $file = file(__DIR__ . '/Morphalou3.1_allWordsOnly.csv');
    $i = 0;

    while (count($motsAuHasard) < $_POST['nbMots']) {
        $motAuHasard = $file[array_rand($file)];
        if (
            !empty($motAuHasard) &&
            (
                trim(strlen($motAuHasard)) <= $_POST['tailleGrilleX'] ||
                trim(strlen($motAuHasard)) <= $_POST['tailleGrilleY']
            )
        ) {
            $motsAuHasard[] = trim($motAuHasard);
        }
    }

}
// caractères spéciaux

$bilan = [];
$tries = 0;                       // used to index the array of possible solutions
$maxTries = 50;                   // the program makes several grid choices, and then picks one up among them
mb_internal_encoding("UTF-8");  // you must ensure that the present file is encoded in UTF-8

$possibilites=[];

$xGrille = $_POST['tailleGrilleX'];
$yGrille = $_POST['tailleGrilleY'];
if ($xGrille < 1) {
    $xGrille = 15;
}
if ($yGrille < 1) {
    $yGrille = 15;
}
// setting writing directions :
$HAUT = (bool)$_POST['HAUT'];
$HAUTDROITE = (bool)$_POST['HAUTDROITE'];
$DROITE = (bool)$_POST['DROITE'];
$BASDROITE = (bool)$_POST['BASDROITE'];
$BAS = (bool)$_POST['BAS'];
$BASGAUCHE = (bool)$_POST['BASGAUCHE'];
$GAUCHE = (bool)$_POST['GAUCHE'];
$HAUTGAUCHE = (bool)$_POST['HAUTGAUCHE'];

// }:O
a:

$motsVisibles = empty($motsAuHasard) ? array_filter(str_replace('_', ' ', array_map('trim', explode(',', $_POST['mots'])))) : $motsAuHasard;
$motsCaches = array_filter(array_map('trim', explode(',', $_POST['motsSecrets'])));

// in order to convert multibyte characters into html entities :
$motsCaches = array_map('htmlentities', $motsCaches);
$motsVisibles = array_map('htmlentities', $motsVisibles);

// ajouter les mots secrets
$mots=$motsVisibles;
$nb_mots_caches=0;
foreach($motsCaches as $m) {
    if(!in_array($m, $motsVisibles)) {
        $mots[]=$m;
        $nb_mots_caches++;
    }
}

/* then convert each word into an array, taking htmlentities into account */
$mots = array_map('str_split', $mots);
$tmpMots = $mots;

foreach ($tmpMots as $rank => $mot) {
    $newMot = [];                //init
    $ongoingEntity = 0;          // ...
    $l = 0;                      // ...
    $entityToReplace = '';
    foreach ($mot as $key => $letter) {
        if ($letter === '&') {           // entity begins
            $ongoingEntity = 1;
            $entityToReplace = '';
        }
        if ($ongoingEntity === 0) {      // standard character
            $newMot[$l] = $letter;
            $l++;
        } else {                         // entity continues
            $entityToReplace .= $letter;
        }
        if ($letter === ';') {           // entity ends
            $ongoingEntity = 0;
            $newMot[$l] = $entityToReplace;
            $l++;
        }
    }
    $mots[$rank] = $newMot;
}

shuffle($mots);
$bilan[$tries] = '';
$motsPlaces[$tries] = [];



$grille = [];
// grid initialization
for ($x = 1; $x <= $xGrille; $x++) {
    $grille[$x] = [];
    for ($y = 1; $y <= $yGrille; $y++) {
        $grille[$x][$y] = '';
    }
}
$OneSuccess = 0; //number of words successfully placed on the grid
/*  1 2 3 . . .
 * 1
 * 2
 * 3
 * .
 * .
 * .
 * */
$tot = count($mots);

for ($i = 1; $i <= $tot; $i++) {
    $chercher = '';
    $choisir = '';
    unset($possibilites);
    try {
        $mot = choisirMotAuHasard();
    } catch (Exception $e) {
    }
    $chercher = chercherPlacesMot($mot);

    try {
        $choisir = choisirPossibilite($mot);
    } catch (Exception $e) {
    }

    if ($chercher === "mot trop long") {
        $bilan[$tries] .= 'Le mot <span style="color:crimson"><strong>';
        foreach ($mot as $letter) {
            $bilan[$tries] .= $letter;
        }
        $bilan[$tries] .= '</strong></span> est trop long pour la grille.<br/>';
    } elseif ($choisir === 'aucune place trouvée') {
        $bilan[$tries] .= ' Le mot <strong>';
        $myWord = '';
        foreach ($mot as $letter) {
            $myWord .= $letter;
        }
        if (!in_array($myWord, $motsCaches, true)) {
            $bilan[$tries] .= $myWord;
        } else {
            $bilan[$tries] .= "***";
        }

        $bilan[$tries] .= '</strong> n\'a pas pu être placé dans la grille.<br/>';
    } else {
        $OneSuccess++;
        $motsPlaces[$tries][] = $mot;
    }
}

if ($bilan[$tries] === '') {
    $bilan[$tries] = 'Tous les mots ont pu être placés dans la grille.';
}

$toutesLesGrilles[$tries] = $grille;
$success[$tries] = $OneSuccess;
$tries++;

/**
 * @throws Exception
 */
function choisirMotAuHasard()
{
    global $mots;
    $maxIndex = count($mots) - 1;
    return array_splice($mots, random_int(0, $maxIndex), 1)[0];
}

function chercherPlacesMot($mot)
{
    global $HAUT;
    global $HAUTDROITE;
    global $DROITE;
    global $BASDROITE;
    global $BAS;
    global $BASGAUCHE;
    global $GAUCHE;
    global $HAUTGAUCHE;
    global $xGrille;
    global $yGrille;
    global $possibilites;
    /* parcourir toutes les cases de départ possible,
     * et en fonction de celles-ci tous les positionnements possibles, chercherPlacesMot
     * enregistrer les possibilités dans un tableau
     */
    $l = count($mot);

    if ($l > $xGrille && $l > $yGrille) { // mot ne rentrant pas dans la grille
        return "mot trop long";
    }

    $placesTrouvees = 0;

    for ($x = 1; $x <= $xGrille; $x++) {
        for ($y = 1; $y <= $yGrille; $y++) {
            /* déterminer quelles sont les directions possibles
             * sans tenir compte du contenu ni du
             * mot, ni de la grille, juste des dimensions
             * on met tout dans un tableau indexé numériquement
             * qui contient des possibilités de départ/direction formatées ainsi :
             * xDep,yDep,xDir,yDir
             */

            //test de la direction HAUT, pour $x,$y
            // seul $y varie, de manière décroissante
            if ($HAUT && $y - $l >= 0) { //possible
                //echo 'HAUT<br/>';//tester les cases
                $free = testerCases($mot, $l, $x, $y, 0, -1);
                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',0,-1';
                    $placesTrouvees++;
                }
            }
            //test de la direction HAUT DROITE, pour $x, $y
            //$x croît et $y décroît
            if ($HAUTDROITE && $y - $l >= 0 && $x + $l <= $xGrille + 1) { //possible
                //echo 'HAUT DROITE<br/>';
                $free = testerCases($mot, $l, $x, $y, 1, (-1));

                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',1,-1';
                    $placesTrouvees++;
                }
            }
            //test de la direction DROITE, pour $x, $y
            //seul $x varie, de manière croissante
            if ($DROITE && $x + $l <= $xGrille + 1) { //possible
                //echo 'DROITE<br/>';
                $free = testerCases($mot, $l, $x, $y, 1, 0);
                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',1,0';
                    $placesTrouvees++;
                }
            }
            //test de la direction BAS DROITE, pour $x, $y
            //$x croît et $y croît
            if ($BASDROITE && $y + $l <= $yGrille + 1 && $x + $l <= $xGrille + 1) { //possible
                //echo 'BAS DROITE<br/>';
                $free = testerCases($mot, $l, $x, $y, 1, 1);
                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',1,1)';
                    $placesTrouvees++;
                }
            }
            //test de la direction BAS, pour $x, $y
            // seul $y varie, de manière croissante
            if ($BAS && $y + $l <= $yGrille + 1) { //possible
                //echo 'BAS<br/>';
                $free = testerCases($mot, $l, $x, $y, 0, 1);
                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',0,1';
                    $placesTrouvees++;
                }
            }
            //test de la direction BAS GAUCHE, pour $x, $y
            // $y croît, $x décroît
            if ($BASGAUCHE && $y + $l <= $yGrille + 1 && $x - $l >= 0) { //possible
                //echo 'BAS GAUCHE<br/>';
                $free = testerCases($mot, $l, $x, $y, -1, 1);
                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',-1,1';
                    $placesTrouvees++;
                }
            }
            //test de la direction GAUCHE, pour $x, $y
            // seul $x décroît
            if ($GAUCHE && $x - $l >= 0) { //possible
                //echo 'GAUCHE<br/>';
                $free = testerCases($mot, $l, $x, $y, -1, 0);
                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',-1,0';
                    $placesTrouvees++;
                }
            }
            //test de la direction HAUT GAUCHE, pour $x, $y
            // $x et $y décroissent
            if ($HAUTGAUCHE && $x - $l >= 0 && $y - $l >= 0) { //possible
                //echo 'HAUT GAUCHE<br/>';
                $free = testerCases($mot, $l, $x, $y, -1, -1);
                if ($free === true) {
                    $possibilites[] = $x . ',' . $y . ',-1,-1';
                    $placesTrouvees++;
                }
            }
        }
    } // on a maintenant toutes les possibilités
    return $placesTrouvees;
}

function testerCases($mot, $l, $xDep, $yDep, $xDir, $yDir)
{
    global $grille;
    $z = 0; // letters' tests count

    while ($z < $l) {

        $newX = $xDep + ($z * $xDir);
        $newY = $yDep + ($z * $yDir);
        $caseATester = $grille[$newX][$newY];

        if ($caseATester === '' || $caseATester === $mot[$z]) {  // empty or same letter = ok continue
            $z++;
        } else {
            return false;
        }

    }
    return true; // if arrived there it means this is all ok
}

/**
 * @throws Exception
 */
function choisirPossibilite($mot)
{

    global $possibilites;
    if (empty($possibilites)) {
        return 'aucune place trouvée';
    }
    //choisir la possibilite
    $totalPossibilites = count($possibilites);
    $choix = random_int(0, $totalPossibilites - 1);
    //placer le mot
    $params = explode(',', $possibilites[$choix]);
    $l = count($mot);
    placerMot($l, $mot, $params);
}

function placerMot($l, $mot, array $all): void
{
    global $grille;
    $z = 0;
    $all = array_map('intval', $all);
    while ($z < $l) {
        $newX = $all[0] + ($z * $all[2]);
        $newY = $all[1] + ($z * $all[3]);
        $grille[$newX][$newY] = $mot[$z];
        $z++;
    }
}

/**
 * @throws Exception
 */
function remplirReste($grille): array
{
    $stdChars = '';
    $specialChars = '';
    $stdChars = str_split('aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    $specialChars = ['&nbsp;', '&nbsp;', '&eacute;', '&egrave;', '&euml;', '&iuml;', '&ccedil;', '&uuml;', '&ouml;', '&ugrave;', '&agrave;'];

    foreach ($stdChars as $value) {
        $allChars[] = $value;
    }
    foreach ($stdChars as $value) {
        $allChars[] = $value;
    }
    foreach ($specialChars as $value) {
        $allChars[] = $value;
    }

    global $xGrille;
    global $yGrille;
    for ($x = 1; $x <= $xGrille; $x++) {
        for ($y = 1; $y <= $yGrille; $y++) {
            $z = random_int(0, count($allChars) - 1);
            //$grille[$x][$y]=(trim($grille[$x][$y],' ')=='')?$allChars[$z]:$grille[$x][$y];
            $grille[$x][$y] = ($grille[$x][$y] === '') ? $allChars[$z] : $grille[$x][$y];
        }
    }
    return $grille;
}

// }:-)
if ($tries < $maxTries) {
    goto a;
}

$maxSuccess = 0;
foreach ($success as $key => $value) {
    if ($value > $maxSuccess) {
        $maxSuccess = $value;
    }
}
$meilleuresGrilles = [];
$meilleursBilans = [];
foreach ($success as $key => $value) {
    if ($value === $maxSuccess) {
        $meilleuresGrilles[] = $toutesLesGrilles[$key];
        $meilleursBilans[] = $bilan[$key];
        $meilleursMotsPlaces[] = $motsPlaces[$key];
    }

}

$indexGrilleChoisie = null;
try {
    $indexGrilleChoisie = random_int(0, count($meilleuresGrilles) - 1);
} catch (Exception $e) {
}

try {
    $meilleuresGrilles[$indexGrilleChoisie] = remplirReste($meilleuresGrilles[$indexGrilleChoisie]);
} catch (Exception $e) {
}

ob_start();
// dessin
echo '<br><div style="position:relative; float: left; text-align:center"><table style="border-collapse:collapse">';
for ($y = 1; $y <= $yGrille; $y++) {
    echo '<tr style="border:1px solid black">';
    for ($x = 1; $x <= $xGrille; $x++) {
        echo '<td style="vertical-align: middle; text-align:center; border:1px solid black; height:26px; width:26px;">';
        echo $meilleuresGrilles[$indexGrilleChoisie][$x][$y];
        echo '</td>';
    }
    echo '</tr>';
}
echo '</table><br/><p style="border:1px solid red; text-align:left">' . $meilleursBilans[$indexGrilleChoisie] . '</p></div><!--html2pdf page break--><div style="position:relative;text-align:center;margin-left:1%;float:left">';

foreach ($meilleursMotsPlaces[$indexGrilleChoisie] as $key => $value) {
    $mot = "";
    foreach ($value as $v) {
        $mot .= $v;
    }
    if (!in_array($mot, $motsCaches)) {
        echo $mot;
        echo '<br>';
    }
}
echo "<b>Il y a $nb_mots_caches mot(s) cache(s) dans la grille.</b>";
echo '</div>';
$retour = ob_get_clean();

require __DIR__ . '/vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;

$html2pdf = new Html2Pdf();

$pages = explode("<!--html2pdf page break-->", $retour);
$html2pdf->writeHTML('<page>' . $pages[0] . '</page>');
$html2pdf->writeHTML('<page>' . $pages[1] . '</page>');

try {
    $html2pdf->output(__DIR__ . "/${_POST['grid']}.pdf", "F");
} catch (\Spipu\Html2Pdf\Exception\Html2PdfException $e) {
    echo $e->getMessage();
}
echo $retour . "<br><div style=\"clear:both\"><a href=\"${_POST['grid']}.pdf\">grille à télécharger</a></div><br>";
