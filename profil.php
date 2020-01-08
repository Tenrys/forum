<?php

include "includes/layout.php";
include "includes/shortcuts.php";

if (!isset($_GET["id"])) {
	home();
}

session_start();

if (isset($_POST["rang"]) && is_numeric($_POST["rang"]) && $_POST["rang"] <= 3 && $_POST["rang"] >= 1 && $id_rang >= 3) {
	$stmt = $db->prepare("UPDATE utilisateurs SET id_rang = ? WHERE id = ?");
	$editSuccess = $stmt->execute([$_POST["rang"], $_GET["id"]]);
}

$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$success = $stmt->execute([$_GET["id"]]);
$user = $stmt->fetch();

if (!$user) {
	home();
}

layout(function() {
	global $db, $user, $rankNames;
?>

<div class="flex-center">
	<section class="profile">
		<header>
			<h1>Profil de <code><?= $user["login"] ?></code></h1>
		</header>

<?php
	if (isset($user)) {
		if (isset($editSuccess) && $editSuccess) {
			echo "<h4 class='success'>Modifications enregistrées avec succès !</h4>";
		}

		if (isset($_SESSION["user"]) && $_SESSION["user"]["id_rang"] >= 3 && $user["id"] != $_SESSION["user"]["id"]) { ?>
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

		echo "<p><b>Inscrit le</b>: <code>{$user["inscription"]}</code></p>";

		if (isset($user["naissance"])) {
			echo "<p><b>Né(e) le</b>: <code>{$user["naissance"]}</code></p>";
		}

		if (isset($user["bio"])) { ?>
			<p><b>Biographie</b>:</p>
			<p>
				<code><?= $user['bio'] ?></code>
			</p>
<?php   }


		if (isset($_SESSION["user"]) && $user["id"] == $_SESSION["user"]["id"]) {
			echo "<a class='button' href='modifier_profil.php'>Modifier</a>";
		}
	}
?>

	</section>
</div>

<?php
}, [ "title" => "Profil de {$user['login']}" ]);
?>