<?php

function CreateNewAudioCall($re_data,$api = false) {
    global $sqlConnect,$config,$me;
    if( $api == false ) {
        if (IS_LOGGED == false) {
            return false;
        }
    }
    if (empty($re_data)) {
        return false;
    }
    $logged_user_id    = $me['user_id'];
    $query1            = mysqli_query($sqlConnect, "DELETE FROM ".T_AUDIO_CALLES." WHERE `from_id` = {$logged_user_id} OR `to_id` = {$logged_user_id}");
    $re_data['active'] = 0;
    $re_data['called'] = $re_data['from_id'];
    $re_data['time']   = Generic::secure(time());
    $fields            = '`' . implode('`, `', array_keys($re_data)) . '`';
    $data              = '\'' . implode('\', \'', $re_data) . '\'';
    $query             = mysqli_query($sqlConnect, "INSERT INTO ".T_AUDIO_CALLES." ({$fields}) VALUES ({$data})");
    if ($query) {
        return mysqli_insert_id($sqlConnect);
    } else {
        return false;
    }
}
/*done*/
function CreateNewAgoraCall($re_data = array()) {
    global $me, $sqlConnect;
    if (IS_LOGGED == false) {
        return false;
    }
    if (empty($re_data)) {
        return false;
    }
    $logged_user_id    = $me['user_id'];
    $query1            = mysqli_query($sqlConnect, "DELETE FROM " . T_AGORA . " WHERE `from_id` = {$logged_user_id} OR `to_id` = {$logged_user_id}");
    $re_data['time']   = time();
    $fields            = '`' . implode('`, `', array_keys($re_data)) . '`';
    $data              = '\'' . implode('\', \'', $re_data) . '\'';
    $query             = mysqli_query($sqlConnect, "INSERT INTO " . T_AGORA . " ({$fields}) VALUES ({$data})");
    if ($query) {
        return mysqli_insert_id($sqlConnect);
    } else {
        return false;
    }
}
function CreateNewVideoCall($re_data,$api = false) {
    global $sqlConnect,$me;
    if( $api == false ) {
        if (IS_LOGGED == false) {
            return false;
        }
    }
    if (empty($re_data)) {
        return false;
    }
    $logged_user_id    = $me['user_id'];
    $query1            = mysqli_query($sqlConnect, "DELETE FROM `videocalles` WHERE `from_id` = {$logged_user_id} OR `to_id` = {$logged_user_id}");
    $re_data['active'] = 0;
    $re_data['called'] = $re_data['from_id'];
    $re_data['time']   = time();
    $fields            = '`' . implode('`, `', array_keys($re_data)) . '`';
    $data              = '\'' . implode('\', \'', $re_data) . '\'';
    $query             = mysqli_query($sqlConnect, "INSERT INTO `videocalles` ({$fields}) VALUES ({$data})");
    if ($query) {
        return mysqli_insert_id($sqlConnect);
    } else {
        return false;
    }
}
/*done*/
function CheckCallAnswer($id = 0,$api = false) {
    global $sqlConnect,$config;
    if( $api == false ) {
        if (IS_LOGGED == false) {
            return false;
        }
    }
    if (empty($id)) {
        return false;
    }
    $data1 = array();
    $query = mysqli_query($sqlConnect, "SELECT * FROM `videocalles`  WHERE `id` = '{$id}' AND `active` = '1' AND `declined` = '0'");
    if (mysqli_num_rows($query) > 0) {
        $sql          = mysqli_fetch_assoc($query);
        $sql['url'] = $config['site_url'] . '/video_call/' . $id;
        $sql['id'] =  $id;
        return $sql;
    }else{
        $query = mysqli_query($sqlConnect, "SELECT * FROM `" . T_AGORA . "`  WHERE `id` = '{$id}' AND `active` = '1' AND `declined` = '0'");
        if (mysqli_num_rows($query)) {
            if (mysqli_num_rows($query) > 0) {
                $sql        = mysqli_fetch_assoc($query);
                $sql['url'] = $config['site_url'] . '/video_call/' . $sql['room_name'];
                return $sql;
            }
        }
    }
    return false;
}
/*done*/
function CheckCallAnswerDeclined($id = 0,$api = false) {
    global $sqlConnect,$config;
    if( $api == false ) {
        if (IS_LOGGED == false) {
            return false;
        }
    }
    if (empty($id)) {
        return false;
    }
    if ($config['agora_chat_video'] == 'on') {
        $query = mysqli_query($sqlConnect, "SELECT COUNT(`id`) FROM " . T_AGORA . " WHERE `id` = '{$id}' AND `declined` = '1'");
    }
    else{
        $query = mysqli_query($sqlConnect, "SELECT COUNT(`id`) FROM `videocalles` WHERE `id` = '{$id}' AND `declined` = '1'");
    }
    return (Sql_Result($query, 0) == 1) ? true : false;
}
/*done*/
function CheckFroInCalls($type = 'video'){
    global $sqlConnect, $config, $me;
    if (IS_LOGGED == false) {
        return false;
    }
    $user_id = $me['user_id'];
    $data1 = array();
    $time = time() - 40;
    $table = '`videocalles`';
    if ($type == 'audio') {
        $table = T_AUDIO_CALLES;
    }
    if ($config['agora_chat_video'] == 'on') {
        $table = T_AGORA;
        $type    = Generic::secure($type);
        $query = mysqli_query($sqlConnect, "SELECT * FROM {$table}  WHERE `to_id` = '{$user_id}' AND `time` > '$time' AND `active` = '0' AND `declined` = 0 AND `type` = '".$type."'");
    }
    else{
        $query = mysqli_query($sqlConnect, "SELECT * FROM {$table}  WHERE `to_id` = '{$user_id}' AND `time` > '$time' AND `active` = '0' AND `declined` = 0");
    }  
    if (mysqli_num_rows($query) > 0) {
        $sql = mysqli_fetch_assoc($query);
        $sql['url'] = $config['site_url'] . '/video_call/' . $sql['id'];
        if ($config['agora_chat_video'] == 'on') {
            $sql['url'] = $config['site_url'] . '/video_call/' . $sql['room_name'];
        }
        $sql['id'] =  $sql['id'];
        return $sql;
    } else {
        return false;
    }
}
function CheckAudioCallAnswer($id = 0) {
    global $sqlConnect, $config, $me;
    if (IS_LOGGED == false) {
        return false;
    }
    if (empty($id)) {
        return false;
    }
    $data1 = array();
    $id    = Generic::secure($id);
    $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AUDIO_CALLES . "  WHERE `id` = '{$id}' AND `active` = '1' AND `declined` = '0'");
    if (mysqli_num_rows($query) > 0) {
        return true;
    } else {
        $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AGORA . "  WHERE `id` = '{$id}' AND `active` = '1' AND `declined` = '0' AND `type` = 'audio'");
        if (mysqli_num_rows($query)) {
            if (mysqli_num_rows($query) > 0) {
                return true;
            }
        }
    }
    return false;

}
function CheckAudioCallAnswerDeclined($id = 0) {
    global $sqlConnect, $config, $me;
    if (IS_LOGGED == false) {
        return false;
    }
    if (empty($id)) {
        return false;
    }

    $id    = Generic::secure($id);
    if ($config['agora_chat_video'] == 'on') {
        $query = mysqli_query($sqlConnect, "SELECT COUNT(`id`) FROM " . T_AGORA . " WHERE `id` = '{$id}' AND `declined` = '1'");
    }else {
        $query = mysqli_query($sqlConnect, "SELECT COUNT(`id`) FROM " . T_AUDIO_CALLES . " WHERE `id` = '{$id}' AND `declined` = '1'");
    }
    return (Sql_Result($query, 0) == 1) ? true : false;
}
/*done*/
function GetAllDataFromCallID($id = 0) {
    global $sqlConnect,$config,$me;
    $user_id = $me['user_id'];
    if (IS_LOGGED == false) {
        return false;
    }
    if (empty($id)) {
        return false;
    }
    $data1 = array();
    $id    = Generic::secure($id);
    $query = mysqli_query($sqlConnect, "SELECT * FROM `videocalles` WHERE `id` = '{$id}'");
    if (mysqli_num_rows($query) > 0) {
        $sql        = mysqli_fetch_assoc($query);
        $sql['url'] = $config['site_url'] . '/video_call/' . $sql['id'];
        return $sql;
    } else {
        return false;
    }
}
// function CheckAudioCallAnswer($id = 0,$api = false) {
//     global $sqlConnect,$config;
//     if( $api == false ) {
//         if (IS_LOGGED == false) {
//             return false;
//         }
//     }
//     if (empty($id)) {
//         return false;
//     }
//     $data1 = array();
//     $id    = Secure($id);
//     $query = mysqli_query($sqlConnect, "SELECT * FROM `audiocalls`  WHERE `id` = '{$id}' AND `active` = '1' AND `declined` = '0'");
//     if (mysqli_num_rows($query) > 0) {
//         if( $api == false ) {
//             return true;
//         }else{
//             $sql = mysqli_fetch_assoc($query);
//             $sql['url'] = $config->uri . '/audio-call/' . $sql['id'];
//             $sql['id'] =  $sql['id'];
//             return $sql;
//         }
//     } else {
//         return false;
//     }
// }
// function CheckAudioCallAnswerDeclined($id = 0,$api = false) {
//     global $sqlConnect;
//     if( $api == false ) {
//         if (IS_LOGGED == false) {
//             return false;
//         }
//     }
//     if (empty($id)) {
//         return false;
//     }
//     $id    = Secure($id);
//     $query = mysqli_query($sqlConnect, "SELECT COUNT(`id`) FROM `audiocalls` WHERE `id` = '{$id}' AND `declined` = '1'");
//     return (Sql_Result($query, 0) == 1) ? true : false;
// }
function Sql_Result($res, $row = 0, $col = 0) {
    $numrows = mysqli_num_rows($res);
    if ($numrows && $row <= ($numrows - 1) && $row >= 0) {
        mysqli_data_seek($res, $row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);
        if (isset($resrow[$col])) {
            return $resrow[$col];
        }
    }
    return false;
}






