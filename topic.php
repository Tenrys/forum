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
                    $stmt = $db->prepare("DELETE FROM conversations WHERE id = ?");
                    $success = $stmt->execute([$_POST["id"]]);
                    if (!$success) {
                        echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
                        die;
                    }
                }
                break;
        }
    }

	$request = "SELECT * FROM topics WHERE id = :id";
    $stmt = $db->prepare($request);
    $success = $stmt->execute(["id" => $_GET["id"]]);
	$topic = $stmt->fetch();

	$request = "SELECT conversations.*, utilisateurs.login AS nom_auteur
	FROM conversations INNER JOIN utilisateurs ON utilisateurs.id = conversations.id_auteur
	WHERE id_topic = :id
    ORDER BY epingle DESC, creation DESC";
    $stmt = $db->prepare($request);
    $success = $stmt->execute(["id" => $_GET["id"]]);
	$results = $stmt->fetchAll();
	var_dump($results);
?>

<title><?= $topic["nom"] ?></title>
<h1><?= $topic["nom"] ?></h1>
<a href="index.php">Retour</a>

<?php
    if (count($results) > 0) {
        foreach ($results as $conversation) { ?>
            <article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px; <?= $conversation['epingle'] ? 'background: #00ff0033;' : '' ?>">
                <h1><a href="conversation.php?id=<?= $conversation['id'] ?>"><?= $conversation["nom"] ?></a><?= $conversation["verrouillage"] ? " (verrouillé)" : "" ?></h1>
                <p><code><?= $conversation["creation"] ?></code></p>
                <p>Créé par <code><?= $conversation["nom_auteur"] ?></code></p>
                <?php if (isModerator()) { ?>
                    <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette conversation?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $conversation['id'] ?>">
                        <input type="submit" value="Supprimer">
                    </form>
                <?php } ?>
            </article>
        <?php }
    } else {
        echo "<p>Aucune conversation n'a été créée pour le moment...</p>";
    }
?>