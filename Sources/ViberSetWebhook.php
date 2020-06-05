<?php
if (!defined('SMF'))
    die('Hacking attempt...');

global $context;

loadTemplate('Viber');
$context['sub_template'] = 'manual';

$data['url'] = "https://" . $_SERVER['HTTP_HOST'] . "/Sources/ViberWebhook.php";
$response = viber_request_set('https://chatapi.viber.com/pa/set_webhook', $data);
//var_dump($response);
$status_mess = $response->status_message;
$context['viber_id'] = $status_mess;

function viber_request_set($url, $data = array())
{
    global $modSettings;
    $auth_token = $modSettings['viber_api'];
    if (!isset($data['auth_token'])) {
        $data['auth_token'] = $auth_token;
    }

    $headers = array(
        'Content-Type:application/json'
    );

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    if ($result !== false) {
        $result = json_decode($result);
    }
	$js_json = json_encode($data);
	//echo $js_json;

    curl_close($ch);

    return $result;
}

