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
		}
	}

	$stmt = $db->prepare("SELECT * FROM topics");
	$stmt->execute();
	$topics = $stmt->fetchAll();
?>

<header>
	<h1>Acceuil du forum</h1>
	<?php
		if (isModerator()) {
			echo "<a class='absolute-top-right button' href='nouveau_topic.php'>Nouveau topic</a>";
		}
	?>
</header>

<?php
	echo "<section class='topics'>";
	foreach ($topics as $topic) {
		if ($topic["rang_min"] <= ($id_rang ?? 0)) { ?>
			<article class="topic">
				<h1><a href="topic.php?id=<?= $topic['id'] ?>"><?= $topic["nom"] ?></a></h1>
				<p><?= $topic["description"] ?? "" ?></p>
				<?php if (isModerator()) { ?>
					<form class="absolute-top-right" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce topic?');">
						<input type="hidden" name="id" value="<?= $topic['id'] ?>">
						<input class="button" type="submit" name="supprimer" value="Supprimer">
					</form>
				<?php } ?>
			</article>
<?php   }
	}
	echo "</section>";
});
?>