<?php
	class DB {
		private static $_instance = null;
		private 	$_pdo,
					$_query,
					$_error = false,
					$_results,
					$_count = 0;

		private function __construct() {
			try {
				$this->_pdo = new PDO('mysql:host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
			}
			catch(PDOException $e) {
				die($e->getMessage());
			}
		}
		
		public static function getInstance() {
			if(!isset(self::$_instance)) {
				self::$_instance = new DB();
			}
			
			return self::$_instance;
		}
		
		public function get($table, $where, $column = '*') {
			return $this->action('SELECT', $column, $table, $where);
		}

		public function delete($table, $where, $column = '') {
			return $this->action('DELETE', $column, $table, $where);
		}
		
		public function insert($table, $fields = array()) {
			$keys = array_keys($fields);
			$values = null;
			$x = 1;
			
			// Prepares a string with appropriate number of '?' to be bound to their values
			foreach($fields as $field) {
				$values .= '?';
				
				if($x < count($fields)) {
					$values .= ', ';
				}
				
				$x++;
			}
			
			$sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})";

			if(!$this->query($sql, $fields)->error()) {
				return true;
			}
			
			return false;
		}
		
		public function update($table, $id, $fields) {
			$set = '';
			$x = 1;
			
			// Prepares a string with appropriate number of '?' to be bound to their values
			foreach($fields as $name => $value) {
				$set .= "{$name} = ?";
				
				if($x < count($fields)) {
					$set .= ', ';
				}
				
				$x++;
			}
			
			$sql = "UPDATE {$table} SET {$set} WHERE id = {$id}";
			
			if(!$this->query($sql, $fields)->error()) {
				return true;
			}
			
			return false;
		}

		public function query($sql, $params = array()) {
			$this->_error = false;
			
			// If no errors preparing sql statement
			if($this->_query = $this->_pdo->prepare($sql)) {
				$x = 1;

				// Binds values to their assigned '?'
				if(count($params)) {
					foreach($params as $param) {
						$this->_query->bindValue($x, $param);
						$x++;
					}
				}
				
				// If no errors executing sql statement
				if($this->_query->execute()) {
					// Sets results and number of rows
					$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
					$this->_count = $this->_query->rowCount();
				}
				else {
					$this->_error = true;
				}
			}
			
			return $this;
		}

		public function action($action, $column, $table, $where) {
			$sql = "{$action} {$column} FROM {$table} WHERE {$where}";

			if(!$this->query($sql)->error()) {
				return $this;
			}

			return false;
		}
		
		public function results() {
			return $this->_results;
		}
		
		public function first() {
			return $this->results()[0];
		}
		
		public function error() {
			return $this->_error;
		}
		
		public function count() {
			return $this->_count;
		}
	}