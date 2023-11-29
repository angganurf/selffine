<?php
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}
$context['lives']  = array();
$posts             = new Posts();
$posts->orderBy('post_id','DESC');
$posts->limit      = 40;
$query_posts       = $posts->livePosts();
$follow            = array();


if (IS_LOGGED) {
	$follow = $user->followSuggestions();
}


if (!empty($query_posts)) {
	$context['lives'] = o2array($query_posts);
}

$follow = (!empty($follow) && is_array($follow)) ? o2array($follow) : array();
//var_dump($context['lives']);

$context['page_link'] = 'lives';
$context['exjs'] = true;
$context['app_name'] = 'lives';
$context['page_title'] = lang('lives');
$context['follow'] = $follow;
$context['content'] = $pixelphoto->PX_LoadPage('lives/templates/lives/index');
