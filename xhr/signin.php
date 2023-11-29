<?php 

if ($action == 'signin') {

    $error  = false;

    if (empty($_POST['username']) || empty($_POST['password'])) {
        $error = lang('enter_ur_n_and_p');
    } 

    if(empty($error)){
        
        $login = User::loginUser();
        if ($login === true) {
            $data['status'] = 200;
        }

        else{
            if (!empty($login) && is_array($login) && !empty($login['two_factor']) && $login['two_factor'] == 1) {
                $data['status'] = 200;
                $data['url'] = $config['site_url'] . "/two_factor";
            }
            elseif (!empty($login) && is_array($login) && !empty($login['sms_validate']) && $login['sms_validate'] == 1) {
                $data['status'] = 200;
                $data['url'] = $config['site_url'] . "/sms_validate";
            }
            else{
                $data['status']  = 400;
                $data['message'] = lang('invalid_un_or_passwd');  
            }
        }
    }

    else{
        $data['status'] = 400;
        $data['message'] = $error;
    }
}

if ($action == 'two_factor_login') {
    $data['status'] = 400;
    $data['message'] = lang('unknown_error');
    if (!empty($_POST['code']) && is_numeric($_POST['code']) && !empty($_COOKIE['sms_user_password']) && !empty($_COOKIE['sms_user_username'])) {

        $username        = Generic::secure($_COOKIE['sms_user_username']);
        $password        = $_COOKIE['sms_user_password'];
        $getUserPassword = $db->where("(username = ? or email = ?)", array(
            $username,
            $username
        ))->getValue(T_USERS, 'password');

        $password_hashed = sha1($password);
        $password_hashed = Generic::secure($password_hashed);

        $db->where("(username = ? or email = ?)", array(
            $username,
            $username
        ));
        if (strlen($getUserPassword) == 40) {
            $db->where("password", $password_hashed);
            $login  = $db->getOne(T_USERS);
        } else if (strlen($getUserPassword) == 60) {
            $validate_password = password_verify($password, $getUserPassword);
            if ($validate_password) {
                $login = $db->where("(username = ? or email = ?)", array(
                    $username,
                    $username
                ))->getOne(T_USERS);
            }
        }

        if (!empty($login) && $login->email_code == sha1(Generic::secure($_POST['code']))) {
            $user_object = new User();
            $session_id  = sha1(rand(11111, 99999)) . time() . md5(microtime());
            $platform_details = $user_object->getUserBrowser();
            $insert_data = array(
                'user_id' => $login->user_id,
                'session_id' => $session_id,
                'time' => time(),
                'platform_details'  => json_encode($platform_details),
                'platform' => $platform_details['platform']
            );
            $insert              = $db->insert(T_SESSIONS, $insert_data);
            $_SESSION['user_id'] = $session_id;
            setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
            $update_user_data = array();
            $update_user_data['ip_address'] = get_ip_address();
            if (!empty($_POST['device_id'])) {
                $update_user_data['device_id'] = Generic::secure($_POST['device_id']);
            }

            $db->where('user_id',$login->user_id)->update(T_USERS,$update_user_data);

            unset($_COOKIE['sms_user_password']);
            unset($_COOKIE['sms_user_username']);
            setcookie('sms_user_password', null, -1);
            setcookie('sms_user_username', null, -1);
            $data['status'] = 200;
            $data['message'] = lang('successfully_loged_in');
        }
        else{
            $data['message'] = lang('wrong_confirmation_code');
        }
    }
    else{
        if (empty($_POST['code']) || !is_numeric($_POST['code'])) {
            $data['message'] = lang('confirmation_code_empty');
        }
    }
}

