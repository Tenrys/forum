<?php
    include "includes/db.php";

    session_start();

    if (isset($_SESSION["user"])) {
        extract($_SESSION["user"]);
    }

    if (isset($_POST["action"]) && isModerator()) {
        switch ($_POST["action"]) {
            case "delete":
                if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
                    $stmt = $db->prepare("DELETE FROM topics WHERE id = ?");
                    $success = $stmt->execute([$_POST["id"]]);
                    if (!$success) {
                        echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
                        die;
                    }
                }
                break;
        }
    }

    $request = "SELECT * FROM topics";
    $stmt = $db->prepare($request);
    $stmt->execute();
    $results = $stmt->fetchAll();
?>

<title>Forum</title>
<h1>Forum</h1>

<?php
    if (isset($_SESSION["user"])) { ?>
        <a href="deconnexion.php">Déconnexion</a>
        <br/>
        <a href="modifier_profil.php">Modifier profil</a>
        <br/>
        <a href="profil.php?id=<?= $id ?>">Mon profil</a>
    <?php } else { ?>
        <a href="connexion.php">Connexion</a>
        <br/>
        <a href="inscription.php">Inscription</a>
    <?php }

    if (isModerator()) {
        echo "<br/><br/>";
        echo "<a href='nouveau_topic.php'>Nouveau topic</a>";
    }

    foreach ($results as $topic) {
        if ($topic["rang_min"] <= ($id_rang ?? 0)) { ?>
            <article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px;">
                <h1><a href="topic.php?id=<?= $topic['id'] ?>"><?= $topic["nom"] ?></a></h1>
                <p><?= $topic["description"] ?? "" ?></p>
                <?php if (isModerator()) { ?>
                    <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce topic?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $topic['id'] ?>">
                        <input type="submit"value="Supprimer">
                    </form>
                <?php } ?>
            </article>
    <?php }
    }
?>