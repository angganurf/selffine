<?php 
/**
 * 
 */
class StoryEndPoint extends Generic
{
	
	function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'create_story':
				self::create_story_();
				break;
			case 'delete_story':
				self::delete_story_();
				break;
			case 'fetch_stories':
				self::fetch_stories_();
				break;
			case 'get_story':
				self::get_story_();
				break;
			case 'get_story_views':
				self::get_story_views();
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



	private function get_story_views()
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
    	elseif ($config['story_system'] != 'on') {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '14',
		            'error_text' => 'Sorry There Is Something Wrong!! Please Try Again Later.'
		        )
		    );
		    self::json($response_data);
    	}
    	elseif (empty($_POST['id'])) {
    		$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
    	}

    	$id = Generic::secure($_POST['id']);
    	$limit = !empty($_POST['limit']) && $_POST['limit'] <= 50 ? Generic::secure($_POST['limit']) : 30;
		$offset  = !empty($_POST['offset']) ? Generic::secure($_POST['offset']) : false;
		if (!empty($offset)) {
			$db->where('id',$offset,'<');
		}

    	$views = $db->where('story_id',$id)->orderBy('id','DESC')->get(T_STORY_VIEWS,$limit);
    	$all_views = array();
    	if (!empty($views)) {
    		foreach ($views as $key => $value) {
    			$user         = new User();
	    		$user->setUserById($value->user_id);
				$user_data = $user->getUserDataById($value->user_id);
				unset($user_data->password);
				unset($user_data->email_code);
				unset($user_data->login_token);
				unset($user_data->edit);
				$user_data->time_text = time2str($user_data->last_seen);
    			$user_data->cover = media($user_data->cover);
    			$user_data->avatar = media($user_data->avatar);
    			$user_data->is_following = $user->isFollowing($user_data->user_id,$me['user_id']);
    			$user_data->is_blocked  = $user->isBlocked($user_data->user_id,false);
    			$value->user_data = $user_data;
    			$all_views[] = $value;
    		}
    	}

    	$data = array(
        	'code'     => '400',
            'status' => 'Bad Request',
            'data' => $all_views
        ); 
        self::json($data);
    }

	private function get_story_()
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
    	elseif ($config['story_system'] != 'on') {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '14',
		            'error_text' => 'Sorry There Is Something Wrong!! Please Try Again Later.'
		        )
		    );
		    self::json($response_data);
    	}
    	elseif (empty($_POST['id'])) {
    		$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
    	}
    	$story   = new Story();
    	$id = Generic::secure($_POST['id']);

    	$stories = $story->getUserStory($id);
    	if (!empty($stories) && !empty($stories[0])) {
    		$story = $stories[0];
    		$is_seen = $db->where('story_id',$id)->where('user_id' , $me['user_id'])->getValue(T_STORY_VIEWS,'COUNT(*)');
    		if ($is_seen == 0) {
    			$db->insert(T_STORY_VIEWS,array(
		            'story_id' => $story->id,
		            'user_id'  => $me['user_id'],
		            'time'     => time()
		        ));
    		}
	    		
			$story->media_file = media($story->media_file);
			$story->media_file = media($story->media_file);
			$story->time_text = time2str($story->time);
			$story->caption = strip_tags($story->caption);
			$response_data       = array(
		        'code'     => '200',
			    'status'   => 'OK',
		        'data'         => $story
		    );
		    self::json($response_data);
    	}
    	else{
    		$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Story not found'
            ); 
            self::json($data);
    	}
    }


	private function create_story_()
	{
		global $me,$config,$imgtypes,$vidtypes;
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
    	elseif ($config['story_system'] != 'on') {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '14',
		            'error_text' => 'Sorry There Is Something Wrong!! Please Try Again Later.'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
			if (!empty($_FILES['file']) && file_exists($_FILES['file']['tmp_name'])) {
				$media   = new Media();
				$getid   = new getID3();
				$story   = new Story();
			    $can_add = $story->setUserById($me['user_id'])->canAddStory();
			
				if ($can_add) {
					$up_size = (!empty($_FILES['file']['size'])) ? $_FILES['file']['size'] : 0;
					$mx_size = $config['max_upload'];

					$metainf = $getid->analyze($_FILES['file']['tmp_name']);
					$file_ex = null;
					$file_tp = null;

					if ($up_size <= $mx_size) {
						if (is_array($metainf) && isset($metainf['fileformat'])) {
							$file_ex = $metainf['fileformat'];
						}

						if (in_array($file_ex, $imgtypes)) {
							$media->setFile(array(
								'file' => $_FILES['file']['tmp_name'],
								'name' => $_FILES['file']['name'],
								'size' => $_FILES['file']['size'],
								'type' => $_FILES['file']['type'],
								'allowed' => 'bmp,gif,jpeg,png,jpg',
							));

							$upload = $media->uploadFile();

							if (!empty($upload) && !empty($upload['filename'])) {

								$sfile   = $upload['filename'];
								$re_data = array(
				                	'user_id' => $me['user_id'],
				                	'time' => time(),
				                	'type' => '1',
				                	'media_file' => $sfile
				                );

				                if (!empty($_POST['caption'])) {
									$text = Generic::cropText($_POST['caption'],500);
									$text = Generic::secure($text);
									$re_data['caption'] = $text;
								}

								$story_id = $story->addStory($re_data);

								if (is_numeric($story_id)) {
									$response_data       = array(
								        'code'     => '200',
									    'status'   => 'OK',
									    'id' => $story_id,
								        'data'         => array(
								            'message' => 'Your post has been published successfully'
								        )
								    );
								    self::json($response_data);
								}

								else{
									$response_data       = array(
								        'code'     => '400',
									    'status'   => 'Bad Request',
								        'errors'         => array(
								            'error_id'   => '21',
								            'error_text' => 'An unknown error occurred. Please try again later!'
								        )
								    );
								    self::json($response_data);
								}
							}
						}

						else if(in_array($file_ex, $vidtypes)) {
							$duration = 0;
							$metainf = $getid->analyze($_FILES['file']['tmp_name']);
							if (!empty($metainf['playtime_seconds'])) {
								$duration = intval($metainf['playtime_seconds'] * 1000);
							}
							$file_tp = 'video';

							$media->setFile(array(
								'file' => $_FILES['file']['tmp_name'],
								'name' => $_FILES['file']['name'],
								'size' => $_FILES['file']['size'],
								'type' => $_FILES['file']['type'],
								'allowed' => 'mp4,mov,3gp,webm',
							));

							$upload = $media->uploadFile();

							if (!empty($upload) && !empty($upload['filename'])) {

								$sfile = $upload['filename'];

								if ($config['ffmpeg_sys'] == 'on') {
									require_once('sys/libs/ffmpeg-php/vendor/autoload.php');

									try{
										$ffmpeg   = new FFmpeg($config['ffmpeg_binary']);
										$filepath = explode('.', $upload['filename'])[0];
										$ffmpeg->input($upload['filename']);
										$ffmpeg->set('-ss','0');
										$ffmpeg->set('-t',$config['story_duration']);
										$ffmpeg->set('-vcodec','h264');
										$ffmpeg->set('-c:v','libx264');
										$ffmpeg->set('-preset',$config['convert_speed']);
										$ffmpeg->set('-acodec','mp3');
										$ffmpeg->set('-hide_banner');

										if ($file_ex != 'mp4') {	
											$ffmpeg->forceFormat('mp4');
										}

										$time  = time();
										$video = $ffmpeg->output("$filepath.$time.mp4")->ready();
										$sfile = "$filepath.$time.mp4";
									}

									catch(Exception $error){
										$response_data       = array(
									        'code'     => '400',
										    'status'   => 'Bad Request',
									        'errors'         => array(
									            'error_id'   => '21',
									            'error_text' => 'An unknown error occurred. Please try again later!'
									        )
									    );
									    self::json($response_data);
									}
								}

								$re_data = array(
				                	'user_id' => $me['user_id'],
				                	'time' => time(),
				                	'type' => '2',
				                	'media_file' => $sfile,
				                	'duration' => $duration
				                );

				                if (!empty($_POST['caption'])) {
									$text = Generic::cropText($_POST['caption'],500);
									$text = Generic::secure($text);
									$re_data['caption'] = $text;
								}

								$story_id = $story->addStory($re_data);

								if (is_numeric($story_id)) {
									$response_data       = array(
								        'code'     => '200',
									    'status'   => 'OK',
									    'id' => $story_id,
								        'data'         => array(
								            'message' => 'Your post has been published successfully'
								        )
								    );
								    self::json($response_data);
								}

								else{
									$response_data       = array(
								        'code'     => '400',
									    'status'   => 'Bad Request',
								        'errors'         => array(
								            'error_id'   => '21',
								            'error_text' => 'An unknown error occurred. Please try again later!'
								        )
								    );
								    self::json($response_data);
								}
							}
						}

						else{
							$response_data       = array(
						        'code'     => '400',
							    'status'   => 'Bad Request',
						        'errors'         => array(
						            'error_id'   => '21',
						            'error_text' => 'An unknown error occurred. Please try again later!'
						        )
						    );
						    self::json($response_data);
						}
					}
					else{
						$mx_size         = $mx_size;
						$response_data       = array(
					        'code'     => '400',
						    'status'   => 'Bad Request',
					        'errors'         => array(
					            'error_id'   => '22',
					            'error_text' => str_replace('{{size}}', $mx_size, 'Your post exceeds the maximum upload size for this site. Maximum upload size: {{size}}')
					        )
					    );
					    self::json($response_data);
					}
				}

				else{
					$response_data       = array(
				        'code'     => '400',
					    'status'   => 'Bad Request',
				        'errors'         => array(
				            'error_id'   => '23',
				            'error_text' => 'You have reached the daily update limit for your story. maximum limit is 20'
				        )
				    );
				    self::json($response_data);
				}
			}
			$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '21',
		            'error_text' => 'An unknown error occurred. Please try again later!'
		        )
		    );
		    self::json($response_data);
    	}
	}


	private function delete_story_()
	{
		global $me;
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
    	elseif (empty($_POST['story_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '24',
		            'error_text' => 'Please Enter the story id'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
    		$story_id = Generic::secure($_POST['story_id']);
			$story    = new Story();
			$stories  = $story->setUserById($me['user_id'])->deleteStory($story_id);
			if ($stories == false) {
				$response_data       = array(
			        'code'     => '400',
				    'status'   => 'Bad Request',
			        'errors'         => array(
			            'error_id'   => '14',
			            'error_text' => 'Sorry There Is Something Wrong!! Please Try Again Later.'
			        )
			    );
			    self::json($response_data);
			}
			else{
				$response_data       = array(
			        'code'     => '200',
				    'status'   => 'OK',
			        'message'     => 'story successfully deleted'
			    );
			    self::json($response_data);
			}
    	}
	}


	private function fetch_stories_() {
    	global $me, $db;
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
    	else{
    		$posts    = new Posts();
    		$story    = new Story();
    		$stories  = $story->setUserById($me['user_id'])->getStories();
    		foreach ($stories as $value) {
    			$value->live_now = $db->where('stream_name','','<>')->where('live_ended',0)->getValue(T_POSTS,'COUNT(*)');
    			$value->live_id = $db->where('stream_name','','<>')->where('live_ended',0)->getValue(T_POSTS,'post_id');
    			$is_seen = $db->where('story_id',$value->id)->where('user_id' , $me['user_id'])->getValue(T_STORY_VIEWS,'COUNT(*)');
	    		if ($is_seen == 0) {
	    			$db->insert(T_STORY_VIEWS,array(
                        'story_id' => $value->id,
                        'user_id'  => $me['user_id'],
                        'time'     => time()
                    ));
	    		}
                
    			$value->media_file = media($value->media_file);
    			$value->avatar = media($value->avatar);
    			$value->stories = $story->getUserStory($value->user_id);
    			foreach ($value->stories as $key => $value2) {
    				$value2->media_file = media($value2->media_file);
    				$value2->time_text = time2str($value2->time);
    				$value2->caption = strip_tags($value2->caption);
    			}
    		}
			$response_data       = array(
		        'code'     => '200',
			    'status'   => 'OK',
		        'data'         => $stories
		    );
		    self::json($response_data);
    	}

    }


}