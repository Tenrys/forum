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

		$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE login = ?");
		$stmt->execute([$login]);
		$user = $stmt->fetch();

		if ($user && password_verify($password, $user["password"])) {
			$_SESSION["user"] = $user;
			if (isset($_GET["from"])) {
				header("Location: {$_GET['from']}.php");
			} else {
				header("Location: index.php");
			}
			die;
		} else {
			$error = "Mot de passe incorrect !";
		}
	}
?>
	<h1>Connexion</h1>
	<a href="index.php">Retour</a>

	<?php
	if (isset($error)) {
		echo "<h4 class='error'>$error</h4>";
	}
	?>

	<form method="post">
		<label for="login">Login</label>
		<input type="text" name="login" required minlength="3" maxlength="255" value="<?= $login ?? '' ?>">

		<label for="password">Mot de passe</label>
		<input type="password" name="password" required minlength="3" maxlength="255" value="<?= $password ?? '' ?>">

		<input type="submit" value="Se connecter">

	</form>
<?php }); ?>