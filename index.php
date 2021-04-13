<?php
session_start();
$_SESSION['grid'] = uniqid('', true);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Train+One&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf"
            crossorigin="anonymous"></script>
    <!-- Bootstrap icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css">
    <!-- Custom stylesheet -->
    <link rel="stylesheet" href="index.css">
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!-- at the end of the body -->

    <script>
        $(function () {
            // initialisation des tooltips bootstrap
            $('[data-toggle="tooltip"]').tooltip();

            $('form[name=generer]').on({
                submit: function (event) {
                    // activer le spinner
                    $("div#spinner").show();
                    var mots = $(this).find('#mots+textarea').val();
                    var motsSecrets = $(this).find('#motsSecrets+textarea').val();
                    var HAUT = $(this).find('input[name=HAUT]').val();
                    var HAUTDROITE = $(this).find('input[name=HAUTDROITE]').val();
                    var DROITE = $(this).find('input[name=DROITE]').val();
                    var BASDROITE = $(this).find('input[name=BASDROITE]').val();
                    var BAS = $(this).find('input[name=BAS]').val();
                    var BASGAUCHE = $(this).find('input[name=BASGAUCHE]').val();
                    var GAUCHE = $(this).find('input[name=GAUCHE]').val();
                    var HAUTGAUCHE = $(this).find('input[name=HAUTGAUCHE]').val();
                    var tailleGrilleX = $(this).find('textarea[name=tailleGrilleX]').val();
                    var tailleGrilleY = $(this).find('textarea[name=tailleGrilleY]').val();
                    var auHasard = $(this).find('input[name=auHasard]').prop("checked");
                    var nbMots = $(this).find('input[name=nbMots]').val();

                    var grid = "<?php echo $_SESSION['grid']; ?>";
                    event.preventDefault();
                    // récupération des valeurs du formulaire :

                    const jqxhr = $.ajax({
                        method: "POST",
                        url   : "motsMeles.php",
                        data  : {
                            mots         : mots,
                            motsSecrets  : motsSecrets,
                            grid         : grid,
                            HAUT         : HAUT,
                            HAUTDROITE   : HAUTDROITE,
                            DROITE       : DROITE,
                            BASDROITE    : BASDROITE,
                            BAS          : BAS,
                            BASGAUCHE    : BASGAUCHE,
                            GAUCHE       : GAUCHE,
                            HAUTGAUCHE   : HAUTGAUCHE,
                            tailleGrilleX: tailleGrilleX,
                            tailleGrilleY: tailleGrilleY,
                            auHasard     : auHasard,
                            nbMots       : nbMots
                        }
                    }).done(function (data) {

                        $("div.conteneur").html(data);
                    })
                                   .fail(function () {
                                       alert("error");
                                   })
                                   .always(function () {
                                       // enlever le spinner
                                       $("div#spinner").hide();
                                       // montrer le success
                                       $("div#success").show();
                                       $("div#success").fadeOut('slow');
                                   });
                }
            });

            $('form[name=generer] input[name=auHasard').on({
                change: function (event) {
                    if ($(this).prop("checked")) {
                        // désactiver les champs de saisie "mots" et "motsSecrets"
                        $('form[name=generer').find('#mots+textarea').prop("disabled", true);
                        $('form[name=generer').find('#motsSecrets+textarea').prop("disabled", true);
                        //activer nbMots
                        $('form[name=generer').find('input[name=nbMots]').prop("disabled", false);
                    } else {
                        $('form[name=generer').find('#mots+textarea').prop("disabled", false);
                        $('form[name=generer').find('#motsSecrets+textarea').prop("disabled", false);
                        $('form[name=generer').find('input[name=nbMots]').prop("disabled", true);
                    }
                }
            });

            $("div#arrow_selector i").on({
                click: function (event) { // rendre inactif
                    if (!!$(this).attr("data-color-active") &&
                        $(this).hasClass($(this).attr("data-color-active"))
                    ) {
                        $(this).removeClass($(this).attr("data-color-active"));
                        $(this).addClass($(this).attr("data-color-inactive"));
                        if (!!$(this).next('input[type=hidden]')) {
                            $(this).next('input').val("0");
                        }
                    } else { // rendre actif
                        if (!!$(this).attr("data-color-inactive") &&
                            $(this).hasClass($(this).attr("data-color-inactive"))
                        ) {
                            $(this).removeClass($(this).attr("data-color-inactive"));
                            $(this).addClass($(this).attr("data-color-active"));
                            if (!!$(this).next('input[type=hidden]')) {
                                $(this).next('input').val("1");
                            }
                        }
                    }
                }
            });
        });
    </script>
    <title>Générateur de Mots Mêlés</title>
</head>
<body>
<!--// contrôle des données POST :-->
<!--// tous les mots-->
<!--// - mots à mettre dans la liste d'aide-->
<!--// - mots additionnels cachés-->
<!--// taille de la grille-->
<!--// sens d'écriture autorisés-->
<!---->
<!--// caractères spéciaux-->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <p>WORD SCRAMBLE GENERATOR <span style="font-size:.8em; font-family: arial, cursive"><sub>by <a href="https://sztan.github.io/">sztan</a></sub></span></p>
    </div>
