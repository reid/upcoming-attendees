<?php

function stop ($msg) {
    header("HTTP/1.1 500 Internal Server Error");
    echo $msg;
    exit(1);
}

if (!isset($_GET['event'])) stop('No event ID');
$event = $_GET['event'];

$types = array('attend');
if (isset($_GET['types'])) $types = explode(',', $_GET['types']);

function upcoming_attendees_html($event, $types = array('attend')) {
    $yql = "http://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20xml%20where%20url%20%3D%20%22http%3A%2F%2Fupcoming.yahoo.com%2Fajax%2Fevent_page_all_attendees.php%3Fevent_id%3D$event%22%3B&format=json&env=http%3A%2F%2Fdatatables.org%2Falltables.env&callback=";

    $json = file_get_contents($yql);
    $json = json_decode($json);

    if (!$json || !is_object($json)) stop('Bad YQL response');

    $html = '';
    foreach ($types as $type) $html .= $json->query->results->rsp->$type;
    if (!$html) stop('Bad YQL data structure');

    return html_entity_decode($html);
}

$html = upcoming_attendees_html($event, $types);

preg_match_all('/property="vcard.*href="(.*)".*\>(.*)\<\/a/', $html, $matches);

$people = array_combine($matches[1], $matches[2]);

$response = array(
    'event' => $event,
    'types' => $types,
    'people' => $people
);

echo json_encode($response);
