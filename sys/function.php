<?php
function pt_push_channel_notifiations($video_id = 0,$type = "added_video") {
    global $config, $db,$me;
    if (IS_LOGGED == false) {
        return false;
    }
    $get_subscribers = $db->where('user_id', $me['user_id'])->get(T_SUBSCRIPTIONS);
    $userIds         = array();
    if (empty($get_subscribers)) {
        return false;
    }
    if ($type == "added_video") {
        $video_uid = $db->where('post_key', $video_id)->getValue(T_VIDEOS, 'id');
    }
    else{
        $video = $db->where('id', $video_id)->getOne(T_POSTS);
        if (empty($video)) {
            return false;
        }
        $video_uid = $video->id;
        $video_id = $video->post_key;
    }
    
    if (empty($video_uid)) {
        return false;
    }
    foreach ($get_subscribers as $key => $subscriber) {
        $userIds[] = "('{$me['user_id']}', '{$subscriber->subscriber_id}', '$video_uid', '{$type}', 'live/{$video_id}', '" . time() . "')";
    }
    $query_implode       = implode(',', $userIds);
    $query_row           = $db->rawQuery("INSERT INTO " . T_NOTIFICATIONS . " (`notifier_id`, `recipient_id`, `text`, `type`, `url`, `time`) VALUES $query_implode");
    if ($query_row) {
        return true;
    }
}
function PT_RunInBackground($data = array()) {
    if (!empty(ob_get_status())) {
        ob_end_clean();
        header("Content-Encoding: none");
        header("Connection: close");
        ignore_user_abort();
        ob_start();
        if (!empty($data)) {
            header('Content-Type: application/json');
            echo json_encode($data);
        }
        $size = ob_get_length();
        header("Content-Length: $size");
        ob_end_flush();
        flush();
        session_write_close();
        if (is_callable('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
    }
}
function PT_GenerateKey($minlength = 20, $maxlength = 20, $uselower = true, $useupper = true, $usenumbers = true, $usespecial = false) {
    $charset = '';
    if ($uselower) {
        $charset .= "abcdefghijklmnopqrstuvwxyz";
    }
    if ($useupper) {
        $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    }
    if ($usenumbers) {
        $charset .= "123456789";
    }
    if ($usespecial) {
        $charset .= "~@#$%^*()_+-={}|][";
    }
    if ($minlength > $maxlength) {
        $length = mt_rand($maxlength, $minlength);
    } else {
        $length = mt_rand($minlength, $maxlength);
    }
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $charset[(mt_rand(0, strlen($charset) - 1))];
    }
    return $key;
}
function PT_Markup($text, $link = true) {
    if ($link == true) {
        $link_search = '/\[a\](.*?)\[\/a\]/i';
        if (preg_match_all($link_search, $text, $matches)) {
            foreach ($matches[1] as $match) {
                $match_decode     = urldecode($match);
                $match_decode_url = $match_decode;
                $count_url        = mb_strlen($match_decode);
                if ($count_url > 50) {
                    $match_decode_url = mb_substr($match_decode_url, 0, 30) . '....' . mb_substr($match_decode_url, 30, 20);
                }
                $match_url = $match_decode;
                if (!preg_match("/http(|s)\:\/\//", $match_decode)) {
                    $match_url = 'http://' . $match_url;
                }
                $text = str_replace('[a]' . $match . '[/a]', '<a href="' . strip_tags($match_url) . '" target="_blank" class="hash" rel="nofollow">' . $match_decode_url . '</a>', $text);
            }
        }
    }
    return $text;
}
function PT_Duration($text) {
    $duration_search = '/\[d\](.*?)\[\/d\]/i';

    if (preg_match_all($duration_search, $text, $matches)) {
        foreach ($matches[1] as $match) {
            $time = explode(":", $match);
            $current_time = ($time[0]*60)+$time[1];
            $text = str_replace('[d]' . $match . '[/d]', '<a  class="hash" href="javascript:void(0)" onclick="go_to_duration('.$current_time.')">' . $match . '</a>', $text);
        }
    }
    return $text;
}
function StartCloudRecording($vendor,$region,$bucket,$accessKey,$secretKey,$cname,$uid,$post_id){
    global $config,$db;
    $post_id = Generic::secure($post_id);

    $_data = [
        'vendor' => $vendor,
        'region' => $region,
        'bucket' => $bucket,
        'accessKey' => $accessKey,
        'secretKey' => $secretKey,
        'cname' => $cname,
        'uid' => $uid,
        'post_id' => $post_id
    ];
    //$db->insert('log',['var' => 'data on start record','val' => json_encode($_data, JSON_PRETTY_PRINT),'time' => time()]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.agora.io/v1/apps/".$config['agora_app_id']."/cloud_recording/acquire");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($config['agora_customer_id'].":".$config['agora_customer_certificate']),'Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS,'{
      "cname": "'.$cname.'",
      "uid": "'.(int)$uid.'",
      "clientRequest":{
      }
    }');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response  = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);
    $resourceId = $data->resourceId;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.agora.io/v1/apps/".$config['agora_app_id']."/cloud_recording/resourceid/".$resourceId."/mode/mix/start");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($config['agora_customer_id'].":".$config['agora_customer_certificate']),'Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS,'{
        "cname":"'.$cname.'",
        "uid":"'.$uid.'",
        "clientRequest":{
            "recordingConfig":{
                "channelType":1,
                "streamTypes":2,
                "audioProfile":1,
                "videoStreamType":1,
                "maxIdleTime":120,
                "transcodingConfig":{
                    "width":480,
                    "height":480,
                    "fps":24,
                    "bitrate":800,
                    "maxResolutionUid":"1",
                    "mixedVideoLayout":1
                    }
                },
            "storageConfig":{
                "vendor":'.$vendor.',
                "region":'.$region.',
                "bucket":"'.$bucket.'",
                "accessKey":"'.$accessKey.'",
                "secretKey":"'.$secretKey.'",
                "fileNamePrefix": [
                    "upload",
                    "videos",
                    "'.date('Y').'",
                    "'.date('m').'"
                ]
            }   
        }
    }');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response  = curl_exec($ch);
    curl_close($ch);
    //$db->insert('log',['var' => 'cur response on start record','val' => $response,'time' => time()]);
    $data = json_decode($response);
    if (!empty($data->sid) && !empty($resourceId)) {
        $db->where('post_id',$post_id)->update(T_POSTS,array('agora_resource_id' => $resourceId,'agora_sid' => $data->sid));
    }
    return true;
}
function StopCloudRecording($data)
{
    global $config,$db;
    if ( empty($data) ||
         $config['agora_live_video'] != 1 || 
         empty($data['resourceId']) || 
         empty($data['sid']) || 
         empty($data['cname']) || 
         empty($data['uid']) || 
         empty($data['post_id'])
        ) {
        return false;
    }
    $post_id = Generic::secure($data['post_id']);

    //$db->insert('log',['var' => 'data on stop record','val' => json_encode($data, JSON_PRETTY_PRINT) ,'time' => time()]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.agora.io/v1/apps/".$config['agora_app_id']."/cloud_recording/resourceid/".$data['resourceId']."/sid/".$data['sid']."/mode/mix/stop");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($config['agora_customer_id'].":".$config['agora_customer_certificate']),'Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS,'{
      "cname": "'.$data['cname'].'",
      "uid": "'.(int)$data['uid'].'",
      "clientRequest":{
      }
    }');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response  = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);

    //$db->insert('log',['var' => 'curl response','val' => $response,'time' => time()]);
    //$db->insert('log',['var' => 'data','val' => json_encode($data, JSON_PRETTY_PRINT),'time' => time()]);

    if (!empty($data) && !empty($data->serverResponse) && !empty($data->serverResponse->fileList)) {
        $db->where('post_id',$post_id)->update(T_POSTS,array('video_location' => $data->serverResponse->fileList));
    }
    return true;
}
function Stream_DeleteVideo($id = 0) {
    global $config, $db;
    if (empty($id)) {
        return false;
    }

    $get_video = $db->where('post_id', $id)->getOne(T_POSTS);
    if (!empty($get_video)) {
        if (strpos($get_video->thumbnail, 'media/upload/photos') !== false) {
            if ($get_video->thumbnail != 'media/upload/photos/thumbnail.jpg') {
                if (file_exists($get_video->thumbnail)) {
                    unlink($get_video->thumbnail);
                }

                if ($config['amazone_s3_2'] == 1) { 
                    $media = new Media();
                    try{
                      $media->streamdeleteFromFTPorS3($get_video->thumbnail);
                    } catch (Exception $e) {

                    }
                }  
            }
            
        }
        

        if (!empty($get_video->video_location)) {
            if (file_exists($get_video->video_location)) {
                unlink($get_video->video_location);
            }
            if ($config['amazone_s3_2'] == 1) { 
                $media = new Media();
                try{
                    $media->streamdeleteFromFTPorS3($get_video->video_location);
                } catch (Exception $e) {

                }
            }
        }

        $posts   = new Posts();
        $posts->setPostId($id);
        $delete = $posts->deletePost();

        if ($delete) {
            return true;
        }
    }
        
    return false;
}

