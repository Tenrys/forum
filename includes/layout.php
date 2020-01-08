<?php

$titles = [
	"connexion" => "Connexion au forum",
	"inscription" => "Inscription au forum",
	"index" => "Page d'acceuil du forum",
	"modifier_profil" => "Modification de votre profil",
	"nouveau_topic" => "Création d'un nouveau topic",
];

function layout($render, $options = []) {
	global $titles;
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link rel="stylesheet" href="style.css">
	<title><?= $titles[basename(parse_url($_SERVER["SCRIPT_NAME"], PHP_URL_PATH), ".php")] ?? $options["title"] ?? "???" ?></title>
</head>
<body>
	<nav>

	</nav>
	<?= $render() ?>
	<footer>
	</footer>
</body>
</html>

<?php
}

?>