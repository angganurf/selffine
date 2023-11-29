<?php 
/**
 * 
 */
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;
class MessagesEndPoint extends Generic
{
	
	function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'get_chats':
				self::get_chats_();
				break;
			case 'get_user_messages':
				self::get_user_messages_();
				break;
			case 'send_message':
				self::send_messages_();
				break;
			case 'send_media_message':
				self::send_media_messages_();
				break;		
			case 'get_media_information':
				self::get_media_information();
				break;	
			case 'clear_messages':
				self::clear_messages_();
				break;
			case 'delete_chat':
				self::delete_chat_();
				break;
			case 'delete_message':
				self::delete_message_();
				break;
			case 'create_call':
				self::create_call_();
				break;
			case 'check_for_answer':
				self::check_for_answer_();
				break;
			case 'cancel_call':
				self::cancel_call_();
				break;
			case 'answer_call':
				self::answer_call_();
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

	private function answer_call_()
	{
		global $me,$config,$pixelphoto,$sqlConnect;
		if (IS_LOGGED == false) {
    		$data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($data);
    	}
    	if (empty($_POST['type']) || !in_array($_POST['type'], ['video','audio'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'type can not be empty'
            ); 
            self::json($data);
        }
        if ( empty($_POST['id']) || !is_numeric($_POST['id'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
        }

    	$id = Generic::secure($_POST['id']);
        if ($_POST['type'] == 'audio') {
            $query = mysqli_query($sqlConnect, "UPDATE ".T_AUDIO_CALLES." SET `active` = 1 WHERE `id` = '$id'");
        } else {
            $query = mysqli_query($sqlConnect, "UPDATE `videocalles` SET `active` = 1 WHERE `id` = '$id'");
        }
        if ($config['agora_chat_video'] == 'on') {
            $query = mysqli_query($sqlConnect, "UPDATE " . T_AGORA . " SET `active` = 1 WHERE `id` = '$id'");
        }
        if ($query) {
            $data = array(
                'code'     => '200',
                'status' => 'OK',
            );
            if ($_POST['type'] == 'audio') {
                if ($config['agora_chat_video'] == 'on') {
                    $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AGORA . " WHERE `id` = '{$id}'");
                }
                else{
                    $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AUDIO_CALLES . " WHERE `id` = '{$id}'");
                }
                
                $sql   = mysqli_fetch_assoc($query);
                if (!empty($sql) && is_array($sql)) {
                    $context['incall']                 = $sql;
                    $context['incall']['in_call_user'] = $user->getUserDataById($sql['from_id']);
                    if ($context['incall']['to_id'] == $me['user_id']) {
                        $context['incall']['user']         = 1;
                        $context['incall']['access_token'] = $context['incall']['access_token'];
                    } else if ($context['incall']['from_id'] == $me['user_id']) {
                        $context['incall']['user']         = 2;
                        $context['incall']['access_token'] = $context['incall']['access_token_2'];
                    }
                    $context['incall']['room'] = $context['incall']['room_name'];
                    $data['calls_html']   = $pixelphoto->PX_LoadPage('home/templates/home/includes/talking');
                }
            }
            self::json($data);
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'call not found'
            ); 
            self::json($data);
        }


	}

	private function cancel_call_()
	{
		global $me,$config,$pixelphoto,$sqlConnect;
		if (IS_LOGGED == false) {
    		$data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($data);
    	}
		$user_id = $me['user_id'];
	    $query   = mysqli_query($sqlConnect, "DELETE FROM `videocalles` WHERE `from_id` = '$user_id'");
	    $query   = mysqli_query($sqlConnect, "DELETE FROM " . T_AGORA . " WHERE `from_id` = '$user_id'");
	    $query   = mysqli_query($sqlConnect, "DELETE FROM " . T_AUDIO_CALLES . " WHERE `from_id` = '$user_id'");
	    $data = array(
        	'code'     => '200',
            'status' => 'OK'
        ); 
        self::json($data);
	}

