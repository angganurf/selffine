<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}

if ($config['agora_live_video'] != 1) {
	header("Location: $site_url/404");
	exit;
}
if (empty($config['agora_app_id'])) {
	header("Location: $site_url/404");
	exit;
}

if ($config['live_video'] == 1 && ($config['who_use_live'] == 'all' || ($config['who_use_live'] == 'admin' && IS_ADMIN) || ($config['who_use_live'] == 'pro' && $me['is_pro'] > 0))) {
}
else{
	header("Location: $site_url/404");
	exit;
}

$context['live_data'] = null;
$view_mode = 'go_live';

if(isset($_GET['id'])){
	$live_exist = $db->where('post_key',Generic::secure($_GET['id']))->getOne(T_POSTS);
	if(!empty($live_exist)){
		$posts             = new Posts();
		$context['live_data'] = $posts->setPostId($live_exist->post_id)->postData();
		$context['live_user_data'] = $db->where('user_id',$live_exist->user_id)->getOne(T_USERS);
		$view_mode = 'watch_live';
	}else{
		header("Location: $site_url/404");
		exit;
	}
}


if($view_mode === 'go_live'){
	$if_live = $db->where('user_id',$me['user_id'])->where('stream_name','','!=')->where('live_time',time() - 5,'>=')->getValue(T_POSTS,'COUNT(*)');
	if ($if_live > 0) {
		header("Location: $site_url/404");
		exit;
	}
	$db->where('time',time()-60,'<')->delete(T_LIVE_SUB);
	require_once('sys/libs/AgoraDynamicKey/sample/RtcTokenBuilderSample.php');

	$context['page_link'] = 'live';
	$context['app_name'] = 'live';
	$context['page_title'] = lang('Live streams');
	$context['content'] = $pixelphoto->PX_LoadPage('live/templates/index');

}else{
	$is_owner          = false;
	$is_following      = false;
	$follow   = $user->followSuggestions();
	if (IS_LOGGED && ($me['user_id'] == $context['live_user_data']->user_id)) {
		$is_owner = true;
	}

	if (IS_LOGGED) {
		$is_following = $user->isFollowing($context['live_user_data']->user_id);
	}

	$context['is_owner'] = $is_owner;
	$context['follow'] = o2array($follow);
	$context['is_following'] = $is_following;
	$context['live_data']->is_still_live = false;
	$context['live_data']->live_sub_users = 0;
	if (  !empty($context['live_data']->stream_name) &&
		  !empty($context['live_data']->live_time) && 
		  //$context['live_data']->live_time >= (time() - 10) &&
		  $context['live_data']->live_ended == 0
		) {
		$context['live_data']->is_still_live = true;
		$context['live_data']->live_sub_users = $db->where('post_id',$context['live_data']->post_id)->where('time',time()-6,'>=')->getValue(T_LIVE_SUB,'COUNT(*)')  + 1;
	}

	if (!empty($context['live_data']->stream_name) && !empty($context['live_data']->video_location)) {
		$context['live_data']->video_location = "https://" . $config['bucket_name_2'] . ".s3.amazonaws.com/" . substr($context['live_data']->video_location, strpos($context['live_data']->video_location, 'upload/'));
		$video_type = 'application/x-mpegURL';
	}

	 //var_dump($context['live_data']);
	// var_dump($context['live_user_data']);
	// exit();
	$context['page_link'] = 'live/' . $context['live_data']->post_key;
	$context['app_name'] = 'live';
	$context['page_title'] = lang('Watch') . ' ' . $context['live_user_data']->username . ' ' . lang('stream');
	$context['content'] = $pixelphoto->PX_LoadPage('live/templates/watch');
}



