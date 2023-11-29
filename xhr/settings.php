<?php

if ($action == 'general' && IS_LOGGED && !empty($_POST['user_id'])) {

	$error  = false;
	$post   = array();
	$post[] = (empty($_POST['username']) || empty($_POST['email']));
	$post[] = (empty($_POST['gender']) || empty(is_numeric($_POST['user_id'])));

	if (in_array(true, $post)) {
		$error = lang('please_check_details');
	}

	else if(empty($user->isOwner($_POST['user_id'])) && empty($user->isAdmin())){
		$error = lang('please_check_details');
	}

	else{

		$user_id   = Generic::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;

		if (empty($user_data)) {
			$error = lang('user_not_exist');
		}

		if ($me->username != $_POST['username']) {
			if (User::userNameExists($_POST['username'])) {
				$error = lang('username_is_taken');
			}	
		}

		if(strlen($_POST['username']) < 4 || strlen($_POST['username']) > 32){
			$error = lang('username_characters_length');
		}

		if(preg_match('/[^\w]+/', $_POST['username'])){
			$error = lang('username_invalid_characters');
		}

		if($me->email != $_POST['email']){
			if (User::userEmailExists($_POST['email'])) {
				$error = lang('email_exists');
			}
		}

		if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
			$error = lang('email_invalid_characters');
		}
	}

	if (empty($error)) {
		
		$up_data = array(
			'username' => Generic::secure($_POST['username']),
			'email' => Generic::secure($_POST['email']),
			'gender' => Generic::secure($_POST['gender']),
		);
		$up_data['subscribe_price'] = 0;
		if (($config['private_photos'] == 'on' || $config['private_photos'] == 'on') && !empty($_POST['subscribe_price']) && is_numeric($_POST['subscribe_price']) && $_POST['subscribe_price'] > 0) {
			$up_data['subscribe_price'] = Generic::secure($_POST['subscribe_price']);
		}

		if ($user->isAdmin()) {
			if (!empty($_POST['active']) && isset($_POST['active'])) {
				if( Generic::secure($_POST['active']) == "on" ){
					$up_data['active'] = 1;
				}else{
					$up_data['active'] = 0;
				}
			}
			if (!empty($_POST['verified']) && isset($_POST['verified'])) {
				if( Generic::secure($_POST['verified']) == "on" ){
					$up_data['verified'] = 1;
				}else{
					$up_data['verified'] = 0;
				}
			}
			if (!empty($_POST['is_pro']) && isset($_POST['is_pro'])) {
				if( Generic::secure($_POST['is_pro']) == "on" ){
					$up_data['is_pro'] = 1;
				}else{
					$up_data['is_pro'] = 0;
				}
			}
			if (!empty($_POST['business_account']) && isset($_POST['business_account'])) {
				if( Generic::secure($_POST['business_account']) == "on" ){
					$up_data['business_account'] = 1;
					$up_data['profile'] = 2;
				}else{
					$up_data['profile'] = 1;
					$up_data['business_account'] = 0;
				}
			}
			if (!empty($_POST['wallet']) && isset($_POST['wallet']) && is_numeric($_POST['wallet']) && $_POST['wallet'] > 0) {
				$up_data['wallet'] = Generic::secure($_POST['wallet']);
			}
		}

		if (!empty($_POST['country']) && in_array($_POST['country'], array_keys($countries_name))) {
			$up_data['country_id'] = Generic::secure($_POST['country']);
		}

		$update  = $user->updateStatic($me->user_id,$up_data);
		$data['status'] = 200;
		$data['message'] = lang('changes_saved');
		
	}
	else{
		
		$data['status']  = 400;
		$data['message'] = $error;
	}
}

