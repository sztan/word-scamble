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
    <!--    <link rel="stylesheet" href="index.css">-->

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!-- at the end of the body -->

    <script>
        $(function () {
            console.log("000000");
            $('form[name=generer]').on({
                submit: function (event) {
                    event.preventDefault();
                    const jqxhr = $.ajax("motsMeles.php")
                                   .done(function (data) {
                                       //alert("success");
                                       $("div.conteneur").html(data);
                                   })
                                   .fail(function () {
                                       alert("error");
                                   })
                                   .always(function () {
                                       alert("complete");
                                   });
                }
            });
        });
    </script>
    <title>Générateur de Mots Mêlés</title>
</head>
<body>
<form name="generer" method="POST">
    <input type="submit">
</form>
<div class="conteneur">
</div>
</body>
</html>


