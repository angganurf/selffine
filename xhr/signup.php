<?php 

if ($action == 'signup' && $config['signup_system'] == 'on') {
	$error  = false;
	$post   = array();
	$post[] = (empty($_POST['username']) || empty($_POST['email']));
	$post[] = (empty($_POST['password']) || empty($_POST['conf_password']));

	if (in_array(true, $post)) {
		$error = lang('please_fill_fields');
	}

	else{

		if (User::userNameExists($_POST['username'])) {
			$error = lang('username_is_taken');
		}

		else if(strlen($_POST['username']) < 4 || strlen($_POST['username']) > 16){
			$error = lang('username_characters_length');
		}

		else if(!preg_match('/^[\w]*[a-zA-Z]{1}[\w]*$/', $_POST['username'])){
			$error = lang('username_invalid_characters');
		}

		else if(User::userEmailExists($_POST['email'])){
			$error = lang('email_exists');
		}

		else if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
			$error = lang('email_invalid_characters');
		}

		else if($_POST['password'] != $_POST['conf_password']){
			$error = lang('password_not_match');
		}

		elseif (strlen($_POST['conf_password']) < 4) {
			$error = lang('password_is_short');
		}
		elseif ($config['account_validation'] == 1 && $config['validation_method'] == 'sms') {
			if (empty($_POST['phone_number'])) {
				$error = lang('phone_number_empty');
			}
			elseif (!empty($_POST['phone_number']) && !preg_match('/^\+?\d+$/', $_POST['phone_number'])) {
				$error = lang('worng_phone_number');
			}
			elseif (!empty($_POST['phone_number']) && User::userPhoneExists($_POST['phone_number'])) {
				$error = lang('phone_already_used');
			}
		}
		$blacklist = $user->isInBlackList($_POST['username'],$_POST['email']);
		if ($blacklist['count'] > 0) {
			if ($blacklist['type'] == 'username') {
				$error = lang('username_in_blacklist');
			}
			elseif ($blacklist['type'] == 'email') {
				$error = lang('email_in_blacklist');
			}
			elseif ($blacklist['type'] == 'email_username') {
				$error = lang('email_username_in_blacklist');
			}
			else{
				$error = lang('ip_in_blacklist');
			}
		}
	}

	if(!empty($config['specific_email_signup'])){
		if (preg_match_all('~@(.*?)(.*)~', $_POST['email'], $matches) && !empty($matches[2]) && !empty($matches[2][0]) && $matches[2][0] !== $config['specific_email_signup']) {
            $error = str_replace('{0}',$config['specific_email_signup'] ,lang('email_provider_specific_mail'));
		}
	}
	//block specific Emails for example gmail.com users couldn't sign up
	if (preg_match_all('~@(.*?)(.*)~', $_POST['email'], $matches) && !empty($matches[2]) && !empty($matches[2][0]) && IsBanned($matches[2][0])) {
		$error = lang('email_provider_banned');
	}
	if (empty($error)) {
		try {
			$register = User::registerUser();
			$data['status']  = 200;
			if ($config['account_validation'] == 1) {
				$data['message'] = lang('click_on_activation_link');
				if ($config['validation_method'] == 'sms') {
					$data['message'] = lang('successfully_joined_created_sms');
					$data['url'] = $config['site_url'] . "/sms_validate";
				}
			} else {
				$data['message'] = lang('successfully_joined_desc');
			}
		} catch (Exception $e) {
			$data['status']  = 400;
			$data['message'] = $e->getMessage();
		}
	}
	else{
		$data['status']  = 400;
		$data['message'] = $error;
	}
}
