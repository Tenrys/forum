<?php
include "includes/layout.php";

session_start();

layout(function() {
	include "includes/shortcuts.php";

	if (!isset($_SESSION["user"])) {
		home();
	}

	extract($_SESSION["user"]);

	if (count($_POST) > 0) {
		$newInfo = &$_POST;

		if (isset($newInfo["password"]) && $newInfo["password"] != "") {
			if ($newInfo["password"] != $newInfo["passwordConfirm"]) {
				$error = "Le mot de passe que vous avez fourni ne correspond pas avec votre confirmation !";
			}
		} else {
			$newInfo["password"] = password_hash($password, PASSWORD_DEFAULT);
		}

		if (!isset($error)) {
			$stmt = $db->prepare("UPDATE utilisateurs
			SET login = :login, password = :password, naissance = :naissance, bio = :bio, email = :email
			WHERE id = :id");
			$success = $stmt->execute([
				"login" => $newInfo["login"],
				"password" => $newInfo["password"],
				"naissance" => empty($newInfo["naissance"]) ? NULL : $newInfo["naissance"],
				"bio" => empty($newInfo["bio"]) ? NULL : $newInfo["bio"],
				"email" => empty($newInfo["email"]) ? NULL : $newInfo["email"],
				"id" => $_SESSION["user"]["id"]
			]);

			$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE login = ?");
			$stmt->execute([$newInfo["login"]]);
			$user = $stmt->fetch();

			// Mise à jour de la session
			$_SESSION["user"] = $user;
			extract($_SESSION["user"]);
		}
	}
?>

<header class="flex-center">
	<h1>Modification du profil</h1>
</header>

<?php
	if (isset($error)) {
		echo "<h4 class='flex-center error'>$error</h4>";
	}
	if (isset($success) && $success) {
		echo "<h4 class='flex-center success'>Modifications enregistrées avec succès !</h4>";
	}
?>
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
				<input type="password" name="password" placeholder="Optionnel" minlength="3" maxlength="255">
			</div>
			<div class="column">
				<label for="passwordConfirm">Mot de passe (confirmation)</label>
				<input type="password" name="passwordConfirm" placeholder="Optionnel" minlength="3" maxlength="255">
			</div>
		</div>

		<div class="columns">
			<div class="column">
				<label for="email">E-mail</label>
				<input type="email" name="email" maxlength="255" placeholder="exemple@gmail.com" value="<?= $email ?>">
			</div>
		</div>

		<div class="columns">
			<div class="column">
				<label for="naissance">Date de naissance</label>
				<input type="date" name="naissance" max="<?= date('Y-m-d', strtotime("-13 years")) ?>" value="<?= $naissance ?>">
			</div>
		</div>

		<div class="columns">
			<div class="column">
				<label for="bio">Biographie</label>
				<textarea name="bio" maxlength="255" rows="4"><?= $bio ?></textarea>
			</div>
		</div>

		<div class="columns">
			<div class="column">
				<input class="button" type="submit" value="Enregistrer">
			</div>
		</div>
	</form>
<?php }); ?>