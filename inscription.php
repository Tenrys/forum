<?php
    include "includes/db.php";

    session_start();

    if (isset($_SESSION["user"])) {
        header("Location: index.php");
        die;
    }

    if (count($_POST) > 0) {
        extract($_POST);

        if ($password == $passwordConfirm) {
            $request = "SELECT * FROM utilisateurs WHERE login = ?";
            $stmt = $db->prepare($request);
            $stmt->execute([$login]);
            $results = $stmt->fetchAll();

            if (count($results) < 1) {
                $request = "INSERT INTO utilisateurs (login, password) VALUES (?, ?);";
                $stmt = $db->prepare($request);
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $success = $stmt->execute([$login, $hashed]);
                if (!$success) {
                    echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
                    die;
                }
            } else {
                $error = "Cet utilisateur existe déjà !";
            }
        } else {
            $error = "Le mot de passe que vous avez fourni ne correspond pas avec votre confirmation !";
        }
    }
?>

<title>Inscription</title>
<h1>Inscription</h1>
<a href="index.php">Retour</a>

<?php
if (isset($error)) {
    echo "<h4 class='error'>$error</h4>";
}
if (isset($success) && $success) {
    echo "<h4 class='success'>Compte créé avec succès ! Vous pouvez dorénavant vous connecter...<br>Vous allez être redirigé dans 5 secondes...</h4>";
    header("Refresh: 5; URL=connexion.php");
} else { ?>
    <form method="post">
        <label for="login">Login</label>
        <input type="text" name="login" required minlength="3" maxlength="255" value="<?= $login ?? '' ?>">

        <label for="password">Mot de passe</label>
        <input type="password" name="password" required minlength="3" maxlength="255">

        <label for="passwordConfirm">Mot de passe (confirmation)</label>
        <input type="password" name="passwordConfirm" required minlength="3" maxlength="255">

        <input type="submit" value="S'inscrire">
    </form>
<?php
}
?>