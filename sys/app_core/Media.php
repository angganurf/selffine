<?php
use Aws\S3\S3Client;

class Media extends Generic{
	protected $path, $file, $name, $size, $type, $crop, $cropHeight, $cropWidth, $allowed,$avatar = false,$compress = true;

    public function __construct() {

    }

	public function setFile($data = array()) {

		
		if (isset($data['file']) && !empty($data['file'])) {
	        $this->file = $this->secure($data['file']);
	    }

	    if (isset($data['name']) && !empty($data['name'])) {
	        $this->name = $this->secure($data['name']);
	    }

	    if (isset($data['size']) && !empty($data['size'])) {
	        $this->size = $this->secure($data['size']);
	    }

	    if (isset($data['type']) && !empty($data['type'])) {
	        $this->type = $this->secure($data['type']);
	    }

	    if (isset($data['allowed']) && !empty($data['allowed'])) {
	        $this->allowed = $this->secure($data['allowed']);
	    }

	    if (isset($data['crop']) && !empty($data['crop'])) {
	        $this->crop = $data['crop'];
	    }

	    if (isset($data['crop']['height']) && !empty($data['crop']['height'])) {
	        $this->cropHeight = $this->secure($data['crop']['height']);
	    }

	    if (isset($data['crop']['width']) && !empty($data['crop']['width'])) {
	        $this->cropWidth = $this->secure($data['crop']['width']);
	    }
	    if (isset($data['avatar']) && !empty($data['avatar'])) {
	        $this->avatar = $this->secure($data['avatar']);
		}
		if (isset($data['compress']) && !empty($data['compress'])) {
	        $this->compress = $this->secure($data['compress']);
		}
	}

