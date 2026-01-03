<?php
require_once 'include/functions.inc.php';
$title = 'Page Technique'; // Titre spécifique à cette page

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

// Configuration de la clé API NASA
$nasa_api_key = 'sQrYscRypEDQdbNimn9re7e3S67PReq3mwISsVaO'; 
$nasa_data = getNasaApod($nasa_api_key);

// Récupération des informations de géolocalisation
$ip = $_SERVER['REMOTE_ADDR'];
$geo_xml = getGeoDataXml($ip);
$ipinfo_data = getIpInfoData($ip);
$whatismyip_xml = getWhatIsMyIpData($ip);
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
        <!-- Contenu de la page technique -->
        <div class="section">
            <h2>NASA - Image du Jour (APOD)</h2>
            <?php if (isset($nasa_data['error']) && $nasa_data['error']): ?>
                <p>Erreur : <?= htmlspecialchars($nasa_data['message']) ?></p>
            <?php else: ?>
                <?php if (isset($nasa_data['media_type']) && $nasa_data['media_type'] === 'video'): ?>
                    <!-- Intégration de la vidéo -->
                    <iframe src="<?= htmlspecialchars($nasa_data['url'] ?? '') ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen 
                            class="nasa-image">
                    </iframe>
                <?php elseif (isset($nasa_data['media_type']) && $nasa_data['media_type'] === 'image'): ?>
                    <!-- Affichage de l'image -->
                    <img src="<?= htmlspecialchars($nasa_data['url'] ?? '') ?>" 
                         alt="<?= htmlspecialchars($nasa_data['title'] ?? 'Image NASA') ?>" 
                         class="nasa-image">
                <?php else: ?>
                    <p>Type de contenu non pris en charge ou données manquantes.</p>
                <?php endif; ?>

                <!-- Affichage des informations supplémentaires -->
                <h3><?= htmlspecialchars($nasa_data['title'] ?? 'Titre indisponible') ?></h3>
                <p><?= htmlspecialchars($nasa_data['explanation'] ?? 'Description indisponible') ?></p>
            <?php endif; ?>
        </div>

        <!-- Autres sections (géolocalisation, etc.) -->
        <!-- Section GeoPlugin -->
        <div class="section">
            <h2>Informations de Géolocalisation (GeoPlugin)</h2>
            <table>
                <tr>
                    <td>IP</td>
                    <td><?= htmlspecialchars($geo_xml->geoplugin_request ?? 'Inconnue') ?></td>
                </tr>
                <tr>
                    <td>Ville</td>
                    <td><?= htmlspecialchars($geo_xml->geoplugin_city ?? 'Inconnue') ?></td>
                </tr>
                <tr>
                    <td>Pays</td>
                    <td><?= htmlspecialchars($geo_xml->geoplugin_countryName ?? 'Inconnu') ?></td>
                </tr>
                <tr>
                    <td>Latitude</td>
                    <td><?= htmlspecialchars($geo_xml->geoplugin_latitude ?? 'Inconnue') ?></td>
                </tr>
                <tr>
                    <td>Longitude</td>
                    <td><?= htmlspecialchars($geo_xml->geoplugin_longitude ?? 'Inconnue') ?></td>
                </tr>
            </table>
        </div>

        <!-- Section IPInfo -->
        <div class="section">
            <h2>Informations de Géolocalisation (IPInfo)</h2>
            <table>
                <tr>
                    <td>IP</td>
                    <td><?= htmlspecialchars($ipinfo_data['ip'] ?? 'Inconnue') ?></td>
                </tr>
                <tr>
                    <td>Ville</td>
                    <td><?= htmlspecialchars($ipinfo_data['city'] ?? 'Inconnue') ?></td>
                </tr>
                <tr>
                    <td>Région</td>
                    <td><?= htmlspecialchars($ipinfo_data['region'] ?? 'Inconnue') ?></td>
                </tr>
                <tr>
                    <td>Pays</td>
                    <td><?= htmlspecialchars($ipinfo_data['country'] ?? 'Inconnu') ?></td>
                </tr>
                <tr>
                    <td>Coordonnées</td>
                    <td><?= htmlspecialchars($ipinfo_data['loc'] ?? 'Inconnues') ?></td>
                </tr>
            </table>
        </div>

        <!-- Section WhatIsMyIP -->
        <div class="section">
            <h2>Informations de Géolocalisation (WhatIsMyIP)</h2>
            <?php if ($whatismyip_xml): ?>
                <table>
                    <tr>
                        <td>IP</td>
                        <td><?= htmlspecialchars($whatismyip_xml->server_data->ip ?? 'Inconnue') ?></td>
                    </tr>
                    <tr>
                        <td>Ville</td>
                        <td><?= htmlspecialchars($whatismyip_xml->server_data->city ?? 'Inconnue') ?></td>
                    </tr>
                    <tr>
                        <td>Région</td>
                        <td><?= htmlspecialchars($whatismyip_xml->server_data->region ?? 'Inconnue') ?></td>
                    </tr>
                    <tr>
                        <td>Pays</td>
                        <td><?= htmlspecialchars($whatismyip_xml->server_data->country ?? 'Inconnu') ?></td>
                    </tr>
                    <tr>
                        <td>Code Postal</td>
                        <td><?= htmlspecialchars($whatismyip_xml->server_data->postalcode ?? 'Inconnu') ?></td>
                    </tr>
                    <tr>
                        <td>Latitude</td>
                        <td><?= htmlspecialchars($whatismyip_xml->server_data->latitude ?? 'Inconnue') ?></td>
                    </tr>
                    <tr>
                        <td>Longitude</td>
                        <td><?= htmlspecialchars($whatismyip_xml->server_data->longitude ?? 'Inconnue') ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <p>Erreur : Les données de l'API WhatIsMyIP ne sont pas disponibles.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php include 'include/footer.inc.php'; ?>
</body>
</html>
