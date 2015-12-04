<?php
	class Search {
		private $_db,
				$_results = array();

		public function __construct() {
			$this->_db = DB::getInstance();
		}

		public function search($input = array()) {
			$where = '';
			$x = 1;

			foreach($input as $field => $value) {
				if(!empty($value)) {
					$fields[$field] = $value;
				}
			}

			foreach($fields as $field => $value) {
				if(!empty($value)) {
					$where .= $field . ' = ' . '\'' . $value . '\'';

					if((count($fields) !== 1) && ($x < count($fields))) {
						$where .= ' AND ';
					}
				}

				$x++;
			}

			$search = $this->_db->get('users', $where);

			if($search->count()) {
				$results = $search->results();
				
				foreach($results as $result) {
					$friend = new User($result->id);

					$this->_results[] = $friend->data();
				}

				return true;
			}
			
			return false;
		}

		public function results() {
			return $this->_results;
		}
	}