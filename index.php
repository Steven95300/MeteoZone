<?php


require_once 'include/functions.inc.php';

// Charger les données des fichiers CSV
$regions = readCsvFile('csv/regions.csv');
$departements = readCsvFile('csv/departments.csv');
$cities = readCsvFile('csv/cities.csv');

// Déterminer la localisation de l'utilisateur via son IP avec IPInfo
$ip = $_SERVER['REMOTE_ADDR'];
$geo_data = getIpInfoData($ip);
$user_city = strtolower($geo_data['city'] ?? 'paris'); // Ville par défaut : Paris


function formatCityName($city) {
    return ucwords(str_replace('-', ' ', $city));
}

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

// Déterminer le dossier en fonction du mode
$imageFolder = $mode === 'night' ? 'imgNuit' : 'imgJour';

// Explorer le dossier et récupérer les fichiers d'image
$images = array_filter(scandir($imageFolder), function ($file) use ($imageFolder) {
    $filePath = $imageFolder . DIRECTORY_SEPARATOR . $file;
    return is_file($filePath) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
});

// Sélectionner une image aléatoire si des fichiers sont disponibles
$randomImage = null;
if (!empty($images)) {
    $randomImage = $images[array_rand($images)];
}

// Si une recherche est effectuée
$searched_city = isset($_GET['city']) ? strtolower(str_replace(' ', '-', $_GET['city'])) : null; // Remplacer les espaces par des tirets
$searched_city_display = $searched_city ? formatCityName($searched_city) : null;
if ($searched_city) {
    setcookie('last_city', json_encode([
        'city' => $searched_city,
        'date' => date('Y-m-d H:i:s')
    ]), time() + (86400 * 30), "/"); // Cookie valable 30 jours
}

// Récupérer les sélections de l'utilisateur pour les listes déroulantes
$selectedCity = isset($_GET['city_dropdown']) ? strtolower(str_replace(' ', '-', $_GET['city_dropdown'])) : null; // Remplacer les espaces par des tirets
$selectedCity_display = $selectedCity ? formatCityName($selectedCity) : null;
if ($selectedCity) {
    setcookie('last_city', json_encode([
        'city' => $selectedCity,
        'date' => date('Y-m-d H:i:s')
    ]), time() + (86400 * 30), "/"); // Cookie valable 30 jours
}

// Récupérer la météo pour la ville de l'utilisateur
$default_weather = getWeather($user_city);

// Si les données météo pour la localisation par défaut ne sont pas disponibles, utiliser "Paris"
if (!$default_weather || !isset($default_weather['current_condition'])) {
    $user_city = 'paris';
    $default_weather = getWeather($user_city);
}

// Si une recherche est effectuée
if ($searched_city) {
    logCityConsultation($searched_city);
}

$searched_weather = $searched_city ? getWeather($searched_city) : null;

// Récupérer les sélections de l'utilisateur pour les listes déroulantes
$selectedRegion = $_GET['region'] ?? null;
$selectedDepartement = $_GET['departement'] ?? null;

