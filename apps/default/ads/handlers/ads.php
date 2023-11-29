<?php
if (IS_LOGGED !== true || $config['user_ads'] != 'on') {
	header("Location: $site_url/welcome");
	exit;
}
$pages = array('create','wallet');
if (!empty($_GET['page']) && in_array($_GET['page'], $pages) && $_GET['page'] == 'create') {
	$context['page_link'] = 'ads/create';
	$context['exjs'] = true;
	$context['app_name'] = 'ads';
	$context['page_title'] = $context['lang']['create'];
	$context['content'] = $pixelphoto->PX_LoadPage('ads/templates/ads/create');
}
else{
	$user = new User();
	$context['user_ads'] = $user->GetUserAds();
	$context['page_link'] = 'ads';
	$context['exjs'] = true;
	$context['app_name'] = 'ads';
	$context['page_title'] = $context['lang']['ads'];
	$context['content'] = $pixelphoto->PX_LoadPage('ads/templates/ads/index');
}