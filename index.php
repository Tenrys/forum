<?php
include "includes/layout.php";

session_start();

layout(function() {
	include "includes/shortcuts.php";

	if (isset($_SESSION["user"])) {
		extract($_SESSION["user"]);
	}

	if (isset($_POST["supprimer"]) && isModerator()) {
		if (isset($_POST["id"]) && is_numeric($_POST["id"])) {
			$stmt = $db->prepare("DELETE FROM topics WHERE id = ?");
			$success = $stmt->execute([$_POST["id"]]);
			if (!$success) {
				echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
				die;
			}
		}
	}

	$stmt = $db->prepare("SELECT * FROM topics");
	$stmt->execute();
	$topics = $stmt->fetchAll();
?>

<h1>Acceuil du forum</h1>

<?php
	if (isset($_SESSION["user"])) { ?>
		<a href="deconnexion.php">Déconnexion</a>
		<br/>
		<a href="modifier_profil.php">Modifier profil</a>
		<br/>
		<a href="profil.php?id=<?= $_SESSION["user"]["id"] ?>">Mon profil</a>
<?php } else { ?>
		<a href="connexion.php">Connexion</a>
		<br/>
		<a href="inscription.php">Inscription</a>
<?php }

	if (isModerator()) {
		echo "<br/><br/>";
		echo "<a href='nouveau_topic.php'>Nouveau topic</a>";
	}

	foreach ($topics as $topic) {
		if ($topic["rang_min"] <= ($id_rang ?? 0)) { ?>
			<article>
				<h1><a href="topic.php?id=<?= $topic['id'] ?>"><?= $topic["nom"] ?></a></h1>
				<p><?= $topic["description"] ?? "" ?></p>
				<?php if (isModerator()) { ?>
					<form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce topic?');">
						<input type="hidden" name="id" value="<?= $topic['id'] ?>">
						<input type="submit" name="supprimer" value="Supprimer">
					</form>
				<?php } ?>
			</article>
<?php   }
	}
});
?>