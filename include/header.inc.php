<?php
$title = $title ?? 'MétéoZone'; // Titre par défaut
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="style.css"> 
</head>
<body class="<?= $mode ?>">
<a href="?mode=<?= $mode === 'day' ? 'night' : 'day' ?>" class="mode-toggle">
    Passer en mode <?= $mode === 'day' ? 'nuit' : 'jour' ?>
</a>
<header style="display: flex; flex-direction: column; align-items: center; background-color: rgba(66, 66, 66, 0.2); color: white; padding: 20px;">
    <div style="display: flex; align-items: center;">
        <a href="index.php" style="text-decoration: none; margin-right: 10px;">
            <img src="img/logo.png" alt="Logo" style="width: 100px; height: 100px;">
        </a>
        <h1 style="margin: 0; font-size: 3em; font-weight: bold;"><?= htmlspecialchars($title) ?></h1> 
    </div>
    <nav style="margin-top: 30px;">
        <ul style="list-style: none; display: flex; padding: 0; margin: 0;">
            <li style="margin-right: 50px;">
                <a href="index.php" style="text-decoration: none; color: white; font-size: 1.2em; font-weight: normal;">Accueil</a>
            </li>
            <li style="margin-right: 50px;">
                <a href="stat.php" style="text-decoration: none; color: white; font-size: 1.2em; font-weight: normal;">Page Statistique</a>
            </li>
            <li>
                <a href="tech.php" style="text-decoration: none; color: white; font-size: 1.2em; font-weight: normal;">Page Technique</a>
            </li>
        </ul>
    </nav>
</header>
</body>
</html>