// Si une ville est sélectionnée via la liste déroulante, récupérer les prévisions météo
$dropdown_weather = null;
if ($selectedCity) {
    $dropdown_weather = getWeather($selectedCity);
    if (!$dropdown_weather || !isset($dropdown_weather['current_condition'])) {
        $dropdown_weather = null;
    } else {
        logCityConsultation($selectedCity); // Ajoute la ville dans les stats
    }
}
?>
<?php include 'include/header.inc.php'; ?>
<div class="container">
    <!-- Formulaire de recherche -->
    <div class="search-form">
        <form method="GET" action="#weather">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>"> <!-- Conserve le mode actuel -->
            <input type="text" name="city" placeholder="Rechercher une ville..." required>
            <button type="submit">Rechercher</button>
        </form>
    </div>


    <div id="weather" class="meteo-block">
        <?php if ($searched_city && $searched_weather): ?>

            <h2>Météo pour <?= htmlspecialchars($searched_city_display ?? ucfirst($user_city)) ?></h2>
            <?php if (isset($searched_weather['current_condition'])): ?>
                <img src="<?= htmlspecialchars($searched_weather['current_condition']['icon']) ?>" alt="Icône météo" style="width: 50px; height: 50px;">
                <p>Température actuelle : <?= htmlspecialchars($searched_weather['current_condition']['tmp']) ?>°C</p>
                <p>Condition : <?= htmlspecialchars($searched_weather['current_condition']['condition']) ?></p>
                <p>Humidité : <?= htmlspecialchars($searched_weather['current_condition']['humidity']) ?>%</p>
                <p>Vent : <?= htmlspecialchars($searched_weather['current_condition']['wnd_spd']) ?> km/h</p>
                <h3>Prévisions pour les jours suivants :</h3>
                <ul>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <?php if (isset($searched_weather["fcst_day_$i"])): ?>
                            <li>
                                <strong><?= htmlspecialchars($searched_weather["fcst_day_$i"]['day_long']) ?> :</strong>
                                <?= htmlspecialchars($searched_weather["fcst_day_$i"]['condition']) ?>,
                                Température : min <?= htmlspecialchars($searched_weather["fcst_day_$i"]['tmin']) ?>°C - max <?= htmlspecialchars($searched_weather["fcst_day_$i"]['tmax']) ?>°C
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            <?php else: ?>
                <p>Erreur : Données indisponibles pour <?= htmlspecialchars($searched_city_display) ?>.</p>
            <?php endif; ?>
        <?php elseif ($dropdown_weather): ?>

            <h2>Météo pour <?= htmlspecialchars($selectedCity_display) ?></h2>
            <?php if (isset($dropdown_weather['current_condition'])): ?>
                <img src="<?= htmlspecialchars($dropdown_weather['current_condition']['icon']) ?>" alt="Icône météo" style="width: 50px; height: 50px;">
                <p>Température actuelle : <?= htmlspecialchars($dropdown_weather['current_condition']['tmp']) ?>°C</p>
                <p>Condition : <?= htmlspecialchars($dropdown_weather['current_condition']['condition']) ?></p>
                <p>Humidité : <?= htmlspecialchars($dropdown_weather['current_condition']['humidity']) ?>%</p>
                <p>Vent : <?= htmlspecialchars($dropdown_weather['current_condition']['wnd_spd']) ?> km/h</p>
                <h3>Prévisions pour les jours suivants :</h3>
                <ul>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <?php if (!empty($dropdown_weather["fcst_day_$i"])): ?>
                            <li>
                                <strong><?= htmlspecialchars($dropdown_weather["fcst_day_$i"]['day_long']) ?> :</strong>
                                <?= htmlspecialchars($dropdown_weather["fcst_day_$i"]['condition']) ?>,
                                Température : min <?= htmlspecialchars($dropdown_weather["fcst_day_$i"]['tmin']) ?>°C - max <?= htmlspecialchars($dropdown_weather["fcst_day_$i"]['tmax']) ?>°C
                            </li>
                        <?php else: ?>
                            <li>Prévision indisponible pour le jour <?= $i ?>.</li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            <?php else: ?>
                <p>Erreur : Données indisponibles pour <?= htmlspecialchars($selectedCity_display) ?>.</p>
            <?php endif; ?>
        <?php else: ?>
  
            <h2>Météo actuelles de votre ville : <?= htmlspecialchars(ucfirst($user_city)) ?></h2>
            <?php if (!$default_weather): ?>
                <p>Les données météo sont temporairement indisponibles. Veuillez réessayer plus tard.</p>
            <?php elseif (isset($default_weather['current_condition'])): ?>
                <img src="<?= htmlspecialchars($default_weather['current_condition']['icon']) ?>" alt="Icône météo" style="width: 50px; height: 50px;">
                <p>Température actuelle : <?= htmlspecialchars($default_weather['current_condition']['tmp']) ?>°C</p>
                <p>Condition : <?= htmlspecialchars($default_weather['current_condition']['condition']) ?></p>
                <p>Humidité : <?= htmlspecialchars($default_weather['current_condition']['humidity']) ?>%</p>
                <p>Vent : <?= htmlspecialchars($default_weather['current_condition']['wnd_spd']) ?> km/h</p>
                <h3>Prévisions pour les jours suivants :</h3>
                <ul>
                    <?php for ($i = 1; $i <= 3; $i++): ?>
                        <?php if (isset($default_weather["fcst_day_$i"])): ?>
                            <li>
                                <strong><?= htmlspecialchars($default_weather["fcst_day_$i"]['day_long']) ?> :</strong>
                                <?= htmlspecialchars($default_weather["fcst_day_$i"]['condition']) ?>,
                                Température : min <?= htmlspecialchars($default_weather["fcst_day_$i"]['tmin']) ?>°C - max <?= htmlspecialchars($default_weather["fcst_day_$i"]['tmax']) ?>°C
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            <?php else: ?>
                <p>Erreur : Données indisponibles pour <?= htmlspecialchars(ucfirst($user_city)) ?>.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Affichage de la dernière ville consultée -->
    <div id="last-city" class="meteo-block">
        <h2>Dernière ville consultée</h2>
        <?php if (isset($_COOKIE['last_city'])): ?>
            <?php 
            $last_city_data = json_decode($_COOKIE['last_city'], true); 
            $last_city_name = htmlspecialchars(ucfirst($last_city_data['city'] ?? 'Inconnue'));
            $last_city_date = htmlspecialchars($last_city_data['date'] ?? 'Inconnue');
            ?>
            <p><strong>Ville :</strong> <?= $last_city_name ?></p>
            <p><strong>Date de consultation :</strong> <?= $last_city_date ?></p>
        <?php else: ?>
            <p>Aucune ville consultée récemment.</p>
        <?php endif; ?>
    </div>

    <!-- Carte interactive -->
    <div class="map-container" id="map">
        <img src="img/carteFr.jpg" usemap="#image-map" alt="Carte de France">

        <map name="image-map">
            <area target="" alt="haut de france" title="haut de france" href="?region=32&mode=<?= htmlspecialchars($mode) ?>#weather" coords="575,15,514,34,504,121,524,155,524,187,524,220,547,219,589,232,620,231,645,259,656,209,684,198,686,174,694,138,690,99,653,81,629,58,616,50" shape="poly">
            <area target="" alt="normandie" title="normandie" href="?region=28&mode=<?= htmlspecialchars($mode) ?>#weather" coords="264,153,305,160,312,193,389,205,423,191,416,162,461,140,499,125,518,172,499,232,452,280,448,326,412,296,374,286,317,284,287,275" shape="poly">
            <area target="" alt="ile de france" title="ile de france" href="?region=11&mode=<?= htmlspecialchars($mode) ?>#weather" coords="515,218,504,241,512,280,535,301,537,318,567,318,572,336,608,331,613,315,637,308,646,287,641,266,618,242" shape="poly">
            <area target="" alt="grand est" title="grand est" href="?region=44&mode=<?= htmlspecialchars($mode) ?>#weather" coords="741,116,706,136,688,205,664,220,659,241,654,283,645,310,660,335,692,356,732,352,758,371,771,384,808,385,845,343,876,352,927,396,953,388,947,336,973,258,984,232,924,215,865,188,800,180,755,158" shape="poly">
            <area target="" alt="bretagne" title="bretagne" href="?region=53&mode=<?= htmlspecialchars($mode) ?>#weather" coords="38,283,44,266,77,254,100,252,130,256,139,239,175,243,194,276,230,266,274,273,286,292,302,285,313,341,293,360,245,374,204,396,153,373,106,350,71,348,48,321" shape="poly">
            <area target="" alt="pays de la loire" title="pays de la loire" href="?region=52&mode=<?= htmlspecialchars($mode) ?>#weather" coords="321,288,353,295,391,303,449,332,451,358,431,385,404,397,387,442,318,458,337,527,273,525,238,484,222,468,223,433,200,415,234,390,273,373,306,369" shape="poly">
            <area target="" alt="centre de val de loire" title="centre de val de loire" href="?region=24&mode=<?= htmlspecialchars($mode) ?>#weather" coords="500,248,465,271,461,300,452,324,455,348,447,370,420,391,399,421,406,455,434,461,482,523,548,523,573,503,611,484,611,446,596,423,602,381,614,354,605,334,576,341,533,321,506,281" shape="poly">
            <area target="" alt="bourgogne franche comté" title="bourgogne franche comté" href="?region=27&mode=<?= htmlspecialchars($mode) ?>#weather" coords="614,318,628,310,642,313,654,337,677,361,706,363,728,360,749,378,781,398,811,384,831,367,846,358,868,363,887,370,906,391,904,407,895,428,886,448,878,457,854,494,838,526,811,530,777,515,761,511,745,543,726,533,698,547,669,501,641,489,620,489" shape="poly">
            <area target="" alt="nouvelle aquitaine" title="nouvelle aquitaine" href="?region=75&mode=<?= htmlspecialchars($mode) ?>#weather" coords="328,464,345,530,332,539,300,543,302,579,290,601,283,712,235,855,266,890,309,905,341,920,360,877,362,843,348,827,353,804,375,788,429,781,451,740,493,673,542,681,569,632,565,590,577,564,564,540,520,527,473,529,446,492,415,468,375,450" shape="poly">
            <area target="" alt="auvergne rhones alpes" title="auvergne rhones alpes" href="?region=84&mode=<?= htmlspecialchars($mode) ?>#weather" coords="567,522,589,513,589,503,618,492,647,501,666,519,681,552,722,557,751,552,759,524,784,526,803,539,827,541,838,554,881,533,902,537,928,650,865,669,800,745,818,759,804,772,766,741,748,756,694,750,675,697,635,687,609,716,593,692,548,721,541,692,580,637,585,579" shape="poly">
            <area target="" alt="occitanie" title="occitanie" href="?region=76&mode=<?= htmlspecialchars($mode) ?>#weather" coords="495,681,512,688,530,688,539,722,564,730,576,718,583,701,599,706,609,725,627,717,635,698,645,704,664,706,681,730,692,758,726,765,747,777,740,820,713,842,683,843,647,874,610,903,620,960,580,975,530,973,423,917,417,933,361,928,346,913,370,863,359,829,359,808,384,791,434,784,452,753" shape="poly">
            <area target="" alt="provence alpes cote d'azur" title="provence alpes cote d'azur" href="?region=93&mode=<?= htmlspecialchars($mode) ?>#weather" coords="750,760,778,768,816,778,830,757,808,743,832,729,853,700,874,695,869,677,898,668,934,702,919,721,927,758,969,772,984,770,945,823,901,869,830,885,784,858,713,850,743,812,755,789" shape="poly">
        </map>
    </div>

    <!-- Sélection via listes déroulantes -->
    <div class="dropdowns">
        <form method="GET" action="#weather">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>"> 
            <label for="region">Région :</label>
            <select name="region" id="region" onchange="this.form.submit()">
                <option value="">-- Sélectionnez une région --</option>
                <?php
                if (($handle = fopen('csv/regions.csv', 'r')) !== false) {
                    fgetcsv($handle); 
                    while (($row = fgetcsv($handle)) !== false) {
                        echo '<option value="' . htmlspecialchars($row[1]) . '"' .
                            (isset($_GET['region']) && $_GET['region'] == $row[1] ? ' selected' : '') . '>' .
                            htmlspecialchars($row[2]) . '</option>';
                    }
                    fclose($handle);
                }
                ?>
            </select>
        </form>

        <?php if (isset($_GET['region'])): ?>
            <form method="GET" action="#weather">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>"> 
                <input type="hidden" name="region" value="<?= htmlspecialchars($_GET['region']) ?>">
                <label for="departement">Département :</label>
                <select name="departement" id="departement" onchange="this.form.submit()">
                    <option value="">-- Sélectionnez un département --</option>
                    <?php
                    if (($handle = fopen('csv/departments.csv', 'r')) !== false) {
                        fgetcsv($handle); 
                        while (($row = fgetcsv($handle)) !== false) {
                            if ($row[1] == $_GET['region']) { 
                                echo '<option value="' . htmlspecialchars($row[2]) . '"' .
                                    (isset($_GET['departement']) && $_GET['departement'] == $row[2] ? ' selected' : '') . '>' .
                                    htmlspecialchars($row[3]) . '</option>';
                            }
                        }
                        fclose($handle);
                    }
                    ?>
                </select>
            </form>
        <?php endif; ?>

        <?php if (isset($_GET['departement'])): ?>
            <form method="GET" action="#weather">
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>"> 
                <input type="hidden" name="region" value="<?= htmlspecialchars($_GET['region']) ?>">
                <input type="hidden" name="departement" value="<?= htmlspecialchars($_GET['departement']) ?>">
                <label for="city_dropdown">Ville :</label>
                <select name="city_dropdown" id="city_dropdown" onchange="this.form.submit()">
                    <option value="">-- Sélectionnez une ville --</option>
                    <?php
                    if (($handle = fopen('csv/cities.csv', 'r')) !== false) {
                        fgetcsv($handle); 
                        while (($row = fgetcsv($handle)) !== false) {
                            if ($row[1] == $_GET['departement']) { 
                                echo '<option value="' . htmlspecialchars(str_replace(' ', '-', $row[5])) . '"' . // Remplacer les espaces par des tirets
                                    (isset($_GET['city_dropdown']) && $_GET['city_dropdown'] == str_replace(' ', '-', $row[5]) ? ' selected' : '') . '>' .
                                    htmlspecialchars($row[4]) . '</option>'; // Afficher la 4ème colonne
                            }
                        }
                        fclose($handle);
                    }
                    ?>
                </select>
            </form>
        <?php endif; ?>
    </div>

    <!-- Image aléatoire -->
    <?php if ($randomImage): ?>
        <aside>
            <figure>
                <img src="<?= htmlspecialchars($imageFolder . '/' . $randomImage) ?>" alt="Image aléatoire">
            </figure>
        </aside>
    <?php endif; ?>
</div>
<?php include 'include/footer.inc.php'; ?>