</nav>
<div class="container">
    <div id="spinner" style="display:none">
        <div class="spinner-grow text-warning" role="status">
            <span class="sr-only"></span>
        </div>
    </div>
    <div id="success" class="text-warning" style="display:none">
        <i class="bi bi-check"></i>
    </div>
    <div>
        <form name="generer" method="POST">

            <!--taille de la grille-->
            <div style="float:left; text-align:center; margin:10px">
                <div>
                    Taille de la grille
                </div>
                <div style="float:left">
                    <label style="font-size:1.5em; display: block; text-align: center" for="tailleGrilleX">x</label>
                    <textarea style="font-size:1.5em; width:auto" class="form-control" cols="2" rows="1"
                              name="tailleGrilleX">12</textarea>
                </div>
                <div style="float:left">
                    <label style="font-size:1.5em; display: block; text-align: center" for="tailleGrilleY">y</label>
                    <textarea style="font-size:1.5em; width:auto" class="form-control" cols="2" rows="1"
                              name="tailleGrilleY">12</textarea>
                </div>
            </div>

            <!--sens d'écriture-->
            <div style="float:left; text-align:center; position: relative; margin:10px;">
                <div>Sens d'écriture autorisés</div>
                <div id="arrow_selector">
                    <div style="clear:both; float:left">
                        <i data-color-inactive="bi-arrow-up-left-circle"
                           data-color-active="bi-arrow-up-left-circle-fill"
                           class="bi bi-arrow-up-left-circle"></i>
                        <input type="hidden" name="HAUTGAUCHE" value="0">
                    </div>
                    <div style="float:left">
                        &nbsp;<i data-color-inactive="bi-arrow-up-circle" data-color-active="bi-arrow-up-circle-fill"
                                 class="bi bi-arrow-up-circle"></i>
                        <input type="hidden" name="HAUT" value="0">
                    </div>
                    <div style="float:left">
                        &nbsp;<i data-color-inactive="bi-arrow-up-right-circle"
                                 data-color-active="bi-arrow-up-right-circle-fill"
                                 class="bi bi-arrow-up-right-circle-fill"></i>
                        <input type="hidden" name="HAUTDROITE" value="1">
                    </div>

                    <div style="clear:both; float:left">
                        <i data-color-inactive="bi-arrow-left-circle" data-color-active="bi-arrow-left-circle-fill"
                           class="bi bi-arrow-left-circle"></i>
                        <input type="hidden" name="GAUCHE" value="0">
                    </div>
                    <div style="visibility:hidden; float:left">
                        &nbsp;<i class="bi bi-circle"></i>
                    </div>
                    <div style="float:left">
                        &nbsp;<i data-color-inactive="bi-arrow-right-circle"
                                 data-color-active="bi-arrow-right-circle-fill"
                                 class="bi bi-arrow-right-circle-fill"></i>
                        <input type="hidden" name="DROITE" value="1">
                    </div>

                    <div style="clear:both; float:left">
                        <i data-color-inactive="bi-arrow-down-left-circle"
                           data-color-active="bi-arrow-down-left-circle-fill"
                           class="bi bi-arrow-down-left-circle"></i>
                        <input type="hidden" name="BASGAUCHE" value="0">
                    </div>
                    <div style="float:left">
                        &nbsp;<i data-color-inactive="bi-arrow-down-circle"
                                 data-color-active="bi-arrow-down-circle-fill"
                                 class="bi bi-arrow-down-circle-fill"></i>
                        <input type="hidden" name="BAS" value="1">
                    </div>
                    <div style="float:left">
                        &nbsp;<i data-color-inactive="bi-arrow-down-right-circle"
                                 data-color-active="bi-arrow-down-right-circle-fill"
                                 class="bi bi-arrow-down-right-circle-fill"></i>
                        <input type="hidden" name="BASDROITE" value="1">
                    </div>
                </div>
            </div>

            <div style="clear:both">
                <label for="tousLesMots">Mettre ici tous les mots à placer dans la grille, les mots seront placés dans
                    la grille et - par défaut - listés à côté.<i style="font-size:1.5em; vertical-align:inherit" class="bi bi-question" data-toggle="tooltip"
                                                                 data-placement="top"
                                                                 title="Les séparateurs autorisés sont les espaces, virgules, points-virgules, ou 'new line'. Il est possible d'insérer des mots comprenant eux-mêmes des espaces en les remplaçant alors par un _ (underscore)."></i></label><br>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="mots">TOUS LES MOTS</span>
                    <textarea name="tousLesMots" class="form-control" placeholder="mot1,mot2,mot3,..." aria-label="mots"
                              aria-describedby="mots" rows="3"></textarea>
                </div>
            </div>
            <label for="motsSecrets">Mettre ici les mots qui - parmi les mots ci-dessus - NE DOIVENT PAS APPARAÎTRE dans
                la liste à côté de la
                grille (mots secrets)</label><br>
            <div class="input-group mb-3">
                <span class="input-group-text" id="motsSecrets">MOTS SECRETS</span>
                <textarea name="motsSecrets" class="form-control" placeholder="mot1,mot2,mot3,..." aria-label="mots"
                          aria-describedby="mots" rows="3"></textarea>
            </div>
            <div>
                <div style="float:left" class="form-check">
                    <input class="form-check-input" type="checkbox" value="" name="auHasard" id="flexCheckDefault">
                    <label class="form-check-label" for="flexCheckDefault">
                        Choisir des mots au hasard (<a href="https://repository.ortolang.fr/api/content/morphalou/latest/LISEZ-MOI.html">Morphalou</a>)
                    </label>
                </div>
                <div style="" class="input-group">
                    <span class="input-group-text" id="nbMots">nombre de mots au hasard</span>
                    <input style="margin-top:4px" name="nbMots" class="form-control" aria-label="nbMots"
                           aria-describedby="nbMots" value="10" rows="3" disabled>
                </div>
            </div>
            <br>
            <div style="clear:both">
                <button class="btn btn-primary" type="submit">SCRAMBLE</button>
            </div>
        </form>
    </div>
    <br>
    <div class="conteneur">
    </div>
</div>
</body>
</html>


