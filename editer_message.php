<?php
include "includes/layout.php";
include "includes/shortcuts.php";

session_start();

if (!isset($_SESSION["user"]) || !isset($_GET["id"])) {
	home();
}

$title = "Nouvelle conversation";

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
	if (!$topic || $topic["rang_min"] > $_SESSION["user"]["id_rang"]) {
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

layout(function() {
	global $title, $message, $firstMessage, $conversation;
?>

<header class="flex-center">
	<h1><?= $title ?></h1>
</header>

<form method="post" style="padding: 0 25%;">
	<div class="columns">
		<div class="column">
			<?php if ($_GET["id"] == "new" || isset($message) && isset($firstMessage) && $firstMessage["id"] == $message["id"]) { ?>
				<label for="nom">Nom de la conversation</label>
				<input type="text" name="nom" required maxlength="255" value="<?= isset($conversation["nom"]) ? $conversation["nom"] : "" ?>">
			<?php } ?>
		</div>
	</div>

	<div class="columns">
		<div class="column">
			<textarea required name="contenu" placeholder="Bla bla bla..." rows="5"><?= isset($message) ? $message["contenu"] : "" ?></textarea>
		</div>
	</div>

	<div class="columns">
		<div class="column">
			<input class="button" type="submit" value="<?= $_GET['id'] == 'new' ? 'Créer' : 'Modifier' ?>">
		</div>
	</div>
</form>
<?php }, [ "title" => $title ]); ?>
