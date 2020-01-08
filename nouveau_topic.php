<?php
include "includes/layout.php";
include "includes/shortcuts.php";

session_start();

layout(function() {
	global $db, $rankNames;

	if (!isModerator()) {
		home();
	}

	if (count($_POST) > 0) {
		extract($_POST);

		$stmt = $db->prepare("INSERT INTO topics (nom, description, rang_min) VALUES (:nom, :description, :rang_min)");
		$success = $stmt->execute([
			"nom" => $_POST["nom"],
			"description" => $_POST["description"],
			"rang_min" => $_POST["rang_min"]
		]);

		home();
	}
?>

<header class="flex-center">
	<h1>Nouveau topic</h1>
</header>

<form method="post" style="padding: 0 25%;">
	<div class="columns">
		<div class="column">
			<label for="nom">Nom du topic</label>
			<input type="text" name="nom" required minlength="3" maxlength="255" value="<?= $nom ?? '' ?>">
		</div>
	</div>

	<div class="columns">
		<div class="column">
			<label for="description">Description</label>
			<input type="text" name="description" placeholder="Optionnel" maxlength="255" value="<?= $description ?? '' ?>">
		</div>
	</div>

	<div class="columns">
		<div class="column">
			<label for="rang_min">Rang minimum</label>
			<select name="rang_min">
				<option value='0'>Aucun</option>
				<?php foreach ($rankNames as $k => $v) {
					echo "<option value='$k'>$v</option>";
				} ?>
			</select>
			<sub>Pour visibilité et accès</sub>
		</div>
	</div>

	<div class="columns">
		<div class="column">
			<input class="button" type="submit" value="Créer">
		</div>
	</div>
</form>
<?php }); ?>
