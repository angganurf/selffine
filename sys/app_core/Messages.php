<?php 

class Messages extends User{

	public function getMessages($to_id = false,$offset = false,$new = false,$order = 'ASC',$e = '>'){
		global $me;
		if (empty($to_id) || !is_numeric($to_id)) {
			return false;
		}

		else if(empty($this->user_id) || !is_numeric($this->user_id)){
			return false;
		}

		$data    = array();
		$user_id = $this->user_id;
		$update  = array();
		$sql     = pxp_sqltepmlate('chat/get.conversation.data',array(
			't_messages' => T_MESSAGES,
			'offset' => $offset,
			'from_id' => $user_id,
			'to_id' => $to_id,
			'new' => $new,
			'total_limit' => $this->limit,
			'order' => $order,
			'E' => $e
		));

		try {
			$messages = self::$db->rawQuery($sql);
		} 
		catch (Exception $e) {
			$messages = array();
		}

		if (is_array($messages) && count($messages) > 0) {

			foreach ($messages as $message) {
				$message->reply_data = array();
				if (!empty($message->reply_id)) {
					$message->reply_data = $this->messageData($message->reply_id);
				}
				$message->story_data = array();
				if (!empty($message->story_id)) {

					$story   = new Story();

			    	$story = self::$db->where('id',$message->story_id)->getOne(T_STORY);
			    	if (!empty($story)) {
			    		$is_seen = self::$db->where('story_id',$message->story_id)->where('user_id' , $me['user_id'])->getValue(T_STORY_VIEWS,'COUNT(*)');
			    		if ($is_seen == 0) {
			    			self::$db->insert(T_STORY_VIEWS,array(
					            'story_id' => $story->id,
					            'user_id'  => $me['user_id'],
					            'time'     => time()
					        ));
			    		}
				    		
						$story->media_file = media($story->media_file);
						$story->media_file = media($story->media_file);
						$story->time_text = time2str($story->time);
						$story->caption = strip_tags($story->caption);
						$message->story_data = $story;
					}
				}
				if (empty($message->seen)) {
					$update[] = $message->id;
				}

				$message->text = $this->linkifyHTags($message->text);
			}

			if (!empty($update)) {
				self::$db->where('id',$update,"IN");
				self::$db->update(T_MESSAGES,array(
					'seen' => time()
				));
			}

			

			$data = $messages;
		}

		return $data;
	}

	public function messageData($msg_id = false){
		global $me;
		if (empty($msg_id) || !is_numeric($msg_id)) {
			return false;
		}

		self::$db->where('`id`',$msg_id);
		$mssg = array();
		$data = self::$db->getOne(T_MESSAGES);
		if (!empty($data)) {
			$data->text = $this->linkifyHTags($data->text);
			$data->reply_data = array();
			$data->story_data = array();
			if (!empty($data->reply_id)) {
				$data->reply_data = $this->messageData($data->reply_id);
			}
			if (!empty($data->story_id)) {

				$story   = new Story();

		    	$story = self::$db->where('id',$data->story_id)->getOne(T_STORY);
		    	if (!empty($story)) {
		    		$is_seen = self::$db->where('story_id',$data->story_id)->where('user_id' , $me['user_id'])->getValue(T_STORY_VIEWS,'COUNT(*)');
		    		if ($is_seen == 0) {
		    			self::$db->insert(T_STORY_VIEWS,array(
				            'story_id' => $story->id,
				            'user_id'  => $me['user_id'],
				            'time'     => time()
				        ));
		    		}
			    		
					$story->media_file = media($story->media_file);
					$story->media_file = media($story->media_file);
					$story->time_text = time2str($story->time);
					$story->caption = strip_tags($story->caption);
					$data->story_data = $story;
				}
			}
			$mssg       = $data;
		}
		
		return $mssg;
	}


	public function sendMessage($re_data = array()){
		if (empty($re_data) || !is_array($re_data)) {
			return false;
		}

		$msg_id   = self::$db->insert(T_MESSAGES,$re_data);
		$msg_data = null;
		$from_id  = $re_data['from_id'];
		$to_id    = $re_data['to_id'];

		if (!empty($msg_id)) {
			$msg_data = $this->messageData($msg_id);
		}

		$this->createChat($from_id,$to_id);
		if (self::$config['push'] == 1) {
			$user         = new User();
			$to_data = $user->getUserDataById($to_id);
            $ids             = '';
            if (!empty($to_data->device_id)) {
                $ids = array($to_data->device_id);
            }
            self::$me = toArray(self::$me);
			$send_array = array(
                'send_to' => $ids,
                'notification' => array(
                    'notification_content' => 'sent_message',
                    'notification_title' => self::$me['name'],
                    'notification_image' => self::$me['avatar'],
                    'notification_data' => array(
                        'user_id' => $from_id
                    ),
                    'notification_info' => array(
                        'type' => 'sent_message'
                    )
                )
            );
			$send_user = array();
        	$send_user['user_id'] = self::$me['user_id'];
        	$send_user['email'] = self::$me['email'];
        	$send_user['avatar'] = self::$me['avatar'];
        	$send_user['username'] = self::$me['username'];
        	$send_user['name'] = self::$me['name'];
        	$send_user['url'] = self::$me['url'];
        	$send_array['notification']['notification_data']['user_data'] = $send_user;


            
			SendPushNotification($send_array, 'native');
		}

		return $msg_data;
	}