function px_StripSlashes($value) {
    if (version_compare(PHP_VERSION, '7.4.0', '<=')) {
        if (function_exists("get_magic_quotes_gpc") && !get_magic_quotes_gpc()) return $value;
    }
    if (is_array($value)) {
        return array_map('px_StripSlashes', $value);
    } else {
        return stripslashes($value);
    }
}
function IsBanned($value = '') {
    global $mysqli;
    $query_one    = mysqli_query($mysqli, "SELECT COUNT(`id`) as count FROM " . T_BLACKLIST . " WHERE `value` = '{$value}'");
    $fetched_data = mysqli_fetch_assoc($query_one);
    if ($fetched_data['count'] > 0) {
        return true;
    }
    return false;
}
function IsSharedPost($post_id) {
    global $mysqli;
    $query_one    = mysqli_query($mysqli, "SELECT COUNT(`id`) as count FROM " . T_NOTIF . " WHERE `type` = 'shared_your_post' AND `url` LIKE '%/post/{$post_id}'");
    $fetched_data = mysqli_fetch_assoc($query_one);
    if ($fetched_data['count'] > 0) {
        return true;
    }
    return false;
}
function GetSharedPostOwner($post_id) {
    global $mysqli, $db;
    $query_one    = mysqli_query($mysqli, "SELECT `recipient_id` FROM " . T_NOTIF . " WHERE `type` = 'shared_your_post' AND `url` LIKE '%/post/{$post_id}'");
    $fetched_data = mysqli_fetch_assoc($query_one);
    if (!empty($fetched_data) && $fetched_data['recipient_id'] > 0) {
        $user = $db->arrayBuilder()->where('user_id',$fetched_data['recipient_id'])->get(T_USERS,null,array('*'));
        return (isset($user[0])) ? $user[0] : array();
    }
    return '';
}
function RemoveXSS($val) {
    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++) {
        $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
        $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
    }
    $ra =  array('javascript', 'vbscript', 'expression', '<applet', '<meta', '<xml', '<blink', '<link', '<style', '<script', '<embed', '<object', '<iframe', '<frame', '<frameset', '<ilayer', '<layer', '<bgsound', '<title', '<base', 'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $found = true;
    while ($found == true) {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++) {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                    $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
            $val = preg_replace($pattern, $replacement, $val);
            if ($val_before == $val) {
                $found = false;
            }
        }
    }
    return $val;
}
function isVideo($file) {
    return is_file($file) && (0 === strpos(mime_content_type($file), 'video/'));
}
function blog_categories(){
    global $db,$context;
    $lang = $context['language'];//'english';
    $blog_categories = $db->arrayBuilder()->where('ref','blog_categories')->get(T_LANGS,null,array('lang_key',$lang));
    $data = array();
    foreach ($blog_categories as $key => $value) {
        if(isset($value[$lang])) {
            $data[$value['lang_key']] = $value[$lang];
        }
    }
    return $data;
}
function store_categories(){
    global $db,$context;
    $lang = $context['language'];//'english';
    $blog_categories = $db->arrayBuilder()->where('ref','store_categories')->get(T_LANGS,null,array('lang_key',$lang));
    $data = array();
    foreach ($blog_categories as $key => $value) {
        if(isset($value[$lang])) {
            $data[$value['lang_key']] = $value[$lang];
        }
    }
    return $data;
}
function GetBlogArticles() {
    global $sqlConnect;
    $data          = array();
    $query_one     = "SELECT * FROM `".T_BLOG."` ORDER BY `id` DESC";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    while ($fetched_data = mysqli_fetch_assoc($sql_query_one)) {
        $data[] = GetArticle($fetched_data['id']);
    }
    return $data;
}
function GetArticle($page_name) {
    global $sqlConnect;
    if (empty($page_name)) {
        return false;
    }
    $data          = array();
    $page_name     = Generic::secure($page_name);
    $query_one     = "SELECT * FROM `".T_BLOG."` WHERE `id` = '{$page_name}'";
    $sql_query_one = mysqli_query($sqlConnect, $query_one);
    $fetched_data  = mysqli_fetch_assoc($sql_query_one);
    return $fetched_data;
}
function RegisterNewBlogPost($registration_data) {
    global $sqlConnect;
    if (empty($registration_data)) {
        return false;
    }
    $fields = '`' . implode('`, `', array_keys($registration_data)) . '`';
    $data   = '\'' . implode('\', \'', $registration_data) . '\'';
    $query  = mysqli_query($sqlConnect, "INSERT INTO `".T_BLOG."` ({$fields}) VALUES ({$data})");
    if ($query) {
        return true;
    }
    return false;
}
function PublishArticle($id) {
    global $sqlConnect;
    if (!IS_LOGGED) {
        return false;
    }
    $id    = Generic::secure($id);
    $query = mysqli_query($sqlConnect, "UPDATE `".T_BLOG."` SET `posted` = 1 WHERE `id` = {$id}");
    if ($query) {
        return true;
    }
    return false;
}
function UnPublishArticle($id) {
    global $sqlConnect;
    if (!IS_LOGGED) {
        return false;
    }
    $id    = Generic::secure($id);
    $query = mysqli_query($sqlConnect, "UPDATE `".T_BLOG."` SET `posted` = 0 WHERE `id` = {$id}");
    if ($query) {
        return true;
    }
    return false;
}
function DeleteArticle($id, $thumbnail) {
    global $sqlConnect;
    if (!IS_LOGGED) {
        return false;
    }
    $id    = Generic::secure($id);
    $query = mysqli_query($sqlConnect, "DELETE FROM `".T_BLOG."` WHERE `id` = {$id}");
    if ($query) {
        $media = new Media();
        $cthumbnail = str_replace('_image','_image_c',$thumbnail);
        $media->deleteFromFTPorS3($thumbnail);
        if(file_exists($thumbnail)){
            @unlink($thumbnail);
        }
        $media->deleteFromFTPorS3($cthumbnail);
        if(file_exists($cthumbnail)){
            @unlink($cthumbnail);
        }
        return true;
    }
    return false;
}
function LangsNamesFromDB($lang = 'english') {
    global $sqlConnect;
    $data  = array();
    $query = mysqli_query($sqlConnect, "SHOW COLUMNS FROM `".T_LANGS."`");
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        $data[] = $fetched_data['Field'];
    }
    unset($data[0]);
    unset($data[1]);
    unset($data[2]);
    return $data;
}
function GetLangDetails($lang_key = '') {
    global $sqlConnect;
    if (empty($lang_key)) {
        return false;
    }
    $lang_key = Generic::secure($lang_key);
    $data     = array();
    $query    = mysqli_query($sqlConnect, "SELECT * FROM `".T_LANGS."` WHERE `lang_key` = '{$lang_key}'");
    while ($fetched_data = mysqli_fetch_assoc($query)) {
        unset($fetched_data['lang_key']);
        unset($fetched_data['id']);
        unset($fetched_data['ref']);
        $data[] = $fetched_data;
    }
    return $data;
}
function update_store_image_view($id){
    global $db;
    $cookie_value = null;
    if( !in_array( $id, explode( ',', $_COOKIE['store_views'] ) ) ) {
        if (isset($_COOKIE['store_views'])) {
            $cookie_value = $_COOKIE['store_views'] . ',' . $id;
        } else {
            $cookie_value = $id;
        }
    }
    if( NULL !== $cookie_value ){
        $db->where('id', $id)->update(T_STORE, array('views' => $db->inc(1)));
        setcookie("store_views", $cookie_value, time() + (10 * 365 * 24 * 60 * 60), "/");
    }
}
function update_store_image_downloads($id){
    global $db;
    $cookie_value = null;
    if( !in_array( $id, explode( ',', $_COOKIE['store_downloads'] ) ) ) {
        if (isset($_COOKIE['store_downloads'])) {
            $cookie_value = $_COOKIE['store_downloads'] . ',' . $id;
        } else {
            $cookie_value = $id;
        }
    }
    if( NULL !== $cookie_value ){
        $db->where('id', $id)->update(T_STORE, array('downloads' => $db->inc(1)));
        setcookie("store_downloads", $cookie_value, time() + (10 * 365 * 24 * 60 * 60), "/");
    }
}
function is_store_item_purchased($id,$_license){
    global $db, $user,$context;
    $transaction = $db->arrayBuilder()
                      ->where('item_license',$_license)
                      ->where('type', 'store')
                      ->where('user_id', $context['user']['user_id'])
                      ->where('item_store_id', Generic::secure($id))
                      ->getOne(T_TRANSACTIONS);
    if($transaction){
        return true;
    }else{
        return false;
    }
}
function coinpayments_api_call($req = array()) {
    global $db, $user,$context,$config;
    $result = array('status' => 400);

    // Generate the query string
    $post_data = http_build_query($req, '', '&');
    // echo $post_data;
    // echo "<br>";
    // Calculate the HMAC signature on the POST data
    $hmac = hash_hmac('sha512', $post_data, $config['coinpayments_secret']);
    // echo $hmac;
    // exit();

    $ch = curl_init('https://www.coinpayments.net/api.php');
    curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    // Execute the call and close cURL handle
    $data = curl_exec($ch);
    // Parse and return data if successful.

    if ($data !== FALSE) {
        $info = json_decode($data, TRUE);
        if (!empty($info) && !empty($info['result'])) {
            $result = array('status' => 200,
                            'data' => $info['result']);
        }
        else{
            $result['message'] = $info['error'];
        }
    } else {
        $result['message'] = 'cURL error: '.curl_error($ch);
    }
    return $result;
}
function GetSiteHtml($url='')
{
    global $db, $user,$context,$config;
    $ch = curl_init();
    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Mobile Safari/537.36',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => false,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
        CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => $config['site_url'],
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );
    curl_setopt_array( $ch, $options );
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $dom = new domdocument();
    @$dom->loadhtml($data);
    $metas = $dom->getelementsbytagname('meta');
    return $metas;
}
function detect_safe_search($path) {
    global $db, $user,$context,$config;
    $content = '{"requests": [{"image": {"source": {"imageUri": "' . $path . '"}},"features": [{"type": "SAFE_SEARCH_DETECTION","maxResults": 1},{"type": "WEB_DETECTION","maxResults": 2}]}]}';
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://vision.googleapis.com/v1/images:annotate?key=" . $config["vision_api_key"]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Content-Length: " . strlen($content)
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        if (!empty($data->error)) {
            return true;
        }
        if (!empty($data->responses[0]->error)) {
            return true;
        } elseif ($data->responses[0]->safeSearchAnnotation->adult == "LIKELY" || $data->responses[0]->safeSearchAnnotation->adult == "VERY_LIKELY") {
            return false;
        } else {
            return true;
        }
    }
    catch (Exception $e) {
        return true;
    }
}
function SendSMS($to, $message) {
    global $db, $user,$context,$config;
    
    if (empty($to)) {
        throw new Exception('Please add phone number');
    }
    if (empty($message)) {
        throw new Exception('Please add message');
    }
    if ($config['twilio_provider'] == 1) {

        $account_sid = $config['sms_twilio_username'];
        $auth_token  = $config['sms_twilio_password'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.twilio.com/2010-04-01/Accounts/".$account_sid."/Messages.json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "Body=".$message."&From=".$config['sms_twilio_phone']."&To=".$to);
        curl_setopt($ch, CURLOPT_USERPWD, $account_sid . ':' . $auth_token);

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        if (!empty($result)) {
            $result = json_decode($result);
            if ($result->status == 401) {
                throw new Exception($result->message);
            }
            return true;
        }
        else{
            throw new Exception(lang("something_went_wrong_please_try_again_later_"));
        }
    }
    elseif ($config['bulksms_provider'] == 1) {
        $to_ = @explode("+", $to);
        if (empty($to_[1])) {
            throw new Exception('Invalid phone number');
        }
        $messages = json_encode(array(
          array('to'=> $to, 'body'=>$message)
        ));
        $url = 'https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30';
        $username = $config['bulksms_username'];
        $password = $config['bulksms_password'];

        $ch = curl_init( );
        $headers = array(
        'Content-Type:application/json',
        'Authorization:Basic '. base64_encode("$username:$password")
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $messages );
        // Allow cUrl functions 20 seconds to execute
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
        // Wait 10 seconds while trying to connect
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        $server_response = curl_exec( $ch );
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        $curl_info = curl_getinfo( $ch );
        $http_status = $curl_info[ 'http_code' ];
        if ($http_status == 200 || $http_status == 201) {
            return true;
        }
        $server_response = json_decode($server_response);
        throw new Exception($server_response->detail);
        curl_close( $ch );
        return true;
    }
    elseif ($config['messagebird_provider'] == 1) {
        require_once('sys/libs/messagebird/vendor/autoload.php');
        $messageBird = new \MessageBird\Client($config['messagebird_key']);
        $messageB = new MessageBird\Objects\Message;
        $messageB->originator = $config['messagebird_test_phone'];
        $messageB->recipients = array($to);
        $messageB->body = $message;
        $response = $messageBird->messages->create($messageB);
        return true;
    }
    elseif ($config['msg91_provider'] == 1) {
        //Your authentication key
        $authKey = $config['msg91_authKey'];
        //Multiple mobiles numbers separated by comma
        $mobileNumber = $to;
        //Sender ID,While using route4 sender id should be 6 characters long.
        $senderId = rand(111111,999999);
        //Define route
        $route = "4";
        //Prepare you post parameters
        $postData = array(
            'authkey' => $authKey,
            'mobiles' => $mobileNumber,
            'message' => $message,
            'sender' => $senderId,
            'route' => $route
        );
        if (!empty($config['msg91_dlt_id'])) {
            $postData["DLT_TE_ID"] = $config['msg91_dlt_id'];
        }
        //API URL
        $url="http://api.msg91.com/api/sendhttp.php";
        // init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
            //,CURLOPT_FOLLOWLOCATION => true
        ));
        //Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //get response
        $output = curl_exec($ch);
        //Print error if any
        if(curl_errno($ch))
        {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        return true;
    }
    elseif ($config['infobip_provider'] == 1) {
        $sms = '{
                  "messages": [
                    {
                      "destinations": [
                        {
                          "to": "'.$to.'"
                        }
                      ],
                      "from": "'.$config["site_name"].'",
                      "text": "'.$message.'"
                    }
                  ]
                }';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $config["infobip_base_url"].'/sms/2/text/advanced');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sms);

        $headers = array();
        $headers[] = 'Authorization: App '.$config["infobip_api_key"];
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Accept: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        $result = json_decode($result,true);
        if (!empty($result['messages'])) {
            return true;
        }
        else{
            throw new Exception(lang("something_went_wrong_please_try_again_later_"));
        }
    }
}
function GetTiktokVideoDownloadLink($url='')
{
    global $db;

    $result = array('status' => 400,
                    'message' => lang("something_went_wrong_please_try_again_later_"));

    $ch = curl_init();
    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.111 Mobile Safari/537.36',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => false,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
        CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => 'https://www.tiktok.com/',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );
    curl_setopt_array( $ch, $options );
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $srt2 = strval($data);
    for ($i=0; $i < 1000; $i++) { 
        
        $f = strpos($srt2, '<script');
        $s = strpos(substr($srt2, strpos($srt2, '<script')), '>');
        $d = $f + ($s + 1);
        $srt2 = substr($srt2, $d);
        $f = strpos($srt2, '</script>');
        $result = substr($srt2, 0,$f);
        if (!empty(json_decode($result))) {
            $js = json_decode($result,true);
            if (!empty($js['SharingVideoModule']) && !empty($js['SharingVideoModule']['videoData']) && !empty($js['SharingVideoModule']['videoData']['itemInfo']) && !empty($js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']) && !empty($js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']['video']) && !empty($js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']['video']['downloadAddr'])) {
                return array('status' => 200,
                             'video_url' => $js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']['video']['downloadAddr'],
                             'cover' => $js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']['video']['cover'],
                             'id' => $js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']['video']['id'],
                             'title' => $js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']['author']['nickname'],
                             'desc' => $js['SharingVideoModule']['videoData']['itemInfo']['itemStruct']['desc'],
                            );
            }
            
        }

        $srt2 = substr($srt2, ($f + 9));
    }
    return $result;
}
function SaveTiktokVideo($url='')
{
    global $pixelphoto;

    if (!file_exists('media/upload/photos/' . date('Y'))) {
        @mkdir('media/upload/photos/' . date('Y'), 0777, true);
    }
    if (!file_exists('media/upload/photos/' . date('Y') . '/' . date('m'))) {
        @mkdir('media/upload/photos/' . date('Y') . '/' . date('m'), 0777, true);
    }

    if (!file_exists('media/upload/videos/' . date('Y'))) {
        @mkdir('media/upload/videos/' . date('Y'), 0777, true);
    }
    if (!file_exists('media/upload/videos/' . date('Y') . '/' . date('m'))) {
        @mkdir('media/upload/videos/' . date('Y') . '/' . date('m'), 0777, true);
    }

    if (!file_exists('media/upload/files/' . date('Y'))) {
        @mkdir('media/upload/files/' . date('Y'), 0777, true);
    }
    if (!file_exists('media/upload/files/' . date('Y') . '/' . date('m'))) {
        @mkdir('media/upload/files/' . date('Y') . '/' . date('m'), 0777, true);
    }

    $ch = curl_init();
    $headers = array(
        'Range: bytes=0-',
    );
    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_FOLLOWLOCATION => true,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_USERAGENT => 'okhttp',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
        CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => 'https://www.tiktok.com/',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );
    curl_setopt_array( $ch, $options );

    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $filename = 'media/upload/videos/' . date('Y') . '/' . date('m').'/' .$pixelphoto->generateKey(50,50) . ".mp4";
    $d = fopen($filename, "w");
    fwrite($d, $data);
    fclose($d);

    //PT_UploadToS3($filename);

    return $filename;
}
function SaveTiktokImage($url='')
{
    global $pixelphoto;

    if (!file_exists('media/upload/photos/' . date('Y'))) {
        @mkdir('media/upload/photos/' . date('Y'), 0777, true);
    }
    if (!file_exists('media/upload/photos/' . date('Y') . '/' . date('m'))) {
        @mkdir('media/upload/photos/' . date('Y') . '/' . date('m'), 0777, true);
    }

    if (!file_exists('media/upload/videos/' . date('Y'))) {
        @mkdir('media/upload/videos/' . date('Y'), 0777, true);
    }
    if (!file_exists('media/upload/videos/' . date('Y') . '/' . date('m'))) {
        @mkdir('media/upload/videos/' . date('Y') . '/' . date('m'), 0777, true);
    }

    if (!file_exists('media/upload/files/' . date('Y'))) {
        @mkdir('media/upload/files/' . date('Y'), 0777, true);
    }
    if (!file_exists('media/upload/files/' . date('Y') . '/' . date('m'))) {
        @mkdir('media/upload/files/' . date('Y') . '/' . date('m'), 0777, true);
    }

    $ch = curl_init();
    $headers = array(
        'Range: bytes=0-',
    );
    $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => false,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_FOLLOWLOCATION => true,
        CURLINFO_HEADER_OUT    => true,
        CURLOPT_USERAGENT => 'okhttp',
        CURLOPT_ENCODING       => "utf-8",
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_COOKIEJAR      => 'cookie.txt',
    CURLOPT_COOKIEFILE     => 'cookie.txt',
        CURLOPT_REFERER        => 'https://www.tiktok.com/',
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_MAXREDIRS      => 10,
    );
    curl_setopt_array( $ch, $options );

    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $filename = 'media/upload/photos/' . date('Y') . '/' . date('m').'/' .$pixelphoto->generateKey(50,50) . ".jpg";
    $d = fopen($filename, "w");
    fwrite($d, $data);
    fclose($d);

    //PT_UploadToS3($filename);
    
    return $filename;
}