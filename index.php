<?php
    session_start();

    if (isset($_SESSION["user"])) {
        extract($_SESSION["user"]);
    }
?>

<title>Index</title>
<h1>Index</h1>

<?php
if (isset($_SESSION["user"])) {
    echo "<a href='deconnexion.php'>Déconnexion</a>";
    echo "<br/>";
    echo "<a href='modifier_profil.php'>Modifier profil</a>";
    echo "<br/>";
    echo "<a href='profil.php?id=$id'>Profil</a>";
} else {
    echo "<a href='connexion.php'>Connexion</a>";
    echo "<br/>";
    echo "<a href='inscription.php'>Inscription</a>";
}
?>