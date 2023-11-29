<?php
if (IS_LOGGED || $config['two_factor'] != 1) {
	header("Location: $site_url");
	exit;
}
if (empty($_COOKIE['sms_user_password']) || empty($_COOKIE['sms_user_username'])) {
	header("Location: $site_url");
	exit;
}


$config['header'] = false;
$config['footer'] = false;
$context['page_title'] = lang('activate_account');
$context['app_name'] = 'two_factor';
$context['page'] = 'two_factor';
$context['xhr_url'] = "$site_url/aj/signin";
$context['content'] = $pixelphoto->PX_LoadPage('two_factor/templates/two_factor/index');