<?php

class Project extends AppModel {

	// RELATIONSHIPS
	var $belongsTo = array('User');
	var $hasMany = array('State','Twilio','ProjectLog');
	var $hasAndBelongsToMany = array('Phone');

	// VALIDATION

	var $validate = array('name' => array('rule' => 'notEmpty',
										  'message' => 'Name must be at least 1 character',
										  'allowEmpty' => false,
										  'required' => true));

	// FUNCTIONS


	function regExReplaceBrackets($string){
		// Get all the "replacement" values inside a "{something}"
		// - does not include the actual brackets
		preg_match_all('/{([^}]+)}/i',$string,$tmp);// $repl_temp= "matches" array
		$replacements = array();
		if(!empty($tmp) && isset($tmp[1])){
			$replacements = $tmp[1];
		}
		$replacements = array_unique($replacements);
		return $replacements;
	}


	function replace_url_brackets($words,$url){
		// $words is the array of words

		// Run a few times
		for($i=0;$i<100;$i++){
			// Check for brackets
			if(stripos($url,'{'.$i.'}') !== false){
				// Exists
				// - replace
				$w = isset($words[$i]) ? $words[$i] :'';
				$url = str_ireplace('{'.$i.'}',$w,$url);
			}
		}

		return $url;
	}


	function get_json_value($str,$obj){
		// Given a JSON string and a JSON object
		// - return the correct fucking thing

		// Is this the root level of the string?
		$tmp = explode('.',$str);
		if(count($tmp) == 1){
			// Root level now, return it
			if(isset($obj->{$str})){
				return $obj->{$str};
			} else {
				return '';
			}
		}
		if(count($tmp) > 1){
			$first = array_shift($tmp);
			$the_rest = implode('.',$tmp);

			// Go Deeper

			// $obj->value must exist
			if(is_numeric($first)){
				if(isset($obj[$first])){	
					return $this->get_json_value($the_rest,$obj[$first]);
				} else {
					return '';
				}

			} else {
				if(isset($obj->{$first})){	
					return $this->get_json_value($the_rest,$obj->{$first});
				} else {
					return '';
				}
			}
			
		} else {
			return 'error';
		}

	}


	function set_json($path,$value,$obj){
		// Set an object when given a path
		// - only sets an individual object

		// Is this the root level of the string?
		$tmp = explode('.',$path);
		if(count($tmp) == 0){
			// Fucked up, probably a trailing "." in the attribute
			return $obj;
		}
		if(count($tmp) == 1){
			// Root level now, set the value return it
			//pr('tmp');
			if(!isset($obj->{$tmp[0]})){
				$obj->{$tmp[0]} = new Object();
			}
			$obj->{$tmp[0]} = new Object();
			$obj->{$tmp[0]} = $value;
			
			return $obj;
		}
		if(count($tmp) > 1){
			$first = array_shift($tmp); // awesome
			$the_rest = implode('.',$tmp);

			// Go Deeper

			// Nothing numeric for now
			if(is_numeric($first)){
				// Pretend it is an object?
				// - kinda a fucked up way of doing this
				/*
				if(is_array($obj)){
					if(isset($obj[$first])){	
						$tmp = $this->set_json($the_rest,$value,$obj[$first]);
						return $tmp;
					} else {
						$obj = array();
						$obj[$first] = new Object();
						return $this->set_json($the_rest,$value,$obj[$first]);
					}
					//return $this->set_json($the_rest,$value,$obj[$first]);
				} else {
					$obj = array();
					$obj[$first] = new Object();
					return $this->set_json($the_rest,$value,$obj[$first]);
				}*/

				// Duplicate of the information just below, holding here until we replace it with array-specific code
				if(isset($obj->{$first})){
					// Isset, and more stuff is required
					if(!is_object($obj->{$first})){
						$obj->{$first} = new Object(); // awesome = {}
					}
					// Add to Object
					$tmp = $this->set_json($the_rest,$value,$obj->{$first});
					$obj->{$first} = $tmp;
					return $obj;
				} else {
					//pr(3);
					$obj->{$first} = new Object();
					$tmp = $this->set_json($the_rest,$value,$obj->{$first});
					$obj->{$first} = $tmp;
					return $obj;
				}

			} else {
				if(isset($obj->{$first})){
					// Isset, and more stuff is required
					if(!is_object($obj->{$first})){
						$obj->{$first} = new Object(); // awesome = {}
					}
					// Add to Object
					$tmp = $this->set_json($the_rest,$value,$obj->{$first});
					$obj->{$first} = $tmp;
					return $obj;
				} else {
					//pr(3);
					$obj->{$first} = new Object();
					$tmp = $this->set_json($the_rest,$value,$obj->{$first});
					$obj->{$first} = $tmp;
					return $obj;
				}
			}
			
		} else {
			pr('error');
			return 'error';
		}

	}


