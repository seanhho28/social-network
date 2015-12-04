<?php
	class User {
		private	$_db,
				$_data,
				$_friends = array(),
				$_sessionName,
				$_cookieName,
				$_isLoggedIn;
		
		public function __construct($user = null) {
			$this->_db = DB::getInstance();
			$this->_sessionName = Config::get('session/session_name');
			$this->_cookieName = Config::get('remember/cookie_name');
			
			if(!$user) {
				if(Session::exists($this->_sessionName)) {
					$user = Session::get($this->_sessionName);
					
					if($this->find($user)) {
						$this->_isLoggedIn = true;
					}
					else {
						// Logout
					}
				}
			}
			else {
				$this->find($user);
			}
		}

		public function find($input = null) {
			if($input) {
				$field = (is_numeric($input)) ? 'id' : 'username';
				$data = $this->_db->get('users', "{$field} = '{$input}'");
				
				if($data->count()) {
					$this->_data = $data->first();				
					
					return true;
				}
			}
			
			return false;
		}
		
		public function create($fields = array()) {
			if(!$this->_db->insert('users', $fields)) {
				throw new Exception('There was a problem creating an account');
			}

			$id = $this->_db->get('users', "username = '{$fields['username']}'")->first()->id;

			if(!$this->_db->insert('users_privacy', array('id' => $id))) {
				$this->_db->delete('users', "id = '{$id}'");
				throw new Exception('There was a problem creating an account');
			}
		}

		public function login($username = null, $password = null, $remember = false) {
			if(!$username && !$password && $this->exists()) {
				Session::put($this->_sessionName, $this->data()->id);
			}
			else {
				$user = $this->find($username);
				
				if($user) {
					if($this->data()->password === Hash::make($password, $this->data()->salt)) {
						Session::put($this->_sessionName, $this->data()->id);
						
						if($remember) {
							$hash = Hash::unique();
							$hashCheck = $this->_db->get('users_session', "user_id = '{$this->data()->id}'");
							
							if(!$hashCheck->count()) {
								$this->_db->insert('users_session', array(
									'user_id' => $this->data()->id,
									'hash' => $hash
								));
							}
							else {
								$hash = $hashCheck->first()->hash;
							}
							
							Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
						}
						
						return true;
					}
				}
			}
			
			return false;
		}

		public function update($fields = array(), $id = null) {
			if(!$id && $this->isLoggedIn()) {
				$id = $this->data()->id;
			}
			
			if(!$this->_db->update('users', $id, $fields)) {
				throw new Exception('There was a problem updating profile');
			}
		}

		public function getPrivacy() {
			$uid = $this->data()->id;
			$settings = $this->_db->get('users_privacy', "id = '{$uid}'");

			if($settings->count()) {
				return $settings->first();
			}
		
			return false;
		}

		public function updatePrivacy($fields = array(), $id = null) {
			if(!$id && $this->isLoggedIn()) {
				$id = $this->data()->id;
			}
			
			if(!$this->_db->update('users_privacy', $id, $fields)) {
				throw new Exception('There was a problem updating privacy settings');
			}
		}

		public function logout() {
			$this->_db->delete('users_session', "user_id = '{$this->data()->id}'");
			Session::delete($this->_sessionName);
			Cookie::delete($this->_cookieName);
		}

		public function getFriends() {
			$uid = $this->data()->id;
			$friends = $this->_db->get('friends', "uid = '{$uid}'");

			if($friends->count()) {
				$friendsData = $friends->results();
				
				foreach($friendsData as $friendData) {
					$friend = new User($friendData->fid);

					$this->_friends[] = $friend->data();
				}

				return true;
			}
			
			return false;
		}

		public function isFriend($fid) {
			$uid = $this->data()->id;
			$friend = $this->_db->get('friends', "uid = '{$uid}' AND fid = '{$fid}'");

			if($friend->count()) {
				return true;
			}

			return false;
		}

		public function getFriendRequests($fid = null) {
			$uid = $this->data()->id;
			$where = (!$fid) ? "uid = {$uid}" : "uid = {$uid} AND fid = {$fid}";
			$request = $this->_db->get('friends_request', $where);

			if($request->count()) {
				return true;
			}

			return false;
		}

		public function requestFriend($fid) {
			$uid = $this->data()->id;
			$timestamp = date('Y-m-d H:i:s');

			$fields = array(
				'uid' => $fid,
				'fid' => $uid,
				'timestamp' => $timestamp
			);
			
			if(!$this->_db->insert('friends_request', $fields)) {
				throw new Exception('There was a problem requesting friend');
			}

			$notification = new Notification();

			$notification->set(array(
				'uid' => $uid,
				'fid' => $fid,
				'type' => 'friend_requested',
				'timestamp' => $timestamp
			));

			$notification->set(array(
				'uid' => $fid,
				'fid' => $uid,
				'type' => 'friend_request',
				'timestamp' => $timestamp
			));
		}

		public function addFriend($fid) {
			$uid = $this->data()->id;

			$request = new Request();

			$requestInfo = array(
				'uid' => $uid,
				'fid' => $fid
			);
			
			if ($request->delete('friend', $requestInfo)) {
				$timestamp = date('Y-m-d H:i:s');

				$fields = array(
					'uid' => $uid,
					'fid' => $fid,
					'timestamp' => $timestamp
				);
				
				if(!$this->_db->insert('friends', $fields)) {
					throw new Exception('There was a problem adding friend');
				}

				$id = $this->_db->get('friends', "uid = '{$uid}' AND fid = '{$fid}'")->first()->id;

				$fields2 = array(
					'uid' => $fid,
					'fid' => $uid,
					'timestamp' => $timestamp
				);

				if(!$this->_db->insert('friends', $fields2)) {
					$this->_db->delete('friends', "id = '{$id}'");
					throw new Exception('There was a problem adding friend');
				}

				$notification = new Notification();

				$notification->set(array(
					'uid' => $uid,
					'fid' => $fid,
					'type' => 'friend_added',
					'timestamp' => $timestamp
				));

				$notification->set(array(
					'uid' => $fid,
					'fid' => $uid,
					'type' => 'friend_added',
					'timestamp' => $timestamp
				));

				return true;
			}

			return false;
		}

		public function unfriend($fid) {
			if($this->isFriend($fid)) {
				$uid = $this->data()->id;

				if($this->isFriend($fid)) {
					if(!$this->_db->delete('friends', "uid = '{$uid}' AND fid = '{$fid}'")) {
						throw new Exception('There was a problem unfriending');
					}

					$fields = array(
						'uid' => $uid,
						'fid' => $fid
					);

					if(!$this->_db->delete('friends', "uid = '{$fid}' AND fid = '{$uid}'")) {
						$this->_db->insert('friends', $fields);
						throw new Exception('There was a problem unfriending');
					}

					return true;
				}
			}

			return false;
		}
		
		public function exists() {
			return (!empty($this->_data)) ? true : false;
		}
		
		public function data() {
			return $this->_data;
		}

		public function friends() {
			return $this->_friends;
		}
		
		public function isLoggedIn() {
			return $this->_isLoggedIn;
		}

		// Checks user permissions
		/*
		public function hasPermission($key) { // Checks type of user
			$group = $this->_db->get('groups', "id = {$this->data()->group}");
			
			if($group->count()) {
				$permissions = json_decode($group->first()->permissions, true);
				
				if($permissions[$key] == true) {
					return true;
				}
			}
			
			return false;
		}
		*/
	}