function getStoreItemPrice($license_options, $return_text = true){
    $price = 0;
    if(!empty($license_options)){
        $min = null;
        $max = null;
        $_license_options = unserialize( $license_options );
        foreach($_license_options as $key => $value){
            if (is_null($min) || $min>$value){
                $min = $value;
            }
            if (is_null($max) || $max<$value){
                $max = $value;
            }
        }
        if($min > 0 && $min <> $max){
            if($return_text === true){
                $price = $min . '~' . $max;
            }else{
                $price = array($min,$max);
            }
        }else if( $min > 0 && $min === $max ){
            $price = $max;
        }else{
            $price = $max;
        }
    }
    return $price;
}
function UploadToDigitalOcean($filename, $delete = true) {
    global $config; 
    // Create a new cURL resource
    $curl = curl_init();

    if (!$curl) {
        die("Couldn't initialize a cURL handle");
    }

    // Set the file URL to fetch through cURL
    curl_setopt($curl, CURLOPT_URL, $config['site_url'] . "/command.php");

    // Set a different user agent string (Googlebot)
    curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');

    // Follow redirects, if any
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    // Fail the cURL request if response code = 400 (like 404 errors)
    curl_setopt($curl, CURLOPT_FAILONERROR, true);

    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS,
            "file=".$filename."&is_delete=".$delete."&token=".$_COOKIE['user_id']);

    // Return the actual result of the curl result instead of success code
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Wait for 10 seconds to connect, set 0 to wait indefinitely
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

    // Execute the cURL request for a maximum of 50 seconds
    curl_setopt($curl, CURLOPT_TIMEOUT, 50);

    // Do not check the SSL certificates
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    // Fetch the URL and save the content in $html variable
    $html = curl_exec($curl);
    // Check if any error has occurred
    if (curl_errno($curl))
    {
        //echo 'cURL error: ' . curl_error($curl);
    }
    else
    {
        // cURL executed successfully
        //print_r(curl_getinfo($curl));
    }
    // close cURL resource to free up system resources
    curl_close($curl);
}
function HashPassword($password = '', $hashed_password = '') {
    if (empty($password)) {
        return '';
    }
    $hash = 'sha1';
    if (strlen($hashed_password) == 60) {
        $hash = 'password_hash';
    }
    if ($hash == 'password_hash') {
        if (password_verify($password, $hashed_password)) {
            return true;
        }
    } else {
        $password = $hash($password);
    }
    if ($password == $hashed_password) {
        return true;
    }
    return false;
}