else if ($action == 'profile' && IS_LOGGED && !empty($_POST['user_id'])) {

	$error     = false;
	$request   = array();
	$request[] = (isset($_POST['fname']) && isset($_POST['lname']));
	$request[] = (isset($_POST['about']) && isset($_POST['website']));
	$request[] = (isset($_POST['facebook']) && isset($_POST['google']));
	$request[] = (isset($_POST['twitter']) && is_numeric($_POST['user_id']));

	if (in_array(false, $request)) {
		$error = lang('please_check_details');
	}

	else if(empty($user->isOwner($_POST['user_id'])) && empty($user->isAdmin())){
		$error = lang('unknown_error');
	}

	else{
		$user_id   = Generic::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		if (empty($user_data)) {
			$error = lang('user_not_exist');
		}

		if (len($_POST['fname']) > 12) {
			$error = lang('fname_is_long');
		}

		if (len($_POST['lname']) > 20) {
			$error = lang('lname_is_long');
		}

		if (len($_POST['about']) > 150) {
			$error = lang('about_is_long');
		}

		if (len($_POST['website']) && empty(Generic::isUrl($_POST['website']))) {
			$error = lang('invalid_webiste_url');
		}
		
		if (len($_POST['facebook']) && empty(Generic::isUrl($_POST['facebook']))) {
			$error = lang('invalid_facebook_url');
		}		
		
		if (len($_POST['google']) && empty(Generic::isUrl($_POST['google']))) {
			$error = lang('invalid_google_url');
		}		

		if (len($_POST['twitter']) && empty(Generic::isUrl($_POST['twitter']))) {
			$error = lang('invalid_twitter_url');
		}

		$profile = 1;
		if ($user_data->is_pro && $_POST['profile'] == 2) {
			$profile = 2;
		}
	}

	if (empty($error)) {	
		$up_data = array(
			'fname' => ((len($_POST['fname'])) ? Generic::secure($_POST['fname']) : ''),
			'lname' => ((len($_POST['lname'])) ? Generic::secure($_POST['lname']) : ''),
			'about' => ((len($_POST['about'])) ? Generic::secure($_POST['about']) : ''),
			'website' => ((len($_POST['website'])) ? urlencode($_POST['website']) : ''),
			'facebook' => ((len($_POST['facebook'])) ? urlencode($_POST['facebook']) : ''),
			'google' => ((len($_POST['google'])) ? urlencode($_POST['google']) : ''),
			'twitter' => ((len($_POST['twitter'])) ? urlencode($_POST['twitter']) : ''),
			'profile' => $profile,
		);

		if ($context['user']['business_account'] == 1 && $config['business_account'] == 'on') {

			if (!empty($_POST['b_site']) && Generic::isUrl($_POST['b_site'])) {
				$up_data['b_site'] = Generic::secure($_POST['b_site']);
			}

			if (!empty($_POST['b_name'])) {
				$up_data['b_name'] = Generic::secure($_POST['b_name']);
			}

			if (!empty($_POST['b_email'])) {
				$up_data['b_email'] = Generic::secure($_POST['b_email']);
			}

			if (!empty($_POST['b_phone'])) {
				$up_data['b_phone'] = Generic::secure($_POST['b_phone']);
			}

			if (!empty($_POST['b_site_action']) && in_array($_POST['b_site_action'], array_keys($context['call_action']))) {
				$up_data['b_site_action'] = Generic::secure($_POST['b_site_action']);
			}
		}

		// if (!empty($_POST['business_account']) && isset($_POST['business_account'])) {
		// 	if( Generic::secure($_POST['business_account']) == "on" ){
		// 		$up_data['business_account'] = 1;
		// 	}else{
		// 		$up_data['business_account'] = 0;
		// 	}
		// }

		$update          = $user->updateStatic($me->user_id,$up_data);
		$data['status']  = 200;
		$data['message'] = lang('changes_saved');	
	}

	else{
		
		$data['status']  = 400;
		$data['message'] = $error;
	}
}

