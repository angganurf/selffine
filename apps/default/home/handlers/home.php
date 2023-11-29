<?php 
if (IS_LOGGED !== true) {
	header("Location: $site_url/welcome");
	exit;
}


$context['posts']  = array();
$posts             = new Posts();
$story             = new Story();
$posts->limit      = 3;
$posts->comm_limit = 4;
$tlposts           = $posts->setUserById($me['user_id'])->getTimelinePosts();
$boost_post_query = $db->where('boosted',1)->orderBy('RAND()')->getOne(T_POSTS);
$boost_post = array();
if (!empty($boost_post_query)) {
	$posts->setPostId($boost_post_query->post_id);
	$boost_post = $posts->postData('');
}

$live_data = array();
$pro_users       = array();
$live_list = '';
if ($config['live_video'] == 1) {
    // if (!empty($pro_users)){
    //     $db->where('user_id', $pro_users, 'IN');
    //     $db->where('privacy', 0);
    //     $db->orderBy('live_time', 'DESC');
    //     $live_data = $db->where('live_time',0,'>')->get(T_POSTS, 4);
    // }

    if (empty($live_data)) {
		$live_data = $db->where('type', 'live')->where('live_time',0,'>')->orderBy('live_time', 'DESC')->get(T_POSTS, $posts->limit);
		foreach ($live_data as $key => $video) {
			$posts->setPostId($video->post_id);
			$video = $context['video'] = $posts->postData('');
			$live_list .= $pixelphoto->PX_LoadPage('home/templates/home/list', array(
			    'ID' => $video->post_id,
			    'TITLE' => $video->description,
			    'VIEWS' => $video->views,
			    'VIEWS_NUM' => number_format($video->views),
			    'USER_DATA' => $video->user_data,
			    'THUMBNAIL' => preg_replace('/([^:])(\/{2,})/', '$1/',media($video->thumbnail)),//($video->thumbnail !== '') ? media($video->thumbnail) : media($video->avatar),
			    'URL' => '',//$video->url,
			    'TIME' => time2str($video->time),
			    'DURATION' => '',//$video->duration,
			    'VIDEO_ID' => $video->post_key,
			    'VIDEO_ID_' => $video->post_key,//PT_Slug($video->title, $video->video_id),
			    'GIF' => ''//$video->gif
			));
		}
    }
}

$follow   = $user->followSuggestions();
$stories  = $story->setUserById($me['user_id'])->getStories();
$stories  = o2array($stories);
$trending = $posts->getFeaturedPosts();
$post_sys = array(
	($config['upload_images'] == 'on'),
	($config['upload_videos'] == 'on'),
	($config['import_videos'] == 'on'),
	($config['import_images'] == 'on'),
	($config['story_system'] == 'on'),
);


if (!empty($tlposts)) {
	$context['posts'] = o2array($tlposts);
}
if (!empty($boost_post) && !empty($context['posts'])) {
	array_unshift($context['posts'],o2array($boost_post));
}
elseif (empty($context['posts']) && !empty($boost_post)) {
	$context['posts'][] = o2array($boost_post);
}
$context['is_boosted'] = false;
$activities = $posts->getUsersActivities(0,5);
$activities = o2array($activities);

$user = new User();
$context['pro_members'] = $user->GetProUsers();
$context['funding'] = $user->GetFunding(4);
$context['sidebar_ad'] = $user->GetRandomAd('sidebar');
$context['page_link'] = '';
$trending = (!empty($trending)) ? o2array($trending) : array();
$context['posts'] = $context['posts'];
$context['follow'] = o2array($follow);
$context['stories'] = $stories;
$context['trending'] = $trending;
$context['activities'] = $activities;
$context['post_sys'] = (in_array(true, $post_sys));
$context['exjs'] = true;
$config['footer'] = false;
$context['app_name'] = 'home';
$context['page_title'] = $context['lang']['home_page'];
$context['LIVE_LIST'] = $live_list;
$context['content'] = $pixelphoto->PX_LoadPage('home/templates/home/index');
