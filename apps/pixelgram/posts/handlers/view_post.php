<?php

if (empty($_GET['pid']) || !is_numeric($_GET['pid'])) {
	header("Location: $site_url/404");
	exit;
}

$post_id           = $_GET['pid'];
$posts             = new Posts();
$post_data         = null;
$fetched_data      = $posts->setPostId($post_id)->postData();
$is_owner          = false;
$is_following      = false;
$follow   = $user->followSuggestions();
if (!empty($fetched_data)) {
	$post_data = o2array($fetched_data);
	if (!empty($post_data['youtube'])) {
		$post_data['type'] = 'youtube';
		if (!empty($post_data['parent'])) {
			$post_data['parent']['type'] = 'youtube';
		}
	}
	if (!empty($post_data['dailymotion'])) {
		$post_data['type'] = 'dailymotion';
		if (!empty($post_data['parent'])) {
			$post_data['parent']['type'] = 'dailymotion';
		}
	}
	if (!empty($post_data['vimeo'])) {
		$post_data['type'] = 'vimeo';
		if (!empty($post_data['parent'])) {
			$post_data['parent']['type'] = 'vimeo';
		}
	}
	if (!empty($post_data['playtube'])) {
		$post_data['type'] = 'playtube';
		if (!empty($post_data['parent'])) {
			$post_data['parent']['type'] = 'playtube';
		}
	}
	if (!empty($post_data['mp4'])) {
		$post_data['type'] = 'mp4';
		if (!empty($post_data['parent'])) {
			$post_data['parent']['type'] = 'mp4';
		}
	}
}
else{
	header("Location: $site_url/404");
	exit;
}

if (IS_LOGGED && ($me['user_id'] == $post_data['user_id'])) {
	$is_owner = true;
}

if (IS_LOGGED) {
	$is_following = $user->isFollowing($post_data['user_id']);
}
$context['page_title'] = $post_data['description'] . ' - ' . lang('post');
$context['post_data'] = $post_data;
$context['is_owner'] = $is_owner;
$context['follow'] = o2array($follow);
$context['is_following'] = $is_following;
$context['exjs'] = true;
$context['app_name'] = 'posts';
$context['xhr_url'] = "$site_url/aj/posts";
$context['page_link'] = 'post/'.$post_id;

if (isset($_GET['type']) && $_GET['type'] == 'embed') {
	$config['header'] = false;
	$config['footer'] = false;
	$context['app_name'] = 'embed';
	$context['content'] = $pixelphoto->PX_LoadPage('posts/templates/posts/embed_post');
}
else{
	$context['content'] = $pixelphoto->PX_LoadPage('posts/templates/posts/view-post');
}