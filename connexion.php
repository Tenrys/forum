<?php
    include "includes/db.php";

    session_start();

    if (isset($_SESSION["user"])) {
        header("Location: index.php");
        die;
    }

    if (count($_POST) > 0) {
        extract($_POST);

        $request = "SELECT * FROM utilisateurs WHERE login = ?;";
        $stmt = $db->prepare($request);
        $stmt->execute([$login]);
        $results = $stmt->fetchAll();

        if (count($results) > 0 && password_verify($password, $results[0]["password"])) {
            $_SESSION["user"] = $results[0];
            if (isset($_GET["from"])) {
                header("Location: {$_GET['from']}.php");
            } else {
                header("Location: index.php");
            }
            die;
        } else {
            $error = "Mot de passe incorrect !";
        }
    }
?>

<title>Connexion</title>
<h1>Connexion</h1>
<a href="index.php">Retour</a>

<?php
if (isset($error)) {
    echo "<h4 class='error'>$error</h4>";
}
?>
<form method="post">
    <label for="login">Login</label>
    <input type="text" name="login" required minlength="3" maxlength="255" value="<?= $login ?? '' ?>">

    <label for="password">Mot de passe</label>
    <input type="password" name="password" required minlength="3" maxlength="255" value="<?= $password ?? '' ?>">

    <input type="submit" value="Se connecter">

</form>