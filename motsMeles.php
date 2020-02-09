<!doctype html>
<html>
   <head>
      <meta charset='UTF-8'>
   </head>
   <body>
<?php
$bilan=array();
$tries=0;                         // used to index the array of possible solutions
$maxTries=50;                   // the program makes several grid choices, and then picks one up among them
mb_internal_encoding("UTF-8");    // you must ensure that the present file is encoded in UTF-8
a:
$mots=["Montluel",
       "Stanislas",
       "Agathe",
       "Noémmie",
       "Mélisse",
       "Anatoline",
       "Gwladys",
       "Chanay",
       "Marguerite",
       "Aglaé",
       "Fanny",
       "Guillaume",
       "Blanchette",
       "Grisette",
       "Gatto",
       "Moustache",
       "Chaussette",
       "Camille",
       "Hanaé",
       "Sacha",
       "Badis",
       "Safia",
       "Elisa",
       "Nathalie",
       "Mohamed-Amine",
       "Maëline",
       "Elodie",
       "Resoul",
       "Salomé",
       "Mélissa"];
//$mots=["Stan","Anat","Mel","Noe"];

$mots=array_map('htmlentities',$mots); // in order to convert multibytes characters into html entities
/* then convert each word into an array, taking htmlentities into account */
$mots=array_map('str_split',$mots);
$tmpMots=$mots;
/*echo '<pre>';
var_dump($mots);
echo '</pre>';*/
foreach($tmpMots as $rank=>$mot) {
    $newMot=[];                //init
    $ongoingEntity=0;          // ...
    $l=0;                      // ...
    foreach ($mot as $key=>$letter) {
       if($letter=='&') {           // entity begins
           $ongoingEntity = 1;
           $entityToReplace = '';
       }
       if ($ongoingEntity == 0) {   // standard character
           $newMot[$l]=$letter;
           $l++;
       }
       else {                       // entity continues
           $entityToReplace.=$letter;
       }
       if($letter==';') {           // entity ends
           $ongoingEntity = 0;
           $newMot[$l]=$entityToReplace;
           $l++;
       }
   }
   $mots[$rank]=$newMot;
}


shuffle($mots);
$bilan[$tries]='';
$motsPlaces[$tries]='';

// taille de la grille
$xGrille=17;
$yGrille=17;

// autorisations des sens d'écritures :
$HAUT=true;
$HAUTDROITE=true;
$DROITE=true;
$BASDROITE=true;
$BAS=true;
$BASGAUCHE=true;
$GAUCHE=true;
$HAUTGAUCHE=true;

// initialistion d(u contenu d)e la grille
for ($x=1;$x<=$xGrille;$x++) {
   for ($y=1;$y<=$yGrille;$y++) {
      $grille[$x][$y]='';
   }
}
$OneSuccess=0; //nb de mots placés avec succès
/*  1 2 3 . . .
 * 1
 * 2
 * 3
 * .
 * .
 * .
 * */
$tot=count($mots);


for ($i=1;$i<=$tot;$i++) {
   $chercher='';
   $choisir='';
   unset($possibilites);
   $mot=choisirMotAuHasard();
   $chercher=chercherPlacesMot($mot);

   $choisir=choisirPossibilite($mot);

   if ($chercher==="mot trop long") {
       $bilan[$tries].='Le mot <span style="color:crimson"><strong>';
       foreach($mot as $letter) {
           $bilan[$tries].= $letter;
       }
       $bilan[$tries].= '</strong></span> est trop long pour la grille.<br/>';
   }

   elseIf ($choisir==='aucune place trouvée') {
      $bilan[$tries].=' Le mot <strong>';
       foreach($mot as $letter) {
          $bilan[$tries].= $letter;
       }
       $bilan[$tries].= '</strong> n\'a pas pu être placé dans la grille.<br/>';
   }

   else {
      $OneSuccess++;
      $motsPlaces[$tries][]=$mot;
   }
}

if ($bilan[$tries]=='') $bilan[$tries]='Tous les mots ont pu être placés dans la grille.';

$toutesLesGrilles[$tries]=$grille;
$success[$tries]=$OneSuccess;
$tries++;




function choisirMotAuHasard() {
   global $mots;
   $maxIndex=count($mots)-1;
   $mot=array_splice($mots,rand(0,$maxIndex),1)[0];
   return $mot;
}