function media($path = ""){
    global $site_url, $config;
    if (strpos($path, "http") === 0) {
        return $path;
    }
    if ($config['amazone_s3'] == 1) {
        if (!empty($config['amazone_cloudfront_distribution'])) {
            return 'https://'.$config['amazone_cloudfront_distribution'].'.cloudfront.net/'.$path;
        }
        return 'https://'.$config['bucket_name'].'.s3.amazonaws.com/'.$path;
    }
    elseif ($config['digital_ocean'] == 1) {
        if ($config['digital_ocean_cdn'] == 1) {
            return 'https://'.$config['digital_ocean_space_name'].'.'.$config['digital_ocean_region'].'.cdn.digitaloceanspaces.com/'.$path;
        }
        return 'https://'.$config['digital_ocean_space_name'].'.'.$config['digital_ocean_region'].'.digitaloceanspaces.com/'.$path;
    }
    else if ($config['google_cloud_storage'] == 1) {
        return 'https://storage.googleapis.com/'.$config['google_cloud_storage_bucket_name'].'/'.$path; 
    }
    else if ($config['backblaze_storage'] == 1) {
        if (!empty($config['backblaze_endpoint']) && filter_var($config['backblaze_endpoint'], FILTER_VALIDATE_URL)) {
            return $config['backblaze_endpoint'] . "/" . $path;
        }
        return 'https://'.$config['backblaze_bucket_name'].'.s3.'.$config['backblaze_bucket_region'].'.backblazeb2.com/' . $path;
    }
    else if ($config['ftp_upload'] == 1) {
        return $config['ftp_endpoint'].'/'.$path;
    }elseif ($config['wasabi_storage'] == 1) {
        if (!empty($config['wasabi_endpoint']) && filter_var($config['wasabi_endpoint'], FILTER_VALIDATE_URL)) {
            return $config['wasabi_endpoint'] . "/" . $path;
        }
        if (empty($config['wasabi_bucket_name']) || empty($config['wasabi_access_key']) || empty($config['wasabi_secret_key']) || empty($config['wasabi_bucket_region'])) {
            return $config['site_url'] . '/' . $path;
        }
        return $site_url.'/'.$path;
    }
    else{
        if (strpos($path, "http") === 0) {
            return $path;
        } else {
            return $site_url.'/'.$path;
        }
    }
    return $path;
}

