<?php 
/**
 * 
 */
class LiveEndPoint extends Generic
{
	
	function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'create_live':
				self::create_live();
				break;
			case 'check_comments':
				self::check_comments();
				break;
			case 'delete':
				self::delete();
				break;
			case 'create_thumb':
				self::create_thumb();
				break;
			default:
				$response_data = array(
			        'code'     => '400',
			        'status'   => 'Bad Request',
			        'errors'         => array(
			            'error_id'   => '1',
			            'error_text' => 'Error: 404 API Version Not Found'
			        )
			    );
			    self::json($response_data);
				break;
		}
	}

	private function create_thumb()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
		if (IS_LOGGED == false) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($response_data);
    	}

    	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && $_POST['post_id'] > 0 && !empty($_FILES['thumb'])) {
	        $is_post = $db->where('post_id',Generic::secure($_POST['post_id']))->where('user_id',$me['user_id'])->getValue(T_POSTS,'COUNT(*)');
	        if ($is_post > 0) {

	            $media = new Media();
	            $media->setFile(array(
	                'file' => $_FILES['thumb']['tmp_name'],
	                'name' => $_FILES['thumb']['name'],
	                'size' => $_FILES['thumb']['size'],
	                'type' => $_FILES['thumb']['type'],
	                'allowed' => 'jpeg,jpg,png',
	                'crop' => array(
	                    'width' => 1076,
	                    'height' => 604
	                ),
	                'avatar' => false
	            ));
	    
	            $upload = $media->uploadFile();

	            if (!empty($upload['filename'])) {
	                $thumb = $upload['filename'];
	                if (!empty($thumb)) {
	                    $db->where('post_id',Generic::secure($_POST['post_id']))->where('user_id',$me['user_id'])->update(T_POSTS,array('thumbnail' => $thumb));
	                    $data = array(
			            	'code'     => '200',
			                'status' => 'OK',
			                'message' => 'File uploaded successfully'
			            ); 
			            self::json($data);
	                }
	                else{
	                	$data = array(
			            	'code'     => '400',
			                'status' => 'Bad Request',
			                'message' => 'File not uploaded'
			            ); 
			            self::json($data);
	                }
	            }
	            else{
	            	$data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => 'File not uploaded'
		            ); 
		            self::json($data);
	            }
	        }
	        else{
	        	$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Post not found'
	            ); 
	            self::json($data);
	        }
	    }
	    else{
	    	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'post_id , thumb can not be empty'
            ); 
            self::json($data);
	    }

    }

	private function delete()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
		if (IS_LOGGED == false) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($response_data);
    	}

    	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && $_POST['post_id'] > 0) {
	        $db->where('post_id',Generic::secure($_POST['post_id']))->where('user_id',$me['user_id'])->update(T_POSTS,array('live_ended' => 1));
	        if ($config['live_video_save'] == 0) {
	            Stream_DeleteVideo(Generic::secure($_POST['post_id']));
	        }
	        else{
	            if ($config['agora_live_video'] == 1 && !empty($config['agora_app_id']) && !empty($config['agora_customer_id']) && !empty($config['agora_customer_certificate']) && $config['live_video_save'] == 1) {
	                $post = $db->where('post_id',Generic::secure($_POST['post_id']))->getOne(T_POSTS);
	                if (!empty($post)) {
	                    StopCloudRecording(array(
	                                            'resourceId' => $post->agora_resource_id,
	                                            'sid' => $post->agora_sid,
	                                            'cname' => $post->stream_name,
	                                            'post_id' => $post->post_id,
	                                            'uid' => explode('_', $post->stream_name)[1]
	                                        )
	                                    );
	                }
	            }
	            if ($config['agora_live_video'] == 1) {
	                try {
	                    Stream_DeleteVideo(Generic::secure($_POST['post_id']));
	                } catch (Exception $e) {
	                    
	                }
	            }
	        }
	        $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Live deleted successfully'
            ); 
            self::json($data);
	    }
	    else{
	    	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'post_id can not be empty'
            ); 
            self::json($data);
	    }
    }

	private function check_comments()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
		if (IS_LOGGED == false) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($response_data);
    	}

    	if (!empty($_POST['post_id']) && is_numeric($_POST['post_id']) && $_POST['post_id'] > 0) {
			$post_id = Generic::secure($_POST['post_id']);
	        $post_data = $context['video_data'] = $db->where('post_id',$post_id)->getOne(T_POSTS);
	        $_user = new User();

			if (!empty($post_data)) {
	            if ($post_data->live_ended == 0) {

	            	$offset = (!empty($_POST['offset']) && is_numeric($_POST['offset']) && $_POST['offset'] > 0 ? Generic::secure($_POST['offset']) : 0);
                    $limit = (!empty($_POST['limit']) && is_numeric($_POST['limit']) && $_POST['limit'] > 0 && $_POST['limit'] <= 50 ? Generic::secure($_POST['limit']) : 20);
                    if (!empty($offset)) {
                        $db->where('id',$offset,'>');
                    }
					$comments = $db->where('post_id',$post_id)->where('text','','!=')->get(T_POST_COMMENTS,$limit);
					$html = '';
	                $count = 0;
	                $comments_data = array();
					foreach ($comments as $key => $get_comment) {
						if (!empty($get_comment->text)) {
	                        $user_data   = $_user->getUserDataById($get_comment->user_id);
	                        unset($user_data->password);
							unset($user_data->email_code);
							unset($user_data->login_token);
							unset($user_data->edit);
							$user_data->time_text = time2str($user_data->last_seen);
			    			$user_data->cover = media($user_data->cover);
			    			$user_data->avatar = media($user_data->avatar);
			    			$user_data->is_following = $_user->isFollowing($user_data->user_id,$me['user_id']);
			    			$user_data->is_blocked  = $_user->isBlocked($user_data->user_id,false);
	                        $get_comment->user_data = $user_data;
	                        $comments_data[] = $get_comment;
						}
					}

	                $word = lang('Offline');
	                $joined_data = array();
                    $left_data = array();
	                if (!empty($post_data->live_time) && $post_data->live_time >= (time() - 10)) {
	                    //$db->where('post_id',$post_id)->where('time',time()-6,'<')->update(T_LIVE_SUB,array('is_watching' => 0));
	                    $word = lang('Live');
	                    $count = $db->where('post_id',$post_id)->where('time',time()-6,'>=')->getValue(T_LIVE_SUB,'COUNT(*)');

	                    if ($me['user_id'] == $post_data->user_id) {
	                        $joined_users = $db->where('post_id',$post_id)->where('time',time()-6,'>=')->where('is_watching',0)->get(T_LIVE_SUB);
	                        $joined_ids = array();
	                        if (!empty($joined_users)) {
	                            foreach ($joined_users as $key => $value) {
	                                $joined_ids[] = $value->user_id;
	                                
	                                $user_data   = $_user->getUserDataById($value->user_id);
	                                unset($user_data->password);
									unset($user_data->email_code);
									unset($user_data->login_token);
									unset($user_data->edit);
									$user_data->time_text = time2str($user_data->last_seen);
					    			$user_data->cover = media($user_data->cover);
					    			$user_data->avatar = media($user_data->avatar);
					    			$user_data->is_following = $_user->isFollowing($user_data->user_id,$me['user_id']);
					    			$user_data->is_blocked  = $_user->isBlocked($user_data->user_id,false);
					    			$joined_data[] = $user_data;
	                            }
	                            if (!empty($joined_ids)) {
	                                $db->where('post_id',$post_id)->where('user_id',$joined_ids,'IN')->update(T_LIVE_SUB,array('is_watching' => 1));
	                            }
	                        }

	                        $left_users = $db->where('post_id',$post_id)->where('time',time()-6,'<')->where('is_watching',1)->get(T_LIVE_SUB);
	                        $left_ids = array();
	                        if (!empty($left_users)) {
	                            foreach ($left_users as $key => $value) {
	                                $left_ids[] = $value->user_id;
	                                $user_data   = $_user->getUserDataById($value->user_id);
	                                unset($user_data->password);
									unset($user_data->email_code);
									unset($user_data->login_token);
									unset($user_data->edit);
									$user_data->time_text = time2str($user_data->last_seen);
					    			$user_data->cover = media($user_data->cover);
					    			$user_data->avatar = media($user_data->avatar);
					    			$user_data->is_following = $_user->isFollowing($user_data->user_id,$me['user_id']);
					    			$user_data->is_blocked  = $_user->isBlocked($user_data->user_id,false);
					    			$left_data[] = $user_data;
	                            }
	                            if (!empty($left_ids)) {
	                                $db->where('post_id',$post_id)->where('user_id',$left_ids,'IN')->delete(T_LIVE_SUB);
	                            }
	                        }
	                    }
	                }
	                $still_live = 'offline';
	                if (!empty($post_data) && $post_data->live_time >= (time() - 10)){
	                    $still_live = 'live';
	                }
	                $response_data = array(
                        'api_status' => 200,
                        'comments' => $comments_data,
                        'joined' => $joined_data,
                        'left' => $left_data,
                        'count' => $count,
                        'word' => $word,
                        'still_live' => $still_live
                    );

	                if ($me['user_id'] == $post_data->user_id) {
	                    if ($_POST['page'] == 'live') {
	                        $time = time();
	                        $db->where('post_id',$post_id)->update(T_POSTS,array('live_time' => $time));
	                    }
	                }
	                else{
	                    if (!empty($post_data->live_time) && $post_data->live_time >= (time() - 10) && $_POST['page'] == 'watch') {
	                        $is_watching = $db->where('user_id',$me['user_id'])->where('post_id',$post_id)->getValue(T_LIVE_SUB,'COUNT(*)');
	                        if ($is_watching > 0) {
	                            $db->where('user_id',$me['user_id'])->where('post_id',$post_id)->update(T_LIVE_SUB,array('time' => time()));
	                        }
	                        else{
	                            $db->insert(T_LIVE_SUB,array('user_id' => $me['user_id'],
	                                                         'post_id' => $post_id,
	                                                         'time' => time(),
	                                                         'is_watching' => 0));
	                        }
	                    }
	                }

	                $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'data' => $response_data
		            ); 
		            self::json($data);

	            }
	            else{
	                $data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => 'Live ended'
		            ); 
		            self::json($data);
	            }
	            
			}
			else{
				$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Post not found'
	            ); 
	            self::json($data);
			}
		}
		else{
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'post_id can not be empty'
            ); 
            self::json($data);
		}
    }

	private function create_live()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
		if (IS_LOGGED == false) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($response_data);
    	}

    	$if_live = $db->where('user_id',$me['user_id'])->where('stream_name','','!=')->where('live_time',time() - 5,'>=')->getValue(T_POSTS,'COUNT(*)');
		if ($if_live > 0) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'You are already live'
            ); 
            self::json($data);
		}
		$db->where('time',time()-60,'<')->delete(T_LIVE_SUB);


		if ($config['live_video'] == 1 && ($config['who_use_live'] == 'all' || ($config['who_use_live'] == 'admin' && IS_ADMIN) || ($config['who_use_live'] == 'pro' && $me['is_pro'] > 0))) {
	    }
	    else{
	        $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'You ca not use live'
            ); 
            self::json($data);
	    }
		if (empty($_POST['stream_name'])) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'stream_name can no be empty'
            ); 
            self::json($data);
		}
		else{
	        $video_id        = PT_GenerateKey(15, 15);
	        $check_for_video = $db->where('post_key', $video_id)->getValue(T_POSTS, 'count(*)');
	        if ($check_for_video > 0) {
	            $video_id = PT_GenerateKey(15, 15);
	        }
	        $token = null;
            if (!empty($_POST['token']) && !is_null($_POST['token'])) {
                $token = Generic::secure($_POST['token']);
            }
			$post_id = $db->insert(T_POSTS,array('user_id' => $me['user_id'],
	                                             'type' => 'live',
	                                             'description' => 'live video '.$me['username'],
	                                             'stream_name' => Generic::secure($_POST['stream_name']),
	                                             'registered' => date('Y') . '/' . intval(date('m')),
	                                             'post_key' => $video_id,
	                                             'agora_token' => $token,
	                                             'time' => time()));
	        PT_RunInBackground(array('status' => 200,'post_id' => $post_id));
	        if ($config['agora_live_video'] == 1 && !empty($config['agora_app_id']) && !empty($config['agora_customer_id']) && !empty($config['agora_customer_certificate']) && $config['live_video_save'] == 1) {
	            if ($config['amazone_s3_2'] == 1 && !empty($config['bucket_name_2']) && !empty($config['amazone_s3_key_2']) && !empty($config['amazone_s3_s_key_2']) && !empty($config['region_2'])) {
	                $region_array = array(
	                    'us-east-1' => 0,
	                    'us-east-2' => 1,
	                    'us-west-1' => 2,
	                    'us-west-2' => 3,
	                    'eu-west-1' => 4,
	                    'eu-west-2' => 5,
	                    'eu-west-3' => 6,
	                    'eu-central-1' => 7,
	                    'ap-southeast-1' => 8,
	                    'ap-southeast-2' => 9,
	                    'ap-northeast-1' => 10,
	                    'ap-northeast-2' => 11,
	                    'sa-east-1' => 12,
	                    'ca-central-1' => 13,
	                    'ap-south-1' => 14,
	                    'cn-north-1' => 15,
	                    'us-gov-west-1' => 17);
	                if (in_array(strtolower($config['region_2']),array_keys($region_array) )) {
	                    StartCloudRecording(1,
	                                        $region_array[strtolower($config['region_2'])],
	                                        $config['bucket_name_2'],
	                                        $config['amazone_s3_key_2'],
	                                        $config['amazone_s3_s_key_2'],
	                                        $_POST['stream_name'],
	                                        explode('_', $_POST['stream_name'])[1],
	                                        $post_id);
	                }
	            }
	        }
	        pt_push_channel_notifiations($post_id,'started_live_video');
	        $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'post_id' => $post_id
            ); 
            self::json($data);
		}
    }




}