	function send_sms($options = array()){
		
		$defaults = array('message' => 'message string',
						  'To' => array(), // default is incoming phone number
						  'From' => '',
						  'action_id' => 0);

		$options = array_merge($defaults,$options);
		
		// Respond with whatever was set

		// Parse the send_sms Template
		$send_sms = $options['message'];

		// Parse out the {r.value} stuff

		// Do all the replacements
		$send_sms = $this->replace_brackets($send_sms);

		// Send to who?
		// - sending to multiple people?
		
		// Send SMS
		// - To and From are switched for outgoing messages (look at the function to remember)
		$this->Twilio->send_msg($options['From'],$options['To'],$send_sms,Configure::read('Project.id'),$options['action_id']);
		

	}


	function set_state($options = array()){
		// Move the specified User to a State
		// - currently, only the sending in User can change State
				
		$defaults = array('new_state' => 'default',
						  'old_state' => 'default',
						  'action_id' => 0,
						  'pp_id' => 0);

		$options = array_merge($defaults,$options);
		
		$goto_state = Inflector::slug(trim($options['new_state']));
		
		$goto_state = $this->replace_brackets($goto_state);

		// Should validate the State?...

		$new_state = trim($goto_state);

		// Log State change
		$logData = array('project_id' => Configure::read('Project.id'),
						 'related_id' => $options['action_id'],
						 'request_hash' => Configure::read('request_hash'),
						 'type' => 'action_state_change',
						 'data' => json_encode(array('old_state' => $options['old_state'],
						 							 'new_state' => $new_state))
						 );
		$this->ProjectLog =& ClassRegistry::init('ProjectLog');
		$this->ProjectLog->create();
		$this->ProjectLog->save($logData);

		// Save it
		$this->PhonesProject =& ClassRegistry::init('PhonesProject');
		$this->PhonesProject->create();
		$pp_data = array('id' => $options['pp_id'],
						 'state' => $new_state);
		if(!$this->PhonesProject->save($pp_data)){
			pr('failed saving PhonesProject state');
		}

		pr('Updated User State');


	}


	function obj_to_multiple_paths($obj){
		// Turn an object into multiple paths leading to a value
		// - returns array of strings



	}


	function arrays_to_obj($obj){
		// Turn any array values into Obj values

		if(is_array($obj)){
			// Get values, iterate through each
			$tmp = new Object();
			foreach($obj as $key => $val){
				$tmp->{$key} = $this->arrays_to_obj($val);
			}
			return $tmp;
		}

		// Iterate through each Object element as well
		if(is_object($obj)){
			foreach($obj as $key => $val){
				$obj->{$key} = $this->arrays_to_obj($val);
			}
			return $obj;
		}

		return $obj;

	}