function chercherPlacesMot($mot){
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
    * et en fonction de celles-ci tous les positionnements possibles, echercherPlacesMott
    * enregistrer les possibilitédans un tableau
    */
   $l=count($mot);

   if ($l>$xGrille && $l>$yGrille) return "mot trop long"; // mot ne rentrant pas dans la grille

   $placesTrouvees=0;
   for ($x=1;$x<=$xGrille;$x++) {
      for ($y=1;$y<=$yGrille;$y++) {
         /* déterminer quelles sont les directions possibles
          * sans tenir compte du contenu ni du
          * mot, ni de la grille, juste des dimensions
          * on met tout dans un tableau indexé numériquement
          * qui contient des possibilités de départ/direction formatées ainsi :
          * xDep,yDep,xDir,yDir
          */

         //test de la direction HAUT, pour $x,$y
         // seul $y varie, de manière dècroissante
         if ($HAUT) {
            if ($y-$l>=0) { //possible
               //echo 'HAUT<br/>';//tester les cases
               $free=testerCases($mot,$l,$x,$y,0,-1);
               if ($free==true) {
                  $possibilites[]=$x.','.$y.',0,-1';
                  $placesTrouvees++;
               }
            }
         }
         //test de la direction HAUT DROITE, pour $x, $y
         //$x croît et $y décroît
         if ($HAUTDROITE) {
            if ($y-$l>=0 && $x+$l<=$xGrille+1) { //possible
               //echo 'HAUT DROITE<br/>';
               $free=testerCases($mot,$l,$x,$y,1,(-1));

               if ($free==true) {
                  $possibilites[]=$x.','.$y.',1,-1';
                  $placesTrouvees++;
               }
            }
         }
         //test de la direction DROITE, pour $x, $y
         //seul $x varie, de manière croissante
         if ($DROITE) {
            if ($x+$l<=$xGrille+1) { //possible
               //echo 'DROITE<br/>';
               $free=testerCases($mot,$l,$x,$y,1,0);
               if ($free==true) {
                  $possibilites[]=$x.','.$y.',1,0';
                  $placesTrouvees++;
               }
            }
         }
         //test de la direction BAS DROITE, pour $x, $y
         //$x croît et $y croît
         if ($BASDROITE) {
            if ($y+$l<=$yGrille+1 && $x+$l<=$xGrille+1) { //possible
               //echo 'BAS DROITE<br/>';
               $free=testerCases($mot,$l,$x,$y,1,1);
               if ($free==true) {
                  $possibilites[]=$x.','.$y.',1,1)';
                  $placesTrouvees++;
               }
            }
         }
         //test de la direction BAS, pour $x, $y
         // seul $y varie, de manière croissante
         if ($BAS) {
            if ($y+$l<=$yGrille+1) { //possible
               //echo 'BAS<br/>';
               $free=testerCases($mot,$l,$x,$y,0,1);
               if ($free==true) {
                  $possibilites[]=$x.','.$y.',0,1';
                  $placesTrouvees++;
               }
            }
         }
         //test de la direction BAS GAUCHE, pour $x, $y
         // $y croît, $x décroît
         if ($BASGAUCHE) {
            if ($y+$l<=$yGrille+1 && $x-$l>=0) { //possible
               //echo 'BAS GAUCHE<br/>';
               $free=testerCases($mot,$l,$x,$y,-1,1);
               if ($free==true) {
                  $possibilites[]=$x.','.$y.',-1,1';
                  $placesTrouvees++;
               }
            }
         }
         //test de la direction GAUCHE, pour $x, $y
         // seul $x décroît
         if ($GAUCHE) {
            if ($x-$l>=0) { //possible
               //echo 'GAUCHE<br/>';
               $free=testerCases($mot,$l,$x,$y,-1,0);
               if ($free==true) {
                  $possibilites[]=$x.','.$y.',-1,0';
                  $placesTrouvees++;
               }
            }
         }
         //test de la direction HAUT GAUCHE, pour $x, $y
         // $x et $y décroissent
         if($HAUTGAUCHE) {
            if ($x-$l>=0 && $y-$l>=0) { //possible
               //echo 'HAUT GAUCHE<br/>';
               $free=testerCases($mot,$l,$x,$y,-1,-1);
               if ($free==true) {
                  $possibilites[]=$x.','.$y.',-1,-1';
                  $placesTrouvees++;
               }
            }
         }
      }
   } // on a maintenant toutes les possibilités
return $placesTrouvees;
}

