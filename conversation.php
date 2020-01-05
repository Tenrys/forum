<?php
    include "includes/db.php";

    session_start();

    if (isset($_SESSION["user"])) {
        extract($_SESSION["user"]);
    }

	$request = "SELECT * FROM conversations WHERE id = ?";
    $stmt = $db->prepare($request);
    $success = $stmt->execute([$_GET["id"]]);
	$conversation = $stmt->fetch();

	if (!isset($conversation) || count($conversation) < 1) {
		header("Location: index.php");
		die;
	}

	if (isset($_POST["action"])) {
        switch ($_POST["action"]) {
            case "delete":
                if (isset($_POST["id"]) && is_numeric($_POST["id"]) && isModerator()) {
                    $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
                    $success = $stmt->execute([$_POST["id"]]);
                    if (!$success) {
                        echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
                        die;
                    }
                }
                break;
            case "new":
                $stmt = $db->prepare("INSERT INTO messages (contenu, id_auteur, id_conversation) VALUES (?, ?, ?)");
                $success = $stmt->execute([$_POST["contenu"], $_SESSION["user"]["id"], $conversation["id"]]);
                if (!$success) {
                    echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
                    die;
                }
                break;
        }
    }

	if (isset($_POST["like"]) || isset($_POST["dislike"])) {
		if (!isset($_SESSION["user"])) {
			header("Location: connexion.php");
			die;
		} else {
			$type = isset($_POST["like"]) ? 1 : 0;
			$params = [
				"id" => $_POST["id"],
				"id_utilisateur" => $_SESSION["user"]["id"],
				"type" => $type,
			];

			// Si on a déjà donné une réaction contraire au message, on la supprime
			$db->prepare("DELETE FROM reactions WHERE id_utilisateur = :id_utilisateur AND id_message = :id AND type = :other_type")->execute([
				"id" => $_POST["id"],
				"id_utilisateur" => $_SESSION["user"]["id"],
				"other_type" => !$type,
			]);

			$stmt= $db->prepare("SELECT * FROM reactions WHERE id_utilisateur = :id_utilisateur AND id_message = :id AND type = :type");
			$success = $stmt->execute($params);
			if (!$success) {
				echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
				die;
			}
			$results = $stmt->fetch();
			// Est-ce que l'on a déjà donné cette réaction ?
			if ($results) {
				// Si oui, on la supprime.
				$db->prepare("DELETE FROM reactions WHERE id_utilisateur = :id_utilisateur AND id_message = :id AND type = :type")->execute($params);
			} else {
				// Si non, on la rajoute.
				$db->prepare("INSERT INTO reactions (id_utilisateur, id_message, type) VALUES (:id_utilisateur, :id, :type)")->execute($params);
			}
		}
	}

	$request = "SELECT
		messages.*,
		utilisateurs.login AS nom_auteur,
		(SELECT COUNT(*) FROM reactions WHERE id_message = messages.id AND type = 0) AS dislikes,
		(SELECT COUNT(*) FROM reactions WHERE id_message = messages.id AND type = 1) AS likes
	FROM messages
	INNER JOIN utilisateurs ON utilisateurs.id = messages.id_auteur
	WHERE id_conversation = ?
	ORDER BY creation ASC";
    $stmt = $db->prepare($request);
    $success = $stmt->execute([$_GET["id"]]);
	$results = $stmt->fetchAll();

	if (count($results) < 1) {
		header("Location: index.php");
		die;
	}
?>

<title><?= $conversation["nom"] ?></title>
<h1><?= $conversation["nom"] ?></h1>
<a href="topic.php?id=<?= $conversation["id_topic"] ?>">Retour</a>

<?php
	foreach ($results as $message) { ?>
		<article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px;">
			<p><b><?= $message["nom_auteur"] ?></b>&nbsp;<code><?= $message["creation"] ?></code></p>
			<hr/>
			<p><?= $message["contenu"] ?></p>
			<hr/>
			<div class="actions">
				<form method="post">
					<input type="hidden" name="id" value="<?= $message['id'] ?>">
					<ul>
						<li><input type="submit" name="like" value="<?= $message["likes"] ?> 👍"></li>
						<li><input type="submit" name="dislike" value="<?= $message["dislikes"] ?> 👎"></li>
					</ul>
				</form>
				<ul>
				<?php
					if ($message["id_auteur"] == $_SESSION["user"]["id"] || isModerator()) { ?>
						<li><a href='modifier_message.php?id=<?= $message['id'] ?>'>Modifier</a></li>
						<li>
							<form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message?');">
								<input type="hidden" name="action" value="delete">
								<input type="hidden" name="id" value="<?= $message['id'] ?>">
								<input type="submit" value="Supprimer">
							</form>
						</li>
					<?php }
				?>
				</ul>
			</div>
		</article>
	<?php }

	if (!$conversation["verrouillage"] && isset($_SESSION["user"]) || isModerator()) { ?>
		<article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px;">
			<p><b>Nouveau message</b></p>
			<hr/>
			<form method="post">
				<input type="hidden" name="action" value="new">
				<textarea required name="contenu" placeholder="Bla bla bla..." style="width: 100%;" rows="5"></textarea><br/>
				<input type="submit" value="Envoyer">
			</form>
		</article>
	<?php }
?>