	public function createChat($from_id = null,$to_id = null){
		if (empty(IS_LOGGED) || empty($from_id) || empty($to_id)) {
			return false;
		}

		$time    = time();
		$t_chats = T_CHATS;

		self::$db->where('from_id',$from_id);
		self::$db->where('to_id',$to_id);
		$chat1   = self::$db->getValue(T_CHATS,"COUNT(id)");

		if (empty($chat1)) {

			self::$db->insert(T_CHATS,array('from_id' => $from_id,'to_id' => $to_id,'time' => $time));

			self::$db->where('from_id',$to_id);
			self::$db->where('to_id',$from_id);
			$chat2 = self::$db->getValue(T_CHATS,"COUNT(id)");
			if (empty($chat2)) {
				self::$db->insert(T_CHATS,array('to_id' => $from_id,'from_id' => $to_id,'time' => $time));
			}
		}
		else{
			self::$db->where('from_id',$from_id);
			self::$db->where('to_id',$to_id);
			self::$db->update(T_CHATS,array('time' => $time));

			self::$db->where('from_id',$to_id);
			self::$db->where('to_id',$from_id);
			$chat2 = self::$db->getValue(T_CHATS,"COUNT(id)");
			if (empty($chat2)) {
				self::$db->insert(T_CHATS,array('to_id' => $from_id,'from_id' => $to_id,'time' => $time));
			}
			else{
				self::$db->where('from_id',$to_id);
				self::$db->where('to_id',$from_id);
				self::$db->update(T_CHATS,array('time' => $time));
			}
		}
	}

	public function getChats($offset = false){
		if(empty($this->user_id) || !is_numeric($this->user_id)){
			return false;
		}

		$data    = array();
		$user_id = $this->user_id;
		$sql     = pxp_sqltepmlate('chat/get.chat.history',array(
			't_messages' => T_MESSAGES,
			't_chats' => T_CHATS,
			't_users' => T_USERS,
			't_blocks' => T_PROF_BLOCKS,
			'offset' => $offset,
			'user_id' => $user_id,
			'total_limit' => $this->limit
		));

		try {
			$chats = self::$db->rawQuery($sql);
		} 
		catch (Exception $e) {
			$chats = array();
		}
		
		if (!empty($chats)) {
			$data = $chats;
		}

		return $data;
	}

	public function deleteChat($to_id = null){
		if (empty($this->user_id) || empty($to_id)) {
			return false;
		}

		$user_id = $this->user_id;
		self::$db->where('from_id',$user_id);
		self::$db->where('to_id',$to_id);
		self::$db->update(T_MESSAGES,array('deleted_fs1' => '1'));

		self::$db->where('to_id',$user_id);
		self::$db->where('from_id',$user_id);
		self::$db->update(T_MESSAGES,array('deleted_fs2' => '1'));

		self::$db->where('from_id',$user_id);
		self::$db->where('to_id',$to_id);
		return self::$db->delete(T_CHATS);
	}

	public function clearChat($to_id = null){
		if (empty($this->user_id) || empty($to_id)) {
			return false;
		}

		$user_id = $this->user_id;
		self::$db->where('from_id',$user_id);
		self::$db->where('to_id',$to_id);
		$q1 = self::$db->update(T_MESSAGES,array('deleted_fs1' => '1'));

		self::$db->where('to_id',$user_id);
		self::$db->where('from_id',$to_id);
		$q2 = self::$db->update(T_MESSAGES,array('deleted_fs2' => '1'));

		// self::$db->where('to_id',$to_id);
		// $q2 = self::$db->update(T_MESSAGES,array('deleted_fs2' => '1'));

		return ($q1 && $q2);
	}

	public function deleteMessages($to_id = null,$messages = array()){
		if (empty($this->user_id) || empty($to_id)) {
			return false;
		}

		$user_id = $this->user_id;
		self::$db->where('id',$messages,"IN");
		self::$db->where('from_id',$user_id);
		$q1 = self::$db->update(T_MESSAGES,array('deleted_fs1' => '1'));
		
		self::$db->where('id',$messages,"IN");
		self::$db->where('to_id',$user_id);
		$q2 = self::$db->update(T_MESSAGES,array('deleted_fs2' => '1'));

		return ($q1 && $q2);
	}

	public function countNewMessages(){
		if (empty($this->user_id)) {
			return false;
		}

		$user_id = $this->user_id;
		self::$db->where('to_id',$user_id);
		self::$db->where('seen',0);
		return self::$db->getValue(T_MESSAGES,"COUNT(`id`)");
	}
}