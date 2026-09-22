<?php

$grafanaUrl = 'http://192.168.102.129:3000';
$dashboardUid = [
    'ahlsell' => 'pQgw1ofVk',
];

// Put a NEW Grafana service-account token here.
// The token previously used in this file should be revoked because it was shared publicly.
$authToken = 'REPLACE_WITH_NEW_GRAFANA_TOKEN';
$filePath = __DIR__;

$yaml = yaml_parse_file($filePath . '/artNr.yaml');
if ($yaml === false || !is_array($yaml)) {
    throw new RuntimeException('Could not parse artNr.yaml');
}

function getGarafanaData($grafanaUrl, $dashboardUid, $authToken)
{
    $url = rtrim($grafanaUrl, '/') . '/api/dashboards/uid/' . rawurlencode($dashboardUid);
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $authToken,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        echo "Grafana cURL error for dashboard {$dashboardUid}: {$error}" . PHP_EOL;
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo "Grafana API returned HTTP {$httpCode} for dashboard {$dashboardUid}." . PHP_EOL;
        echo "Response: {$response}" . PHP_EOL;
        return null;
    }

    $data = json_decode($response);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Invalid JSON from Grafana: ' . json_last_error_msg() . PHP_EOL;
        return null;
    }

    if (!isset($data->dashboard) || !isset($data->dashboard->panels) || !is_array($data->dashboard->panels)) {
        echo "Grafana response for {$dashboardUid} does not contain dashboard panels." . PHP_EOL;
        return null;
    }

    return $data;
}

function updateGrafanaDashboardWithNameTransforms($grafanaUrl, $dashboardUid, $authToken, $yaml)
{
    $grafana = getGarafanaData($grafanaUrl, $dashboardUid, $authToken);
    if ($grafana === null) {
        return;
    }

    $grafanaOriginal = json_encode($grafana);

    foreach ($grafana->dashboard->panels as $id => $panel) {
        $grafana->dashboard->panels[$id]->transformations = makeTransformations($yaml);
    }

    if (md5(json_encode($grafana)) !== md5($grafanaOriginal)) {
        echo "Updating dashboard {$dashboardUid}: ";
        setGrafanaDashboard($grafanaUrl, $dashboardUid, $authToken, $grafana);
        echo PHP_EOL;
    }
}

function setGrafanaDashboard($grafanaUrl, $dashboardUid, $authToken, $data)
{
    $url = rtrim($grafanaUrl, '/') . '/api/dashboards/db';

    // Grafana's GET response includes meta, but the save endpoint expects
    // dashboard + overwrite (and optionally message/folder fields).
    $payload = [
        'dashboard' => $data->dashboard,
        'overwrite' => true,
        'message' => 'Updated article-name transformations',
    ];

    $json = json_encode($payload);
    if ($json === false) {
        echo 'Could not encode Grafana update: ' . json_last_error_msg();
        return false;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $authToken,
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        echo "Grafana cURL error while updating {$dashboardUid}: {$error}";
        return false;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        echo "Grafana update failed with HTTP {$httpCode}. Response: {$response}";
        return false;
    }

    echo "OK (HTTP {$httpCode}) {$response}";
    return true;
}

function makeTransformations($artsAndNames)
{
    $return = [];

    foreach ($artsAndNames as $values) {
        if (!is_array($values) || count($values) === 0) {
            continue;
        }

        $artNr = key($values);
        $name = $values[$artNr];

        $transformation = new stdClass();
        $transformation->id = 'renameByRegex';
        $transformation->options = new stdClass();
        $transformation->options->regex = '.*' . preg_quote((string)$artNr, '/') . '.*';
        $transformation->options->renamePattern = (string)$name;

        $return[] = $transformation;
    }

    return $return;
}

foreach ($yaml as $client => $clientData) {
    if (!isset($dashboardUid[$client])) {
        echo "Skipping Grafana update for '{$client}': no dashboard UID configured." . PHP_EOL;
        continue;
    }

    updateGrafanaDashboardWithNameTransforms(
        $grafanaUrl,
        $dashboardUid[$client],
        $authToken,
        $clientData
    );
}
