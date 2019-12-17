<?php
    session_start();

    if (isset($_SESSION["user"])) {
        extract($_SESSION["user"]);
    }

    $db = new PDO("mysql:host=localhost;dbname=forum", "root", "");

    $request = "SELECT * FROM topics";
    $stmt = $db->prepare($request);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<title>Index</title>
<h1>Index</h1>

<?php
    if (isset($_SESSION["user"])) { ?>
        <a href="deconnexion.php">Déconnexion</a>
        <br/>
        <a href="modifier_profil.php">Modifier profil</a>
        <br/>
        <a href="profil.php?id=<?= $id ?>">Mon profil</a>
    <?php } else { ?>
        <a href="connexion.php">Connexion</a>
        <br/>
        <a href="inscription.php">Inscription</a>
    <?php }

    foreach ($results as $topic) {
        if ($topic["rang_min"] <= $id_rang) { ?>
            <article style="margin: 1em 0; padding: 0 0.5em; border: 1px solid black; border-radius: 2px;">
                <h1><a href="topic.php?id=<?= $topic['id'] ?>"><?= $topic["nom"] ?></a></h1>
                <p><?= $topic["description"] ?? "" ?></p>
            </article>
    <?php }
    }
?>