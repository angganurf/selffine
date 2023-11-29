<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
// error_reporting(0);
//header("Cache-Control: max-age=2592000");
define("ROOTPATH", dirname(dirname(__FILE__)) );

session_start();
require_once('sys/server/tables.php');
require_once('sys/db.php');
require_once('sys/context_data.php');
require_once('sys/pxp-autoload.php');
require_once('sys/server/utils.php');
require_once('sys/libs/getID3-1.9.14/getid3/getid3.php');
require_once('sys/libs/SimpleImage-master/src/claviska/SimpleImage.php');
require_once('sys/settings.php');

if ($config['developer_mode'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

$generic_init = array(
	'db' => $db,
	'site_url' => $site_url,
	'config' => $config,
	'mysqli' => $mysqli,
);


$pixelphoto = new Generic($generic_init);
$user  = new User();
$me    = array();
$langs = $user->getLangs();

$context['theme_url'] = $site_url.'/apps/'.$config['theme'];
$context['loggedin'] = $user->isLogged();
$context['is_admin'] = false;
$context['langs']    = $langs;
$context['site_url']    = $site_url;
$config['currency_array'] = (Array) json_decode($config['currency_array']);
$config['currency_symbol_array'] = (Array) json_decode($config['currency_symbol_array']);
if (!empty($config['exchange'])) {
    $config['exchange'] = (Array) json_decode($config['exchange']);
}
$config['stripe_currency_array'] = array('USD','EUR','AUD','BRL','CAD','CZK','DKK','HKD','HUF','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','RUB','SGD','SEK','CHF','THB','GBP');
$config['paypal_currency_array'] = array('USD','EUR','AUD','BRL','CAD','CZK','DKK','HKD','HUF','INR','ILS','JPY','MYR','MXN','TWD','NZD','NOK','PHP','PLN','GBP','RUB','SGD','SEK','CHF','THB');
$config['2checkout_currency_array'] = array('USD','EUR','AED','AFN','ALL','ARS','AUD','AZN','BBD','BDT','BGN','BMD','BND','BOB','BRL','BSD','BWP','BYN','BZD','CAD','CHF','CLP','CNY','COP','CRC','CZK','DKK','DOP','DZD','EGP','FJD','GBP','GTQ','HKD','HNL','HRK','HUF','IDR','ILS','INR','JMD','JOD','JPY','KES','KRW','KWD','KZT','LAK','LBP','LKR','LRD','MAD','MDL','MMK','MOP','MRO','MUR','MVR','MXN','MYR','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PEN','PGK','PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','SAR','SBD','SCR','SEK','SGD','SYP','THB','TND','TOP','TRY','TTD','TWD','UAH','UYU','VND','VUV','WST','XCD','XOF','YER','ZAR');
$config['paystack_currency_array'] = array('USD','GHS','NGN','ZAR');
$config['cashfree_currency_array'] = array('INR','USD','BDT','GBP','AED','AUD','BHD','CAD','CHF','DKK','EUR','HKD','JPY','KES','KWD','LKR','MUR','MYR','NOK','NPR','NZD','OMR','QAR','SAR','SEK','SGD','THB','ZAR');
$config['iyzipay_currency_array'] = array('USD','EUR','GBP','IRR','TL');
$config["withdrawal_payment_method"] = json_decode($config['withdrawal_payment_method'],true);
$context['config']    = $config;
$context['dirname_theme']    = dirname(__dir__).'/apps/'.$config['theme'];
$context['images']    = sprintf('%s/media/img',$config['site_url']);

if ($context['loggedin'] === true) {
    define('IS_LOGGED', $context['loggedin']);


	$context['user'] = $user->getLoggedInUser();
	$context['user'] = Generic::toArray($context['user']);
	$me       = $context['me'] = $context['user'];
	$user_lang = $context['user']['language'];
	$countries = "lang/countries/english.php";
	if (file_exists($countries)) {
		$countries = "lang/countries/$user_lang.php";
	}

	$user->updateLastSeen();


	require_once($countries);

	$context['countries_name'] = $countries_name;
	$context['is_admin']       = (($me['admin'] == 1) ? true : false);
    define('IS_ADMIN', $context['is_admin']);
	$_SESSION['lang']          = $me['language'];
}

if (!empty($_GET['lang']) && in_array($_GET['lang'], array_keys($langs))) {

    $lang_name = $user::secure(strtolower($_GET['lang']));
    $_SESSION['lang'] = $lang_name;

    if ($context['loggedin'] === true) {
        $db->where('user_id', $me['user_id'])->update(T_USERS, array('language' => $lang_name));
    }
}

if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = $config['language'];
}
$context['language'] = $_SESSION['lang'];
$lang                = $user->fetchLanguage($context['language']);
$context['lang']     = $lang;
$context['csrf_token']  = pxp_gencsrf_token();
$context['currency_symbol']  = Pxp_GetCurrency($config['currency']);
if (!defined('IS_LOGGED')) {
    define('IS_LOGGED', $context['loggedin']);
}
if (!defined('IS_ADMIN')) {
    define('IS_ADMIN', $context['is_admin']);
}

if (!empty($_GET['ref']) && $context['loggedin'] == false && !isset($_COOKIE['src']) && $config['affiliate_system'] == 1) {

    $get_ip = get_ip_address();
    if (!isset($_SESSION['ref']) && !empty($get_ip)) {
        $_GET['ref'] = $user::secure($_GET['ref']);
        $user->setUserByName($_GET['ref']);
		$user_data = $user->userData($user->getUser());
		$user_data = o2array($user_data);
        if (!empty($user_data)) {
            //if (ip_in_range($user_data['ip_address'], '/24') === false && $user_data['ip_address'] != $get_ip) {
                $_SESSION['ref'] = $user_data['username'];
            //}
        }
    }
}

//Ability to set light/dark mode by default
if($config['site_display_mode'] === 'day' && !isset($_COOKIE['mode'])){
    setcookie("mode", 'day', time() + (10 * 365 * 24 * 60 * 60), "/");
}else if($config['site_display_mode'] === 'night' && !isset($_COOKIE['mode'])){
    setcookie("mode", 'night', time() + (10 * 365 * 24 * 60 * 60), "/");
}

$terms_langs = $db->where("lang_key LIKE '%terms_of_use_page%' OR lang_key LIKE '%privacy_policy_page%' OR lang_key LIKE '%about_page%'")->get(T_LANGS);
$context['terms_of_use_page'] = 1;
$context['privacy_policy_page'] = 1;
$context['about_page'] = 1;
foreach ($terms_langs as $key => $value) {
    if (in_array($value->ref, array('on','off'))) {
        if ($value->ref == 'on') {
            $context[$value->lang_key] = 1;
        }
        else{
            $context[$value->lang_key] = 0;
        }
    }
    else{
        $db->where('lang_key',$value->lang_key)->update(T_LANGS,array('ref' => 'on'));
    }
}

$context['call_action'] = array(
    '1' => 'read_more',
    '2' => 'shop_now',
    '3' => 'view_now',
    '4' => 'visit_now',
    '5' => 'book_now',
    '6' => 'learn_more',
    '7' => 'play_now',
    '8' => 'bet_now',
    '9' => 'donate',
    '10' => 'apply_here',
    '11' => 'quote_here',
    '12' => 'order_now',
    '13' => 'book_tickets',
    '14' => 'enroll_now',
    '15' => 'find_card',
    '16' => 'get_quote',
    '17' => 'get_tickets',
    '18' => 'locate_dealer',
    '19' => 'order_online',
    '20' => 'preorder_now',
    '21' => 'schedule_now',
    '22' => 'sign_up_now',
    '23' => 'subscribe',
    '24' => 'register_now',
    '25' => 'go_to'
);

$config['filesVersion'] = '1.6';

require_once('sys/function.php');
require_once('sys/cron_job.php');
require_once('sys/onesignal_config.php');
require_once('sys/libs/webtopay.php');
require_once('sys/libs/twilio/vendor/autoload.php');
