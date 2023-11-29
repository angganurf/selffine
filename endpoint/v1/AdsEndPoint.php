<?php 
/**
 * 
 */
class AdsEndPoint extends Generic
{
	
	function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'create_ad':
				self::create_ad();
				break;
			case 'edit_ad':
				self::edit_ad();
				break;
			case 'delete_ad':
				self::delete_ad();
				break;
			case 'get_ads':
				self::get_ads();
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


	private function get_ads()
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
		$user_ads = $user->GetUserAds();
		$all_data = array();
		foreach ($user_ads as $key => $value) {
			$value->ad_media = media($value->ad_media);
			$all_data[] = $value;
		}

		$data = array(
        	'code'     => '200',
            'status' => 'OK',
            'data' => $all_data
        ); 
        self::json($data);
    }


	private function delete_ad()
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

		if (empty($_POST['id'])) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
		}
		else{

			$user = new User();
			$media = new Media();
			$ad = $user->GetAdByID($_POST['id']);
			if (!empty($ad) && $ad->user_id == $me['user_id']) {
				$db->where('id',$ad->id)->delete(T_ADS);
				$photo_file = $ad->ad_media;
				if (file_exists($photo_file)) {
		            @unlink(trim($photo_file));
		        }
		        else if($config['amazone_s3'] == 1 || $config['ftp_upload'] == 1 || $config['google_cloud_storage'] == 1 || $config['digital_ocean'] == 1 || $config['wasabi_storage'] == 1 || $config['backblaze_storage'] == 1){
		            $media->deleteFromFTPorS3($photo_file);
		        }
				$data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Ad deleted successfully'
	            ); 
	            self::json($data);
			}
			else{
				$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Ad not found'
	            ); 
	            self::json($data);
			}
		}

    }



	private function edit_ad()
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



    	$bidding_array = array('clicks','views');
		$appears_array = array('post','sidebar');
		$data['status'] = 400;
		if (empty($_POST['company']) || empty($_POST['url']) || empty($_POST['title']) || empty($_POST['location']) || empty($_POST['description']) || empty($_POST['bidding']) || !in_array($_POST['bidding'], $bidding_array) || empty($_POST['appears']) || !in_array($_POST['appears'], $appears_array) || empty($_POST['country']) || empty($_POST['gender']) || empty($_POST['id']) || empty($_POST['status'])) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'company , url , title , location , description , bidding , appears , country , gender , id , status can not be empty'
            ); 
            self::json($data);
		}
		elseif (!Generic::isUrl($_POST['url'])) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'url is invalid'
            ); 
            self::json($data);
		}
		elseif ($me['wallet'] < 1) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Please top up your wallet'
            ); 
            self::json($data);
		}
		else{
			$user = new User();
			$ad = $user->GetAdByID($_POST['id']);
			if (!empty($ad) && $ad->user_id == $me['user_id']) {
				$country = '';
				$status_array = array('0','1');
				$status = 1;
				if (!empty($_POST['country'])) {
					$country = Generic::secure('{'.implode('},{', $_POST['country']).'}');
				}
				if (in_array($_POST['status'], $status_array)) {
					$status = Generic::secure($_POST['status']);
				}

				$insert_array = array('name' => Generic::secure($_POST['company']),
			                          'url'  => Generic::secure($_POST['url']),
			                          'headline' => Generic::secure($_POST['title']),
			                          'location' => Generic::secure($_POST['location']),
			                          'appears'  => Generic::secure($_POST['appears']),
			                          'bidding'  => Generic::secure($_POST['bidding']),
			                          'audience' => $country,
			                          'gender'   => Generic::secure($_POST['gender']),
			                          'description'   => Generic::secure($_POST['description']),
			                          'posted'   => time(),
			                          'user_id'  => $me['user_id'],
			                          'status'   => $status);
				$db->where('id',$ad->id)->update(T_ADS,$insert_array);
				$data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Ad edited successfully'
	            ); 
	            self::json($data);
			}
			else{
				$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Ad not found'
	            ); 
	            self::json($data);
			}
		}
    }


	private function create_ad()
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


    	$bidding_array = array('clicks','views');
		$appears_array = array('post','sidebar');
		$data['status'] = 400;
		if (empty($_POST['company']) || empty($_POST['url']) || empty($_POST['title']) || empty($_POST['location']) || empty($_POST['description']) || empty($_POST['bidding']) || !in_array($_POST['bidding'], $bidding_array) || empty($_POST['appears']) || !in_array($_POST['appears'], $appears_array) || empty($_FILES['image']) || empty($_POST['country']) || empty($_POST['gender'])) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'company , url , title , location , description , bidding , appears , image , country , gender can not be empty'
            ); 
            self::json($data);
		}
		elseif (!Generic::isUrl($_POST['url'])) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'url is invalid'
            ); 
            self::json($data);
		}
		elseif ($me['wallet'] < 1) {
			$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Please top up your wallet'
            ); 
            self::json($data);
		}
		else{
			$media  = new Media();
			$media->setFile(array(
				'file' => $_FILES['image']['tmp_name'],
				'name' => $_FILES['image']['name'],
				'size' => $_FILES['image']['size'],
				'type' => $_FILES['image']['type'],
				'allowed' => 'jpeg,jpg,png,webp,gif',
			));
			$image = $media->uploadFile();
			if (!empty($image['filename'])) {
				$country = '';
				if (!empty($_POST['country'])) {
					$country = Generic::secure('{'.implode('},{', $_POST['country']).'}');
				}

				$insert_array = array('name' => Generic::secure($_POST['company']),
			                          'url'  => Generic::secure($_POST['url']),
			                          'headline' => Generic::secure($_POST['title']),
			                          'location' => Generic::secure($_POST['location']),
			                          'appears'  => Generic::secure($_POST['appears']),
			                          'bidding'  => Generic::secure($_POST['bidding']),
			                          'audience' => $country,
			                          'gender'   => Generic::secure($_POST['gender']),
			                          'description'   => Generic::secure($_POST['description']),
			                          'posted'   => time(),
			                          'user_id'  => $me['user_id'],
			                          'ad_media' => $image['filename']);
				$db->insert(T_ADS,$insert_array);
				$data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Ad created successfully'
	            ); 
	            self::json($data);
			}
			else{
				$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Invalid file'
	            ); 
	            self::json($data);
			}
		}








    }


}