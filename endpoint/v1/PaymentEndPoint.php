<?php 
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use SecurionPay\SecurionPayGateway;
use SecurionPay\Exception\SecurionPayException;
use SecurionPay\Request\CheckoutRequestCharge;
use SecurionPay\Request\CheckoutRequest;

class PaymentEndPoint extends Generic {



	public function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'top_wallet_stripe':
				self::top_wallet_stripe();
				break;
			case 'top_wallet_authorize':
				self::top_wallet_authorize();
				break;
			case 'top_wallet_securionpay_token':
				self::top_wallet_securionpay_token();
				break;
			case 'top_wallet_securionpay_handle':
				self::top_wallet_securionpay_handle();
				break;
			case 'top_wallet_yoomoney':
				self::top_wallet_yoomoney();
				break;
			case 'top_wallet_yoomoney_success':
				self::top_wallet_yoomoney_success();
				break;
			case 'top_wallet_iyzipay':
				self::top_wallet_iyzipay();
				break;
			case 'top_wallet_iyzipay_paid':
				self::top_wallet_iyzipay_paid();
				break;
			case 'top_wallet_cashfree':
				self::top_wallet_cashfree();
				break;
			case 'top_wallet_cashfree_paid':
				self::top_wallet_cashfree_paid();
				break;
			case 'top_wallet_bank_transfer':
				self::top_wallet_bank_transfer();
				break;
			case 'top_wallet_paysera':
				self::top_wallet_paysera();
				break;
			case 'top_wallet_paysera_success':
				self::top_wallet_paysera_success();
				break;
			case 'top_wallet_paystack':
				self::top_wallet_paystack();
				break;
			case 'top_wallet_paystack_success':
				self::top_wallet_paystack_success();
				break;
			case 'top_wallet_razorpay':
				self::top_wallet_razorpay();
				break;
			case 'top_wallet_razorpay_success':
				self::top_wallet_razorpay_success();
				break;
			case 'top_wallet_coinbase':
				self::top_wallet_coinbase();
				break;
			case 'top_wallet_coinbase_success':
				self::top_wallet_coinbase_success();
				break;
			case 'top_wallet_coinpayments':
				self::top_wallet_coinpayments();
				break;
			case 'top_wallet_coinpayments_check':
				self::top_wallet_coinpayments_check();
				break;
			case 'top_wallet_up':
				self::top_wallet_up();
				break;
			case 'pay_using_wallet':
				self::pay_using_wallet();
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