	function set_attributes($options = array()){
		// Set Application and User-level attributes

		$defaults = array('field' => '',
						  'use_obj' => false,
						  'obj' => '', // would be an Object usually
						  'user_meta' => array(),
						  'app_meta' => array(),
						  'action_id' => 0,
						  'pp_id' => 0);
		
		$options = array_merge($defaults,$options);

		$original_user_meta = json_encode($options['user_meta']); // for comparison later in function
		$original_app_meta = json_encode($options['app_meta']);

		if($options['use_obj']){

			// Coming from the fucking thing
			// - convert to the appropriate formatting (key=value) like in an input field
			// 		- dope

			$options['obj'] = $this->arrays_to_obj($options['obj']);
			
			foreach($options['obj'] as $key => $right){
				// No parsing of variables

				$left = Sanitize::paranoid($key,array('.','_'));
				if(empty($left)){
					// probably never get here?
					continue;
				}


				// Check for a match
				$tmp_left = explode('.',$left);
				switch($tmp_left[0]){

					case 'u':
						if(count($tmp_left) <= 2 || $tmp_left[1] != 'meta'){
							// Missing the 'meta' value
							// - log error
							pr('expecting u.meta.something');
							break;
						}

						array_shift($tmp_left);	// remove "u"
						array_shift($tmp_left); // remove "meta"
						$the_rest = implode('.',$tmp_left);
						$replace_with = '';

						$options['user_meta'] = $this->set_json($the_rest,$right,$options['user_meta']);
						

						break;
					
					case 'a':
						// Application variables
						if(count($tmp_left) <= 1){
							// Missing the 'meta' value
							// - log error
							pr('expecting a.something');
							break;
						}

						array_shift($tmp_left);	// remove "a" (but nothing else needs to be removed)
						$the_rest = implode('.',$tmp_left);
						$replace_with = '';
						
						$options['app_meta'] = $this->set_json($the_rest,$right,$options['app_meta']);

						//pr('Replaced app_meta');
						//pr($options['app_meta']);

						break;
					
					default:
						pr('missing thing');
						exit;

				}



			}

		} else {
			
			// Get each of the values to set
			// - later, you should be able to insert {} values on EITHER SIDE of the =
			$tmp_conditions = explode(',',$options['field']);
			$all = true;
			foreach($tmp_conditions as $key => $tmp_cond){
				// Parse attribute as best as possible

				// Do replacements
				$tmp_cond = $this->replace_brackets($tmp_cond);

				$tmp = explode('=',trim($tmp_cond));
				if(count($tmp) != 2){
					// Failed badly, should have caught this beforehand
					break;
				}

				// Left side is attribute, Right side is value (or | "pipe" separated values)
				$left = Sanitize::paranoid($tmp[0],array('.','_'));
				$right = Sanitize::paranoid($tmp[1],array('.','|',',',' ','{','}','_','-'));
				if(empty($left)){
					break;
				}


				// Now the $left and $right side has no {} brackets

				// Go through and set the actual values
				
				// Check for a match
				$tmp_left = explode('.',$left);
				switch($tmp_left[0]){

					case 'u':
						if(count($tmp_left) <= 2 || $tmp_left[1] != 'meta'){
							// Missing the 'meta' value
							// - log error
							pr('expecting u.meta.something');
							break;
						}

						array_shift($tmp_left);	// remove "u"
						array_shift($tmp_left); // remove "meta"
						$the_rest = implode('.',$tmp_left);
						$replace_with = '';

						$options['user_meta'] = $this->set_json($the_rest,$right,$options['user_meta']);
						
						//pr('Replaced user_meta');
					
						break;
					
					case 'a':
						// Application variables
						if(count($tmp_left) <= 1){
							// Missing the 'meta' value
							// - log error
							pr('expecting a.something');
							break;
						}

						array_shift($tmp_left);	// remove "a" (but nothing else needs to be removed)
						$the_rest = implode('.',$tmp_left);
						$replace_with = '';
						
						$options['app_meta'] = $this->set_json($the_rest,$right,$options['app_meta']);

						//pr('Replaced app_meta');
						//pr($options['app_meta']);

						break;
					
					default:
						pr('missing thing');
						exit;

				}
			
			}
		} // End use_obj

		// Log Attribute setting
		$logData = array('project_id' => Configure::read('Project.id'),
						 'related_id' => $options['action_id'],
						 'request_hash' => Configure::read('request_hash'),
						 'type' => 'action_attribute',
						 'data' => json_encode(array('tmp' => 'in progress. attr(s) was set though'))
						 );
		$this->ProjectLog =& ClassRegistry::init('ProjectLog');
		$this->ProjectLog->create();
		$this->ProjectLog->save($logData);


		// Save the updated User meta object!
		// - later, save multiple meta objects, including "g"="global"
		$new_user_meta = json_encode($options['user_meta']);
		if($new_user_meta != $original_user_meta){

			pr('Updated User Attributes');
			pr($options['user_meta']);

			// Save it
			$this->PhonesProject =& ClassRegistry::init('PhonesProject');
			$this->PhonesProject->create();
			$pp_data = array('id' => $options['pp_id'],
							 'meta' => $new_user_meta);
			if(!$this->PhonesProject->save($pp_data)){
				pr('failed saving PhonesProject meta');
			}
		}


		// Save the updated Application meta object!
		$new_app_meta = json_encode($options['app_meta']);
		if($new_app_meta != $original_app_meta){

			pr('Updated Application Attributes');
			pr($options['app_meta']);
			
			// Save it
			$this->create();
			$project_data = array('id' => Configure::read('Project.id'),
							 	  'meta' => $new_app_meta);
			if(!$this->save($project_data)){
				pr('Failed saving Project meta');
			}
		}

		// Return new values
		// - probably be used in a Configure::write('app_meta'), or 'user_meta'
		return array('user_meta' => $options['user_meta'],
					 'app_meta' => $options['app_meta']);

	}


