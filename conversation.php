<?php
    include "includes/shortcuts.php";

    session_start();

    if (isset($_SESSION["user"])) {
        extract($_SESSION["user"]);
    }

	if (isset($_POST["verrouiller"]) && isModerator()) {
		$stmt = $db->prepare("UPDATE conversations SET verrouillage = NOT verrouillage WHERE id = ?");
		$success = $stmt->execute([$_GET["id"]]);
		if (!$success) {
			echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
			die;
		}
	}

	if (isset($_POST["epingler"]) && isModerator()) {
		$stmt = $db->prepare("UPDATE conversations SET epingle = NOT epingle WHERE id = ?");
		$success = $stmt->execute([$_GET["id"]]);
		if (!$success) {
			echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
			die;
		}
	}

    $stmt = $db->prepare("SELECT conversations.*, topics.rang_min
	FROM conversations
	INNER JOIN topics ON topics.id = conversations.id_topic
	WHERE conversations.id = ?");
    $success = $stmt->execute([$_GET["id"]]);
	$conversation = $stmt->fetch();

	if (!isset($conversation) || !$conversation) {
		home();
	}

	if (isset($_POST["supprimer"]) && isset($_POST["id"]) && is_numeric($_POST["id"]) && isModerator()) {
		$stmt = $db->prepare("SELECT * FROM messages WHERE id_conversation = ?");
		$stmt->execute([$conversation["id"]]);
		$firstMessage = $stmt->fetch();
		if ($firstMessage["id"] == $_POST["id"]) {
			$stmt = $db->prepare("DELETE FROM conversations WHERE id = ?");
			$success = $stmt->execute([$conversation["id"]]);
			if (!$success) {
				echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
				die;
			}
		} else {
			$stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
			if (!$success) {
				echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
				die;
			}
		}
	}

	if (
		isset($_POST["nouveau"])
		&& isset($_SESSION["user"])
		&& (
			   ($conversation["rang_min"] <= $_SESSION["user"]["id_rang"] && !$conversation["verrouillage"])
			   || isModerator()
		   )
	) {
		$stmt = $db->prepare("INSERT INTO messages (contenu, id_auteur, id_conversation) VALUES (?, ?, ?)");
		$success = $stmt->execute([$_POST["contenu"], $_SESSION["user"]["id"], $conversation["id"]]);
		if (!$success) {
			echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
			die;
		}
    }

	if (isset($_POST["like"]) || isset($_POST["dislike"])) {
		if (!isset($_SESSION["user"])) {
			header("Location: connexion.php");
			die;
		}

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

    $stmt = $db->prepare("SELECT
		messages.*,
		utilisateurs.login AS nom_auteur,
		(SELECT COUNT(*) FROM reactions WHERE id_message = messages.id AND type = 0) AS dislikes,
		(SELECT COUNT(*) FROM reactions WHERE id_message = messages.id AND type = 1) AS likes
	FROM messages
	INNER JOIN utilisateurs ON utilisateurs.id = messages.id_auteur
	WHERE id_conversation = ?
	ORDER BY creation ASC");
    $success = $stmt->execute([$_GET["id"]]);
	$results = $stmt->fetchAll();

	if (count($results) < 1) {
		home();
	}
?>

<title><?= $conversation["nom"] ?></title>
<h1><?= $conversation["nom"] ?></h1>
<a href="topic.php?id=<?= $conversation["id_topic"] ?>">Retour</a>

<?php
	foreach ($results as $id => $message) { ?>
		<article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px;" id="<?= $id ?>">
			<p><b><?= $message["nom_auteur"] ?></b>&nbsp;<code><?= $message["creation"] ?></code></p>
			<hr/>
			<p><?= $message["contenu"] ?></p>
			<hr/>
			<div class="actions">
				<ul>
					<form method="post">
						<input type="hidden" name="id" value="<?= $message['id'] ?>">
						<li><input type="submit" name="like" value="<?= $message["likes"] ?> 👍"></li>
						<li><input type="submit" name="dislike" value="<?= $message["dislikes"] ?> 👎"></li>
					</form>
				</ul>
				<ul>
					<?php if ((isset($_SESSION["user"]) && $message["id_auteur"] == $_SESSION["user"]["id"]) || isModerator()) { ?>
						<li><a href='editer_message.php?id=<?= $message['id'] ?>'>Modifier</a></li>
						<form method="post">
							<input type="hidden" name="id" value="<?= $message['id'] ?>">
							<li><input type="submit" name="supprimer" value="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer <?= $id == 0 ? 'cette conversation' : 'ce message' ?>?');"></li>
							<?php if ($id == 0 && isModerator()) { ?>
								<li><input type="submit" name="verrouiller" value="<?= $conversation['verrouillage'] ? 'Déverrouiller' : 'Verrouiller' ?>"></li>
								<li><input type="submit" name="epingler" value="<?= $conversation['epingle'] ? 'Désépingler' : 'Épingler' ?>"></li>
							<?php } ?>
						</form>
					<?php } ?>
				</ul>
			</div>
		</article>
	<?php }

	if (!$conversation["verrouillage"] && isset($_SESSION["user"]) || isModerator()) { ?>
		<article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px;">
			<p><b>Nouveau message</b></p>
			<hr/>
			<form method="post">
				<textarea required name="contenu" placeholder="Bla bla bla..." style="width: 100%;" rows="5"></textarea><br/>
				<input type="submit" name="nouveau" value="Envoyer">
			</form>
		</article>
	<?php }
?>
