<?php
	class Notification {
		private $_db,
				$_notifications = array(),
				$_count = 0;

		public function __construct() {
			$this->_db = DB::getInstance();
		}

		public function get($uid) {
			$amount = 25;

			$notifs = $this->_db->get('notifications', "uid = {$uid} ORDER BY timestamp DESC LIMIT {$amount}");

			if($notifs->count()) {
				$notifications = $notifs->results();

				foreach($notifications as $notification) {
					if(!$notification->seen) {
						$this->_count++;

						if(!$this->_db->update('notifications', $notification->id, array('seen' => '1'))) {
							throw new Exception('There was a problem updating read notification');
						}
					}

					$friend = new User($notification->fid);
					$friendUsername = $friend->data()->username;
					$friendName = $friend->data()->firstName . ' ' . $friend->data()->middleName . ' ' . $friend->data()->lastName;
					$datetime = new DateTime($notification->timestamp);
					$timestamp = $datetime->format('l, F j, Y \a\t g:ia');
					
					switch($notification->type) {
						case 'friend_requested':
							$this->add(array(
								'username' => $friend->data()->username,
								'profilePicture' => $friend->data()->profilePicture,
								'message' => "You sent <a href=\"profile.php?user={$friendUsername}\"><span class=\"name\">{$friendName}</span></a> a friend request.",
								'timestamp' => $timestamp
							));
						break;
						case 'delete_friend_request':
							$this->add(array(
								'username' => $friend->data()->username,
								'profilePicture' => $friend->data()->profilePicture,
								'message' => "You deleted <a href=\"profile.php?user={$friendUsername}\"><span class=\"name\">{$friendName}</span></a>'s friend request.",
								'timestamp' => $timestamp
							));
						break;
						case 'friend_request':
							$this->add(array(
								'username' => $friend->data()->username,
								'profilePicture' => $friend->data()->profilePicture,
								'message' => "<a href=\"profile.php?user={$friendUsername}\"><span class=\"name\">{$friendName}</span></a> sent you a friend request.",
								'timestamp' => $timestamp
							));
						break;
						case 'friend_added':
							$this->add(array(
								'username' => $friend->data()->username,
								'profilePicture' => $friend->data()->profilePicture,
								'message' => "You and <a href=\"profile.php?user={$friendUsername}\"><span class=\"name\">{$friendName}</span></a> are now friends.",
								'timestamp' => $timestamp
							));
						break;
						case 'posted':
							$this->add(array(
								'username' => $friend->data()->username,
								'profilePicture' => $friend->data()->profilePicture,
								'message' => "You posted on <a href=\"profile.php?user={$friendUsername}\"><span class=\"name\">{$friendName}</span></a>'s profile.",
								'timestamp' => $timestamp
							));
						break;
						case 'new_post':
							$this->add(array(
								'username' => $friend->data()->username,
								'profilePicture' => $friend->data()->profilePicture,
								'message' => "<a href=\"profile.php?user={$friendUsername}\"><span class=\"name\">{$friendName}</span></a> posted on your profile.",
								'timestamp' => $timestamp
							));
						break;
					}
				}

				return true;
			}

			return false;
		}

		public function set($fields = array()) {
			if(!$this->_db->insert('notifications', $fields)) {
				throw new Exception('There was a problem setting notification');
			}
		}

		public function delete($id) {
			if($this->exists($id)) {
				if(!$this->_db->delete('notifications', "id = '{$id}'")) {
					throw new Exception('There was a problem deleting notification');
				}

				return true;
			}

			return false;
		}

		public function add($notification = array()) {
			$this->_notifications[] = $notification;
		}

		public function notifications() {
			return $this->_notifications;
		}

		public function exists($id) {
			$notification = $this->_db->get('notifications', "id = {$id}");

			if($notification->count()) {
				return true;
			}

			return false;
		}

		public function count() {
			return $this->_count;
		}
	}