	function replace_brackets($field){

		// Replace words
		$tmp_words = explode(' ',Configure::read('Body'));
		$field = $this->replace_url_brackets($tmp_words,$field);

		// Get bracket replacements
		$replacements = $this->regExReplaceBrackets($field);

		foreach($replacements as $repl){
			// See if the value exists

			$tmp = explode('.',$repl);
			if(count($tmp) <= 1){
				$field = str_ireplace('{'.$repl.'}','',$field);
				continue;
			}

			// Get the replacement value
			$first_val = array_shift($tmp);
			switch($first_val){
				
				case 'w':
					// Words submitted previously
					/*
					$val = $fuck_it;
					$replace_with = '';
					

					$field = str_ireplace('{'.$repl.'}',$replace_with,$field);
					*/
					break;
						
				case 'u':
					// User attribute
					$tmp2 = array_shift($tmp); // get rid of "meta."
					$the_rest = implode('.',$tmp);
					$replace_with = '';
					$replace_with = $this->get_json_value($the_rest,Configure::read('user_meta'));

					$field = str_ireplace('{'.$repl.'}',$replace_with,$field);
					break;
				
				case 'a':
					// Application attributes
					$the_rest = implode('.',$tmp);
					$replace_with = '';
					$replace_with = $this->get_json_value($the_rest,Configure::read('app_meta'));

					$field = str_ireplace('{'.$repl.'}',$replace_with,$field);
					break;

				case 'r':
					// Response JSON object

					// Does the value exist?
					// - need to do this recursively
					$the_rest = implode('.',$tmp);
					$replace_with = '';
					$action_json = Configure::read('action_json');
					if(!$action_json){
						$action_json = array();
					}
					foreach($action_json as $action_json_obj){
						$replace_with = $this->get_json_value($the_rest,$action_json_obj);
					}

					$field = str_ireplace('{'.$repl.'}',$replace_with,$field);
					
					break;

				default:
					$field = str_ireplace('{'.$repl.'}','',$field);
					break;

			}

		}

		return $field;
	}



}