else if ($action == 'edit-avatar' && IS_LOGGED && !empty($_POST['user_id'])) {

	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || $user->isAdmin())){

		$user_id   = Generic::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		$data      = array('status' => 400);

		if (!empty($me)) {
			if (!empty($_FILES['avatar']) && file_exists($_FILES['avatar']['tmp_name'])) {
				$media = new Media();

				if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
					$data['status']  = 400;
					$data['message'] = "Upload failed with error code " . $_FILES['avatar']['error'];
				}else{
					$info = getimagesize($_FILES['avatar']['tmp_name']);
					if ($info === FALSE) {
						$data['status']  = 400;
						$data['message'] = "Unable to determine image type of uploaded file";
					}else if ( ($info[2] !== IMAGETYPE_JPEG) && ($info[2] !== IMAGETYPE_PNG)) {
						$data['status']  = 400;
						$data['message'] = "Not a jpeg or png";
					}else{
						$media->setFile(array(
							'file' => $_FILES['avatar']['tmp_name'],
							'name' => $_FILES['avatar']['name'],
							'size' => $_FILES['avatar']['size'],
							'type' => $_FILES['avatar']['type'],
							'allowed' => 'jpeg,jpg,png',
							'crop' => array(
								'height' => 400,
								'width' => 400,
							),
							'avatar' => true
						));
		
						$upload = $media->uploadFile();
		
						if (!empty($upload)) { 
							if(isset($upload['error'])){
								$data['status']  = 400;
								$data['message'] = $upload['error'];
							}else{
								if ($config['adult_images'] == 1 && !detect_safe_search(media($upload['filename']))) {
									if (file_exists($upload['filename'])) {
										@unlink($upload['filename']);
									}
									$media->deleteFromFTPorS3($upload['filename']);
									$data['status']  = 400;
									$data['message'] = lang('adult_image_file');
									echo json_encode($data, JSON_PRETTY_PRINT);
									exit();
								}
								$avatar = $upload['filename'];
								$data['status']  = 200;
								$data['message'] = lang('ur_avatar_changed');
								$data['avatar']  = media($avatar);
			
								$user->updateStatic($me->user_id,array(
									'avatar' => $avatar
								));
							}
						}
					}
				}

			}
		}
	}
}

else if ($action == 'change-password' && IS_LOGGED && !empty($_POST['user_id'])) {
    
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || $user->isAdmin())){

		$user_id   = Generic::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		$data      = array('status' => 400);
		$error     = false;

		if (!empty($me)) {
			$post   = array();
			$post[] = ((empty($_POST['old_password']) && !$user->isAdmin()));
			$post[] = (empty($_POST['new_password']) || empty($_POST['conf_password']));

			if (in_array(true, $post)) {
				$error = lang('please_check_details');
			}

			else{
				if (!HashPassword($_POST['old_password'], $user_data->password) && !$user->isAdmin()) {
					$error = lang('password_not_match');
				}
				if($_POST['new_password'] != $_POST['conf_password']){
					$error = lang('password_not_match');
				}

				if (strlen($_POST['conf_password']) < 4) {
					$error = lang('password_is_short');
				}
			}

			if (empty($error)) {
				$user->updateStatic($me->user_id,array(
					'password' => password_hash($_POST['conf_password'], PASSWORD_DEFAULT)
				));

				$data['status']  = 200;
				$data['message'] = lang('changes_saved');
			}

			else{
				$data['message'] = $error;
			}
		}
	}
}

else if ($action == 'delete-account' && IS_LOGGED && !empty($_POST['user_id'])) {

	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || $user->isAdmin())){

		$user_id   = Generic::secure($_POST['user_id']);
		$user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
		$me        = $user_data;
		$data      = array('status' => 400);
		$error     = false;

		if (!empty($me)) {

			if (!HashPassword($_POST['password'], $me->password)) {
				$error = lang('please_check_details');
			}

			if (empty($error)) {
				$user->setUserById($user_id)->delete();
				User::signoutUser();
				$data['status']  = 200;
				$data['message'] = lang('ur_account_deleted');
			}

			else{
				$data['message'] = $error;
			}
		}
	}
}

