<?php 
/**
 * 
 */
class ReelEndPoint extends Generic
{
	
	function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'create_reel':
				self::create_reel();
				break;
			case 'get_user_reels':
				self::get_user_reels();
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

	private function get_user_reels()
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

    	$user_id = $me['user_id'];
    	if (!empty($_POST['user_id']) && is_numeric($_POST['user_id'])) {
    		$user_id = Generic::secure($_POST['user_id']);
    	}

    	$limit = !empty($_POST['limit']) && $_POST['limit'] <= 50 ? Generic::secure($_POST['limit']) : 30;
		$offset  = !empty($_POST['offset']) ? Generic::secure($_POST['offset']) : false;
    	$posts   = new Posts();
    	$posts->setUserById($user_id);
		$posts->limit = $limit;
		$all_data = array();
		$user_posts   = $posts->getUserReels($offset);
		foreach ($user_posts as $key => $value) {
			$value->file = media($value->file);
			$value->extra = media($value->extra);
			$value->thumb = media($value->thumb);
			$all_data[] = $value;
		}
		$data = array(
        	'code'     => '200',
            'status' => 'OK',
            'data' => $all_data
        ); 
        self::json($data);

    }

	private function create_reel()
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
    	if (empty($_FILES['video']) || empty($_FILES['thumb'])) {
    		$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'video , thumb can not be empty'
            ); 
            self::json($data);
    	}


		if ($config['ffmpeg_sys'] == 'on' && !empty($_FILES['video']) && file_exists($_FILES['video']['tmp_name'])){
			require_once('sys/libs/ffmpeg-php/vendor/autoload.php');
			
		    if(!file_exists($config['ffmpeg_binary'])){
	            header("Content-type: application/json");
	            $data['status']  = 400;
	            $data['message'] = 'FFMPEG Executable file not found';
	            $data['ffmpeg_binary']  = $config['ffmpeg_binary'];
	            $data['exist']  = file_exists($config['ffmpeg_binary']);
	            echo json_encode($data, JSON_PRETTY_PRINT);
	            exit();
	        }
			$ffmpeg  = new FFmpeg($config['ffmpeg_binary']);
			$media   = new Media();
			$posts   = new Posts();
			$notif   = new Notifications();
			$up_size = (!empty($_FILES['video']['size'])) ? $_FILES['video']['size'] : 0;
			$mx_size = $config['max_upload'];

			if ($up_size <= $mx_size) {
				$media->setFile(array(
					'file' => $_FILES['video']['tmp_name'],
					'name' => $_FILES['video']['name'],
					'size' => $_FILES['video']['size'],
					'type' => $_FILES['video']['type'],
					'allowed' => 'mp4,mov,3gp,webm',
				));
				//we remove false "$media->uploadFile(0, false);" to fix Upload delete etc isn't working with amazon s3
				$upload = $media->uploadFile(0, false);
						// print_r($upload);

				if (!empty($upload)) {
					try{
						$filepath = explode('.', $upload['filename'])[0];
						$filext   = explode('.', $upload['filename'])[1];
						// print_r($filepath);
	                    $ffmpeg->input($upload['filename']);
	                    $ffmpeg->set('-ss', '0');
	                    if((int)$config['max_video_duration'] > 0) {
	                        //$ffmpeg->set('-t', (int)$config['max_video_duration']);
	                    }
	                    $ffmpeg->set('-vcodec', 'h264');
	                    //$ffmpeg->set('-c:v', 'libx264');
	                    //$ffmpeg->set('-crf', '23');
	                   // $ffmpeg->set('-profile:v', 'main');
	                    $ffmpeg->set('-level', '3.0');
	                    $ffmpeg->set('-preset', $config['convert_speed']);
	                    //$ffmpeg->set('-acodec', 'aac');
	                   //$ffmpeg->set('-ac', '2');
	                   // $ffmpeg->set('-b:a', '128k');
	                   // $ffmpeg->set('-pix_fmt', 'yuv420p');
	                   // $ffmpeg->set('-movflags', 'faststart');
	                    //$ffmpeg->set('-hide_banner');
	                    $ffmpeg->forceFormat('mp4');
						 // -c:v libx264 -crf 23 -profile:v baseline -level 3.0 -pix_fmt yuv420p \
						 //  -c:a aac -ac 2 -b:a 128k \
						 //  -movflags faststart \
						$time  = time();
						$video = $ffmpeg->output("$filepath.final.mp4")->ready();
						// print_r($video);
						// die();
						$video = "$filepath.final.mp4";

						$media->initDir('photos');

		                $dir      = "media/upload/photos/" . date('Y') . '/' . date('m');
		                $hash     = sha1(time() + time() - rand(9999,9999));

		                $thumb    = "$dir/$hash.video_thumb.jpeg";
		                $full_dir = $root;

		                $input_path = $upload['filename'];
		                $output_path = $thumb;

		                #Generete thumb

		                $_ffmpeg = new FFmpeg($config['ffmpeg_binary']);
		                $_ffmpeg->input($upload['filename']);
						$_ffmpeg->set('-ss','2');
						$_ffmpeg->set('-vframes','1');
						$_ffmpeg->set('-f','mjpeg');
		                $output_thumb = $_ffmpeg->output("$output_path")->ready();

		                $re_data = array(
		                	'user_id' => $me['user_id'],
		                	'time' => time(),
		                	'type' => 'reels',
		                );

		                if (!empty($_POST['caption'])) {
							$text = Generic::cropText($_POST['caption'],$config['caption_len']);
							$re_data['description'] = $text;
						}

						$post_id = $posts->insertPost($re_data);

						if (is_numeric($post_id)) {
							$re_data = array(
								'post_id' => $post_id,
								'file' => $video,
								'extra' => $thumb,
							);

							$media = new Media;
							$media->uploadToS3($video);
							$media->uploadToS3($thumb);

							$posts->setPostId($post_id);
							$posts->insertMedia($re_data);

							
							$post_data = o2array($posts->postData());

							$data['html']    = $pixelphoto->PX_LoadPage('home/templates/home/includes/post-video');	
							$data['status']  = 200;
							$data['message'] = lang('post_published');

							#Notify mentioned users
							$notif->notifyMentionedUsers($_POST['caption'],pid2url($post_id));

							@unlink($upload['filename']);
							$media->deleteFromFTPorS3($upload['filename']);

							$data = array(
				            	'code'     => '200',
				                'status' => 'OK',
				                'message' => 'Reel uploaded successfully'
				            ); 
				            self::json($data);
						}
						else{
							$data = array(
				            	'code'     => '400',
				                'status' => 'Bad Request',
				                'message' => 'Post not inserted'
				            ); 
				            self::json($data);
						}

					}
					catch(Exception $error){
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
			else{
				$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'Max upload limit ' . $mx_size
	            ); 
	            self::json($data);
			}
		}
		else{
			$media  = new Media();
			$posts  = new Posts();
			// print_r( $_FILES['video']);die;
			$media->setFile(array(
				'file' => $_FILES['video']['tmp_name'],
				'name' => $_FILES['video']['name'],
				'size' => $_FILES['video']['size'],
				'type' => $_FILES['video']['type'],
				'allowed' => 'mp4,mov,3gp,webm',
			));

			$video = $media->uploadFile();

			$media->setFile(array(
				'file' => $_FILES['thumb']['tmp_name'],
				'name' => $_FILES['thumb']['name'],
				'size' => $_FILES['thumb']['size'],
				'type' => $_FILES['thumb']['type'],
				'allowed' => 'jpeg,jpg,png',
				'crop' => array(
					'width' => '600',
					'height' => '400',
				)
			));

			$image = $media->uploadFile();
			if (!empty($video['filename']) && !empty($image['filename'])) {
	            
	            $re_data = array(
	            	'user_id' => $me['user_id'],
	            	'time' => time(),
	            	'type' => 'reels',
	            );

	            if (!empty($_POST['caption'])) {
					$text = Generic::cropText($_POST['caption'],500);
					$re_data['description'] = $text;
				}

				$post_id = $posts->insertPost($re_data);

				if (is_numeric($post_id)) {
					$re_data = array(
						'post_id' => $post_id,
						'file' => $video['filename'],
						'extra' => $image['cname'],
					);

					$posts->setPostId($post_id);
					$posts->insertMedia($re_data);
		
					$post_data = o2array($posts->postData());

					$data = array(
		            	'code'     => '200',
		                'status' => 'OK',
		                'message' => 'Reel uploaded successfully'
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
	                'message' => $video['error']
	            ); 
	            self::json($data);
			}
		}
    }

}