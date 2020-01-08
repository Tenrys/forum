<?php
include "includes/layout.php";

session_start();

layout(function() {
	include "includes/shortcuts.php";

	if (isset($_SESSION["user"])) {
		home();
	}

	if (count($_POST) > 0) {
		extract($_POST);

		if ($password == $passwordConfirm) {
			$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE login = ?");
			$stmt->execute([$login]);
			$user = $stmt->fetch();

			if ($user) {
				$stmt = $db->prepare("INSERT INTO utilisateurs (login, password) VALUES (?, ?)");
				$hashed = password_hash($password, PASSWORD_DEFAULT);
				$success = $stmt->execute([$login, $hashed]);
			} else {
				$error = "Cet utilisateur existe déjà !";
			}
		} else {
			$error = "Le mot de passe que vous avez fourni ne correspond pas avec votre confirmation !";
		}
	}
?>

<header>
	<h1>Inscription</h1>
</header>

<?php
	if (isset($error)) {
		echo "<h4 class='error'>$error</h4>";
	}
	if (isset($success) && $success) {
		echo "<h4 class='success'>Compte créé avec succès ! Vous pouvez dorénavant vous connecter...<br>Vous allez être redirigé dans 5 secondes...</h4>";
		header("Refresh: 5; URL=connexion.php");
	} else { ?>
		<form method="post" style="padding: 0 25%;">
			<div class="columns">
				<div class="column">
					<label for="login">Login</label>
					<input type="text" name="login" required minlength="3" maxlength="255" value="<?= $login ?? '' ?>">
				</div>
			</div>
			<div class="columns">
				<div class="column">
					<label for="password">Mot de passe</label>
					<input type="password" name="password" required minlength="3" maxlength="255">
				</div>
				<div class="column">
					<label for="passwordConfirm">Mot de passe (confirmation)</label>
					<input type="password" name="passwordConfirm" required minlength="3" maxlength="255">
				</div>
			</div>

			<div class="columns">
				<div class="column">
					<input class="button" type="submit" value="S'inscrire">
				</div>
			</div>
		</form>
	<?php }
});
?>