else if ($action == 'notifications' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || $user->isAdmin())){

		$user_id   = $user::secure($_POST['user_id']);
		$data      = array('status' => 400);
		$error     = false;
		$up_data   = array(
			'n_on_like' => ((!empty($_POST['on_like_post'])) ? '1' : '0'),
			'n_on_comment_like' => ((!empty($_POST['on_comment_like'])) ? '1' : '0'),
			'n_on_comment' => ((!empty($_POST['on_commnet_post'])) ? '1' : '0'),
			'n_on_follow' => ((!empty($_POST['on_follow'])) ? '1' : '0'),
			'n_on_mention' => ((!empty($_POST['on_mention'])) ? '1' : '0'),
			'n_on_comment_reply' => ((!empty($_POST['on_comment_reply'])) ? '1' : '0'),
		);

		$update = $user->updateStatic($user_id,$up_data);
		if (!empty($update)) {
			$data['status']  = 200;
			$data['message'] = lang('changes_saved');
		}
	}
}

else if ($action == 'privacy' && IS_LOGGED && !empty($_POST['user_id'])) {
	if(is_numeric($_POST['user_id']) && ($user->isOwner($_POST['user_id']) || $user->isAdmin())){
		$user_id = $user::secure($_POST['user_id']);
		$data    = array('status' => 400);
		$error   = false;

		if (isset($_POST['p_privacy']) && isset($_POST['c_privacy'])) {	

			$up_data = array(
				'p_privacy' => ((in_array($_POST['p_privacy'], array('0','1','2'))) ? $_POST['p_privacy'] : '2'),
				'c_privacy' => ((in_array($_POST['c_privacy'], array('1','2'))) ? $_POST['c_privacy'] : '1'),
				'search_engines' => ((isset($_POST['search_engines']) && $_POST['search_engines'] == 'on') ? '1' : '0'),
				'show_subscribers' => ((isset($_POST['show_subscribers']) && $_POST['show_subscribers'] == 'on') ? '1' : '0'),
			);

			$update = $user->updateStatic($user_id,$up_data);

			if (!empty($update)) {
				$data['status']  = 200;
				$data['message'] = lang('changes_saved');
			}
		}
	}
}

else if ($action == 'unblock-user' && IS_LOGGED && !empty($_POST['id'])) {
	if(is_numeric($_POST['id'])){
		$user_id = $user::secure($_POST['id']);
		$data    = array('status' => 304);
		$unblock = $user->unBlockUser($user_id);
		
		if (!empty($unblock)) {
			$data['status']  = 200;
			$data['message'] = lang('user_unblocked');
		}
	}
}

else if ($action == 'verify' && IS_LOGGED) {
	$data    = array('status' => 400);
	$me      = $user->user_data;
	if (!empty($_POST['name']) && !empty($_FILES['passport']) && !empty($_FILES['photo'])) {
		$inserted_data = array();
		$is_ok = false;
		$media = new Media();
		$media->setFile(array(
			'file' => $_FILES['photo']['tmp_name'],
			'name' => $_FILES['photo']['name'],
			'size' => $_FILES['photo']['size'],
			'type' => $_FILES['photo']['type'],
			'allowed' => 'jpeg,jpg,png',
			'crop' => array(
				'height' => 600,
				'width' => 600,
			),
			'avatar' => true
		));

		$upload = $media->uploadFile();

		if (!empty($upload['filename'])) { 
			$is_ok = true;
			$inserted_data['photo'] = $upload['filename'];
		}
		else{
			$data['message'] = lang('your_photo_invalid');
		}

		if ($is_ok == true) {
			$media->setFile(array(
				'file' => $_FILES['passport']['tmp_name'],
				'name' => $_FILES['passport']['name'],
				'size' => $_FILES['passport']['size'],
				'type' => $_FILES['passport']['type'],
				'allowed' => 'jpeg,jpg,png',
				'crop' => array(
					'height' => 600,
					'width' => 600,
				),
				'avatar' => true
			));

			$upload = $media->uploadFile();
			if (!empty($upload['filename'])) { 
				$is_ok = true;
				$inserted_data['passport'] = $upload['filename'];
			}
			else{
				$is_ok = false;
				$data['message'] = lang('your_ip_invalid');
			}
		}

		if ($is_ok == true) {
			$inserted_data['name'] = Generic::secure($_POST['name']);
			$inserted_data['message'] = !empty($_POST['message']) ? Generic::secure($_POST['message']) : '';
			$inserted_data['user_id'] = $me->user_id;
			$inserted_data['time'] = time();
			$id = $user->sendVerificationRequest($inserted_data);
			if ($id > 0) {
				$data['message'] = lang('request_done');
				$data['status'] = 200;
			}
			else{
				$data['message'] = lang('unknown_error');
			}
		}
	}
	else{
		$data['message'] = lang('please_check_details');
	}
}

