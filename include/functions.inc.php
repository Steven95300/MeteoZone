<?php

/**
 * Récupère les statistiques des villes consultées.
 *
 * @return array Un tableau associatif où les clés sont les noms des villes et les valeurs sont le nombre de consultations.
 */
function getCityStatistics() {
    $filePath = 'csv/cities_consulted.csv'; // Chemin du fichier CSV
    $statistics = [];

    if (file_exists($filePath)) {
        $data = array_map('str_getcsv', file($filePath));
        array_shift($data); // Supprimer les en-têtes

        foreach ($data as $row) {
            $city = $row[0];
            if (!isset($statistics[$city])) {
                $statistics[$city] = 0;
            }
            $statistics[$city]++;
        }
    }

    arsort($statistics); // Trier par nombre de consultations
    return $statistics;
}

    function logCityConsultation($cityName) {
        $cityName = str_replace(' ', '-', $cityName); 
        $filePath = 'csv/cities_consulted.csv'; 
        $date = date('Y-m-d H:i:s'); // Date et heure de la consultation

        // Vérifier si le fichier existe, sinon ajouter les en-têtes
        $fileExists = file_exists($filePath);
        $handle = fopen($filePath, 'a');
        if (!$handle) {
            error_log("Impossible d'ouvrir le fichier : $filePath");
            return;
        }

        if (!$fileExists) {
            fputcsv($handle, ['city', 'date']); // En-têtes du fichier CSV
        }

        // Ajouter la ville et la date dans le fichier
        fputcsv($handle, [$cityName, $date]);
        fclose($handle);
    }

    // Fonction pour lire un fichier CSV 
    function readCsvFile($filepath) {
        $data = [];
        if (($handle = fopen($filepath, "r")) !== false) {
            $headers = fgetcsv($handle); 
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    // Fonction pour récupérer la météo d'une ville
    function getWeather($city) {
        $url = "https://www.prevision-meteo.ch/services/json/" . urlencode($city); 
        $response = @file_get_contents($url); 

        if ($response === false) {
            error_log("Erreur : Impossible de se connecter à l'API météo pour la ville : $city");
            return [
                'error' => true,
                'message' => "Impossible de récupérer les données météo pour la ville : $city. Vérifiez votre connexion ou l'URL de l'API."
            ];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erreur JSON : " . json_last_error_msg());
            return [
                'error' => true,
                'message' => "Erreur lors du décodage des données JSON : " . json_last_error_msg()
            ];
        }

        return $data;
    }

    // Fonction pour récupérer les données de géolocalisation via GeoPlugin (JSON)
    function getGeoDataJson($ip) {
        $geo_url = "http://www.geoplugin.net/json.gp?ip={$ip}";
        return json_decode(file_get_contents($geo_url), true);
    }

    // Fonction pour récupérer les données de géolocalisation via GeoPlugin (XML)
    function getGeoDataXml($ip) {
        $geo_url = "http://www.geoplugin.net/xml.gp?ip={$ip}";
        return simplexml_load_file($geo_url);
    }

    // Fonction pour récupérer les données de géolocalisation via IPInfo
    function getIpInfoData($ip) {
        $ipinfo_url = "https://ipinfo.io/{$ip}/geo";
        $response = @file_get_contents($ipinfo_url);

        if ($response === false) {
            error_log("Erreur : Impossible de se connecter à l'API IPInfo pour l'IP : $ip");
            return [
                'city' => 'paris', // Valeur par défaut si l'API échoue
            ];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Erreur JSON : " . json_last_error_msg());
            return [
                'city' => 'paris', // Valeur par défaut si le JSON est invalide
            ];
        }

        return $data;
    }

    // Fonction pour récupérer les données de géolocalisation via WhatIsMyIP
    function getWhatIsMyIpData($ip) {
        $whatismyip_url = "https://api.whatismyip.com/ip-address-lookup.php?key=70b331416b68597415a7c1e811075724&input={$ip}&output=xml";
        $response = @file_get_contents($whatismyip_url);

        if ($response === false) {
            error_log("Erreur : Impossible de se connecter à l'API WhatIsMyIP pour l'IP : $ip");
            return null; // Retourner null si l'API échoue
        }

        if (strpos($response, '<') === false) {
            error_log("Erreur : Réponse invalide de l'API WhatIsMyIP pour l'IP : $ip");
            return null; // Retourner null si la réponse n'est pas valide
        }

        return simplexml_load_string($response);
    }



    function getNasaApod($api_key) {
        // Date actuelle
        $today = date('Y-m-d');

        // Utiliser la dernière date valide si la date actuelle dépasse la limite
        $date_to_use = $today;

        
        $nasa_url = "https://api.nasa.gov/planetary/apod?api_key={$api_key}&date={$date_to_use}";

        $response = @file_get_contents($nasa_url);

        if ($response === false) {
            return [
                'error' => true,
                'message' => "Impossible de récupérer les données de l'API NASA. URL : {$nasa_url}"
            ];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => true,
                'message' => "Erreur lors du décodage des données JSON de l'API NASA : " . json_last_error_msg()
            ];
        }

        // Vérifier si le type de contenu est pris en charge
        if (!isset($data['media_type']) || !in_array($data['media_type'], ['image', 'video'])) {
            return [
                'error' => true,
                'message' => "Type de contenu non pris en charge par l'API NASA. Données reçues : " . print_r($data, true)
            ];
        }

        return $data;
    }



?>