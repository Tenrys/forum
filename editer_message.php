<?php
include "includes/layout.php";

session_start();

layout(function() {
	include "includes/shortcuts.php";

	if (!isset($_SESSION["user"]) || !isset($_GET["id"])) {
		home();
	}

	$title = "Nouveau topic";

	if (is_numeric($_GET["id"])) {
		$stmt = $db->prepare("SELECT * FROM messages WHERE id = ?");
		$stmt->execute([$_GET["id"]]);
		$message = $stmt->fetch();
		if (!$message) {
			home();
		}

		if ($message["id_auteur"] != $_SESSION["user"]["id"] && !isModerator()) {
			home();
		}

		$stmt = $db->prepare("SELECT * from messages WHERE id_conversation = ?");
		$stmt->execute([$message["id_conversation"]]);
		$firstMessage = $stmt->fetch();

		$stmt = $db->prepare("SELECT * from conversations WHERE id = ?");
		$stmt->execute([$message["id_conversation"]]);
		$conversation = $stmt->fetch();

		$title = "Edition de message";

		if (count($_POST) > 0) {
			if ($firstMessage["id"] == $message["id"]) {
				$stmt = $db->prepare("UPDATE conversations
				SET nom = :nom
				WHERE id = :id_conversation");
				$success = $stmt->execute([
					"nom" => $_POST["nom"],
					"id_conversation" => $message["id_conversation"],
				]);
			}

			$stmt = $db->prepare("UPDATE messages
			SET contenu = :contenu
			WHERE id = :id");
			$success = $stmt->execute([
				"contenu" => $_POST["contenu"],
				"id" => $message["id"],
			]);

			header("Location: conversation.php?id={$message['id_conversation']}#{$message['id']}");
			die;
		}
	} else if ($_GET["id"] == "new" && is_numeric($_GET["id_topic"])) {
		$stmt = $db->prepare("SELECT * FROM topics WHERE id = ?");
		$success = $stmt->execute([$_GET["id_topic"]]);
		$topic = $stmt->fetch();
		if (!$topic || $topic["rang_min"]) {
			home();
		}

		if (count($_POST) > 0) {
			$stmt = $db->prepare("INSERT INTO conversations (nom, id_auteur, id_topic) VALUES (:nom, :id_auteur, :id_topic)");
			$success = $stmt->execute([
				"nom" => $_POST["nom"],
				"id_auteur" => $_SESSION["user"]["id"],
				"id_topic" => $_GET["id_topic"],
			]);
			$conversationId = $db->lastInsertId();

			$stmt = $db->prepare("INSERT INTO messages (contenu, id_auteur, id_conversation) VALUES (:contenu, :id_auteur, :id_conversation)");
			$success = $stmt->execute([
				"contenu" => $_POST["contenu"],
				"id_auteur" => $_SESSION["user"]["id"],
				"id_conversation" => $conversationId,
			]);
			$messageId = $db->lastInsertId();

			header("Location: conversation.php?id={$conversationId}#{$messageId}");
			die;
		}
	} else {
		home();
	}
?>

<header>
	<h1><?= $title ?></h1>
</header>

<?php
	if (isset($error)) {
		echo "<h4 class='error'>$error</h4>";
	}
?>

<form method="post">
	<?php if ($_GET["id"] == "new" || isset($message) && isset($firstMessage) && $firstMessage["id"] == $message["id"]) { ?>
		<label for="nom">Nom du topic</label>
		<input type="text" name="nom" required maxlength="255" value="<?= isset($conversation["nom"]) ? $conversation["nom"] : "" ?>">
		<br/>
	<?php } ?>

	<textarea required name="contenu" placeholder="Bla bla bla..." rows="5"><?= isset($message) ? $message["contenu"] : "" ?></textarea><br/>

	<input class="button" type="submit" value="<?= $_GET['id'] == 'new' ? 'Créer' : 'Modifier' ?>">
</form>
<?php }, [ "title" => $title ]); ?>
