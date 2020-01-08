<?php
include "includes/layout.php";
include "includes/shortcuts.php";

session_start();

$stmt = $db->prepare("SELECT * FROM topics WHERE id = :id");
$success = $stmt->execute(["id" => $_GET["id"]]);
$topic = $stmt->fetch();

layout(function() {
	global $topic, $db;

	if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
		if (isset($_POST["supprimer"]) && isModerator()) {
			$stmt = $db->prepare("DELETE FROM conversations WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
		}

		if (isset($_POST["verrouiller"]) && isModerator()) {
			$stmt = $db->prepare("UPDATE conversations SET verrouillage = NOT verrouillage WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
		}

		if (isset($_POST["epingler"]) && isModerator()) {
			$stmt = $db->prepare("UPDATE conversations SET epingle = NOT epingle WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
		}
	}

	if ($topic["rang_min"] > ($_SESSION["user"]["id_rang"] ?? 0) && !isModerator()) {
		home();
	}

	$stmt = $db->prepare("SELECT conversations.*, utilisateurs.login AS nom_auteur
	FROM conversations
	INNER JOIN utilisateurs ON utilisateurs.id = conversations.id_auteur
	WHERE id_topic = :id
	ORDER BY epingle DESC, creation DESC");
	$success = $stmt->execute(["id" => $_GET["id"]]);
	$conversations = $stmt->fetchAll();
?>

<header class="flex-center">
	<h1><?= $topic["nom"] ?></h1>
	<?php if (isset($_SESSION["user"])) { ?>
		<a class="absolute-top-right button" href='editer_message.php?id=new&id_topic=<?= $topic['id'] ?>'>Nouvelle conversation</a>
	<?php } ?>
</header>

<?php
	if (count($conversations) > 0) {
		foreach ($conversations as $conversation) { ?>
			<article class="conversation <?= $conversation["epingle"] ? 'pinned' : '' ?>">
				<h1><a href="conversation.php?id=<?= $conversation['id'] ?>"><?= $conversation["nom"] ?></a><?= $conversation["verrouillage"] ? " (verrouillé)" : "" ?></h1>
				<p>Créé par <a href="profil.php?id=<?= $conversation['id_auteur'] ?>"><code><?= $conversation["nom_auteur"] ?></code></a> le <code><?= $conversation["creation"] ?></code></p>
				<?php if (isModerator()) { ?>
					<form method="post" >
						<input type="hidden" name="id" value="<?= $conversation['id'] ?>">
						<input class="button" type="submit" name="supprimer" value="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette conversation?');">
						<input class="button" type="submit" name="verrouiller" value="<?= $conversation['verrouillage'] ? 'Déverrouiller' : 'Verrouiller' ?>">
						<input class="button" type="submit" name="epingler" value="<?= $conversation['epingle'] ? 'Désépingler' : 'Épingler' ?>">
					</form>
				<?php } ?>
			</article>
		<?php }
	} else {
		echo "<p>Aucune conversation n'a été créée pour le moment...</p>";
	}
}, [ "title" => $topic["nom"] ]);
?>