<?php
require_once 'include/functions.inc.php';
$title = 'Statistiques des villes les plus consultées'; // Titre spécifique à cette page

// Gestion du mode jour/nuit via cookie
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'] === 'night' ? 'night' : 'day';
    setcookie('theme_mode', $mode, time() + (86400 * 30), "/"); // Cookie valable 30 jours
} elseif (isset($_COOKIE['theme_mode'])) {
    $mode = $_COOKIE['theme_mode'] === 'night' ? 'night' : ($_COOKIE['theme_mode'] === 'day' ? 'day' : null);
    if ($mode === null) {
        setcookie('theme_mode', '', time() - 3600, "/"); // Supprime le cookie si la valeur est invalide
        $mode = 'day'; // Mode par défaut
    }
} else {
    $mode = 'day'; // Mode par défaut
}

// Récupérer les statistiques des villes les plus consultées
$statistics = getCityStatistics();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="icon" type="image/png" href="img/logo.png">
    <link rel="stylesheet" href="style.css"> <!-- Link to the external stylesheet -->
</head>
<body class="<?= $mode ?>">
    <?php include 'include/header.inc.php'; ?>
    <a href="?mode=<?= $mode === 'day' ? 'night' : 'day' ?>" class="mode-toggle">
        Passer en mode <?= $mode === 'day' ? 'nuit' : 'jour' ?>
    </a>
    <div class="container">
        <div class="stat-block">
            <h2>Top des villes consultées</h2>
            <?php if (!empty($statistics)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ville</th>
                            <th>Nombre de consultations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalSearches = 0; // Initialiser le total
                        foreach ($statistics as $city => $count): 
                            $totalSearches += $count; // Ajouter au total
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($city) ?></td>
                                <td><?= htmlspecialchars($count) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong><?= htmlspecialchars($totalSearches) ?></strong></td> <!-- Afficher le total -->
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Aucune donnée disponible pour les statistiques.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'include/footer.inc.php'; ?>
</body>
</html>