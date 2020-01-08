<?php

include "includes/layout.php";

layout(function() {
	include "includes/shortcuts.php";

	if (!isset($_GET["id"])) {
		home();
	}

	session_start();

	if (isset($_SESSION["user"])) {
		extract($_SESSION["user"]);
	}

	if (isset($_POST["rang"]) && is_numeric($_POST["rang"]) && $_POST["rang"] <= 3 && $_POST["rang"] >= 1 && $id_rang >= 3) {
		$stmt = $db->prepare("UPDATE utilisateurs SET id_rang = ? WHERE id = ?");
		$editSuccess = $stmt->execute([$_POST["rang"], $_GET["id"]]);
		if (!$editSuccess) {
			echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
			die;
		}
	}

	$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
	$success = $stmt->execute([$_GET["id"]]);
	$user = $stmt->fetch();
	if (!$success) {
		echo "Erreur MySQL: {$stmt->errorInfo()[2]}";
		die;
	}

	if (!$user) {
		home();
	}
?>

<h1>Profil de <?= $user["login"] ?></h1>
<a href="index.php">Retour</a>

<?php
	if (isset($user)) {
		if (isset($editSuccess) && $editSuccess) {
			echo "<h4 class='success'>Modifications enregistrées avec succès !</h4>";
		}

		echo "<h2>{$user["login"]}</h2>";

		if ($id_rang >= 3 && $user["id"] != $_SESSION["user"]["id"]) { ?>
			<form method="post">
				<select name="rang" onchange="this.form.submit()">
				<?php foreach ($rankNames as $k => $v) {
					echo "<option value='$k'" . $user['id_rang'] == $k ? ' selected' : '' . ">$v</option>";
				} ?>
				</select>
			</form>
<?php   } else {
			echo "<h3>" . ($rankNames[$user["id_rang"]] ?? 'Rang inconnu') . "</h3>";
		}

		if (isset($user["email"])) {
			echo "<p><b>E-mail</b>: <code>{$user["email"]}</code></p>";
		}

		if (isset($user["naissance"])) {
			echo "<p><b>Né(e) le</b>: <code>{$user["naissance"]}</code></p>";
		}

		if (isset($user["bio"])) { ?>
			<p><b>Biographie</b>:</p>
			<p>
				<code><?= $user['bio'] ?></code>
			</p>
<?php   }

		echo "<p><b>Inscrit le</b>: <code>{$user["inscription"]}</code></p>";

		if ($user["id"] == $_SESSION["user"]["id"]) {
			echo "<a href='modifier_profil.php'>Modifier</a>";
		}
	}
}, [ "title" => "Profil de {$user['login']}" ]);
?>