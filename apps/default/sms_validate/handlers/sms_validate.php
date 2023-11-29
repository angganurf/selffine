<?php
if (IS_LOGGED || $config['signup_system'] != 'on') {
	header("Location: $site_url");
	exit;
}
if ($config['account_validation'] != 1 || $config['validation_method'] != 'sms') {
	header("Location: $site_url");
	exit();
}
if (empty($_COOKIE['sms_user_id']) || !is_numeric($_COOKIE['sms_user_id'])) {
	header("Location: $site_url");
	exit();
}


$config['header'] = false;
$config['footer'] = false;
$context['page_title'] = lang('activate_account');
$context['app_name'] = 'sms_validate';
$context['page'] = 'sms_validate';
$context['xhr_url'] = "$site_url/aj/signin";
$context['content'] = $pixelphoto->PX_LoadPage('sms_validate/templates/sms_validate/index');