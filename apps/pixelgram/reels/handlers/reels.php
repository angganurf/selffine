<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}
if ($config['upload_reels'] == 'off') {
	header("Location: $site_url");
	exit;
}
$context['posts']  = array();
$posts             = new Posts();
$posts->orderBy('post_id','DESC');
$posts->limit      = 40;
$query_posts       = $posts->exploreReels();
$follow            = array();


if (IS_LOGGED) {
	$follow = $user->followSuggestions();
}

$boost_post = null;
if(isset($posts->exploreBoostedReels()[0])){
	$boost_post = $posts->exploreBoostedReels()[0];
}
if (!empty($query_posts)) {
	$context['posts'] = o2array($query_posts);
}
if (!empty($boost_post) && !empty($context['posts'])) {
	array_unshift($context['posts'],o2array($boost_post));
}
elseif (empty($context['posts']) && !empty($boost_post)) {
	$context['posts'][] = o2array($boost_post);
}
$context['is_boosted'] = false;

$follow = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();

$context['page_link'] = 'reels';
$context['exjs'] = true;
$context['app_name'] = 'reels';
$context['page_title'] = 'reels';
$context['follow'] = $follow;
$context['content'] = $pixelphoto->PX_LoadPage('reels/templates/explore/index');