function testerCases($mot,$l,$xDep,$yDep,$xDir,$yDir) {
   global $grille;
   $z=0; // letters' tests count
   /*echo '$mot: '.$mot.'<br/>';
   echo '$l: '.$l.'<br/>';
   echo '$xDep: '.$xDep.'<br/>';
   echo '$yDep: '.$yDep.'<br/>';
   echo '$xDir: '.$xDir.'<br/>';
   echo '$yDir: '.$yDir.'<br/>';*/

   while ($z<$l) {

      $newX= $xDep+($z*$xDir);
      $newY= $yDep+($z*$yDir);
      $caseATester = $grille[$newX][$newY];
      /*echo '$z: '.$z;
      echo '$grille['.$newX.']['.$newY.']: '.$grille[$newX][$newY].'</br>';
      if ($caseATester<>'') echo $mot.' '.$caseATester.' - '.$mot[$z].'<br/>';*/
      if($caseATester==''||$caseATester==$mot[$z]) {} // empty or same letter = ok continue
      else {
         //echo 'bad : X:'.$newX.' Y:'.$newY.' $caseATester='.$caseATester.' $mot[$z]='.$mot[$z].'<br/>';
         return false;
         //break;
      }
      $z++;
   }
   return true; // if arrived there it means this is all ok
}

function choisirPossibilite($mot) {

   global $possibilites;
   if (!(isset($possibilites))) {
      return 'aucune place trouvée';
   }
   //choisir la possibilite
   $totalPossibilites=count($possibilites);
   $choix=rand(0,$totalPossibilites-1);
   //placer le mot
   $params=explode(',',$possibilites[$choix]);
   $l=count($mot);
   placerMot($l,$mot,$params);
}

function placerMot($l,$mot,array $all) {
   global $grille;
   $z=0;
   while ($z<$l) {
      $newX= $all[0]+($z*$all[2]);
      $newY= $all[1]+($z*$all[3]);
      $grille[$newX][$newY]=$mot[$z];
      $z++;
   }
}

function remplirReste($grille) {
   $stdChars='';
   $specialChars='';
   $stdChars=str_split('aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzzABCDEFGHIJKLMNOPQRSTUVWXYZ');
   //$stdChars=str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
   $specialChars=['&nbsp;','&nbsp;','&eacute;','&egrave','&euml','&ccedil;','&uuml;','&ouml;','&ugrave','&agrave'];

   foreach ($stdChars as $value) $allChars[]=$value;
   foreach ($stdChars as $value) $allChars[]=$value;
   foreach ($specialChars as $value) $allChars[]=$value;


   global $xGrille;
   global $yGrille;
   for ($x=1;$x<=$xGrille;$x++) {
      for ($y=1;$y<=$yGrille;$y++) {
         $z=rand(0,count($allChars)-1);
         //$grille[$x][$y]=(trim($grille[$x][$y],' ')=='')?$allChars[$z]:$grille[$x][$y];
		 $grille[$x][$y]=($grille[$x][$y]=='')?$allChars[$z]:$grille[$x][$y];
      }
   }
   return $grille;
}


if($tries<$maxTries) goto a;

$maxSuccess=0;
foreach($success as $key=>$value) {
   if ($value>$maxSuccess) $maxSuccess=$value;
}

foreach($success as $key=>$value) {
   if ($value==$maxSuccess) {
      $meilleuresGrilles[]=$toutesLesGrilles[$key];
      $meilleursBilans[]=$bilan[$key];
      $meilleursMotsPlaces[]=$motsPlaces[$key];
   }

}

$indexGrilleChoisie=rand(0,count($meilleuresGrilles)-1);

$meilleuresGrilles[$indexGrilleChoisie]=remplirReste($meilleuresGrilles[$indexGrilleChoisie]);

// dessin
echo '<div style="position:relative;left:10%;float:left"><table style="border-collapse:collapse">';
for ($y=1;$y<=$yGrille;$y++) {
echo '<tr style="border:1px solid black">';
   for ($x=1;$x<=$xGrille;$x++) {
      echo '<td style="text-align:center; border:1px solid black; height:26px; width:26px;">';
      echo $meilleuresGrilles[$indexGrilleChoisie][$x][$y];
      echo '</td>';
   }
   echo '</tr>';
}
echo '</table><br/><br/>'.$meilleursBilans[$indexGrilleChoisie].'</div><div style="position:relative;text-align:center;right:20%;float:right">';


foreach ($meilleursMotsPlaces[$indexGrilleChoisie] as $key=>$value){
   foreach($value as $v) {
       echo $v;
   }
   echo '</br>';
}
echo '</div>';

?>
   </body>
</html>