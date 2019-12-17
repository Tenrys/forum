<?php
    session_start();

    if (isset($_SESSION["user"])) {
        extract($_SESSION["user"]);
    }

    $db = new PDO("mysql:host=localhost;dbname=forum", "root", "");

	$request = "SELECT * FROM topics WHERE id = :id";
    $stmt = $db->prepare($request);
    $success = $stmt->execute(["id" => $_GET["id"]]);
	$topic = $stmt->fetch(PDO::FETCH_ASSOC);

	$request = "SELECT conversations.*, utilisateurs.login AS nom_auteur
	FROM conversations INNER JOIN utilisateurs ON utilisateurs.id = conversations.id_auteur
	WHERE id_topic = :id
	ORDER BY epingle DESC, creation DESC";
    $stmt = $db->prepare($request);
    $success = $stmt->execute(["id" => $_GET["id"]]);
	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
	var_dump($results);
?>

<title><?= $topic["nom"] ?></title>
<h1><?= $topic["nom"] ?></h1>
<a href="index.php">Retour</a>

<?php
    foreach ($results as $conversation) { ?>
		<article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px; <?= $conversation['epingle'] ? 'background: #00ff0033;' : '' ?>">
			<h1><a href="conversation.php?id=<?= $conversation['id'] ?>"><?= $conversation["nom"] ?></a><?= $conversation["verrouillage"] ? " (verrouillé)" : "" ?></h1>
			<p><code><?= $conversation["creation"] ?></code></p>
			<p>Créé par <code><?= $conversation["nom_auteur"] ?></code></p>
		</article>
    <?php }
?>