<?php

/**
 * This example script covers the basic daily queue integration workflow.
 * See http://serpmetrics.com/docs/ for more details.
 */

include('smapi.php');
$smapi = new SMapi(array('key'=>'YOUR_API_KEY','secret'=>'YOUR_API_SECRET'));


// add a keyword to the queue with multiple engine_code's
$res = $smapi->add('flights to nyc', array('google_en-us','bing_en-us'));
if ($res['status'] == 'ok') {
    $keyword_id = $res['data']['keyword_id'];
    // save to db
}


// add multiple keywords on a single engine_code
$res = $smapi->add(array('flights to nyc','flights to jfk'), 'google_en-us');
if ($res['status'] == 'ok') {
    foreach ($res['data'] as $r) {
        if ($r['response']['status'] == 'ok') {
            $keyword_id = $r['response']['data']['keyword_id'];
            $phrase = $r['response']['data']['keyword'];
            // save to db
        }
    }
}


// check a keyword pair for new data
$res = $smapi->check($keyword_id, 'google_en-us');
if ($res['status'] == 'ok') {
    foreach ($res['data'] as $timestamp => $check_id) {
        // check each against db for unprocessed serps
    }
}


// pull serp data for a check_id found above
$res = $smapi->serp($check_id);
if ($res['status'] == 'ok') {
    foreach($res['data']['results'] as $pos => $result) {
        // process position / result data
    }
}