	private function pay_using_wallet()
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
        if (empty($_POST['type']) || !in_array($_POST['type'], array('pro','fund','store','unlock_image','unlock_video','subscribe'))) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'type can not be empty'
            ); 
            self::json($data);
        }


	    $price = 0;
	    $user = new User();
	    if ($_POST['type'] == 'pro') {
	    	if ($me['wallet'] < $config['pro_price']) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Please top up your wallet'
	            ); 
	            self::json($data);
	    	}
            $update = $user->updateStatic($me['user_id'],array('is_pro' => 1,'verified' => 1));
            $amount = $config['pro_price'];
            $date   = time();

            $db->insert(T_PAYMENTS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'pro_member',
                                          'date' => $date));

            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'pro_member',
                                          'time' => $date));
            $wallet = $me['wallet'] - $amount;
            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
            $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Upgraded to pro'
            ); 
            self::json($data);
        }
        elseif ($_POST['type'] == 'fund') {

        	if (empty($_POST['fund_id']) || empty($_POST['amount'])) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'fund_id , amount can not be empty'
	            ); 
	            self::json($data);
	    	}


            $fund_id = Generic::secure($_POST['fund_id']);
            $fund = $user->GetFundingById($fund_id);
            $amount = Generic::secure($_POST['amount']);
            if (!empty($fund) && !empty($fund->id)) {
                $wallet = $me['wallet'] - $amount;
                $admin_com = 0;
                if (!empty($config['donate_percentage']) && is_numeric($config['donate_percentage']) && $config['donate_percentage'] > 0) {
                    $admin_com = ($config['donate_percentage'] * $amount) / 100;
                    $amount = $amount - $admin_com;
                }
                $db->where('user_id',$fund->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                $db->insert(T_FUNDING_RAISE,array('user_id' => $me['user_id'],
                                                  'funding_id' => $fund_id,
                                                  'amount' => $amount,
                                                  'time' => time()));
                
                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'donate',
                                          'time' => time(),
                                          'admin_com' => $admin_com));
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                $notif   = new Notifications();
                $hashed_id = $fund_id;
                if (!empty($fund->hashed_id)) {
                    $hashed_id = $fund->hashed_id;
                }
                if ($fund->user_id != $me['user_id']) {

                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $fund->user_id,
                        'type' => 'donated',
                        'url' => $config['site_url'] . "/funding/".$hashed_id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                }
                $data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Payment successfully done'
	            ); 
	            self::json($data);
            }
            else{
            	$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Fund not found'
	            ); 
	            self::json($data);
            }
        }
        elseif ($_POST['type'] == 'store') {

        	if (empty($_POST['id']) || empty($_POST['license_id'])) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'id , license_id can not be empty'
	            ); 
	            self::json($data);
	    	}

            $id = Generic::secure($_POST['id']);
            $item_license = Generic::secure($_POST['license_id']);
            $store_image = $db->arrayBuilder()->where('id',$id)->getOne(T_STORE);
            if (!empty($store_image)) {
                $license_options = unserialize($store_image['license_options']);
                if (!empty($license_options[$item_license])) {
                    $amount = $license_options[$item_license];
                    $u = $db->arrayBuilder()->where('user_id',$store_image['user_id'])->getOne(T_USERS);
                    $commesion = $amount / 2;
                    $balance = $u['balance'] + $commesion;
                    $update = $user->updateStatic($store_image['user_id'],array('balance' => $balance));
                    $db->insert(T_TRANSACTIONS,array(
                        'user_id'       => $me['user_id'],
                        'amount'        => $amount,
                        'type'          => 'store',
                        'item_store_id' => $id,
                        'admin_com'     => $commesion,
                        'time'          => time(),
                        'item_license'  => $item_license
                        )
                    );

                    $db->where('id',$id)->update(T_STORE, array( 'sells' => $db->inc(1)));
                    $wallet = $me['wallet'] - $amount;
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                    $notif   = new Notifications();


                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $store_image['user_id'],
                        'type' => 'store_purchase',
                        'url' => $config['site_url'] . "/store/".$id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => 'Payment successfully done'
		            ); 
		            self::json($data);
                }
                else{
                	$data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => 'License not found'
		            ); 
		            self::json($data);
                }
            }
            else{
            	$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Product not found'
	            ); 
	            self::json($data);
            }
        }
        elseif ($_POST['type'] == 'unlock_image' && $config['private_photos'] == 'on') {

        	if (empty($_POST['post_id'])) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'post_id can not be empty'
	            ); 
	            self::json($data);
	    	}

            $post_id = Generic::secure($_POST['post_id']);
            $post = $db->where('post_id',$post_id)->getOne(T_POSTS);
            if (!empty($post) && $post->user_id != $me['user_id'] && !empty($post->price)) {
                $is_bought = $db->where('post_id',$post_id)->where('type','unlock image')->getValue(T_TRANSACTIONS,'COUNT(*)');
                if ($is_bought < 1) {
                    $amount = $post->price;
                    $admin_com = 0;
                    if ($config['private_photos_commission'] > 0) {
                        $admin_com = ($config['private_photos_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $wallet = $me['wallet'] - $post->price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'post_id' => $post_id,
                                              'type' => 'unlock image',
                                              'time' => time(),
                                              'admin_com' => $admin_com));
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                    $db->where('user_id',$post->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'unlock_user_image',
                        'url' => $config['site_url'] . "/post/".$post_id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => 'Payment successfully done'
		            ); 
		            self::json($data);
                }
                else{
                	$data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => 'You already bought this post'
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
        elseif ($_POST['type'] == 'unlock_video' && $config['private_videos'] == 'on') {

        	if (empty($_POST['post_id'])) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'post_id can not be empty'
	            ); 
	            self::json($data);
	    	}


            $post_id = Generic::secure($_POST['post_id']);
            $post = $db->where('post_id',$post_id)->getOne(T_POSTS);
            if (!empty($post) && $post->user_id != $me['user_id'] && !empty($post->price)) {
                $is_bought = $db->where('post_id',$post_id)->where('type','unlock video')->getValue(T_TRANSACTIONS,'COUNT(*)');
                if ($is_bought < 1) {
                    $amount = $post->price;
                    $admin_com = 0;
                    if ($config['private_videos_commission'] > 0) {
                        $admin_com = ($config['private_videos_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $wallet = $me['wallet'] - $post->price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'post_id' => $post_id,
                                              'type' => 'unlock video',
                                              'time' => time(),
                                              'admin_com' => $admin_com));
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                    $db->where('user_id',$post->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $post->user_id,
                        'type' => 'unlock_user_video',
                        'url' => $config['site_url'] . "/post/".$post_id,
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => 'Payment successfully done'
		            ); 
		            self::json($data);
                }
                else{
                	$data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => 'You already bought this post'
		            ); 
		            self::json($data);
                }
            }
            else{
            	$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'post not found'
	            ); 
	            self::json($data);
            }
        }
        elseif ($_POST['type'] == 'subscribe' && ($config['private_videos'] == 'on' || $config['private_photos'] == 'on')) {

        	if (empty($_POST['user_id'])) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'user_id can not be empty'
	            ); 
	            self::json($data);
	    	}

            $user_id = Generic::secure($_POST['user_id']);
            $user_data = $db->where('user_id',$user_id)->getOne(T_USERS);
            if (!empty($user_data) && $user_data->user_id != $me['user_id'] && !empty($user_data->subscribe_price)) {
                $month = 60 * 60 * 24 * 30;
                $is_subscribed = $db->where('user_id',$user_data->user_id)->where('subscriber_id',$me['user_id'])->where('time',(time() - $month),'>=')->getValue(T_SUBSCRIBERS,'COUNT(*)');
                if ($is_subscribed < 1) {
                    $amount = $user_data->subscribe_price;
                    $admin_com = 0;
                    if ($config['monthly_subscribers_commission'] > 0) {
                        $admin_com = ($config['monthly_subscribers_commission'] * $amount) / 100;
                        $amount = $amount - $admin_com;
                    }
                    $wallet = $me['wallet'] - $user_data->subscribe_price;
                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'subscription_id' => $user_data->user_id,
                                              'type' => 'subscribe',
                                              'time' => time(),
                                              'admin_com' => $admin_com));
                    $db->insert(T_SUBSCRIBERS,array('user_id' => $user_data->user_id,
                                                    'subscriber_id' => $me['user_id'],
                                                    'time' => time()));
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));
                    $db->where('user_id',$user_data->user_id)->update(T_USERS,array('balance'=>$db->inc($amount)));
                    $notif   = new Notifications();
                    $re_data = array(
                        'notifier_id' => $me['user_id'],
                        'recipient_id' => $user_data->user_id,
                        'type' => 'have_new_subscriber',
                        'url' => $config['site_url'] . "/".$me['username'],
                        'time' => time()
                    );
                    try {
                        $notif->notify($re_data);
                    } catch (Exception $e) {
                    }
                    $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => 'Payment successfully done'
		            ); 
		            self::json($data);
                }
                else{
                    $data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => 'You already subscribed'
		            ); 
		            self::json($data);
                }
            }
            else{
            	$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'User do not have subscribe option'
	            ); 
	            self::json($data);
            }
        }
    }

	private function top_wallet_up()
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
        if (empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }
        $user = new User();

        $amount = Generic::secure($_POST['amount']);
	    $wallet = $me['wallet'] + $amount;
	    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

	    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
		                                 'amount' => $amount,
		                                 'type' => 'Advertise',
		                                 'time' => time()));
	    $data = array(
        	'code'     => '200',
            'status' => 'OK',
            'message' => 'Payment successfully done'
        ); 
        self::json($data);
    }

	private function top_wallet_coinpayments_check()
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


        $payment_data           = $db->objectBuilder()->where('user_id',$me['user_id'])->where('method_name', 'coinpayments')->orderBy('id','DESC')->getOne(T_PENDING_PAYMENTS);
		$coinpayments_txn_id = '';
	    if (!empty($payment_data)) {
	        $coinpayments_txn_id = $payment_data->payment_data;
	    }
	    $user = new User();


		if (!empty($coinpayments_txn_id)) {
	        $result = coinpayments_api_call(array('key' => $config['coinpayments_id'],
	                                              'version' => '1',
	                                              'format' => 'json',
	                                              'cmd' => 'get_tx_info',
	                                              'full' => '1',
	                                              'txid' => $coinpayments_txn_id));
	        if (!empty($result) && $result['status'] == 200) {
	            if ($result['data']['status'] == -1) {
	                $db->where('user_id', $me['user_id'])->where('payment_data', $coinpayments_txn_id)->delete(T_PENDING_PAYMENTS);

	                $notif   = new Notifications();
	                $re_data = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $me['user_id'],
						'type' => 'coinpayments_canceled',
						'url' => $site_url.'/settings/wallet/'.$me['username'],
						'time' => time()
					);
	                $notif->notify($re_data);
	                $data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => 'Payment declined'
		            ); 
		            self::json($data);
	            }
	            elseif ($result['data']['status'] == 100) {
					$amount   = $result['data']['checkout']['amountf'];
					$updateUser = $db
	                        ->where("id", $me['user_id'])
	                        ->update(T_USERS, ["wallet" => $db->inc($amount)]);

	                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
			                                          'amount' => $amount,
			                                          'type' => 'Advertise',
			                                          'time' => time()));
	                $notif   = new Notifications();
	                $re_data = array(
						'notifier_id' => $me['user_id'],
						'recipient_id' => $me['user_id'],
						'type' => 'coinpayments_approved',
						'url' => $site_url.'/settings/wallet/'.$me['username'],
						'time' => time()
					);
	                $notif->notify($re_data);

	                $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => 'Payment successfully done'
		            ); 
		            self::json($data);
	            }
	        }
	        else{
	        	$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => $result['message']
	            ); 
	            self::json($data);
	        }
	    }
	    else{
	    	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'No pending payment found'
            ); 
            self::json($data);
	    }
    }

	private function top_wallet_coinpayments()
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
        if (empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }

        $amount   = (int)Generic::secure($_POST['amount']);

        $result = coinpayments_api_call(array('key' => $config['coinpayments_id'],
                                              'version' => '1',
                                              'format' => 'json',
                                              'cmd' => 'create_transaction',
                                              'amount' => $amount,
                                              'currency1' => $config['currency'],
                                              'currency2' => 'BTC',
                                              'custom' => $amount,
                                              'cancel_url' => $config['site_url'] . "/aj/go_pro/cancel_coinpayments",
                                              'buyer_email' => $me['email']));

        
        if (!empty($result) && $result['status'] == 200) {
            $db->insert(T_PENDING_PAYMENTS,array('user_id' => $me['user_id'],
                                                 'payment_data' => $result['data']['txn_id'],
                                                 'method_name' => 'coinpayments',
                                                 'time' => time()));
            
            $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'url' => $result['data']['checkout_url']
            ); 
            self::json($data);
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => $result['message']
            ); 
            self::json($data);
        }
    }

	private function top_wallet_coinbase_success()
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
        if (empty($_POST['coinbase_hash'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'coinbase_hash can not be empty'
            ); 
            self::json($data);
        }

        $coinbase_hash = Generic::secure($_POST['coinbase_hash']);
        $user_data = $db->where('coinbase_hash',$coinbase_hash)->getOne(T_USERS);
        $user = new User();
        if (!empty($user_data)) {

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges/'.$user_data->coinbase_code);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'X-Cc-Api-Key: '.$config['coinbase_key'];
            $headers[] = 'X-Cc-Version: 2018-03-22';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $url = $config['site_url'] . "/settings/wallet/".((!empty($me) && !empty($me['username'])) ? $me['username'] : '');
                header("Location: " . $url);
                exit();
            }
            curl_close($ch);
            $result = json_decode($result,true);
            $update_data = array('coinbase_hash' => '',
                                 'coinbase_code' => '');
            if (!empty($result) && !empty($result['data']) && !empty($result['data']['pricing']) && !empty($result['data']['pricing']['local']) && !empty($result['data']['pricing']['local']['amount']) && !empty($result['data']['payments']) && !empty($result['data']['payments'][0]['status']) && $result['data']['payments'][0]['status'] == 'CONFIRMED') {

                $amount = (int)$result['data']['pricing']['local']['amount'];
                $wallet = $user_data->wallet + $amount;
                $update_data['wallet'] = $wallet;
                $db->insert(T_TRANSACTIONS,array('user_id' => $user_data->user_id,
                                          'amount' => $amount,
                                          'type' => 'Advertise',
                                          'time' => time()));
                $user->updateStatic($user_data->user_id,$update_data);
                $data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Payment successfully done'
	            ); 
	            self::json($data);
            }
            else{
            	$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Something went wrong'
	            ); 
	            self::json($data);
            }
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'user not found'
            ); 
            self::json($data);
        }
    }

	private function top_wallet_coinbase()
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
        if (empty($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }

        try {
        	$user = new User();

            $amount = Generic::secure($_POST['amount']);
            $coinbase_hash = rand(1111,9999).rand(11111,99999);
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://api.commerce.coinbase.com/charges');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $postdata =  array('name' => 'Top Up Wallet','description' => 'Top Up Wallet','pricing_type' => 'fixed_price','local_price' => array('amount' => $amount , 'currency' => $config['currency']), 'metadata' => array('coinbase_hash' => $coinbase_hash),"redirect_url" => $config['site_url'] . "/aj/go_pro/coinbase_handle?coinbase_hash=".$coinbase_hash,'cancel_url' => $config['site_url'] . "/aj/go_pro/coinbase_cancel?coinbase_hash=".$coinbase_hash);


            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postdata));

            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'X-Cc-Api-Key: '.$config['coinbase_key'];
            $headers[] = 'X-Cc-Version: 2018-03-22';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                $data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => curl_error($ch)
	            ); 
	            self::json($data);
            }
            curl_close($ch);
            $result = json_decode($result,true);
            if (!empty($result) && !empty($result['data']) && !empty($result['data']['hosted_url']) && !empty($result['data']['id']) && !empty($result['data']['code'])) {
                $user->updateStatic($me['user_id'],array('coinbase_hash' => $coinbase_hash,
                                                         'coinbase_code' => $result['data']['code']));

                $data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'url' => $result['data']['hosted_url']
	            ); 
	            self::json($data);
            }
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Something went wrong'
            ); 
            self::json($data);
        }
        catch (Exception $e) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => $e->getMessage()
            ); 
            self::json($data);
        }



    }

	private function top_wallet_razorpay_success()
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
        if (empty($_POST['amount']) || empty($_POST['payment_id'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount , payment_id can not be empty'
            ); 
            self::json($data);
        }

        $user = new User();
        $payment_id = Generic::secure($_POST['payment_id']);
	    $data = array(
	        'amount' => Generic::secure($_POST['amount'])*100,
			'currency' => $config['currency'],
	    );

	    $url = 'https://api.razorpay.com/v1/payments/' . $payment_id . '/capture';
	    $key_id = $config['razorpay_key'];
	    $key_secret = $config['razorpay_secret'];
	    $params = http_build_query($data);
	    //cURL Request
	    $ch = curl_init();
	    //set the url, number of POST vars, POST data
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	    $request = curl_exec ($ch);
	    curl_close ($ch);
	    $tranx = json_decode($request);
	    $err = curl_error($ch);

	    if($err){
	        if (!empty($tranx->error)) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => $tranx->error->description
	            ); 
	            self::json($data);
	    	} 
	    }else{
	    	if (!empty($tranx->error)) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => $tranx->error->description
	            ); 
	            self::json($data);
	    	}
	        if( $tranx->status == 'captured'){
	            $url = '';
                $amount = (int)$tranx->amount / 100;
                $wallet = $me['wallet'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'type' => 'Advertise',
                                              'time' => time()));
	            $data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'url' => $url
	            ); 
	            self::json($data);
	        }else{
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'error while proccess payment'
	            ); 
	            self::json($data);
	        }
	    }
    }

	private function top_wallet_razorpay()
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
        if (empty($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }

        $url = 'https://api.razorpay.com/v1/orders';
	    $key_id = $config['razorpay_key'];
	    $key_secret = $config['razorpay_secret'];
	    //cURL Request
	    $ch = curl_init();
	    //set the url, number of POST vars, POST data
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, [
	        'amount' => Generic::secure($_POST['amount'])*100,
			'currency' => 'INR',
	    ]);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	    $request = curl_exec ($ch);
	    curl_close ($ch);
	    $tranx = json_decode($request);
	    $err = curl_error($ch);

	    if($err){
	    	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => $tranx->error->description
            ); 
            self::json($data);
	    }else{
	    	if (!empty($tranx->error)) {
	    		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => $tranx->error->description
	            ); 
	            self::json($data);
	    	}
	    	$data = array(
            	'code'     => '200',
                'status' => 'OK',
                'order_id' => $tranx->id
            ); 
            self::json($data);
	    }

    }

	private function top_wallet_paystack_success()
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
        if (empty($_POST['amount']) || empty($_POST['reference'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount , reference can not be empty'
            ); 
            self::json($data);
        }

        $payment  = CheckPaystackPayment($_POST['reference']);
	    if ($payment) {
	        $amount = Generic::secure($_POST['amount']);
	        $wallet = $me['wallet'] + $amount;
	        $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

	        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
	                                      'amount' => $amount,
	                                      'type' => 'Advertise',
	                                      'time' => time()));
	        $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Payment successfully done'
            ); 
            self::json($data);

	    }
	    else {
	        $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Something went wrong'
            ); 
            self::json($data);
	    }
    }

	private function top_wallet_paystack()
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
        if (empty($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }

        $amount = $org_amount = Generic::secure($_POST['amount']);
        if (!empty($config['currency_array']) && in_array($config['paystack_currency'], $config['currency_array']) && $config['paystack_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['paystack_currency']])) {
            $amount= (($amount * $config['exchange'][$config['paystack_currency']]));
        }
        $amount = $amount * 100;
        $callback_url = $config['site_url'] . "/aj/paystack/success&amount=".$org_amount; 
        $result = array();
        $reference = uniqid();

        //Set other parameters as keys in the $postdata array
        $postdata =  array('email' => $me['email'], 'amount' => $amount,"reference" => $reference,'callback_url' => $callback_url,'currency' => $config['paystack_currency']);
        $url = "https://api.paystack.co/transaction/initialize";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($postdata));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [
          'Authorization: Bearer '.$config['paystack_secret_key'],
          'Content-Type: application/json',

        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $request = curl_exec ($ch);

        curl_close ($ch);

        if ($request) {
            $result = json_decode($request, true);
            if (!empty($result)) {
                 if (!empty($result['status']) && $result['status'] == 1 && !empty($result['data']) && !empty($result['data']['authorization_url']) && !empty($result['data']['access_code'])) {
                    $db->where('user_id',$me['user_id'])->update(T_USERS,array('paystack_ref' => $reference));
                    $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'url' => $result['data']['authorization_url']
		            ); 
		            self::json($data);
                }
                else{
                	$data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => $result['message']
		            ); 
		            self::json($data);
                }
            }
            else{
                $data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Something went wrong'
	            ); 
	            self::json($data);
            }
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Something went wrong'
            ); 
            self::json($data);
        }

    }

	private function top_wallet_paysera_success()
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
        $user = new User();

        $response = WebToPay::checkResponse($_GET, array(
	        'projectid'     => $config['paysera_project_id'],
	        'sign_password' => $config['paysera_password'],
	    ));

	    if ($response['type'] !== 'macro') {
	    	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Only macro payment callbacks are accepted'
            ); 
            self::json($data);
	    }
	    $amount = Generic::secure($_GET['amount']);
	    $wallet = $me['wallet'] + $amount;
	    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

	    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
	                                  'amount' => $amount,
	                                  'type' => 'Advertise',
	                                  'time' => time()));
	    $data = array(
        	'code'     => '200',
            'status' => 'OK',
            'message' => 'Payment successfully done'
        ); 
        self::json($data);

    }

	private function top_wallet_paysera()
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
        if (empty($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }


        $amount = intval($_POST['amount']);
	    $url = '';
	    try {
	        $self_url = $config['site_url'];
	        $payment_url = WebToPay::getPaymentUrl();

	        $request = WebToPay::buildRequest(array(
	            'projectid'     => $config['paysera_project_id'],
	            'sign_password' => $config['paysera_password'],
	            'orderid'       => rand(1111,4444),
	            'amount'        => $amount,
	            'currency'      => $config['currency'],
	            'country'       => 'TR',
	            'accepturl'     => $self_url.'/aj/go_pro/paysera_success?amount='.$amount,
	            'cancelurl'     => $self_url.'/aj/go_pro/paysera_cancel',
	            'callbackurl'   => $self_url.'/aj/go_pro/paysera_callback',
	            'test'          => ($config['paysera_test_mode'] == 'test') ? 1 : 0,
	        ));

	        $url = $payment_url . '?data='. $request['data'] . '&sign=' . $request['sign'];

	        $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'url' => $url
            ); 
            self::json($data);
	    }
	    catch (WebToPayException $e) {
	    	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => $e->getMessage()
            ); 
            self::json($data);
	    }
    }

	private function top_wallet_bank_transfer()
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
        if (empty($_FILES['image']) || empty($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'image , amount can not be empty'
            ); 
            self::json($data);
        }

        $media = new Media();
        $media->setFile(array(
            'file' => $_FILES['image']['tmp_name'],
            'name' => $_FILES['image']['name'],
            'size' => $_FILES['image']['size'],
            'type' => $_FILES['image']['type'],
            'allowed' => 'jpeg,jpg,png'
        ));

        $upload = $media->uploadFile();

        $description = 'Upgrade to pro';
        $price = $config['pro_price'];
        $mode  = 'pro_member';
        $funding_id  = 0;

        $description = 'Wallet top up';
        $mode  = 'wallet';
        $price = Generic::secure($_POST['amount']);
        if (!empty($upload)) { 
            $image = $upload['filename'];
            $db->insert(T_BANK_TRANSFER,array('user_id' => $me['user_id'],
                                      'receipt_file' => $image,
                                      'description' => $description,
                                      'price' => $price,
                                      'mode' => $mode,
                                      'funding_id' => $funding_id));
            $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Bank transfer request submited'
            ); 
            self::json($data);
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'invalid image'
            ); 
            self::json($data);
        }
    }

	private function top_wallet_cashfree_paid()
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
        if (empty($_POST['txStatus']) || $_POST['txStatus'] != 'SUCCESS' || empty($_POST['orderId']) || empty($_POST['amount']) || empty($_POST['orderAmount']) || empty($_POST['referenceId']) || empty($_POST['paymentMode']) || empty($_POST['txMsg']) || empty($_POST['txTime']) || empty($_POST['signature'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'txStatus , orderId , amount , orderAmount , referenceId , paymentMode , txMsg , txTime , signature can not be empty'
            ); 
            self::json($data);
        }


        $orderId = $_POST["orderId"];
	    $amount = Generic::secure($_POST["amount"]);
		$orderAmount = $_POST["orderAmount"];
		$referenceId = $_POST["referenceId"];
		$txStatus = $_POST["txStatus"];
		$paymentMode = $_POST["paymentMode"];
		$txMsg = $_POST["txMsg"];
		$txTime = $_POST["txTime"];
		$signature = $_POST["signature"];
		$data = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
		$hash_hmac = hash_hmac('sha256', $data, $config['cashfree_secret_key'], true) ;
		$computedSignature = base64_encode($hash_hmac);
		$user = new User();
		if ($signature == $computedSignature) {
	        $wallet = $me['wallet'] + $amount;
	        $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

	        $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
	                                      'amount' => $amount,
	                                      'type' => 'Advertise',
	                                      'time' => time()));
	        $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Payment successfully done'
            ); 
            self::json($data);

	    } else {
	        $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'hash not matched'
            ); 
            self::json($data);
	    }
    }

	private function top_wallet_cashfree()
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
        if (empty($_POST['name']) || empty($_POST['phone']) || empty($_POST['email']) || empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount , name , phone , email can not be empty'
            ); 
            self::json($data);
        }


        $result = array();
	    $order_id = uniqid();
	    $name = Generic::secure($_POST['name']);
	    $email = Generic::secure($_POST['email']);
	    $phone = Generic::secure($_POST['phone']);
	    $price = $org_amount = Generic::secure($_POST['amount']);
        if (!empty($config['currency_array']) && in_array($config['cashfree_currency'], $config['currency_array']) && $config['cashfree_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['cashfree_currency']])) {
            $price= (($price * $config['exchange'][$config['cashfree_currency']]));
            $price = round($price, 2);
        }

	    $callback_url = $config['site_url'] . "/aj/go_pro/cashfree_paid?amount=".$org_amount;


	    $secretKey = $config['cashfree_secret_key'];
		$postData = array( 
		  "appId" => $config['cashfree_client_key'], 
		  "orderId" => "order".$order_id, 
		  "orderAmount" => $price, 
		  "orderCurrency" => $config['cashfree_currency'], 
		  "orderNote" => "", 
		  "customerName" => $name, 
		  "customerPhone" => $phone, 
		  "customerEmail" => $email,
		  "returnUrl" => $callback_url, 
		  "notifyUrl" => $callback_url,
		);
		 // get secret key from your config
		 ksort($postData);
		 $signatureData = "";
		 foreach ($postData as $key => $value){
		      $signatureData .= $key.$value;
		 }
		 $signature = hash_hmac('sha256', $signatureData, $secretKey,true);
		 $signature = base64_encode($signature);
		 $cashfree_link = 'https://test.cashfree.com/billpay/checkout/post/submit';
		 if ($config['cashfree_mode'] == 'live') {
		 	$cashfree_link = 'https://www.cashfree.com/checkout/post/submit';
		 }

		$form = '<form id="redirectForm" method="post" action="'.$cashfree_link.'"><input type="hidden" name="appId" value="'.$config['cashfree_client_key'].'"/><input type="hidden" name="orderId" value="order'.$order_id.'"/><input type="hidden" name="orderAmount" value="'.$price.'"/><input type="hidden" name="orderCurrency" value="INR"/><input type="hidden" name="orderNote" value=""/><input type="hidden" name="customerName" value="'.$name.'"/><input type="hidden" name="customerEmail" value="'.$email.'"/><input type="hidden" name="customerPhone" value="'.$phone.'"/><input type="hidden" name="returnUrl" value="'.$callback_url.'"/><input type="hidden" name="notifyUrl" value="'.$callback_url.'"/><input type="hidden" name="signature" value="'.$signature.'"/></form>';

		$data = array(
        	'code'     => '200',
            'status' => 'OK',
            'html' => $form,
            'action' => $cashfree_link,
            'appId' => $config['cashfree_client_key'],
            'orderId' => $order_id,
            'orderAmount' => $price,
            'orderCurrency' => 'INR',
            'orderNote' => '',
            'customerName' => $name,
            'customerEmail' => $email,
            'customerPhone' => $phone,
            'returnUrl' => $callback_url,
            'notifyUrl' => $callback_url,
            'signature' => $signature,
        ); 
        self::json($data);
    }

	private function top_wallet_iyzipay_paid()
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
        if (empty($_POST['token']) || empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount , token can not be empty'
            ); 
            self::json($data);
        }

        require_once('sys/libs/iyzipay/samples/config.php');

		# create request class
		$user = new User();
		$request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
		$request->setLocale(\Iyzipay\Model\Locale::TR);
		$request->setConversationId($me['conversation_id']);
		$request->setToken($_POST['token']);

		# make request
		$checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, Config::options());

		# print result
		if ($checkoutForm->getPaymentStatus() == 'SUCCESS') {
            $amount = Generic::secure($_GET['amount']);
            $wallet = $me['wallet'] + $amount;
            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                          'amount' => $amount,
                                          'type' => 'Advertise',
                                          'time' => time()));
            
            $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Payment successfully done'
            ); 
            self::json($data);
		}
		else{
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Payment not accepted'
            ); 
            self::json($data);
		}
    }

	private function top_wallet_iyzipay()
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
        if (empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }

        require_once("sys/libs/iyzipay/samples/config.php");

        $user = new User();
		$amount = $org_amount = Generic::secure($_POST['amount']);
	    if (!empty($config['currency_array']) && in_array($config['iyzipay_currency'], $config['currency_array']) && $config['iyzipay_currency'] != $config['currency'] && !empty($config['exchange']) && !empty($config['exchange'][$config['iyzipay_currency']])) {
	        $amount= (($amount * $config['exchange'][$config['iyzipay_currency']]));
	    }
		$callback_url = $config['site_url'] . "aj/go_pro/iyzipay_paid?amount=".$org_amount;

		
		$request->setPrice($amount);
		$request->setPaidPrice($amount);
		$request->setCallbackUrl($callback_url);
		

		$basketItems = array();
		$firstBasketItem = new \Iyzipay\Model\BasketItem();
		$firstBasketItem->setId("BI".rand(11111111,99999999));
		$firstBasketItem->setName("Top Up Wallet");
		$firstBasketItem->setCategory1("Top Up Wallet");
		$firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
		$firstBasketItem->setPrice($amount);
		$basketItems[0] = $firstBasketItem;
		$request->setBasketItems($basketItems);
		$checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, Config::options());
	    $content = $checkoutFormInitialize->getCheckoutFormContent();
		if (!empty($content)) {
			$db->where('user_id',$me['user_id'])->update(T_USERS,array('conversation_id' => $ConversationId));
			$data = array(
            	'code'     => '200',
                'status' => 'OK',
                'html' => $content
            ); 
            self::json($data);
		}
		else{
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Something went wrong'
            ); 
            self::json($data);
		}
    }

	private function top_wallet_yoomoney_success()
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
        if (empty($_POST['notification_type']) || empty($_POST['operation_id']) || empty($_POST['amount']) || empty($_POST['currency']) || empty($_POST['datetime']) || empty($_POST['sender']) || empty($_POST['codepro']) || empty($_POST['label']) || empty($_POST['sha1_hash'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'notification_type , operation_id , amount , currency , datetime , sender , codepro , label , sha1_hash can not be empty'
            ); 
            self::json($data);
        }



        $hash = sha1($_POST['notification_type'].'&'.
	    $_POST['operation_id'].'&'.
	    $_POST['amount'].'&'.
	    $_POST['currency'].'&'.
	    $_POST['datetime'].'&'.
	    $_POST['sender'].'&'.
	    $_POST['codepro'].'&'.
	    $config['yoomoney_notifications_secret'].'&'.
	    $_POST['label']);

	    $_POST['codepro'] = (is_string($_POST['codepro']) && strtolower($_POST['codepro']) == 'true' ? true : false);
	    

	    if ($_POST['sha1_hash'] != $hash || $_POST['codepro'] == true) {
	    	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'hash not matched'
            ); 
            self::json($data);
	    }
	    else{

	        if (!empty($_POST['label'])) {
	            $_user = new User();
	            $user_data   = $_user->getUserDataById(Generic::secure($_POST['label']));
	            if (!empty($user_data)) {
	                $amount = Generic::secure($_POST['amount']);
	                $wallet = $user_data->wallet + $amount;
	                $update = $_user->updateStatic($user_data->user_id,array('wallet' => $wallet));

	                $db->insert(T_TRANSACTIONS,array('user_id' => $user_data->user_id,
	                                              'amount' => $amount,
	                                              'type' => 'Advertise',
	                                              'time' => time()));

	                $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => 'Payment successfully done'
		            ); 
		            self::json($data);
	            }
	        }
	        $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Something went wrong'
            ); 
            self::json($data);
	    }
    }

	private function top_wallet_yoomoney()
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
        if (empty($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }
        $amount = Generic::secure($_POST['amount']);
        $order_id = uniqid();
        $receiver = $config['yoomoney_wallet_id'];
        $successURL = $config['site_url'] . "/aj/go_pro/yoomoney_success";
        $form = '<form id="yoomoney_form" method="POST" action="https://yoomoney.ru/quickpay/confirm.xml">    
                    <input type="hidden" name="receiver" value="'.$receiver.'"> 
                    <input type="hidden" name="quickpay-form" value="donate"> 
                    <input type="hidden" name="targets" value="transaction '.$order_id.'">   
                    <input type="hidden" name="paymentType" value="PC"> 
                    <input type="hidden" name="sum" value="'.$amount.'" data-type="number"> 
                    <input type="hidden" name="successURL" value="'.$successURL.'">
                    <input type="hidden" name="label" value="'.$me['user_id'].'">
                </form>';

        $data = array(
        	'code'     => '200',
            'status' => 'OK'
        ); 
        $data['html'] = $form;
        $data['action'] = 'https://yoomoney.ru/quickpay/confirm.xml';
        $data['receiver'] = $receiver;
        $data['quickpay-form'] = 'donate';
        $data['targets'] = 'transaction '.$order_id;
        $data['paymentType'] = 'PC';
        $data['sum'] = $amount;
        $data['successURL'] = $successURL;
        $data['label'] = $me['user_id'];
        self::json($data);
    }

	private function top_wallet_securionpay_handle()
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
        if (empty($_POST) || empty($_POST['charge'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'charge can not be empty'
            ); 
            self::json($data);
        }

        $url = "https://api.securionpay.com/charges?limit=10";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $config['securionpay_secret_key'].":password");
        $resp = curl_exec($curl);
        curl_close($curl);
        $resp = json_decode($resp,true);
        $user = new User();
        if (!empty($resp) && !empty($resp['list'])) {
            foreach ($resp['list'] as $key => $value) {
                if ($value['id'] == $_POST['charge']['id']) {
                    if (!empty($value['metadata']) && !empty($value['metadata']['user_key']) && !empty($value['amount'])) {
                        if ($me['securionpay_key'] == $value['metadata']['user_key']) {
                            $db->where('user_id',$me['user_id'])->update(T_USERS,array('securionpay_key' => 0));
                            $amount = intval(Generic::secure($value['amount'])) / 100;
                            $wallet = $me['wallet'] + $amount;
                            $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                            $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                                          'amount' => $amount,
                                                          'type' => 'Advertise',
                                                          'time' => time()));
                            $data = array(
				            	'code'     => '200',
				                'status' => 'OK',
				                'message' => 'Payment successfully done'
				            ); 
				            self::json($data);
                        }
                        else{
                        	$data = array(
				            	'code'     => '400',
				                'status' => 'Bad Request',
				                'message' => 'user not found'
				            ); 
				            self::json($data);
                        }
                    }
                    else{
                        $data = array(
			            	'code'     => '400',
			                'status' => 'Bad Request',
			                'message' => 'user_key metadata empty'
			            ); 
			            self::json($data);
                    }
                }
            }
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'No Payments found'
            ); 
            self::json($data);
        }
    }

	private function top_wallet_securionpay_token()
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
        if (empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }

        $amount = Generic::secure($_POST['amount']);
        $user = new User();
        require_once('sys/libs/securionpay/vendor/autoload.php');
        $securionPay = new SecurionPayGateway($config['securionpay_secret_key']);
        $user_key = rand(1111,9999).rand(11111,99999);

        $checkoutCharge = new CheckoutRequestCharge();
        $checkoutCharge->amount(($amount * 100))->currency('USD')->metadata(array('user_key' => $user_key));

        $checkoutRequest = new CheckoutRequest();
        $checkoutRequest->charge($checkoutCharge);

        $signedCheckoutRequest = $securionPay->signCheckoutRequest($checkoutRequest);
        if (!empty($signedCheckoutRequest)) {
            $db->where('user_id',$me['user_id'])->update(T_USERS,array('securionpay_key' => $user_key));
            $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'token' => $signedCheckoutRequest
            ); 
            self::json($data);
        }
        else{
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Something went wrong'
            ); 
            self::json($data);
        }
    }

	private function top_wallet_stripe()
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
        if (empty($_POST['token']) || empty($_POST['price']) || !is_numeric($_POST['price'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'price , token can not be empty'
            ); 
            self::json($data);
        }
        require_once('sys/stripe_config.php');


		try {
			$amount = Generic::secure($_POST['price']);
			$customer = \Stripe\Customer::create(array(
                'source' => $_POST['token']
            ));
            $charge   = \Stripe\Charge::create(array(
                'customer' => $customer->id,
                'amount' => $amount * 100,
                'currency' => $config['stripe_currency']
            ));
            if ($charge) {
            	$user = new User();
            	$db->where('user_id',$me['user_id'])->update(T_USERS,array('StripeSessionId' => ''));
                
                $wallet = $me['wallet'] + $amount;
                $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                              'amount' => $amount,
                                              'type' => 'Advertise',
                                              'time' => time()));
                $data = array(
	                'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Payment successfully done',
	            );
	            self::json($data);
            }
		} catch (Exception $e) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => $e->getMessage()
            ); 
            self::json($data);
		}
	}

	private function top_wallet_authorize()
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
        if (empty($_POST['amount']) || !is_numeric($_POST['amount'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'amount can not be empty'
            ); 
            self::json($data);
        }
        if (empty($_POST['card_number']) || empty($_POST['card_month']) || empty($_POST['card_year']) || empty($_POST['card_cvc'])) {
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'card_number , card_month , card_year , card_cvc can not be empty'
            ); 
            self::json($data);
        }

        require_once('sys/libs/authorize/vendor/autoload.php');
        $user = new User();
        $amount = Generic::secure($_POST['amount']);
        $APILoginId = $config['authorize_login_id'];
        $APIKey = $config['authorize_transaction_key'];
        $refId = 'ref' . time();
        define("AUTHORIZE_MODE", $config['authorize_test_mode']);
        
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName($APILoginId);
        $merchantAuthentication->setTransactionKey($APIKey);

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($_POST['card_number']);
        $creditCard->setExpirationDate($_POST['card_year'] . "-" . $_POST['card_month']);
        $creditCard->setCardCode($_POST['card_cvc']);

        $paymentType = new AnetAPI\PaymentType();
        $paymentType->setCreditCard($creditCard);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setPayment($paymentType);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId($refId);
        $request->setTransactionRequest($transactionRequestType);
        $controller = new AnetController\CreateTransactionController($request);
        if ($config['authorize_test_mode'] == 'SANDBOX') {
            $Aresponse = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        }
        else{
            $Aresponse = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
        
        if ($Aresponse != null) {
            if ($Aresponse->getMessages()->getResultCode() == 'Ok') {
                $trans = $Aresponse->getTransactionResponse();
                if ($trans != null && $trans->getMessages() != null) {
                    $wallet = $me['wallet'] + $amount;
                    $update = $user->updateStatic($me['user_id'],array('wallet' => $wallet));

                    $db->insert(T_TRANSACTIONS,array('user_id' => $me['user_id'],
                                                  'amount' => $amount,
                                                  'type' => 'Advertise',
                                                  'time' => time()));
                    $data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => "Payment successfully done"
		            ); 
		            self::json($data);
                }
                else{
                    $error = "Payment did not accepted";
                    if ($trans->getErrors() != null) {
                        $error = $trans->getErrors()[0]->getErrorText();
                    }
                    $data = array(
		            	'code'     => '400',
		                'status' => 'Bad Request',
		                'message' => $error
		            ); 
		            self::json($data);
                }
            }
            else{
                $trans = $Aresponse->getTransactionResponse();
                $error = "Payment pending or did not accepted";
                if (!empty($trans) && $trans->getErrors() != null) {
                    $error = $trans->getErrors()[0]->getErrorText();
                }
                $data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => $error
	            ); 
	            self::json($data);
            }
        }
        else{
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Something went wrong'
            ); 
            self::json($data);
        }
	}
}