elseif ($action == 'delete_session') {
	if (!empty($_POST['id'])) {
		$id = Generic::secure($_POST['id']);
		$user->delete_session($id);
	}
}
elseif ($action == 'change_profile') {
	if ($user->user_data->is_pro && $user->user_data->profile == 1) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 2));
	}
	elseif ($user->user_data->is_pro && $user->user_data->profile == 2) {
		$db->where('user_id',$user->user_data->user_id)->update(T_USERS,array('profile' => 1));
	}
	$data['status'] = 200;
}
elseif ($action == 'two_factor_verify' && IS_LOGGED && $config['two_factor'] == 1) {
	$data['status']  = 400;
	if (empty($_POST['code']) || !is_numeric($_POST['code'])) {
		$data['message'] = lang('confirmation_code_empty');
	}
	else{
		$found = $db->where('user_id',$me['user_id'])->where('email_code',sha1(Generic::secure($_POST['code'])))->getValue(T_USERS,'COUNT(*)');
		if ($found > 0) {
			$user->updateStatic($me['user_id'],[
				'email_code' => '',
				'two_factor' => 1,
			]);
			$data['status']  = 200;
			$data['message']  = lang('two_factor_enabled');
		}
		else{
			$data['message']  = lang('wrong_confirmation_code');
		}
	}
}
elseif ($action == 'two_factor' && IS_LOGGED && !empty($_POST['user_id']) && $config['two_factor'] == 1) {
	$data['status']  = 400;
	if (empty($_POST['two_factor']) || !in_array($_POST['two_factor'], ['enable','disable'])) {
		$data['message'] = lang('please_select_enable_two_factor');
	}
	elseif ($config['two_factor_type'] == 'both' || $config['two_factor_type'] == 'phone') {
		if (empty($_POST['phone_number'])) {
			$data['message'] = lang('phone_number_empty');
		}
		elseif (!empty($_POST['phone_number']) && !preg_match('/^\+?\d+$/', $_POST['phone_number'])) {
			$data['message'] = lang('worng_phone_number');
		}
		elseif (!empty($_POST['phone_number']) && $me['phone_number'] != $_POST['phone_number'] && User::userPhoneExists($_POST['phone_number'])) {
			$data['message'] = lang('phone_already_used');
		}
	}

	if (empty($data['message'])) {
		if ($_POST['two_factor'] == 'enable' && $me['two_factor'] == 1) {
			$data['message'] = lang('two_factor_already_enabled');
			header("Content-type: application/json");
			echo json_encode($data, JSON_PRETTY_PRINT);
			exit();
		}
		$data['modal'] = '';
		$update_data = array();
		if ($_POST['two_factor'] == 'enable') {
			$random_activation = rand(11111, 99999);
			$email_code = sha1($random_activation);
			$update_data['email_code'] = $email_code;
			if (!empty($_POST['phone_number']) && $_POST['phone_number'] != $me['phone_number']) {
				$update_data['phone_number'] = Generic::secure($_POST['phone_number']);
			}
			$message = "Your confirmation code is: $random_activation";

			if ($config['two_factor_type'] == 'email' || $config['two_factor_type'] == 'both') {
				$send_email_data = array(
					'from_email' => $config['site_email'],
					'from_name' => $config['site_name'],
					'to_email' => $me['email'],
					'to_name' => $me['username'],
					'subject' => 'Please verify that it’s you',
					'charSet' => 'UTF-8',
					'message_body' => $message,
					'is_html' => true
				);
				$send_message = Generic::sendMail($send_email_data);
			}
			if ($config['two_factor_type'] == 'phone' || $config['two_factor_type'] == 'both') {
				try {
					SendSMS(Generic::secure($_POST['phone_number']), $message);
				} catch (Exception $e) {
					$data['message'] = $e->getMessage();
					header("Content-type: application/json");
					echo json_encode($data, JSON_PRETTY_PRINT);
					exit();
				}
			}
			$data['message'] = lang('we_have_sent_you_code');
			$data['modal'] = 'show';
		}
		else{
			$update_data['two_factor'] = 0;
			$data['message'] = lang('two_factor_disabled');
		}

		$user->updateStatic($me['user_id'],$update_data);
		$data['status']  = 200;
        
	}
}
elseif ($action == 'withdraw' && IS_LOGGED && !empty($_POST['user_id']) && $config['withdraw_system'] == 'on') {
	$error  = false;

	if (empty($_POST['withdraw_method']) || !in_array($_POST['withdraw_method'], array_keys($config['withdrawal_payment_method'])) || $config['withdrawal_payment_method'][$_POST['withdraw_method']] != 1) {
        $error = lang('please_select_payment_method');
    }
    elseif ($_POST['withdraw_method'] == 'bank') {
        if (empty($_POST['iban']) || empty($_POST['country']) || empty($_POST['full_name']) || empty($_POST['swift_code']) || empty($_POST['address'])) {
            $error = lang('please_check_details');
        }
    }
    elseif ($_POST['withdraw_method'] == 'paypal') {
        if (empty($_POST['paypal_email'])) {
            $error = lang('please_check_details');
        } elseif (!empty($_POST['paypal_email']) && !filter_var($_POST['paypal_email'], FILTER_VALIDATE_EMAIL)) {
            $error = lang('email_invalid_characters');
        }
    }
    else {
        if (empty($_POST['transfer_to'])) {
            $error = lang('please_check_details');
        }
    }

    if (empty($_POST['amount'])) {
    	$error = lang('please_check_details');
    }
    else if($me['balance'] < $_POST['amount']){
		$error = lang('amount_more_balance');
	}

	else if(!is_numeric($_POST['amount']) || $_POST['amount'] < $config['m_withdrawal']){
		$error = lang('amount_less_50').' '.$config['m_withdrawal'];
	}

	$db->where('user_id',$me['user_id']);
    $db->where('status',0);
    $requests = $db->getValue(T_WITHDRAWAL, 'count(*)');

    if (!empty($requests)) {
        $error = lang('cant_request_withdrawal');
    }


	if (empty($error)) {
		$insert_array = array('type' => Generic::secure($_POST['withdraw_method']),
								'user_id'   => $me['user_id'],
            					'amount'    => Generic::secure($_POST['amount']),
            					'requested' => time(),
            					'currency' => $config['currency'],
								);

        if (!empty($_POST['paypal_email']) && $_POST['withdraw_method'] == 'paypal') {
            $update  = $user->updateStatic($me['user_id'],array('paypal_email' => Generic::secure($_POST['paypal_email'])));
            $insert_array['transfer_info']       = Generic::secure($_POST['paypal_email']);
        }
        else if ($_POST['withdraw_method'] == 'bank' && !empty($_POST['iban']) && !empty($_POST['country']) && !empty($_POST['full_name']) && !empty($_POST['swift_code']) && !empty($_POST['address'])) {
            $insert_array['iban']       = Generic::secure($_POST['iban']);
            $insert_array['country']    = Generic::secure($_POST['country']);
            $insert_array['full_name']  = Generic::secure($_POST['full_name']);
            $insert_array['swift_code'] = Generic::secure($_POST['swift_code']);
            $insert_array['address']    = Generic::secure($_POST['address']);
            $update  = $user->updateStatic($me['user_id'],array('paypal_email' => ''));
        }
        else{
            $insert_array['transfer_info']       = Generic::secure($_POST['transfer_to']);
            $update  = $user->updateStatic($me['user_id'],array('paypal_email' => ''));
        }
        

        $insert  = $db->insert(T_WITHDRAWAL,$insert_array);
        if (!empty($insert)) {
            $data['status']  = 200;
            $data['message'] = lang('withdrawal_request_sent');
        }
	}
	else{
		
		$data['status']  = 400;
		$data['message'] = $error;
	}
}


