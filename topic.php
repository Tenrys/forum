<?php
include "includes/layout.php";

session_start();

layout(function() {
	include "includes/shortcuts.php";

	if (isset($_SESSION["user"])) {
		extract($_SESSION["user"]);
	}

	if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
		if (isset($_POST["supprimer"]) && isModerator()) {
			$stmt = $db->prepare("DELETE FROM conversations WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
			if (!$success) {
				echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
				die;
			}
		}

		if (isset($_POST["verrouiller"]) && isModerator()) {
			$stmt = $db->prepare("UPDATE conversations SET verrouillage = NOT verrouillage WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
			if (!$success) {
				echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
				die;
			}
		}

		if (isset($_POST["epingler"]) && isModerator()) {
			$stmt = $db->prepare("UPDATE conversations SET epingle = NOT epingle WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
			if (!$success) {
				echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
				die;
			}
		}
	}

	$stmt = $db->prepare("SELECT * FROM topics WHERE id = :id");
	$success = $stmt->execute(["id" => $_GET["id"]]);
	$topic = $stmt->fetch();

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

<h1><?= $topic["nom"] ?></h1>
<a href="index.php">Retour</a>

<?php
	if (isset($_SESSION["user"])) { ?>
		<br/><br/>
		<a href='editer_message.php?id=new&id_topic=<?= $topic['id'] ?>'>Nouvelle conversation</a>
	<?php }

	if (count($conversations) > 0) {
		foreach ($conversations as $conversation) { ?>
			<article>
				<h1><a href="conversation.php?id=<?= $conversation['id'] ?>"><?= $conversation["nom"] ?></a><?= $conversation["verrouillage"] ? " (verrouillé)" : "" ?></h1>
				<p>Créé par <code><?= $conversation["nom_auteur"] ?></code> le <code><?= $conversation["creation"] ?></code></p>
				<?php if (isModerator()) { ?>
					<form method="post" >
						<input type="hidden" name="id" value="<?= $conversation['id'] ?>">
						<input type="submit" name="supprimer" value="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette conversation?');">
						<input type="submit" name="verrouiller" value="<?= $conversation['verrouillage'] ? 'Déverrouiller' : 'Verrouiller' ?>">
						<input type="submit" name="epingler" value="<?= $conversation['epingle'] ? 'Désépingler' : 'Épingler' ?>">
					</form>
				<?php } ?>
			</article>
		<?php }
	} else {
		echo "<p>Aucune conversation n'a été créée pour le moment...</p>";
	}
}, [ "title" => $topic["nom"] ]);
?>