function un2url($username = ""){
    global $site_url;
    $url = sprintf('%s/%s',$site_url,$username);
    return $url;
}

function br2nl($st) {
    $breaks   = array(
        "\r\n",
        "\r",
        "\n"
    );
    $st       = str_replace($breaks, "", $st);
    $st_no_lb = preg_replace("/\r|\n/", "", $st);
    return preg_replace('/<br(\s+)?\/?>/i', "\r", $st_no_lb);
}
function pid2url($post_id = 0){
    global $site_url;
    $url = sprintf('%s/post/%u',$site_url,$post_id);
    return $url;
}

function croptxt($text = "", $len = 100,$ellip = '..') {
    if (empty($text) || !is_string($text) || !is_numeric($len) || $len < 1) {
        return '';
    }
    if (strlen($text) > $len) {
        $text = mb_substr($text, 0, $len, "UTF-8") . $ellip;
    }
    return $text;
}

function o2array($obj) {
	
    if (is_object($obj))
        $obj = (array) $obj;

    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = o2array($val);
        }
    } 

    else {
        $new = $obj;
    }

    return $new;
}

function get_ip_address() {
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP))
                    return $ip;
            }
        } else {
            if (filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && filter_var($_SERVER['HTTP_X_FORWARDED'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && filter_var($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && filter_var($_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP))
        return $_SERVER['HTTP_FORWARDED'];
    return $_SERVER['REMOTE_ADDR'];
}

function px_multiple_files($file_post = array()) {
    if (!is_array($file_post)) {
        return array();
    }
    $wo_file_array = array();
    $wo_file_count = count($file_post['name']);
    $wo_file_keys  = array_keys($file_post);
    for ($i=0; $i < $wo_file_count; $i++) {
        foreach ($wo_file_keys as $key) {
            $wo_file_array[$i][$key] = $file_post[$key][$i];
        }
    }
    return $wo_file_array;
}

function px_covtime($youtube_time) {
    preg_match_all('/(\d+)/', $youtube_time, $parts);
    // Put in zeros if we have less than 3 numbers.
    if (count($parts[0]) == 1) {
        array_unshift($parts[0], "0", "0");
    } elseif (count($parts[0]) == 2) {
        array_unshift($parts[0], "0");
    }
    $sec_init         = $parts[0][2];
    $seconds          = $sec_init % 60;
    $seconds_overflow = floor($sec_init / 60);
    $min_init         = $parts[0][1] + $seconds_overflow;
    $minutes          = ($min_init) % 60;
    $minutes_overflow = floor(($min_init) / 60);
    $hours            = $parts[0][0] + $minutes_overflow;
    if ($hours != 0)
        return $hours . ':' . $minutes . ':' . $seconds;
    else
        return $minutes . ':' . $seconds;
}

function pxp_sqltepmlate($path = '',$data = array()){
  $temp_path = ROOTPATH . "/sys/sql_templates/$path.sql";
  $template  = false;
  if (file_exists($temp_path)) {
    $if   = '/(\{\%\s{0,1}if\s{1}(?P<key>[\w]+)\s{0,1}\%\}(?P<sq>.+?)\{\%\s{0,1}endif\s{0,1}\%\})/is';
  	$ifeq = '/(\{\%\s{0,1}if\s{1}[\'\"]?(?P<key>[^\s]+?)[\'\"]?\s==\s[\'\"]?(?P<val>[^\s]+?)[\'\"]?\s{0,1}\%\}(?P<sq>.+?)\{\%\s{0,1}endif\s{0,1}\%\})/is';

    $template = file_get_contents($temp_path);

    foreach ($data as $key => $value) {
        $template = preg_replace_callback($ifeq, function($m) use($data) {

            if ($m && !empty($m['key']) && !empty($m['val']) && !empty($data[$m['key']]) && ($data[$m['key']] == $m['val'])) {
                return (!empty($m['sq'])) ? $m['sq'] : '';
            }
            else{
                return '';
            }

        },$template);

    	$template = preg_replace_callback($if, function($m) use($data) {
            if ($m && !empty($m['key']) && !empty($data[$m['key']])) {
                return (!empty($m['sq'])) ? $m['sq'] : '';
            }
            else{
                return '';
            }

        },$template);

        $template = preg_replace("/\{\%\s{0,1}$key\s{0,1}\%\}/i",$value, $template);
    	$template = preg_replace("/\{\@(.*?)\@\}/is",'', $template);
    }

  }

  return $template;
}

function pxp_link($path = "") {
    global $site_url;
    return sprintf('%s/%s',$site_url,$path);
}

function url($url = '',$path = ''){
    return sprintf('%s/%s',$url,$path);
}

function ToDate($time = '') {
    return date('c', $time);
}

function time2str($ptime) {
    $etime = time() - $ptime;
    if ($etime < 1) {
        return sprintf('%d %s',0,lang('seconds'));
    }
    $a = array(
        365 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    $a_plural = array(
        'year' => 'years',
        'month' => 'months',
        'day' => 'days',
        'hour' => 'hours',
        'minute' => 'minutes',
        'second' => 'seconds'
    );
    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? lang($a_plural[$str]) : lang($str)) . ' ' . lang('time_ago');
        }
    }
}


function pre($val = null){
    echo "<pre>";
    print_r($val);
    echo "</pre>";
    exit();
}

function pxp_mentions($text = ""){
    preg_match_all('/(?:^|\s|,)\B@([a-zA-Z0-9_]{4,32})/is', $text, $mentions);
    $users = array();
    if (is_array($mentions) && !empty($mentions[1])) {
        $users = $mentions[1];
    }

    return $users;
}

function pxp_get_hashtags($text = ""){

    preg_match_all('/#([^`~!@$%^&*\#()\-+=\\|\/\.,<>?\'\":;{}\[\]* ]{4,120})/is', $text, $hashtags);
    $tags = array();
    if (is_array($hashtags) && !empty($hashtags[1])) {
        $tags = $hashtags[1];
    }

    return $tags;
}

function is($boolval  = null){
    return ($boolval == true);
}

function not($boolval  = null){
    return ($boolval != true);
}

function date4mat($time = 0,$format = ''){
    return date($format,$time);
}

function icon($icon_name = '',$icon_type = 'svg'){
    global $site_url;
    $ipath = sprintf('%s/media/icons/%s.%s',$site_url,$icon_name,$icon_type);
    return $ipath;
}

function len($val = ""){
    if (is_string($val)) {
        $val = strlen($val);
    }

    elseif (is_array($val)) {
        $val = count($val);
    }
    
    return $val;
}

function minify_js($code = ''){
    $code = preg_replace('/(\r\n|\n|\t|\s{2,})/is', '', $code);
    return $code;
}

function pxp_acp_link($path = ''){
    global $site_url;
    return sprintf('%s/admin-panel/%s',$site_url,$path);
}

function pxp_getconfig(){
    global $db;
    $data    = array();
    $configs = $db->get(T_CONFIG,null,array('name','value'));
    
    foreach ($configs as $key => $config) {
        $data[$config->name] = $config->value;
    }

    return $data;
}

function encode($html = ""){
    return htmlspecialchars($html);
}

function decode($html = ""){
    return htmlspecialchars_decode($html);
}

function toArray($obj) {
    if (is_object($obj))
        $obj = (array) $obj;
    if (is_array($obj)) {
        $new = array();
        foreach ($obj as $key => $val) {
            $new[$key] = toArray($val);
        }
    } else {
        $new = $obj;
    }
    return $new;
}

function lang($key = ""){
    global $lang,$config, $db;
    if ($key == "") {
        return '';
    }
    $repl = array(
        'site_name' => $config['site_name'],
    );

    $text = "";//(array_key_exists($key, $lang) == true) ? $lang[$key] : "Lang key ($key) not exists";
    if(array_key_exists($key, $lang) == true){
        $text = $lang[$key] ;
    }else{
        $keyd = trim(strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/','_', $key)));
        $exist = $db->where('lang_key', $keyd )->getValue(T_LANGS,'count(*)');
        if( $exist === 0 ){
            $db->insert(T_LANGS,array(
                'lang_key' => $keyd,
                'english' => $key
            ));
        }
        $text = $key;
    }


    foreach ($repl as $key => $value) {
        $text = preg_replace("/\{{2}$key\}{2}/", $value, $text);
    }
    $text = str_replace('% d', '%d', $text);
    $text = str_replace('٪ d', '%d', $text);
    $text = str_replace('٪d', '%d', $text);
    $text = str_replace('% ', '%d', $text);
    $text = str_replace('%d', ' %d ', $text);
    return $text;
}

function pxp_gencsrf_token() {
    if (!empty($_SESSION['csrf'])) {
        return $_SESSION['csrf'];
    }
    
    $hash = substr(sha1(rand(1111, 9999)), 0, 70);
    $slat = time();
    $hash = sprintf('%d:%s',$slat,$hash);

    $_SESSION['csrf'] = $hash;

    return $hash;
}

function pxp_verifcsrf_token($hash = '') {
    if (empty($_SESSION['csrf']) || empty($hash)) {
        return false;
    }

    return ($hash == $_SESSION['csrf']) ? true : false;
}

function ip_in_range($ip, $range) {
    if (!is_numeric($ip)) {
        return false;
    }
    if (strpos($range, '/') == false) {
        $range .= '/32';
    }
    // $range is in IP/CIDR format eg 127.0.0.1/24
    list($range, $netmask) = explode('/', $range, 2);
    $range_decimal    = ip2long($range);
    $ip_decimal       = ip2long($ip);
    $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
    $netmask_decimal  = ~$wildcard_decimal;
    return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
}
function Pxp_GetCurrency($currency) {
    global $lang,$config, $db;
    if (empty($currency)) {
        return false;
    }
    if (!in_array($currency,array_keys($config['currency_symbol_array']))) {
        return '$';
    }
    return $config['currency_symbol_array'][$currency];
    // $currency_ = '$';
    // switch ($currency) {
    //     case 'USD':
    //         $currency_ = '$';
    //         break;
    //     case 'JPY':
    //         $currency_ = '¥';
    //         break;
    //     case 'TRY':
    //         $currency_ = '₺';
    //         break;
    //     case 'GBP':
    //         $currency_ = '£';
    //         break;
    //     case 'EUR':
    //         $currency_ = '€';
    //         break;
    //     case 'AUD':
    //         $currency_ = '$';
    //         break;
    //     case 'INR':
    //         $currency_ = '₹';
    //         break;
    //     case 'RUB':
    //         $currency_ = 'RUB';
    //         break;
    //     case 'PLN':
    //         $currency_ = 'zł';
    //         break;
    //     case 'ILS':
    //         $currency_ = 'ILS';
    //         break;
    //     case 'BRL':
    //         $currency_ = 'R$';
    //         break;
    // }
    // return $currency_;
}
function getPageFromPath($path = '') {
    if (empty($path)) {
        return false;
    }
    $path = explode("//", $path);
    $data = array();
    $data['options'] = array();
    if (!empty($path[0])) {
        $data['page'] = $path[0];
    }
    if (!empty($path[1])) {
        unset($path[0]);
        $data['options'] = $path;
        foreach ($path as $key => $value) {
            preg_match_all('/(.*)=(.*)/m', $value, $matches);
            if (!empty($matches) && !empty($matches[1]) && !empty($matches[1][0]) && !empty($matches[2]) && !empty($matches[2][0])) {
                $_GET[$matches[1][0]] = $matches[2][0];
            }
            
        }
    }
    return $data;
}
function CheckPaystackPayment($ref)
{
    global $me,$config, $db;
    if (empty($ref) || IS_LOGGED == false) {
        return false;
    }
    $ref = Generic::secure($ref);
    $user = $db->where('user_id',$me['user_id'])->where('paystack_ref',$ref)->getValue(T_USERS,"COUNT(*)");
    if ($user < 1) {
        return false;
    }
    $result = array();
    //The parameter after verify/ is the transaction reference to be verified
    $url = 'https://api.paystack.co/transaction/verify/'.$ref;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
      $ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '.$config['paystack_secret_key']]
    );
    $request = curl_exec($ch);
    curl_close($ch);

    if ($request) {
        $result = json_decode($request, true);
        if($result){
          if($result['data']){
            if($result['data']['status'] == 'success'){
                $db->where('user_id',$me['user_id'])->where('paystack_ref',$ref)->update(T_USERS,array('paystack_ref' => ''));
                return true;
            }else{
              die("Transaction was not successful: Last gateway response was: ".$result['data']['gateway_response']);
            }
          }else{
            die($result['message']);
          }

        }else{
          die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable.");
        }
      }else{
        die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
      }
}
function BlurUploadedImage($file)
{
    global $me, $sqlConnect,$db,$config,$pixelphoto,$context;
    $new_string        = pathinfo($file, PATHINFO_FILENAME) . '.' . strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);
    if ($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif') {
        $folder   = 'photos';
        $fileType = 'image';
    } else if ($file_extension == 'mp4' || $file_extension == 'webm' || $file_extension == 'flv') {
        $folder   = 'videos';
        $fileType = 'video';
    } else {
        $folder   = 'files';
        $fileType = 'file';
    }
    $dir         = "media/upload";
    $generate    = date('Y') . '/' . date('m') . '/' . $pixelphoto->generateKey(50,50) . '_' . date('d') . '_' . md5(time());
    $file_path   = "{$folder}/" . $generate . "_{$fileType}.{$file_extension}";
    $filename    = $dir . '/' . $file_path;


    $image = imagecreatefromjpeg($file);

    $imgsize = list($w, $h) = @getimagesize($file);
    $finfof  = $imgsize['mime'];
    $image_c = 'imagejpeg';
    if ($finfof == 'image/jpeg') {
        $image   = @imagecreatefromjpeg($file);
        $image_c = 'imagejpeg';
    } else if ($finfof == 'image/gif') {
        $image   = @imagecreatefromgif($file);
        $image_c = 'imagegif';
    } else if ($finfof == 'image/png') {
        $image   = @imagecreatefrompng($file);
        $image_c = 'imagepng';
    } else if ($finfof == 'image/webp') {
        if (function_exists('imagecreatefromwebp')) {
            $image = @imagecreatefromwebp($file);
            $image_c = 'imagewebp';
        }
    } else {
        $image = @imagecreatefromjpeg($file);
    }
    $size = array('sm'=>array('w'=>intval($w), 'h'=>intval($h)));  
    if ($config['downsize_blurred_photo'] > 1) {
        $size = array('sm'=>array('w'=>intval($w/$config['downsize_blurred_photo']), 'h'=>intval($h/$config['downsize_blurred_photo'])));  
    }         
    $sm = imagecreatetruecolor($size['sm']['w'],$size['sm']['h']);
    imagecopyresampled($sm, $image, 0, 0, 0, 0, $size['sm']['w'], $size['sm']['h'], $w, $h);
    if ($config['photo_blurred_number'] > 0) {
        for ($x=1; $x <= $config['photo_blurred_number']; $x++){
            imagefilter($sm, IMG_FILTER_GAUSSIAN_BLUR, 999);
        }
    }
    
    imagecopyresampled($image, $sm, 0, 0, 0, 0, $w, $h, $size['sm']['w'], $size['sm']['h']);
    @imagejpeg($image, $filename);
    imagedestroy($sm);
    imagedestroy($image);

    if ($context['ftp_upload'] == 1 || $context['amazone_s3'] == 1 || $context['google_cloud_storage'] == 1 || $context['digital_ocean'] == 1 || $context['wasabi_storage'] == 1 || $context['backblaze_storage'] == 1) {
        Generic::$config['ftp_upload'] = $context['ftp_upload'];
        Generic::$config['amazone_s3'] = $context['amazone_s3'];
        Generic::$config['google_cloud_storage'] = $context['google_cloud_storage'];
        Generic::$config['digital_ocean'] = $context['digital_ocean'];
        Generic::$config['wasabi_storage'] = $context['wasabi_storage'];
        Generic::$config['backblaze_storage'] = $context['backblaze_storage'];
        $media = new Media();
        $c_path = str_replace('_'.$fileType, '_'.$fileType.'_c', $file);
        $media->cropImage(350, 350, $filename, $c_path, 90);
        $upload_     = $media->uploadToS3($file, true);
        $upload_     = $media->uploadToS3($filename, true);
        if($c_path !== ''){
            $upload_     = $media->uploadToS3($c_path, true);
        }
    }
    return $filename;


}
use Google\Cloud\Storage\StorageClient;