if ($action == 'activate_sms') {
    $data['status'] = 400;
    $data['message'] = lang('unknown_error');
    if (!empty($_POST['code']) && is_numeric($_POST['code']) && !empty($_COOKIE['sms_user_id']) && is_numeric($_COOKIE['sms_user_id'])) {
        $user = $db->where('user_id',Generic::secure($_COOKIE['sms_user_id']))->where('email_code',sha1(Generic::secure($_POST['code'])))->getOne(T_USERS);
        if (!empty($user)) {
            $db->where('user_id',$user->user_id)->update(T_USERS,[
                'email_code' => '',
                'active' => 1
            ]);
            unset($_COOKIE['sms_user_id']);
            setcookie('sms_user_id', null, -1);
            $data['status'] = 200;
            $data['message'] = lang('account_activated');
        }
        else{
            $data['message'] = lang('wrong_confirmation_code');
        }
    }
    else{
        if (empty($_POST['code']) || !is_numeric($_POST['code'])) {
            $data['message'] = lang('confirmation_code_empty');
        }
    }
}
if ($action == 'reset') {
    $error = '';
    if ($config['recaptcha'] == 'on' && !empty($config['recaptcha_secret_key'])) {
        if (empty($_POST['g-recaptcha-response'])) {
            $error = lang('please_fill_fields');
        }
        else{
            $recaptcha_data = array(
            'secret' => $config['recaptcha_secret_key'],
            'response' => $_POST['g-recaptcha-response']
            );

            $verify = curl_init();
            curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($verify, CURLOPT_POST, true);
            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($recaptcha_data));
            curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($verify);
            $response = json_decode($response);
            if (!$response->success) {
                $error = lang('reCaptcha_error');
            }
        }
    }
    if (empty($_POST['email'])) {
        $error = lang('please_fill_fields');
    } 
    if(!User::userEmailExists($_POST['email'])){
        $error = lang('email_not_exists');
    }

    if (empty($error)) {

        $user = new User;
        $user_data = $user->setUserByEmail($_POST['email'])->getUser();

        $email_code = sha1(time() + rand(111,999));
        $update_data = array('email_code' => $email_code);
        $db->where('user_id', $user_data->user_id);
        $update = $db->update(T_USERS, $update_data);

        $password_text = "Hello {{NAME}},
<br><br>".lang('v2_reset_password_msg')."
<br>
<a href=\"{{RESET_LINK}}\">".lang('v2_reset_password')."</a>
<br><br>
{{SITE_NAME}} Team.";

        $password_text = str_replace(
            array("{{NAME}}","{{SITE_NAME}}", "{{RESET_LINK}}"),
            array($user_data->name, $config['site_name'], $site_url . '/reset-password/' . $email_code),
            $password_text 
        );

        $send_email_data = array(
            'from_email' => $config['site_email'],
            'from_name' => $config['site_name'],
            'to_email' => $_POST['email'],
            'to_name' => $user_data->name,
            'subject' => lang('v2_reset_password'),
            'charSet' => 'UTF-8',
            'message_body' => $password_text,
            'is_html' => true
        );
        $send_message = Generic::sendMail($send_email_data);
        if ($send_message) {
            $data['status'] = 200;
            $data['message'] = lang('sent_email');
        } else {
            $data['status'] = 400;
            $data['message'] = lang('unknown_error');
        }
    } else {
        $data['status'] = 400;
        $data['message'] = $error;
    }
}

if ($action == 'reset-new') {

    $error  = false;
    $post   = array();
    $post[] = (empty($_POST['password']) || empty($_POST['confirm_passwd']));
    $post[] = (empty($_POST['code']));

    if (in_array(true, $post)) {
        $error = lang('please_fill_fields');
    } else {
        if($_POST['password'] != $_POST['confirm_passwd']){
            $error = lang('password_not_match');
        } else if (strlen($_POST['confirm_passwd']) < 4) {
            $error = lang('password_is_short');
        }
    }
    if (empty($error)) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_id = User::validateCode($_POST['code']);
        if (!$user_id) {
            exit;
        }

        $update_data = array(
            'password' => $password,
            'email_code' => sha1(microtime())
        );

        $update = $db->where('user_id', $user_id)->update(T_USERS, $update_data);
        if ($update) {
            $platform_details = $user->getUserBrowser();
            $session_id  = sha1(rand(11111, 99999)) . time() . md5(microtime());
            $insert_data = array(
                'user_id' => $user_id,
                'session_id' => $session_id,
                'time' => time(),
                'platform_details'  => json_encode($platform_details),
                'platform' => $platform_details['platform']
            );
            $insert              = $db->insert(T_SESSIONS, $insert_data);
            $_SESSION['user_id'] = $session_id;
            setcookie("user_id", $session_id, time() + (10 * 365 * 24 * 60 * 60), "/");
            $data['status'] = 200;
            $data['link'] = $site_url;
        }
    } else {
        $data['status'] = 400;
        $data['message'] = $error;
    }
}
if ($action == 'resend_code') {
    $email_code = sha1(time() + rand(111,999));
    $message_body = 'Hello {{NAME}},
                <br><br>
                Please confirm your email address by clicking the link below:
                <br>
                <a href="{{CONFIRM_LINK}}">Confirm email address</a>
                <br><br>
                {{SITE_NAME}} Team.
                ';
    $message_body = str_replace(
        array("{{NAME}}","{{SITE_NAME}}", "{{CONFIRM_LINK}}"),
        array($me['username'], $config['site_name'], $config['site_url'] . '/co/' . $email_code),
        $message_body 
    );
    $send_email_data = array(
        'from_email' => $config['site_email'],
        'from_name' => $config['site_name'],
        'to_email' => $me['email'],
        'to_name' => $me['username'],
        'subject' => 'Confirm your account',
        'charSet' => 'UTF-8',
        'message_body' => $message_body,
        'is_html' => true
    );
    $send_message = Generic::sendMail($send_email_data);
    if ($send_message) {
        $db->where('user_id',$me['user_id'])->update(T_USERS,[
            'email_code' => $email_code
        ]);
        $data['status'] = 200;
        $data['message'] = lang('click_on_activation_link');
    }
    else{
        $data['status'] = 400;
        $data['message'] = lang('email_not_sent');
    }
}