else if ($action == 'business' && IS_LOGGED && $config['business_account'] == 'on') {
	$data    = array('status' => 400);
	$me      = $user->user_data;
	if (!empty($_POST['business_name']) && !empty($_POST['email']) && !empty($_POST['phone'])) {

		if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$data['message'] = lang('email_invalid_characters');
		}
		elseif (empty(Generic::isUrl($_POST['website']))) {
			$data['message'] = lang('invalid_webiste_url');
		}
		elseif ($me->verified == 0 && empty($_FILES['passport']) && empty($_FILES['photo'])) {
			$data['message'] = lang('please_check_details');
		}
		else{
			$inserted_data = array();

			if ($me->verified == 0) {
				$is_ok = false;
				$media = new Media();
				$media->setFile(array(
					'file' => $_FILES['photo']['tmp_name'],
					'name' => $_FILES['photo']['name'],
					'size' => $_FILES['photo']['size'],
					'type' => $_FILES['photo']['type'],
					'allowed' => 'jpeg,jpg,png',
					'crop' => array(
						'height' => 600,
						'width' => 600,
					),
					'avatar' => true
				));

				$upload = $media->uploadFile();

				if (!empty($upload['filename'])) { 
					$is_ok = true;
					$inserted_data['photo'] = $upload['filename'];
				}
				else{
					$data['message'] = lang('your_photo_invalid');
				}

				if ($is_ok == true) {
					$media->setFile(array(
						'file' => $_FILES['passport']['tmp_name'],
						'name' => $_FILES['passport']['name'],
						'size' => $_FILES['passport']['size'],
						'type' => $_FILES['passport']['type'],
						'allowed' => 'jpeg,jpg,png',
						'crop' => array(
							'height' => 600,
							'width' => 600,
						),
						'avatar' => true
					));

					$upload = $media->uploadFile();
					if (!empty($upload['filename'])) { 
						$is_ok = true;
						$inserted_data['passport'] = $upload['filename'];
					}
					else{
						$is_ok = false;
						$data['message'] = lang('your_ip_invalid');
					}
				}
			}
			else{
				$is_ok = true;
			}

			if ($is_ok == true) {
				$inserted_data['name'] = Generic::secure($_POST['business_name']);
				$inserted_data['email'] = Generic::secure($_POST['email']);
				$inserted_data['phone'] = Generic::secure($_POST['phone']);
				$inserted_data['site'] = !empty($_POST['website']) ? Generic::secure($_POST['website']) : '';
				$inserted_data['user_id'] = $me->user_id;
				$inserted_data['time'] = time();
				$id = $db->insert(T_BUS_REQUESTS,$inserted_data);
				if ($id > 0) {
					$data['message'] = lang('bus_request_done');
					$data['status'] = 200;
				}
				else{
					$data['message'] = lang('unknown_error');
				}
			}
		}
	}
	else{
		$data['message'] = lang('please_check_details');
	}
}