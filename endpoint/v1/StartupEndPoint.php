<?php 
/**
 * 
 */
class StartupEndPoint extends Generic
{
	
	function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'startup_avatar':
				self::startup_avatar();
				break;
			case 'startup_info':
				self::startup_info();
				break;
			case 'startup_follow':
				self::startup_follow();
				break;
			case 'startup_suggestions':
				self::startup_suggestions();
				break;
			case 'startup_skip':
				self::startup_skip();
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



	private function startup_skip()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db,$countries_name;
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

    	$user         = new User();

    	if (!empty($_POST['startup_avatar']) && $_POST['startup_avatar'] == 1) {
			$user->updateStatic($me['user_id'],array(
				'startup_avatar' => 1
			));
		}
		if (!empty($_POST['startup_info']) && $_POST['startup_avatar'] == 1) {
			$user->updateStatic($me['user_id'],array(
				'startup_info' => 1
			));
		}
		if (!empty($_POST['startup_follow']) && $_POST['startup_follow'] == 1) {
			$user->updateStatic($me['user_id'],array(
				'startup_follow' => 1
			));
		}

		$data = array(
        	'code'     => '200',
            'status' => 'OK',
            'message' => 'Data updated successfully'
        ); 
        self::json($data);
    }

	private function startup_suggestions()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db,$countries_name;
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
    	$user         = new User();

    	$follow   = $user->followSuggestions();
		$all_users = array();
		foreach ($follow as $key => $user_data) {
			unset($user_data->password);
			unset($user_data->email_code);
			unset($user_data->login_token);
			unset($user_data->edit);
			$user_data->time_text = time2str($user_data->last_seen);
			$user_data->cover = media($user_data->cover);
			$user_data->avatar = media($user_data->avatar);
			$user_data->is_following = $user->isFollowing($user_data->user_id,$me['user_id']);
			$user_data->is_blocked  = $user->isBlocked($user_data->user_id,false);
			$all_users[]= $user_data;
		}

		$data = array(
        	'code'     => '200',
            'status' => 'OK',
            'data' => $all_users
        ); 
        self::json($data);

    }


	private function startup_follow()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db,$countries_name;
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
    	if (empty($_POST['ids'])) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'ids can not be empty'
            ); 
            self::json($data);
		}
		$ids = explode(',', $_POST['ids']);

		if (!is_array($ids)) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'ids can not be empty'
            ); 
            self::json($data);
		}

		$notif        = new Notifications();
		$user         = new User();
		foreach ($ids as $key => $id) {
			if (!empty($id) && is_numeric($id)) {
				$follower_id  = $me['user_id'];
				$following_id = Generic::secure($id);
				
				$user->setUserById($follower_id);
				$status       = $user->follow($following_id);
				if ($status === 1) {

					#Notify post owner
					$notif_conf = $notif->notifSettings($following_id,'on_follow');
					if ($notif_conf) {
						$re_data = array(
							'notifier_id' => $me['user_id'],
							'recipient_id' => $following_id,
							'type' => 'followed_u',
							'url' => un2url($me['username']),
							'time' => time()
						);
						
						$notif->notify($re_data);
					}	
				}
			}
		}
		$user->updateStatic($me['user_id'],array(
			'startup_follow' => 1
		));
		$data = array(
        	'code'     => '200',
            'status' => 'OK',
            'message' => 'Data updated successfully'
        ); 
        self::json($data);

    }



	private function startup_info()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db,$countries_name;
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

    	if (empty($_POST['country']) && empty($_POST['fname']) && empty($_POST['lname'])) {
    		$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'country , fname , lname can not be empty'
            ); 
            self::json($data);
		}
		elseif (!empty($_POST['fname']) && len($_POST['fname']) > 12) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'fname to long'
            ); 
            self::json($data);
		}
		elseif (!empty($_POST['lname']) && len($_POST['lname']) > 20) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'lname to long'
            ); 
            self::json($data);
		}
		else{
			$user         = new User();
			$up_data = array();
			if (!empty($_POST['country']) && in_array($_POST['country'], array_keys($countries_name))) {
				$up_data['country_id'] = Generic::secure($_POST['country']);
			}
			if (!empty($_POST['fname'])) {
				$up_data['fname'] = Generic::secure($_POST['fname']);
			}
			if (!empty($_POST['lname'])) {
				$up_data['lname'] = Generic::secure($_POST['lname']);
			}
			$up_data['startup_info'] = 1;
			$user->updateStatic($me['user_id'],$up_data);
			$data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Data updated successfully'
            ); 
            self::json($data);
		}

    }

	private function startup_avatar()
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

    	if (empty($_FILES['photo'])) {
    		$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'photo can not be empty'
            ); 
            self::json($data);
    	}

    	$media = new Media();
		$media->setFile(array(
			'file' => $_FILES['photo']['tmp_name'],
			'name' => $_FILES['photo']['name'],
			'size' => $_FILES['photo']['size'],
			'type' => $_FILES['photo']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array(
				'height' => 150,
				'width' => 150,
			),
			'avatar' => true
		));

		$upload = $media->uploadFile();

		if (!empty($upload)) { 
			$photo = $upload['filename'];
			$user         = new User();

			$user->updateStatic($me['user_id'],array(
				'avatar' => $photo,
				'startup_avatar' => 1
			));
			$data = array(
            	'code'     => '200',
                'status' => 'OK',
                'photo' => Media::getMedia($photo)
            ); 
            self::json($data);
		}
		else{
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'photo is invalid'
            ); 
            self::json($data);
		}
    }

}