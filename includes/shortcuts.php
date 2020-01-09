<?php
	$db = new PDO("mysql:host=127.0.0.1;dbname=forum", "root", "");
	$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	$rankNames = [
		1 => "Membre",
		2 => "Modérateur",
		3 => "Administrateur",
	];

	function isModerator() {
		if (isset($_SESSION)
		&& isset($_SESSION["user"])
		&& isset($_SESSION["user"]["id_rang"])
		&& $_SESSION["user"]["id_rang"] >= 2) {
			return true;
		}
		return false;
	}

	function home() {
		header("Location: index.php");
		die;
	}
?>