function uploadFiletoGoogleCloud($fileContent, $cloudPath) {
    global $config;
    if ($config['google_cloud_storage'] == 0 || empty($config['cloud_file_path']) || empty($config['google_cloud_storage_bucket_name'])) {
        return false;
    }
    require_once('sys/libs/google-lib/vendor/autoload.php');
    $bucketName = $config['google_cloud_storage_bucket_name'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient(array(
            'keyFilePath' => $config['cloud_file_path']
        ));
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
 
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
 
    // upload/replace file 
    $storageObject = $bucket->upload(
            $fileContent,
            ['name' => $cloudPath]
            // if $cloudPath is existed then will be overwrite without confirmation
            // NOTE: 
            // a. do not put prefix '/', '/' is a separate folder name  !!
            // b. private key MUST have 'storage.objects.delete' permission if want to replace file !
    );
 
    // is it succeed ?
    return $storageObject != null;
}

function deleteFiletoGoogleCloud($filename) {
    global $config;
    if ($config['google_cloud_storage'] == 0 || empty($config['cloud_file_path']) || empty($config['google_cloud_storage_bucket_name'])) {
        return false;
    }
    require_once('sys/libs/google-lib/vendor/autoload.php');
    $bucketName = $config['google_cloud_storage_bucket_name'];
    // connect to Google Cloud Storage using private key as authentication
    try {
        $storage = new StorageClient(array(
            'keyFilePath' => $config['cloud_file_path']
        ));
    } catch (Exception $e) {
        // maybe invalid private key ?
        print $e;
        return false;
    }
 
    // set which bucket to work in
    $bucket = $storage->bucket($bucketName);
    $object = $bucket->object($filename);
    $deleted = $object->delete();

    // is it succeed ?
    return $deleted != null;
}
function BackblazeConnect($args=[])
{
    global $config;

    $session = curl_init($args['apiUrl'] . $args['uri']);
    $content_type = '';

    if ($args['uri'] == '/b2api/v2/b2_list_buckets') {
        $data = array("accountId" => $args['accountId']);
        $post_fields = json_encode($data);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post_fields); 
        curl_setopt($session, CURLOPT_POST, true); // HTTP POST
    }
    else if ($args['uri'] == '/b2api/v2/b2_get_upload_url' || $args['uri'] == '/b2api/v2/b2_list_file_names') {
        $data = array("bucketId" => $config['backblaze_bucket_id']);
        $post_fields = json_encode($data);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post_fields); 
        curl_setopt($session, CURLOPT_POST, true); // HTTP POST
    }
    else if ($args['uri'] == '/b2api/v2/b2_delete_file_version') {
        $data = array("fileId" => $args['fileId'], "fileName" => $args['fileName']);
        $post_fields = json_encode($data);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post_fields); 
        curl_setopt($session, CURLOPT_POST, true); // HTTP POST
    }
    elseif (isset($args['file']) && !empty($args['file'])) {
        $handle = fopen($args['file'], 'r');
        $read_file = fread($handle,filesize($args['file']));
        curl_setopt($session, CURLOPT_POSTFIELDS, $read_file); 
    }

    // Add post fields
    
    

    // Add headers
    $headers = array();
    
    if ($args['uri'] == '/b2api/v2/b2_authorize_account') {
        $credentials = base64_encode($config['backblaze_access_key_id'] . ":" . $config['backblaze_access_key']);
        $headers[] = "Accept: application/json";
        $headers[] = "Authorization: Basic " . $credentials;
        curl_setopt($session, CURLOPT_HTTPGET, true);
    }
    else if (isset($args['file']) && !empty($args['file'])) {
        $headers[] = "X-Bz-File-Name: " . $args['file'];
        $headers[] = "Content-Type: " . mime_content_type($args['file']);
        $headers[] = "X-Bz-Content-Sha1: " . sha1_file($args['file']);
        $headers[] = "X-Bz-Info-Author: " . "unknown";
        $headers[] = "X-Bz-Server-Side-Encryption: " . "AES256";
        $headers[] = "Authorization: " . $args['authorizationToken'];
    }
    else{
        $headers[] = "Authorization: " . $args['authorizationToken'];
    }

    curl_setopt($session, CURLOPT_HTTPHEADER, $headers); 

    
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);  // Receive server response
    $server_output = curl_exec($session); // Let's do this!
    curl_close ($session); // Clean up
    
    return $server_output;
}