	// Compress image size
	public function compressImage($source_url, $destination_url, $quality) {
        $imgsize = getimagesize($source_url);
        $finfof  = $imgsize['mime'];
        $image_c = 'imagejpeg';
        if ($finfof == 'image/jpeg') {
            $image = @imagecreatefromjpeg($source_url);
        } else if ($finfof == 'image/gif') {
            $image = @imagecreatefromgif($source_url);
        } else if ($finfof == 'image/png') {
            $image = @imagecreatefrompng($source_url);
        } else if ($finfof == 'image/webp') {
        	if (function_exists('imagecreatefromwebp')) {
        		$image = @imagecreatefromwebp($source_url);
        	}
        } else {
            $image = @imagecreatefromjpeg($source_url);
        }
        $quality = 50;
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($source_url);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image = @imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = @imagerotate($image, -90, 0);
                        break;
                    case 8:
                        $image = @imagerotate($image, 90, 0);
                        break;
                }
            }
        }
        @imagejpeg($image, $destination_url, $quality);
        return $destination_url;
    }

	// Crop image + decrease image quality
	public function cropImage($max_width, $max_height, $source_file, $dst_dir, $quality = 80) {
	    $imgsize = @getimagesize($source_file);
	    $width   = $imgsize[0];
	    $height  = $imgsize[1];
	    $mime    = $imgsize['mime'];
	    switch ($mime) {
	        case 'image/gif':
	            $image_create = "imagecreatefromgif";
	            $image        = "imagegif";
	            break;
	        case 'image/png':
	            $image_create = "imagecreatefrompng";
	            $image        = "imagepng";
	            break;
	        case 'image/jpeg':
	            $image_create = "imagecreatefromjpeg";
	            $image        = "imagejpeg";
	            break;
	        case 'image/webp':
	            if (function_exists('imagecreatefromwebp')) {
	                $image_create = "imagecreatefromwebp";
	                $image        = "imagewebp";
	            }
	            else{
	                return false;
	            }
	            break;
	        default:
	            return false;
	            break;
	    }
	    $dst_img    = @imagecreatetruecolor($max_width, $max_height);
	    $src_img    = $image_create($source_file);
	    $width_new  = $height * $max_width / $max_height;
	    $height_new = $width * $max_height / $max_width;
	    if ($width_new > $width) {
	        $h_point = (($height - $height_new) / 2);
	        @imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
	    } else {
	        $w_point = (($width - $width_new) / 2);
	        @imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
	    }
	    @imagejpeg($dst_img, $dst_dir, $quality);
	    if ($dst_img)
	        @imagedestroy($dst_img);
	    if ($src_img)
	        @imagedestroy($src_img);
	}

	// upload files to sever
	public function uploadFile($type = 0, $delete_from_stroage = true) {
		if( self::$user->is_pro == 0){
			if( !empty( self::$config['free_user_storage_limit'] ) && (int)self::$config['free_user_storage_limit'] > 1 ){
				if( (int)self::$user->uploads >= (int)self::$config['free_user_storage_limit'] ){
					return array(
						'error' => lang('free_limit_storage')
					);
				}
			}
		}
		$c_path = '';
		if (!file_exists('media/upload/photos/' . date('Y'))) {
			@mkdir('media/upload/photos/' . date('Y'), 0777, true);
		}
		if (!file_exists('media/upload/photos/' . date('Y') . '/' . date('m'))) {
			@mkdir('media/upload/photos/' . date('Y') . '/' . date('m'), 0777, true);
		}

		if (!file_exists('media/upload/videos/' . date('Y'))) {
			@mkdir('media/upload/videos/' . date('Y'), 0777, true);
		}
		if (!file_exists('media/upload/videos/' . date('Y') . '/' . date('m'))) {
			@mkdir('media/upload/videos/' . date('Y') . '/' . date('m'), 0777, true);
		}

		if (!file_exists('media/upload/files/' . date('Y'))) {
			@mkdir('media/upload/files/' . date('Y'), 0777, true);
		}
		if (!file_exists('media/upload/files/' . date('Y') . '/' . date('m'))) {
			@mkdir('media/upload/files/' . date('Y') . '/' . date('m'), 0777, true);
		}

		$new_string        = pathinfo($this->name, PATHINFO_FILENAME) . '.' . strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
		$file_extension    = pathinfo($new_string, PATHINFO_EXTENSION);

		if (!empty($this->allowed)) {
			$extension_allowed = explode(',', $this->allowed);
			
			if (!in_array($file_extension, $extension_allowed)) {

				return array(
					'error' => 'File format not supported'
				);
			}
		}

		if ($file_extension == 'jpg' || $file_extension == 'jpeg' || $file_extension == 'png' || $file_extension == 'gif') {
			$folder   = 'photos';
			$fileType = 'image';
		} else if ($file_extension == 'mp4' || $file_extension == 'webm' || $file_extension == 'flv') {
			$folder   = 'videos';
			$fileType = 'video';
		} else {
			$folder   = 'files';
			$fileType = 'file';
		}

		if (empty($folder) || empty($fileType)) {
			return false;
		}

		$ar = array(
			'video/mp4',
			'video/mov',
			'video/mpeg',
			'video/flv',
			'video/avi',
			'video/webm',
			'audio/wav',
			'audio/mpeg',
			'video/quicktime',
			'audio/mp3',
			'image/png',
			'image/jpeg',
			'image/gif',
			'application/pdf',
			'application/msword',
			'application/zip',
			'application/x-rar-compressed',
			'text/pdf',
			'application/x-pointplus',
			'text/css',
			'text/plain',
			'application/x-zip-compressed',
			'application/json'
		);

		if (!in_array($this->type, $ar)) {
			return array(
				'error' => 'File format not supported'
			);
		}

		$dir         = "media/upload";
		$generate    = date('Y') . '/' . date('m') . '/' . $this->generateKey(50,50) . '_' . date('d') . '_' . md5(time());
		$file_path   = "{$folder}/" . $generate . "_{$fileType}.{$file_extension}";
		$filename    = $dir . '/' . $file_path;
		$second_file = pathinfo($filename, PATHINFO_EXTENSION);
		if (move_uploaded_file($this->file, $filename)) {
			if ($second_file == 'jpg' || $second_file == 'jpeg' || $second_file == 'png' || $second_file == 'gif') {

				if ($second_file != 'gif' && $this->compress === true) {
					if (!empty($this->crop)) {
						$crop_image = $this->cropImage($this->cropWidth, $this->cropHeight, $filename, $filename, 100);
					}
					$this->compressImage($filename, $filename, 90);
				}
				if ($this->avatar == false) {
					// Add WaterMark
					if (self::$config['watermark'] == 'on' && !empty(self::$config['watermark_link'])) {
						$this->watermark_image($filename);
						// $watermark = new Watermark($filename);
						// $watermark->setWatermarkImage(self::$config['watermark_link']);
						// $a = $watermark->save();
					}
				    // Add WaterMark
				}

                if (!empty($this->crop)) {
                    $c_path = $dir . '/' . "{$folder}/" . $generate . "_{$fileType}_c.{$file_extension}";
                    self::cropImage(350, 350, $filename, $c_path, 90);
                }
			}

			$last_data             = array();
            $last_data['s3_upload'] = false;
			$last_data['filename'] = $filename;
			$last_data['name']     = $this->name;
			if (!empty($c_path)) {
				$last_data['cname']     = $c_path;
			}
			$upload_     = $this->uploadToS3($filename, $delete_from_stroage);
			if($c_path !== ''){
				$upload_     = $this->uploadToS3($c_path, $delete_from_stroage);
			}
			if( self::$user->is_pro == 0 &&
				!empty( self::$config['free_user_storage_limit'] ) &&
				(int)self::$config['free_user_storage_limit'] > 1
			){
				$_file_size = $this->size/1000;
				self::$db->where('user_id',self::$user->user_id);
				self::$db->update(T_USERS,array('uploads' => self::$db->inc( (int)$_file_size )));
			}
			return $last_data;
		}
	}
	
	public function UploadToDigitalOcean($file_name = '', $delete_file = true){
		if (self::$config['digital_ocean'] == 0 || empty(self::$config['digital_ocean_key']) || empty(self::$config['digital_ocean_s_key']) || empty(self::$config['digital_ocean_space_name']) || empty(self::$config['digital_ocean_region'])) {
			return false;
		}
		require_once('sys/libs/s3-lib/vendor/autoload.php');
		$key = self::$config['digital_ocean_key'];
		$secret = self::$config['digital_ocean_s_key'];
		$space_name = self::$config['digital_ocean_space_name'];
		$region = self::$config['digital_ocean_region'];
		$s3 = new S3Client(array(
                'version' => 'latest',
                'endpoint' => 'https://' . $region . '.digitaloceanspaces.com',
                'region' => $region,
                'credentials' => array(
                    'key' => $key,
                    'secret' => $secret
                )
            ));
        $s3->putObject(array(
            'Bucket' => $space_name,
            'Key' => $file_name,
            'Body' => fopen($file_name, 'r+'),
            'ACL' => 'public-read',
            'CacheControl' => 'max-age=3153600'
        ));
        if (empty($config['delete'])) {
            if ($s3->doesObjectExist($space_name, $file_name)) {
                if ($delete_file == true) {
                    @unlink($file_name);
                }
                return true;
            }
        } else {
            return true;
        }




		
		// $space = new SpacesConnect($key, $secret, $space_name, $region);
		// if(file_exists($file_name)){
		// 	$uploaded = $space->UploadFile($file_name, "public");
		// 	if ($space->DoesObjectExist($file_name)) {
		// 		if ($delete_file == true) {
		// 			@unlink($file_name);
		// 		}
		// 		return true;
		// 	}
		// }else{
		// 	return false;
		// }
	}

	public function compress_image($source_url, $destination_url, $quality) {

       $info = getimagesize($source_url);

        if ($info['mime'] == 'image/jpeg')
              $image = imagecreatefromjpeg($source_url);

        elseif ($info['mime'] == 'image/gif')
              $image = imagecreatefromgif($source_url);

      elseif ($info['mime'] == 'image/png')
              $image = imagecreatefrompng($source_url);

      elseif ($info['mime'] == 'image/webp'){
      		if (function_exists('imagecreatefromwebp')) {
      			$image = imagecreatefromwebp($source_url);
      		}
      	
      }
              

        imagejpeg($image, $destination_url, $quality);
    return $destination_url;
    }

	public function uploadToFtp($filename = '', $delete_file = true) {
		if (self::$config['ftp_upload'] != 1) {
			return false;
		}
		if (empty(self::$config['ftp_host']) || empty(self::$config['ftp_username']) || empty(self::$config['ftp_password']) || empty(self::$config['ftp_port'])) {
            return false;
		}
		require_once('sys/libs/ftp/vendor/autoload.php');

		$ftp = new \FtpClient\FtpClient();
        $ftp->connect(self::$config['ftp_host'], false, self::$config['ftp_port']);
        $login = $ftp->login(self::$config['ftp_username'], self::$config['ftp_password']);

		if ($login) {
            $file_path = substr($filename, 0, strrpos( $filename, '/'));
            $file_path_info = explode('/', $file_path);
            $path = '';
            if (!$ftp->isDir($file_path)) {
                foreach ($file_path_info as $key => $value) {
                    $path .= '/' . $value . '/' ;
                    if (!$ftp->isDir($path)) {
                        $mkdir = $ftp->mkdir($path);
                    }
                } 
            }
            $ftp->chdir($file_path);
            if ($ftp->putFromPath($filename)) {
            	if ($delete_file == true) {
                	@unlink($filename);
            	}
                return true;
            }
		}
		
	}

	public function uploadToS3($filename = '', $delete_file = true) {
		if (self::$config['amazone_s3'] != 1 && self::$config['ftp_upload'] != 1 && self::$config['google_cloud_storage'] != 1 && self::$config['digital_ocean'] != 1 && self::$config['wasabi_storage'] != 1 && self::$config['backblaze_storage'] != 1) {
			return false;
		}
		if (self::$config['amazone_s3'] == 1) {
			if (empty(self::$config['amazone_s3_key']) || empty(self::$config['amazone_s3_s_key']) || empty(self::$config['region']) || empty(self::$config['bucket_name'])) {
	            return false;
			}
			require_once('sys/libs/s3-lib/vendor/autoload.php');
			$s3 = new S3Client([
	            'version'     => 'latest',
	            'region'      => self::$config['region'],
	            'credentials' => [
	                'key'    => self::$config['amazone_s3_key'],
	                'secret' => self::$config['amazone_s3_s_key'],
	            ]
	        ]);

	        $s3->putObject([
	            'Bucket' => self::$config['bucket_name'],
	            'Key'    => $filename,
	            'Body'   => fopen($filename, 'r+'),
	            'ACL'    => 'public-read',
	            'CacheControl' => 'max-age=3153600',
			]);
			
			if ($s3->doesObjectExist(self::$config['bucket_name'], $filename)) {
				if (!empty(self::$config['amazone_cloudfront_distribution'])) {
				    $cf = new CloudFrontClient([
			            'version'     => 'latest',
			            'region'      => self::$config['region'],
			            'credentials' => [
			                'key'    => self::$config['amazone_s3_key'],
			                'secret' => self::$config['amazone_s3_s_key'],
			            ]
			        ]);
				   $cf->createInvalidation([
				      'DistributionId' => self::$config['amazone_cloudfront_distribution'],
				      'InvalidationBatch' => [
				         'CallerReference' => 'UNIQUE_CALLER_IDENTIFIER',
				         'Paths' => [
				            'Items' => [
				               $filename
				             ],
				            'Quantity' => 1
				         ]
				      ]
				   ]);
				}
				if ($delete_file == true) {
					@unlink($filename);
				}
	            return true;
	        }else{
			    return false;
	        }
		}
		else if (self::$config['ftp_upload'] == 1) {
			if (empty(self::$config['ftp_host']) || empty(self::$config['ftp_username']) || empty(self::$config['ftp_password']) || empty(self::$config['ftp_port'])) {
	            return false;
			}
			require_once('sys/libs/ftp/vendor/autoload.php');

			$ftp = new \FtpClient\FtpClient();
	        $ftp->connect(self::$config['ftp_host'], false, self::$config['ftp_port']);
	        $login = $ftp->login(self::$config['ftp_username'], self::$config['ftp_password']);

			if ($login) {
	            $file_path = substr($filename, 0, strrpos( $filename, '/'));
	            $file_path_info = explode('/', $file_path);
	            $path = '';
	            if (!$ftp->isDir($file_path)) {
	                foreach ($file_path_info as $key => $value) {
	                    $path .= '/' . $value . '/' ;
	                    if (!$ftp->isDir($path)) {
	                        $mkdir = $ftp->mkdir($path);
	                    }
	                } 
	            }
	            $ftp->chdir($file_path);
	            if ($ftp->putFromPath($filename)) {
	            	if ($delete_file == true) {
	                	@unlink($filename);
	            	}
	                return true;
	            }
			}
		}
		elseif (self::$config['google_cloud_storage'] == 1) {
			if (self::$config['google_cloud_storage'] == 0 || empty(self::$config['cloud_file_path']) || empty(self::$config['google_cloud_storage_bucket_name'])) {
				return false;
			}
			//set which bucket to work in
			$bucketName = self::$config['google_cloud_storage_bucket_name'];
			// get local file for upload testing
			$fileContent = file_get_contents($filename);
			// NOTE: if 'folder' or 'tree' is not exist then it will be automatically created !
			$cloudPath = $filename;
			$isSucceed = uploadFiletoGoogleCloud($fileContent, $cloudPath);
			if ($isSucceed == true) {			
				return true;
			} else {
				return false;
			}
		}
		elseif (self::$config['digital_ocean'] == 1) {
			if (self::$config['digital_ocean'] == 0 || empty(self::$config['digital_ocean_key']) || empty(self::$config['digital_ocean_s_key']) || empty(self::$config['digital_ocean_space_name']) || empty(self::$config['digital_ocean_region'])) {
				return false;
			}
			require_once('sys/libs/s3-lib/vendor/autoload.php');
			$key = self::$config['digital_ocean_key'];
			$secret = self::$config['digital_ocean_s_key'];
			$space_name = self::$config['digital_ocean_space_name'];
			$region = self::$config['digital_ocean_region'];
			$s3 = new S3Client(array(
	                'version' => 'latest',
	                'endpoint' => 'https://' . $region . '.digitaloceanspaces.com',
	                'region' => $region,
	                'credentials' => array(
	                    'key' => $key,
	                    'secret' => $secret
	                )
	            ));
	        $s3->putObject(array(
	            'Bucket' => $space_name,
	            'Key' => $filename,
	            'Body' => fopen($filename, 'r+'),
	            'ACL' => 'public-read',
	            'CacheControl' => 'max-age=3153600'
	        ));
	        if (empty($config['delete'])) {
	            if ($s3->doesObjectExist($space_name, $filename)) {
	                if ($delete_file == true) {
	                    @unlink($filename);
	                }
	                return true;
	            }
	        } else {
	            return true;
	        }
		}
		elseif (self::$config['backblaze_storage'] == 1) {
			$info = BackblazeConnect(array('apiUrl' => 'https://api.backblazeb2.com',
                                       'uri' => '/b2api/v2/b2_authorize_account',
                                ));
	        if (!empty($info)) {
	            $result = json_decode($info,true);
	            if (!empty($result['authorizationToken']) && !empty($result['apiUrl']) && !empty($result['accountId'])) {
	                $info = BackblazeConnect(array('apiUrl' => $result['apiUrl'],
	                                               'uri' => '/b2api/v2/b2_get_upload_url',
	                                               'authorizationToken' => $result['authorizationToken'],
	                                        ));
	                if (!empty($info)) {
	                    $info = json_decode($info,true);
	                    if (!empty($info) && !empty($info['uploadUrl'])) {
	                        $info = BackblazeConnect(array('apiUrl' => $info['uploadUrl'],
	                                                       'uri' => '',
	                                                       'file' => $filename,
	                                                       'authorizationToken' => $info['authorizationToken'],
	                                                        ));

	                        if (!empty($info)) {
	                            $info = json_decode($info,true);
	                            if (!empty($info) && !empty($info['accountId'])) {
	                                if ($delete_file == true) {
	                                    @unlink($filename);
	                                }
	                                return true;
	                            }
	                        }
	                    }
	                }
	            }
	        }
	        return false;
		}
		elseif (self::$config['wasabi_storage'] == 1) {
			if (empty(self::$config['wasabi_access_key']) || empty(self::$config['wasabi_secret_key']) || empty(self::$config['wasabi_bucket_region'])) {
				return false;
			}
			require_once('sys/libs/s3-lib/vendor/autoload.php');
	        $s3 = new S3Client(array(
	                'version' => 'latest',
	                'endpoint' => 'https://s3.'.self::$config['wasabi_bucket_region'].'.wasabisys.com',
	                'region' => self::$config['wasabi_bucket_region'],
	                'credentials' => array(
	                    'key' => self::$config['wasabi_access_key'],
	                    'secret' => self::$config['wasabi_secret_key']
	                )
	            ));
	        $s3->putObject(array(
	            'Bucket' => self::$config['wasabi_bucket_name'],
	            'Key' => $filename,
	            'Body' => fopen($filename, 'r+'),
	            'ACL' => 'public-read',
	            'CacheControl' => 'max-age=3153600'
	        ));
	        if (empty($config['delete'])) {
	            if ($s3->doesObjectExist(self::$config['wasabi_bucket_name'], $filename)) {
	                if ($delete_file == true) {
	                    @unlink($filename);
	                }
	                return true;
	            }
	        } else {
	            return true;
	        }
		}
		return false;
	}

	public function uploadToGoogleCloud($file_name = '', $delete_file = true) {
		if (self::$config['google_cloud_storage'] == 0 || empty(self::$config['cloud_file_path']) || empty(self::$config['google_cloud_storage_bucket_name'])) {
			return false;
		}
		//set which bucket to work in
		$bucketName = self::$config['google_cloud_storage_bucket_name'];
		// get local file for upload testing
		$fileContent = file_get_contents($file_name);
		// NOTE: if 'folder' or 'tree' is not exist then it will be automatically created !
		$cloudPath = $file_name;
		$isSucceed = uploadFiletoGoogleCloud($fileContent, $cloudPath);
		if ($isSucceed == true) {			
			return true;
		} else {
			return false;
		}
	}

	public function streamdeleteFromFTPorS3($filename) {

	    if (self::$config['amazone_s3_2'] == 0) {
	        return false;
	    }


		$s3Config = (
			empty(self::$config['amazone_s3_key_2']) || 
			empty(self::$config['amazone_s3_s_key_2']) || 
			empty(self::$config['region_2']) || 
			empty(self::$config['bucket_name_2'])
		); 

		if ($s3Config){
			return false;
		}
		require_once('sys/libs/s3-lib/vendor/autoload.php');
		$s3 = new S3Client([
			'version'     => 'latest',
			'region'      => self::$config['region_2'],
			'credentials' => [
				'key'    => self::$config['amazone_s3_key_2'],
				'secret' => self::$config['amazone_s3_s_key_2'],
			]
		]);

		$s3->deleteObject([
			'Bucket' => self::$config['bucket_name_2'],
			'Key'    => $filename,
		]);

		if (!$s3->doesObjectExist(self::$config['bucket_name_2'], $filename)) {
			return true;
		}
	    
	    return true;
	}

	public function deleteFromFTPorS3($filename) {

	    if (self::$config['amazone_s3'] == 0 && self::$config['ftp_upload'] == 0 && self::$config['google_cloud_storage'] == 0 && self::$config['digital_ocean'] == 0 && self::$config['wasabi_storage'] == 0 && self::$config['backblaze_storage'] == 0) {
	        return false;
	    }

	    if (self::$config['ftp_upload'] == 1) {
	    	require_once('sys/libs/ftp/vendor/autoload.php');
	        $ftp = new \FtpClient\FtpClient();
	        $ftp->connect(self::$config['ftp_host'], false, self::$config['ftp_port']);
        	$login = $ftp->login(self::$config['ftp_username'], self::$config['ftp_password']);
	        
	        if ($login) {
	            $file_path = substr($filename, 0, strrpos( $filename, '/'));
	            $file_name = substr($filename, strrpos( $filename, '/') + 1);
	            $file_path_info = explode('/', $file_path);
	            $path = '';
	            if (!$ftp->isDir($file_path)) {
	                return false;
	            }
	            $ftp->chdir($file_path);
	            $ftp->pasv(true);
	            if ($ftp->remove($file_name)) {
	                return true;
	            }
	        }
		}else if(self::$config['google_cloud_storage'] == 1){
			if ( empty(self::$config['cloud_file_path']) || empty(self::$config['google_cloud_storage_bucket_name'])) {
				return false;
			}

			if(deleteFiletoGoogleCloud($filename)){
				return true;
			}

		}else if(self::$config['backblaze_storage'] == 1){
			$info = BackblazeConnect(array('apiUrl' => 'https://api.backblazeb2.com',
	                                       'uri' => '/b2api/v2/b2_authorize_account',
	                                ));
	        if (!empty($info)) {
	            $result = json_decode($info,true);
	            if (!empty($result['authorizationToken']) && !empty($result['apiUrl']) && !empty($result['accountId'])) {
	                $info = BackblazeConnect(array('apiUrl' => $result['apiUrl'],
	                                               'uri' => '/b2api/v2/b2_list_file_names',
	                                               'authorizationToken' => $result['authorizationToken'],
	                                        ));
	                if (!empty($info)) {
	                    $info = json_decode($info,true);
	                    if (!empty($info) && !empty($info['files'])) {
	                        foreach ($info['files'] as $key => $value) {
	                            if ($value['fileName'] == $filename) {
	                                $info = BackblazeConnect(array('apiUrl' => $result['apiUrl'],
	                                                               'uri' => '/b2api/v2/b2_delete_file_version',
	                                                               'authorizationToken' => $result['authorizationToken'],
	                                                               'fileId' => $value['fileId'],
	                                                               'fileName' => $value['fileName'],
	                                                        ));
	                                return true;
	                            }
	                        }
	                    }
	                }
	            }
	        }
		}else if(self::$config['wasabi_storage'] == 1){
			require_once('sys/libs/s3-lib/vendor/autoload.php');
	        if (empty(self::$config['wasabi_bucket_name']) || empty(self::$config['wasabi_access_key']) || empty(self::$config['wasabi_secret_key']) || empty(self::$config['wasabi_bucket_region'])) {
	            return false;
	        }
	        $s3 = new S3Client(array(
	                'version' => 'latest',
	                'endpoint' => 'https://s3.'.self::$config['wasabi_bucket_region'].'.wasabisys.com',
	                'region' => self::$config['wasabi_bucket_region'],
	                'credentials' => array(
	                    'key' => self::$config['wasabi_access_key'],
	                    'secret' => self::$config['wasabi_secret_key']
	                )
	            ));
	        $s3->deleteObject(array(
	            'Bucket' => self::$config['wasabi_bucket_name'],
	            'Key' => $filename
	        ));
	        if (!$s3->doesObjectExist(self::$config['wasabi_bucket_name'], $filename)) {
	            return true;
	        }
	        return false;

		}else if(self::$config['digital_ocean'] == 1){
			require_once('sys/libs/s3-lib/vendor/autoload.php');
			$key = self::$config['digital_ocean_key'];
			$secret = self::$config['digital_ocean_s_key'];
			$space_name = self::$config['digital_ocean_space_name'];
			$region = self::$config['digital_ocean_region'];
			$s3 = new S3Client(array(
	                'version' => 'latest',
	                'endpoint' => 'https://' . $region . '.digitaloceanspaces.com',
	                'region' => $region,
	                'credentials' => array(
	                    'key' => $key,
	                    'secret' => $secret
	                )
	            ));
	        $s3->deleteObject(array(
	            'Bucket' => $space_name,
	            'Key' => $filename
	        ));
	        if (!$s3->doesObjectExist($space_name, $filename)) {
	            return true;
	        }
	        return false;

		}else {
	        $s3Config = (
	            empty(self::$config['amazone_s3_key']) || 
	            empty(self::$config['amazone_s3_s_key']) || 
	            empty(self::$config['region']) || 
	            empty(self::$config['bucket_name'])
	        ); 

	        if ($s3Config){
	            return false;
	        }
	        require_once('sys/libs/s3-lib/vendor/autoload.php');
	        $s3 = new S3Client([
	            'version'     => 'latest',
	            'region'      => self::$config['region'],
	            'credentials' => [
	                'key'    => self::$config['amazone_s3_key'],
	                'secret' => self::$config['amazone_s3_s_key'],
	            ]
	        ]);

	        $s3->deleteObject([
	            'Bucket' => self::$config['bucket_name'],
	            'Key'    => $filename,
	        ]);

	        if (!$s3->doesObjectExist(self::$config['bucket_name'], $filename)) {
	            return true;
	        }
	    }
	    return true;
	}

	public function isImage($file_path = ''){
		if (file_exists($file_path)) {
			$image      = getimagesize($file_path);
			$mime_types = array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_BMP);

	        if (in_array($image[2], $mime_types)) {
	            return true;
	        }
		}

		return false;
	}

	public function ImportImage($url = '', $type = 0,$type2 = '') {
		$this->initDir();
		$dir         = "media/upload";
		$generate = date('Y') . '/' . date('m') . '/' . $this->generateKey(50,50) . '_' . date('d') . '_' . md5(time());
		$file_path   = "photos/" .$generate. "_image.jpg";
		$filename    = $dir . '/' . $file_path;
		$put_file = file_put_contents($filename, $this->curlConnect($url));
		if ($type == 1) {
			$crop_image = $this->cropImage(150, 150, $filename, $filename, 100);
		}
		if (file_exists($filename)) {
			$this->uploadToS3($filename);
		}
		return $filename;
	}

	public function ImportImageAndCrop($url = '', $type = '') {
		$this->initDir();
		$dir         = "media/upload";
		$generate = date('Y') . '/' . date('m') . '/' . $this->generateKey(50,50) . '_' . date('d') . '_' . md5(time());
		if ($type == 'gif') {
			$file_path   = "photos/" .$generate. "_image.gif";
			$c_path   = "photos/" .$generate. "_image_c.gif";
		}
		else{
			$file_path   = "photos/" .$generate. "_image.jpg";
			$c_path   = "photos/" .$generate. "_image_c.jpg";
		}
		$filename    = $dir . '/' . $file_path;
		$c_path    = $dir . '/' . $c_path;
		$put_file = file_put_contents($filename, $this->curlConnect($url));
		if (!empty($c_path)) {
			$crop_image = $this->cropImage(300, 300, $filename, $c_path, 75);
		}
		$upload_     = $this->uploadToS3($filename, true);
		$upload_     = $this->uploadToS3($c_path, true);
		if ($type == 'gif') {
			@unlink($filename);
		}
		return array('filename' => $filename,
	                 'extra'    => $c_path);
	}

	public function initDir($dir = 'photos'){
		if (!file_exists("media/upload/$dir/" . date('Y'))) {
            @mkdir("media/upload/$dir/" . date('Y'), 0777, true);
        }

	    if (!file_exists("media/upload/$dir/" . date('Y') . '/' . date('m'))) {
	        @mkdir("media/upload/$dir/" . date('Y') . '/' . date('m'), 0777, true);
	    }
		
		return $this;
	}

	function watermark_image($target) {

	   if (self::$config['watermark'] != 'on' || empty(self::$config['watermark_link'])) {
	       return false;
	   }

	   try {
	     $image = new \claviska\SimpleImage();

	     $image
	       ->fromFile($target)
	       ->autoOrient()
	       ->overlay(self::$config['watermark_link'], 'top left', 1, 30, 30)
	       ->toFile($target, 'image/jpeg');

	     return true;
	   } catch(Exception $err) {

	     return $err->getMessage();
	   }
	}
}


?>