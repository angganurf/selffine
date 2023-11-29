<?php 

function SendPushNotification($data = array(), $push_type = 'chat') {
    global $sqlConnect,$config;
    if (empty($data)) {
        return false;
    }
    if (empty($data['notification']['notification_content'])) {
        return false;
    }
    if (empty($data['send_to'])) {
        return false;
    }
    if ($config['push'] == 0) {
        return false;
    }
    $app_id = $config['push_id'];
    $app_key = $config['push_key'];

    // $client = new HttpClient(new GuzzleAdapter($guzzle), new GuzzleMessageFactory());
    // $config_data = $One_config;
    // $api = new OneSignal($config_data, $client);
    $final_request_data = array(
        'app_id' => $app_id,
        'include_player_ids' => $data['send_to'],
        'send_after' => new \DateTime('1 second'),
        'isChrome' => false,
        'contents' => array(
            'en' => $data['notification']['notification_content']
        ),
        'headings' => array(
            'en' => $data['notification']['notification_title']
        ),
        'android_led_color' => 'FF0000FF',
        'priority' => 10
    );
    if (!empty($data['notification']['notification_data'])) {
        $final_request_data['data'] = $data['notification']['notification_data'];
    }
    if (!empty($data['notification']['notification_image'])) {
        $final_request_data['large_icon'] = $data['notification']['notification_image'];
    }
    // $send_notification = $api->notifications->add($final_request_data);
    // if ($send_notification['id']) {
    //     return $send_notification['id'];
    // }
    $fields = json_encode($final_request_data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                               'Authorization: Basic '.$app_key));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response);
    if (!empty($response) && !empty($response->id) && $response->id) {
        return $response->id;
    }
    return false;
}
?>