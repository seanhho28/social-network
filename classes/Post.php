<?php
	class Post {
		private	$_db,
				$_posts = array();

		public function __construct($id = null) {
			$this->_db = DB::getInstance();

			if($id) {
				$this->get($id);
			}
		}

		public function set($fields = array()) {
			if(!$this->_db->insert('posts', $fields)) {
				throw new Exception('There was a problem posting');
			}

			$notification = new Notification();
			$timestamp = date('Y-m-d H:i:s');

			$notification->set(array(
				'uid' => $fields['uid'],
				'fid' => $fields['fid'],
				'type' => 'new_post',
				'timestamp' => $timestamp
			));

			$notification->set(array(
				'uid' => $fields['fid'],
				'fid' => $fields['uid'],
				'type' => 'posted',
				'timestamp' => $timestamp
			));
		}

		public function get($uid) {
			$pos = $this->_db->get('posts', "uid = {$uid} ORDER BY timestamp DESC");

			if($pos->count()) {
				$posts = $pos->results();

				foreach($posts as $post) {
					$friend = new User($post->fid);
					$friendName = $friend->data()->firstName . ' ' . $friend->data()->middleName . ' ' . $friend->data()->lastName;
					$datetime = new DateTime($post->timestamp);
					$timestamp = $datetime->format('l, F j, Y \a\t g:ia');

					$this->add(array(
						'username' => $friend->data()->username,
						'name' => $friendName,
						'profilePicture' => $friend->data()->profilePicture,
						'comment' => $post->comment,
						'timestamp' => $timestamp
					));
				}

				return true;
			}

			return false;
		}

		public function add($post) {
			$this->_posts[] = $post;
		}

		public function posts() {
			return $this->_posts;
		}
	}