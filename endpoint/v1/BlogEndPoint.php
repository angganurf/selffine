<?php 

class BlogEndPoint extends Generic
{
	
	function __construct($api_resource_id)
	{
		switch ($api_resource_id) {
			case 'create_blog':
				self::create_blog();
				break;
			case 'get_blogs':
				self::get_blogs();
				break;
			case 'get_blog':
				self::get_blog();
				break;
			case 'edit_blog':
				self::edit_blog();
				break;
			case 'delete_blog':
				self::delete_blog();
				break;
			case 'add_comment':
				self::add_comment();
				break;
			case 'delete_comment':
				self::delete_comment();
				break;
			case 'get_blogs_by_category':
				self::get_blogs_by_category();
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


	private function delete_comment()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	if (empty($_POST['id'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
        }

        $id = Generic::secure($_POST['id']);
        $posts   = new Blogs();
        $comment = $posts->postCommentData($id);
        if (!empty($comment)) {
        	$post_data = $db->arrayBuilder()->where('posted', 1)->where('id', $comment['post_id'])->getOne(T_BLOG);
        	if ($comment['user_id'] == $me['user_id'] || $post_data['user_id'] == $me['user_id']) {
        		$db->where('id', $comment['id'])->delete(T_BLOG_COMMENTS);
        		$data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Comment deleted'
	            ); 
	            self::json($data);
        	}
        	else{
        		$data = array(
	            	'code'     => '400',
	                'status' => 'Bad Request',
	                'message' => 'You do not have permission'
	            ); 
	            self::json($data);
        	}
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Comment not found'
            ); 
            self::json($data);
        }
    }

	private function add_comment()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	if (empty($_POST['id']) || empty($_POST['text'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id , text can not be empty'
            ); 
            self::json($data);
        }


        $posts   = new Blogs();
		$notif   = new Notifications();
		$post_id = $_POST['id'];
		$text    = Generic::cropText($_POST['text'],$config['comment_len']);
		$text    = Generic::secure($text);

		$posts->setBlogId($post_id);
		//$posts->setUserById($me['user_id']);

		$link_regex = '/(http\:\/\/|https\:\/\/|www\.)([^\ ]+)/i';
        $i          = 0;
        preg_match_all($link_regex, $text, $matches);
        foreach ($matches[0] as $match) {
            $match_url = strip_tags($match);
            $syntax    = '[a]' . urlencode($match_url) . '[/a]';
            $text      = str_replace($match, $syntax, $text);
        }

		$re_data = array(
			'text' => $text,
			'time' => time(),
		);
		$insert = $posts->addPostComment($re_data);
		if (!empty($insert)) {
			$comment = $posts->postCommentData($insert);
			$comment['avatar'] = media($comment['avatar']);
			$data = array(
            	'code'     => '200',
                'status' => 'OK',
                'data' => $comment
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


	private function delete_blog()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	if (empty($_POST['id'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
        }
        $post_data = $db->arrayBuilder()->where('posted', 1)->where('id', Generic::secure($_POST['id']))->where('user_id', $me['user_id'])->getOne(T_BLOG);
        if (!empty($post_data)) {
        	$media              = new Media();
        	$old_thumb = $post_data['thumbnail'];
    		$cthumbnail = str_replace('_image','_image_c',$old_thumb);
            $media->deleteFromFTPorS3($old_thumb);
            @unlink($old_thumb);
            $media->deleteFromFTPorS3($cthumbnail);
            @unlink($cthumbnail);
            $db->where('id', Generic::secure($_POST['id']))->where('user_id', $me['user_id'])->delete(T_BLOG);
            $data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Blog deleted successfully'
            ); 
            self::json($data);
        }
        else{
        	$data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'Blog not found'
            ); 
            self::json($data);
        }
    }


	private function edit_blog()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	if (empty($_POST['category']) || empty($_POST['title']) || empty($_POST['description']) || empty($_POST['id'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'category , title , description , id can not be empty'
            ); 
            self::json($data);
        }

        $post_data = $db->arrayBuilder()->where('posted', 1)->where('id', Generic::secure($_POST['id']))->where('user_id', $me['user_id'])->getOne(T_BLOG);
        if (!empty($post_data)) {
        	$category           = Generic::secure($_POST['category']);
	        $title              = Generic::secure($_POST['title']);
	        $description        = Generic::secure($_POST['description']);
			$tags               = !empty($_POST['tags']) ? Generic::secure($_POST['tags']) : '';
	        $content            = !empty($_POST['content']) ? Generic::secure(base64_decode($_POST['content'])) : '';

	        $media_file = '';
	        if (!empty($_FILES['thumbnail']) && file_exists($_FILES['thumbnail']['tmp_name'])) {
	            $media = new Media();
	            $media->setFile(array(
	                'file' => $_FILES['thumbnail']['tmp_name'],
	                'name' => $_FILES['thumbnail']['name'],
	                'size' => $_FILES['thumbnail']['size'],
	                'type' => $_FILES['thumbnail']['type'],
	                'allowed' => 'jpeg,jpg,png',
	                'crop' => array(),
	                'avatar' => false
	            ));
	            $upload = $media->uploadFile();
	            if (!empty($upload)) {
	                $media_file = $upload['filename'];
	            }
			}
			$posted = 0;
			if($me['admin'] === 1){
				$posted = 1;
			}
	        $data_ = array(
				'posted'		=> $posted,
	            'title'         => $title,
	            'content'       => $content,
	            'description'   => $description,
	            'category'      => $category,
	            'tags'          => $tags
			);
			if (!empty($media_file)) {
				$data_['thumbnail'] = $media_file;
			}

			$add   = $db->where('id',$post_data['id'])->update(T_BLOG, $data_);
	        if ($add) {
	        	if (!empty($media_file) && $post_data['thumbnail'] != 'media/upload/photos/d-blog.jpg') {
	        		$media              = new Media();
	        		$old_thumb = $post_data['thumbnail'];
	        		$cthumbnail = str_replace('_image','_image_c',$old_thumb);
	                $media->deleteFromFTPorS3($old_thumb);
	                @unlink($old_thumb);
	                $media->deleteFromFTPorS3($cthumbnail);
	                @unlink($cthumbnail);
	        	}
	            $data = array(
	            	'code'     => '200',
	                'status' => 'OK',
	                'message' => 'Blog updated successfully'
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
                'message' => 'Blog not found'
            ); 
            self::json($data);
        }
    }


	private function get_blog()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	if (empty($_POST['id']) || !is_numeric($_POST['id'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'id can not be empty'
            ); 
            self::json($data);
        }
        $post_data = $db->arrayBuilder()->where('posted', 1)->where('id', Generic::secure($_POST['id']))->getOne(T_BLOG);
	    $user         = new User();
	    if (!empty($post_data)) {
	    	$post_data['category_name'] = lang($post_data['category']);
	        $post_data['full_thumbnail'] = media($post_data['thumbnail']);
	        $post_data['text_time'] = time2str($post_data['created_at']);
    		$user->setUserById($post_data['user_id']);
			$user_data = $user->getUserDataById($post_data['user_id']);
			unset($user_data->password);
			unset($user_data->email_code);
			unset($user_data->login_token);
			unset($user_data->edit);
			$user_data->time_text = time2str($user_data->last_seen);
			$user_data->cover = media($user_data->cover);
			$user_data->avatar = media($user_data->avatar);
			$user_data->is_following = $user->isFollowing($user_data->user_id,$me['user_id']);
			$user_data->is_blocked  = $user->isBlocked($user_data->user_id,false);
			$post_data['user_data'] = $user_data;

			$data = array(
	        	'code'     => '200',
	            'status' => 'OK',
	            'data' => $post_data
	        ); 
	        self::json($data);
	    }
	    $data = array(
        	'code'     => '400',
            'status' => 'Bad Request',
            'message' => 'Post not found'
        ); 
        self::json($data);
    }
    	

	private function get_blogs_by_category()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	if (empty($_POST['category_id'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'category_id can not be empty'
            ); 
            self::json($data);
        }
    	
    	$blogs  = array();
    	$limit = !empty($_POST['limit']) && $_POST['limit'] <= 50 ? Generic::secure($_POST['limit']) : 30;
		$offset  = !empty($_POST['offset']) ? Generic::secure($_POST['offset']) : false;
		if (!empty($offset)) {
			$db->where('id',$offset,'<');
		}
	    $posts = $db->arrayBuilder()->where('posted', 1)->where('category', Generic::secure($_POST['category_id']))->orderBy('id','DESC')->get(T_BLOG,$limit);
	    $user         = new User();
	    foreach ($posts as $key => $post_data) {
	        $post_data['category_name'] = lang($post_data['category']);
	        $post_data['full_thumbnail'] = media($post_data['thumbnail']);
	        $post_data['text_time'] = time2str($post_data['created_at']);
    		$user->setUserById($post_data['user_id']);
			$user_data = $user->getUserDataById($post_data['user_id']);
			unset($user_data->password);
			unset($user_data->email_code);
			unset($user_data->login_token);
			unset($user_data->edit);
			$user_data->time_text = time2str($user_data->last_seen);
			$user_data->cover = media($user_data->cover);
			$user_data->avatar = media($user_data->avatar);
			$user_data->is_following = $user->isFollowing($user_data->user_id,$me['user_id']);
			$user_data->is_blocked  = $user->isBlocked($user_data->user_id,false);
			$post_data['user_data'] = $user_data;
	        $blogs[]    = $post_data;
	    }
	    $data = array(
        	'code'     => '200',
            'status' => 'OK',
            'data' => $blogs
        ); 
        self::json($data);
    }

	private function get_blogs()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	
    	$blogs  = array();
    	$limit = !empty($_POST['limit']) && $_POST['limit'] <= 50 ? Generic::secure($_POST['limit']) : 30;
		$offset  = !empty($_POST['offset']) ? Generic::secure($_POST['offset']) : false;
		if (!empty($offset)) {
			$db->where('id',$offset,'<');
		}
	    $posts = $db->arrayBuilder()->where('posted', 1)->orderBy('id','DESC')->get(T_BLOG,$limit);
	    $user         = new User();
	    foreach ($posts as $key => $post_data) {
	        $post_data['category_name'] = lang($post_data['category']);
	        $post_data['full_thumbnail'] = media($post_data['thumbnail']);
	        $post_data['text_time'] = time2str($post_data['created_at']);
    		$user->setUserById($post_data['user_id']);
			$user_data = $user->getUserDataById($post_data['user_id']);
			unset($user_data->password);
			unset($user_data->email_code);
			unset($user_data->login_token);
			unset($user_data->edit);
			$user_data->time_text = time2str($user_data->last_seen);
			$user_data->cover = media($user_data->cover);
			$user_data->avatar = media($user_data->avatar);
			$user_data->is_following = $user->isFollowing($user_data->user_id,$me['user_id']);
			$user_data->is_blocked  = $user->isBlocked($user_data->user_id,false);
			$post_data['user_data'] = $user_data;
	        $blogs[]    = $post_data;
	    }
	    $data = array(
        	'code'     => '200',
            'status' => 'OK',
            'data' => $blogs
        ); 
        self::json($data);
    }

	private function create_blog()
	{
		global $me,$config,$pixelphoto,$sqlConnect,$db;
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
    	if (empty($_POST['category']) || empty($_POST['title']) || empty($_POST['description'])) {
            $data = array(
            	'code'     => '400',
                'status' => 'Bad Request',
                'message' => 'category , title , description can not be empty'
            ); 
            self::json($data);
        }

        
        $category           = Generic::secure($_POST['category']);
        $title              = Generic::secure($_POST['title']);
        $description        = Generic::secure($_POST['description']);
		$tags               = !empty($_POST['tags']) ? Generic::secure($_POST['tags']) : '';
        $content            = !empty($_POST['content']) ? Generic::secure(base64_decode($_POST['content'])) : '';

        $media_file = 'media/upload/photos/d-blog.jpg';
        if (!empty($_FILES['thumbnail']) && file_exists($_FILES['thumbnail']['tmp_name'])) {
            $media = new Media();
            $media->setFile(array(
                'file' => $_FILES['thumbnail']['tmp_name'],
                'name' => $_FILES['thumbnail']['name'],
                'size' => $_FILES['thumbnail']['size'],
                'type' => $_FILES['thumbnail']['type'],
                'allowed' => 'jpeg,jpg,png',
                'crop' => array(),
                'avatar' => false
            ));
            $upload = $media->uploadFile();
            if (!empty($upload)) {
                $media_file = $upload['filename'];
                //$media_file = Media::getMedia($photo);
            }
		}
		$posted = 0;
		if($me['admin'] === 1){
			$posted = 1;
		}
        $data_ = array(
			'user_id'		=> $me['user_id'],
			'posted'		=> $posted,
            'title'         => $title,
            'content'       => $content,
            'description'   => $description,
            'category'      => $category,
            'tags'          => $tags,
            'thumbnail'     => $media_file,
            'created_at'    => time()
		);
        $add   = RegisterNewBlogPost($data_);
        if ($add) {
			$data = array(
            	'code'     => '200',
                'status' => 'OK',
                'message' => 'Blog created successfully'
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




}