<?php
    include "includes/db.php";

    session_start();

    if (!isModerator()) {
        header("Location: index.php");
        die;
    }

    if (count($_POST) > 0) {
        extract($_POST);

        $stmt = $db->prepare("INSERT INTO topics (nom, description, rang_min) VALUES (:nom, :description, :rang_min)");
        $success = $stmt->execute($_POST);
        if ($success) {
            header("Location: index.php");
            die;
        } else {
            echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
            die;
        }
    }
?>

<title>Nouveau topic</title>
<h1>Nouveau topic</h1>
<a href="index.php">Retour</a>

<?php
if (isset($error)) {
    echo "<h4 class='error'>$error</h4>";
}
?>
<form method="post">
    <label for="nom">Nom du topic</label>
    <input type="text" name="nom" required minlength="3" maxlength="255" value="<?= $nom ?? '' ?>">

    <label for="description">Description</label>
    <input type="text" name="description" placeholder="Optionnel" maxlength="255" value="<?= $description ?? '' ?>">

    <label for="rang_min">Rang minimum</label>
    <sub>Pour visibilité et accès</sub>
    <select name="rang_min">
        <option value='0'>Aucun</option>
        <?php foreach ($rankNames as $k => $v) {
            echo "<option value='$k'>$v</option>";
        } ?>
    </select>

    <input type="submit" value="Créer">
</form>
