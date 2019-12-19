<?php
    include "includes/db.php";

    session_start();

    if (!isset($_SESSION["user"])) {
        header("Location: index.php");
        die;
    }

    extract($_SESSION["user"]);

    if (count($_POST) > 0) {
        $newInfo = &$_POST;

        if (isset($newInfo["password"]) && $newInfo["password"] != "") {
            if ($newInfo["password"] != $newInfo["passwordConfirm"]) {
                $error = "Le mot de passe que vous avez fourni ne correspond pas avec votre confirmation !";
            }
        } else {
            $newInfo["password"] = $password;
        }

        if (!isset($error)) {
            $request = "UPDATE utilisateurs SET login = :login, password = :password, naissance = :naissance, bio = :bio, email = :email WHERE id = :id;";
            $stmt = $db->prepare($request);
            $success = $stmt->execute([
                "login" => $newInfo["login"],
                "password" => $newInfo["password"],
                "naissance" => empty($newInfo["naissance"]) ? NULL : $newInfo["naissance"],
                "bio" => empty($newInfo["bio"]) ? NULL : $newInfo["bio"],
                "email" => empty($newInfo["email"]) ? NULL : $newInfo["email"],
                "id" => $id
            ]);
            if ($success) {
                $request = "SELECT * FROM utilisateurs WHERE login = ?;";
                $stmt = $db->prepare($request);
                $stmt->execute([$newInfo["login"]]);
                $results = $stmt->fetchAll();

                // Mise à jour de la session
                $_SESSION["user"] = $results[0];
                extract($_SESSION["user"]);
            } else {
                echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
            }
        }
    }
?>

<title>Modification du profil</title>
<h1>Modification du profil</h1>
<a href="index.php">Retour</a>

<?php
    if (isset($error)) {
        echo "<h4 class='error'>$error</h4>";
    }
    if (isset($success) && $success) {
        echo "<h4 class='success'>Modifications enregistrées avec succès !</h4>";
    }
?>
<form method="post">
    <label for="login">Login</label>
    <input type="text" name="login" required minlength="3" maxlength="255" value="<?= $login ?? '' ?>">

    <label for="password">Mot de passe</label>
    <input type="password" name="password" placeholder="Optionnel" minlength="3" maxlength="255">

    <label for="passwordConfirm">Mot de passe (confirmation)</label>
    <input type="password" name="passwordConfirm" placeholder="Optionnel" minlength="3" maxlength="255">

    <br/>
    <label for="email">E-mail</label>
    <input type="email" name="email" maxlength="255" placeholder="exemple@gmail.com" value="<?= $email ?>">

    <label for="naissance">Date de naissance</label>
    <input type="date" name="naissance" max="<?= date('Y-m-d', strtotime("-13 years")) ?>" value="<?= $naissance ?>">

    <br/>
    <label for="bio">Biographie</label>
    <br/>
    <textarea name="bio" maxlength="255"><?= $bio ?></textarea>
    <br/>

    <input type="submit" value="Enregistrer">
</form>