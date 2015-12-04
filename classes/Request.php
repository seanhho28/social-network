<?php
	class Request {
		private $_db,
				$_requests = array(),
				$_count = 0;

		public function __construct() {
			$this->_db = DB::getInstance();
		}

		public function get($type, $uid) {
			switch($type) {
				case 'friend':
					$table = 'friends_request';
				break;
			}

			$req = $this->_db->get($table, "uid = '{$uid}' ORDER BY timestamp DESC");


			if($req->count()) {
				$requests = $req->results();

				foreach($requests as $request) {
					$friend = new User($request->fid);
					$friendName = $friend->data()->firstName . ' ' . $friend->data()->middleName . ' ' . $friend->data()->lastName;

					switch($table) {
						case 'friends_request':
							$this->add(array(
								'fid' => $friend->data()->id,
								'username' => $friend->data()->username,
								'name' => $friendName,
								'profilePicture' => $friend->data()->profilePicture
							));
						break;
					}
				}

				return true;
			}

			return false;
		}

		public function set($type, $fields = array()) {
			switch($type) {
				case 'friend':
					$table = 'friends_request';
				break;
			}

			if(!$this->_db->insert($table, $fields)) {
				throw new Exception('There was a problem setting request');
			}
		}

		public function delete($type, $fields = array()) {
			switch($type) {
				case 'friend':
					$table = 'friends_request';
				break;
			}

			$uid = $fields['uid'];
			$fid = $fields['fid'];

			$request = $this->_db->get($table, "uid = '{$uid}' AND fid = '{$fid}'");

			if($request->count()) {
				$id = $request->first()->id;

				if(!$this->_db->delete($table, "id = '{$id}'")) {
					throw new Exception('There was a problem deleting request');
				}

				return true;
			}

			return false;
		}

		public function add($request) {
			$this->_requests[] = $request;
		}

		public function requests() {
			return $this->_requests;
		}

		public function count() {
			return $this->_count;
		}
	}