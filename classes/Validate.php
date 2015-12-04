<?php
	class Validate {
		private	$_passed = false,
				$_errors = array(),
				$_db = null;
		
		public function __construct() {
			$this->_db = DB::getInstance();
		}
		
		public function check($source, $items = array()) {
			foreach($items as $item => $rules) {
				foreach($rules as $rule => $rule_value) {
					$value = trim($source[$item]);
					$item = escape($item);
					
					if($rule === 'required' && (empty($value) || $value === 0)) {
						$this->addError("{$item} is required");
					}
					else if(!empty($value)){
						switch($rule) {
							case 'min':
								if(strlen($value) < $rule_value) {
									$this->addError("{$item} must be a minimum of {$rule_value} characters");
								}
							break;
							case 'max':
								if(strlen($value) > $rule_value) {
									$this->addError("{$item} must be a maximum of {$rule_value} characters");
								}
							break;
							case 'matches':
								if($value != $source[$rule_value]) {
									$this->addError("{$rule_value} must match {$item}");
								}
							break;
							case 'unique':
								if($item === 'signup-username') {
									$item = 'username';
								}
								
								$check = $this->_db->get($rule_value, "{$item} = '{$value}'");
								
								if($check->count()) {
									$this->addError("{$item} already exists");
								}
							break;
							case 'exists':
								$check = $this->_db->get($rule_value, "{$item} = '{$value}'");
								
								if(!$check->count()) {
									$this->addError("{$item} does not exist");
								}
							break;
							case 'type':
								if($rule_value === 'email') {
									if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
										$this->addError("{$item} is an invalid email");
									}
								}
							break;
						}
					}
				}
			}
			
			if(empty($this->_errors)) {
				$this->_passed = true;
			}
			
			return $this;
		}

		public function checkFile($file, $rules = array()) {
			if(empty($file['name'])) {
				$this->addError('No file selected.');
			}
			else {
				foreach($rules as $rule => $value) {
					switch($rule) {
						case 'max':
							if($file['size'] > $value) {
								$this->addError("File size must not exceed {$value} bytes.");
							}
						break;
						case 'type':
							$allowed = $value;
							$fileName = $file['name'];
							$fileExtn = strtolower(end(explode('.', $fileName)));

							if(!in_array($fileExtn, explode(',', $allowed))) {
								$this->addError("Invalid file type (allowed: {$allowed}).");
							}
						break;
					}
				}
			}

			if(empty($this->_errors)) {
				$this->_passed = true;
			}

			return $this;		
		}
		
		public function passed() {
			return $this->_passed;
		}
		
		private function addError($error) {
			$this->_errors[] = $error;
		}
		
		public function errors() {
			return $this->_errors;
		}
	}