	private function check_for_answer_()
	{
		global $me,$config,$pixelphoto;
		if (IS_LOGGED == false) {
    		$data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($data);
    	}
		if (empty($_POST['type']) || !in_array($_POST['type'], ['video','audio'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'type can not be empty'
            ); 
            self::json($data);
        }
        if ( empty($_POST['id']) || !is_numeric($_POST['id'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
        }

        if ($_POST['type'] == 'video') {
	        $selectData = CheckCallAnswer($_POST['id']);
	        if ($selectData !== false) {
	            $data = ['idxxxx' => $selectData];
	            $data = array(
	                'code'     => '200',
               		'status' => 'OK',
	                'url' => $selectData['url'],
	                'message' => 'answered'
	            );
	        } else {
	            $check_declined = CheckCallAnswerDeclined($_POST['id']);
	            $data = ['id' => $check_declined];
	            if ($check_declined) {
	                $data = array(
	                    'code'     => '400',
               			'status' => 'Bad Request',
	                    'message' => 'call_declined'
	                );
	            }
	            else{
	            	$data = array(
	                    'code'     => '400',
               			'status' => 'Bad Request',
	                    'message' => 'no_answer'
	                );
	            }
	        }
        }
        else{
        	$data = array('status' => 500);

	        $selectData = CheckAudioCallAnswer($_POST['id']);
	        if ($selectData !== false) {
	            $data = array(
	                'code'     => '200',
               		'status' => 'OK',
	                'message' => 'answered'
	            );
	            $id    = Generic::secure($_POST['id']);
	            
	            if ($config['agora_chat_video'] == 'on') {
	                $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AGORA . " WHERE `id` = '{$id}'");
	            }
	            else{
	                $query = mysqli_query($sqlConnect, "SELECT * FROM " . T_AUDIO_CALLES . " WHERE `id` = '{$id}'");
	            }
	            $sql   = mysqli_fetch_assoc($query);
	            if (!empty($sql) && is_array($sql)) {
	                $context['incall']                 = $sql;
	                $user         = new User();
	                if ($config['agora_chat_video'] == 'on') {
	                    $context['incall']['in_call_user'] = $user->getUserDataById($sql['to_id']);
	                }
	                else{
	                    $context['incall']['in_call_user'] = $user->getUserDataById($sql['to_id']);
	                    if ($context['incall']['to_id'] == $me['user_id']) {
	                        $context['incall']['user']         = 1;
	                        $context['incall']['access_token'] = $context['incall']['access_token'];
	                    } else if ($context['incall']['from_id'] == $me['user_id']) {
	                        $context['incall']['user']         = 2;
	                        $context['incall']['access_token'] = $context['incall']['access_token_2'];
	                    }
	                }
	                    
	                $context['incall']['room'] = $context['incall']['room_name'];
	                $data['calls_html']   = $pixelphoto->PX_LoadPage('home/templates/home/includes/talking');
	            }
	        } else {

	            $check_declined = CheckAudioCallAnswerDeclined($_POST['id']);
	            if ($check_declined) {
	                $data = array(
	                    'code'     => '400',
               			'status' => 'Bad Request',
	                    'message' => 'call_declined'
	                );
	            }
	            else{
	            	$data = array(
	                    'code'     => '400',
               			'status' => 'Bad Request',
	                    'message' => 'no_answer'
	                );
	            }
	        }
        }
        self::json($data);
	}

	private function create_call_()
	{
		global $me,$config,$pixelphoto;
		if (IS_LOGGED == false) {
    		$data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '17',
		            'error_text' => 'Please Login And Try Again'
		        )
		    );
		    self::json($data);
    	}
		if ( empty($_POST['user_id']) || $_POST['user_id'] == $me['user_id'] ) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'user_id can not be empty'
            ); 
            self::json($data);
        }
        if (empty($_POST['type']) || !in_array($_POST['type'], ['video','audio'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'type can not be empty'
            ); 
            self::json($data);
        }


        if ($config['agora_chat_video'] == 'on') {
            $room_script  = sha1(rand(1111111, 9999999999));
            $context['AgoraToken'] = null;
            if (!empty($config['agora_chat_app_certificate'])) {
            	require_once('sys/libs/AgoraDynamicKey/src/RtcTokenBuilder.php');
                $appID = $config['agora_chat_app_id'];
                $appCertificate = $config['agora_chat_app_certificate'];
                $uid = 0;
                $uidStr = "0";
                $role = RtcTokenBuilder::RoleAttendee;
                $expireTimeInSeconds = 36000000;
                $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
                $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
                $context['AgoraToken'] = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $room_script, $uid, $role, $privilegeExpiredTs);
            }

            $call_type = Generic::secure($_POST['type']);
            $insertData = CreateNewAgoraCall(array(
                'from_id' => $me['user_id'],
                'to_id' => Generic::secure($_POST['user_id']),
                'room_name' => $room_script,
                'type' => $call_type,
                'status' => 'calling',
                'access_token' => $context['AgoraToken']
            ));
            $user         = new User();
            $context['calling_user'] = $user->getUserDataById(Generic::secure($_POST['user_id']));
            if ($insertData > 0) {
                $context['call_type'] = $call_type;
                $data = array(
                	'code'     => '200',
                    'status' => 'OK',
                    'access_token' => '',
                    'id' => $insertData,
                    'url' => $config['site_url'] . '/video_call/' . $room_script,
                    'html' => $pixelphoto->PX_LoadPage('home/templates/home/includes/calling'),
                    'text_no_answer' => lang('no_answer'),
                    'text_please_try_again_later' => lang('try_again_later')
                );
            }
        }
        else{
            $room_script  = sha1(rand(1111111, 9999999));
            $accountSid   = $config['video_accountSid'];
            $apiKeySid    = $config['video_apiKeySid'];
            $apiKeySecret = $config['video_apiKeySecret'];
            $call_id      = substr(md5(microtime()), 0, 15);
            $call_id_2    = substr(md5(time()), 0, 15);
            $token        = new AccessToken($accountSid, $apiKeySid, $apiKeySecret, 3600, $call_id);
            $grant        = new VideoGrant();
            $grant->setRoom($room_script);
            $token->addGrant($grant);
            $token_ = $token->toJWT();
            $token2 = new AccessToken($accountSid, $apiKeySid, $apiKeySecret, 3600, $call_id_2);
            $grant2 = new VideoGrant();
            $grant2->setRoom($room_script);
            $token2->addGrant($grant2);
            $token_2    = $token2->toJWT();
            $vid_array = array(
                'access_token' => Generic::secure($token_),
                'from_id' => $me['user_id'],
                'to_id' => Generic::secure($_POST['user_id']),
                'access_token_2' => Generic::secure($token_2),
                'room_name' => $room_script
            );
            $insertData = CreateNewVideoCall($vid_array);
            if ($insertData > 0) {
                $context['call_type'] = Generic::secure($_POST['type']);
                $html = '';
                
                $context['calling_user'] = $user->getUserDataById($_GET['user_id2']);
                $html = $pixelphoto->PX_LoadPage('home/templates/home/includes/calling');

                $data = array(
                    'code'     => '200',
                    'status' => 'OK',
                    'access_token' => $token_,
                    'id' => $insertData,
                    'url' => $config['site_url'] . '/video_call/' . $insertData,
                    'html' => $html,
                    'text_no_answer' => lang('no_answer'),
                    'text_please_try_again_later' => lang('try_again_later')
                );
            }
        }
        self::json($data);
	}

	private function get_chats_()
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
    	else{
    		$messages  = new Messages();
    		$user    = new User();
    		$messages->limit = !empty($_POST['limit']) && $_POST['limit'] <= 50 ? Generic::secure($_POST['limit']) : 30;
			$offset  = !empty($_POST['offset']) ? Generic::secure($_POST['offset']) : false;
    		$messages->setUserById($me['user_id']);
			$chats_history = $messages->getChats($offset);
			foreach ($chats_history as $key => $value) {
				$user->setUserById($value->user_id);
				$value->avatar = media($value->avatar);
				$value->last_message = strip_tags($value->last_message);
				$value->time_text = time2str($value->time);

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




			}
			$response_data       = array(
		        'code'     => '200',
			    'status'   => 'OK',
			    'data'     => $chats_history
		    );
		    self::json($response_data);
    	}
	}


	private function get_user_messages_()
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
    	elseif (empty($_POST['user_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '20',
		            'error_text' => 'Please Enter the user id'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
    		$data = array();
    		$messages  = new Messages();
    		$user    = new User();
    		$user_id = Generic::secure($_POST['user_id']);
    		$messages->limit = !empty($_POST['limit']) && $_POST['limit'] <= 50 ? Generic::secure($_POST['limit']) : 30;
			$offset  = !empty($_POST['offset']) ? Generic::secure($_POST['offset']) : false;
			$new  = !empty($_POST['new']) && $_POST['new'] == true ? Generic::secure($_POST['new']) : false;
    		$messages->setUserById($me['user_id']);
    		$user->setUserById($user_id);
			$new_user = $user->userData($user->getUser());
			if (empty($new_user)) {
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
			$conv_data['c_privacy']   = $user->chatPrivacy($new_user->user_id);
			$conv_data['is_blocked']  = $user->isBlocked($new_user->user_id);
			$conv_data['ami_blocked'] = $user->isBlocked($new_user->user_id,true);
			$messages->setUserById($me['user_id']);
			$to_id     = $new_user->user_id;
			$user_data = $user->getUserDataById($to_id);
			unset($user_data->password);
			unset($user_data->email_code);
			unset($user_data->login_token);
			unset($user_data->edit);
			$user_data->time_text = time2str($user_data->last_seen);
			$user_data->cover = media($user_data->cover);
			$user_data->avatar = media($user_data->avatar);
			$user_data->is_following = $user->isFollowing($user_data->user_id,$me['user_id']);
			$user_data->is_blocked  = $user->isBlocked($user_id,false);
			$conv_data['user_data'] = $user_data;
			if (!empty($_POST['before']) && empty($_POST['after']) && $_POST['new'] == false) {
				$offset  = !empty($_POST['before']) ? Generic::secure($_POST['before']) : false;
				$conv_data['messages'] = $messages->getMessages($to_id,$offset,$new,'DESC','<');
			}
			elseif (!empty($_POST['after']) && empty($_POST['before']) && $_POST['new'] == false) {
				$offset  = !empty($_POST['after']) ? Generic::secure($_POST['after']) : false;
				$conv_data['messages'] = $messages->getMessages($to_id,$offset,$new,'ASC','>');
			}
			else{
				$conv_data['messages'] = $messages->getMessages($to_id,false,$new,'DESC','>');
			}
			

			foreach ($conv_data['messages'] as $key => $value) {
				if (!empty($value->media_file)) {
					$value->media_file = media($value->media_file);
				}
				$value->text = strip_tags($value->text);
				$value->time_text = time2str($value->time);
				$value->position  = 'left';
                if ($value->from_id == $me['user_id']) {
                    $value->position  = 'right';
                }
			}
			$response_data       = array(
		        'code'     => '200',
			    'status'   => 'OK',
			    'data'     => $conv_data
		    );
		    self::json($response_data);
    	}
	}

	private function get_media_information(){
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
    	elseif (empty($_FILES['media_file'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '35',
		            'error_text' => 'you must upload file'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
			$getID3 = new getID3;
			if (!empty($_FILES['media_file']) && file_exists($_FILES['media_file']['tmp_name'])) {
				$fileinfo = $getID3->analyze($_FILES['media_file']['tmp_name']);
				//var_dump($fileinfo);
				getid3_lib::CopyTagsToComments($tag);
				$response_data       = array(
					'code'     => '200',
					'status'   => 'OK',
					'data'     => array(
						'fileformat' => $fileinfo['fileformat'],
						'mime_type' => $fileinfo['mime_type'],
						'width' => ($fileinfo['xmp']['exif']['PixelXDimension'] !== null ) ? $fileinfo['xmp']['exif']['PixelXDimension'] : $fileinfo['video']['resolution_x'],
						'height' => ($fileinfo['xmp']['exif']['PixelYDimension'] !== null ) ? $fileinfo['xmp']['exif']['PixelYDimension'] : $fileinfo['video']['resolution_y'],
						'size' => $_FILES['media_file']['size']
					)
				);
			}else{
				$response_data       = array(
					'code'     => '400',
					'status'   => 'Bad Request',
					'errors'         => array(
						'error_id'   => '35',
						'error_text' => 'can nopt handle this file'
					)
				);
			}

			self::json($response_data);
		}
	}

	private function send_media_messages_(){
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
    	elseif (empty($_POST['user_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '20',
		            'error_text' => 'Please Enter the user id'
		        )
		    );
		    self::json($response_data);
    	}
    	elseif (empty($_FILES['send_file'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '35',
		            'error_text' => 'you can not send empty message'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
			$text      = '';
			$messages  = new Messages();
			//$notif        = new Notifications();
			$to_id     = Generic::secure($_POST['user_id']);

			$new_string        = pathinfo($_FILES['send_file']['name'], PATHINFO_FILENAME) . '.' . strtolower(pathinfo($_FILES['send_file']['name'], PATHINFO_EXTENSION));
				$file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);
				$media  = new Media();
				$media->setFile(array(
					'file' => $_FILES['send_file']['tmp_name'],
					'name' => $_FILES['send_file']['name'],
					'size' => $_FILES['send_file']['size'],
					'type' => $_FILES['send_file']['type'],
					'allowed' => 'jpg,jpeg,png,gif,zip,txt,mp4,webm,flv',
				));
				$file = $media->uploadFile();
				if (!empty($file) && !empty($file['filename'])) {
					if ($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif') {
						$re_data['media_type'] = 'image';
					}
					elseif ($file_extension == 'mp4' || $file_extension == 'webm' || $file_extension == 'flv') {
						$re_data['media_type'] = 'video';
					}
					else{
						$re_data['media_type'] = 'file';
					}
				}

			$re_data   = array(
				'from_id' => $me['user_id'],
				'to_id' => $to_id,
				'text' => $re_data['media_type'],
				'media_file' => $file['filename'],
				'media_name' => $file['name'],
				'time' => time()
			);
         
			$msg_data = $messages->sendMessage($re_data);
			$msg_data->media_file = media($msg_data->media_file);
			$msg_data->text = strip_tags($msg_data->text);
			$msg_data->time_text = time2str($msg_data->time);
			$msg_data->hash_id = $_POST['hash_id'];
			if (!empty($msg_data)) {
				$response_data       = array(
			        'code'     => '200',
				    'status'   => 'OK',
				    'data'     => $msg_data
			    );
			    self::json($response_data);
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

	private function send_messages_()
	{
		global $me,$db;
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
    	elseif (empty($_POST['user_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '20',
		            'error_text' => 'Please Enter the user id'
		        )
		    );
		    self::json($response_data);
    	}
    	elseif (empty($_POST['text'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '35',
		            'error_text' => 'you can not send empty message'
		        )
		    );
		    self::json($response_data);
    	}
    	elseif (empty($_POST['hash_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '36',
		            'error_text' => 'hash id con not be empty'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
    		$text      = Generic::secure($_POST['text']);
			$messages  = new Messages();
			//$notif        = new Notifications();
			$to_id     = Generic::secure($_POST['user_id']);
			$re_data   = array(
				'from_id' => $me['user_id'],
				'to_id' => $to_id,
				'text' => $text,
				'time' => time()
			);
			if (!empty($_POST['reply_id']) && is_numeric($_POST['reply_id'])) {
				$reply_id     = Generic::secure($_POST['reply_id']);
				$from_id = $me['user_id'];
				$is_found = $db->where('id',$reply_id)->where('((to_id = '.$to_id.' AND from_id = '.$from_id.') OR (to_id = '.$from_id.' AND from_id = '.$to_id.'))')->getValue(T_MESSAGES,'COUNT(*)');
				if ($is_found > 0) {
					$re_data['reply_id'] = $reply_id;
				}
			}
			elseif (!empty($_POST['story_id']) && is_numeric($_POST['story_id'])) {
				$story_id     = Generic::secure($_POST['story_id']);
				$re_data['story_id'] = $story_id;
			}
         
			$msg_data = $messages->sendMessage($re_data);
			if (!empty($msg_data->media_file)) {
				$msg_data->media_file = media($msg_data->media_file);
			}
			$msg_data->text = strip_tags($msg_data->text);
			$msg_data->time_text = time2str($msg_data->time);
			$msg_data->hash_id = $_POST['hash_id'];
			if (!empty($msg_data)) {
				$response_data       = array(
			        'code'     => '200',
				    'status'   => 'OK',
				    'data'     => $msg_data
			    );
			    self::json($response_data);
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


	private function clear_messages_()
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
    	elseif (empty($_POST['user_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '20',
		            'error_text' => 'Please Enter the user id'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
    		$to_id = Generic::secure($_POST['user_id']);
    		$messages = new Messages();
			$messages->setUserById($me['user_id']);
			$clear    = $messages->clearChat($to_id);
			if (!empty($clear)) {
				$response_data       = array(
			        'code'     => '200',
				    'status'   => 'OK',
				    'message'     => 'messages successfully deleted'
			    );
			    self::json($response_data);
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



	private function delete_chat_()
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
    	elseif (empty($_POST['user_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '20',
		            'error_text' => 'Please Enter the user id'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
    		$to_id = Generic::secure($_POST['user_id']);
    		$messages = new Messages();
			$messages->setUserById($me['user_id']);
			$delete   = $messages->deleteChat($to_id);

			if (!empty($delete)) {
				$response_data       = array(
			        'code'     => '200',
				    'status'   => 'OK',
				    'message'     => 'chat successfully deleted'
			    );
			    self::json($response_data);
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


	private function delete_message_()
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
    	elseif (empty($_POST['user_id'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '20',
		            'error_text' => 'Please Enter the user id'
		        )
		    );
		    self::json($response_data);
    	}
    	elseif (empty($_POST['messages']) && !is_array($_POST['messages'])) {
    		$response_data       = array(
		        'code'     => '400',
			    'status'   => 'Bad Request',
		        'errors'         => array(
		            'error_id'   => '37',
		            'error_text' => 'please select the messages that you want to delete them'
		        )
		    );
		    self::json($response_data);
    	}
    	else{
    		$ids = explode(',', Generic::secure($_POST['messages']));
    		$to_id = Generic::secure($_POST['user_id']);
    		$messages = new Messages();
			$messages->setUserById($me['user_id']);
			$clear    = $messages->deleteMessages($to_id,$ids);


			if (!empty($clear)) {
				$response_data       = array(
			        'code'     => '200',
				    'status'   => 'OK',
				    'message'     => 'message successfully deleted'
			    );
			    self::json($response_data);
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



	


	

	

		
}
