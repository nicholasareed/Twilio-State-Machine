<?php

class TextsController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array());

	var $components = array();

	var $doVerifyTwilio = true;


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function test(){
		// Test the responses with basic SMS messages

		if($this->RequestHandler->isGet()){
			return;
		}

		$this->doVerifyTwilio = false;
		Configure::write('demo_mode',true);

		$_REQUEST['To'] = trim($_POST['To']);
		$_REQUEST['From'] = trim($_POST['From']);
		$_REQUEST['Body'] = trim($_POST['Body']);

		$this->incoming();

		exit;

	}


	function quick_test($To,$From,$Body){

		$this->doVerifyTwilio = false;
		Configure::write('demo_mode',true);

		$_REQUEST['To'] = '+'.trim($To);
		$_REQUEST['From'] = '+'.trim($From);
		$_REQUEST['Body'] = trim($Body);

		$this->incoming();

		exit;
	}



	function verify_twilio($key = null){
		// Use your Twilio AuthToken here.  Case matters.
		$MY_KEY = $key;
 
		$expected_signature = $_SERVER["HTTP_X_TWILIO_SIGNATURE"];
		$string_to_sign = $_SERVER['SCRIPT_URI'];
		if(strlen($_SERVER['QUERY_STRING']))
			$string_to_sign .= "?{$_SERVER['QUERY_STRING']}";

		if(isset($_POST)) {
			$data = $_POST;
			ksort($data);
			foreach($data AS $key=>$value)
				$string_to_sign .= "$key$value";
		}
 
		$calculated_signature = base64_encode(hash_hmac("sha1", $string_to_sign, $MY_KEY, true));
 
		if($calculated_signature == $expected_signature)
			return true;
		else
			return false;
	}


	function renderError($error = 0){
		$this->layout = 'empty';
		$this->set(compact('error'));
		echo $this->render('/pages/missing');
		exit;
	}


	function incoming(){
		// This handles all incoming SMS from Twilio
		// - incoming.domain.com will always route here
		
		// People will probably type this into their browser, so we should respond with a fun message
		// - instead of fun, how about informative? Helpful links, etc.

		// Accept either GET or POST requests from Twilio

		// Verification cannot happen until after we know we sent us the message
		// - but we can do basic X-Twilio header checks

		// X-Twilio Header Checks
		if($this->doVerifyTwilio && !isset($_SERVER["HTTP_X_TWILIO_SIGNATURE"])){
			$this->renderError(1);
		}

		// Validate the $To and $From numbers
		// - we will use this to find a User and an App
		$To = $_REQUEST['To'];
		$From = $_REQUEST['From'];
		$Body = $_REQUEST['Body'];

		//$pattern = '/^(?:\+?1)?[-. ]?\\(?[2-9][0-8][0-9]\\)?[-. ]?[2-9][0-9]{2}[-. ]?[0-9]{4}$/';
		$pattern = '/^\+\d{11}$/';

		if(!preg_match($pattern,$To) || !preg_match($pattern,$From)){
			// Invalid phone numbers
			pr($To);
			pr($From);
			pr($Body);
			$this->renderError(2);
		}

		App::import('Sanitize');

		// Get Application for incoming PTN
		$this->Twilio =& ClassRegistry::init('Twilio');
		$this->Twilio->contain(array('User.Profile','Project'));
		$conditions = array('Twilio.ptn' => $To,
							'Twilio.live' => 1);
		$twilio = $this->Twilio->find('first',compact('conditions'));

		// Twilio Number exists?
		if(empty($twilio)){
			$this->renderError(3);
		}

		// User and Profile exist?
		if(!isset($twilio['User']) || !isset($twilio['User']['Profile']) || !isset($twilio['User']['Profile']['id']) || empty($twilio['User']['Profile']['id'])){
			$this->renderError(4);
		}

		// Verify that this is from Twilio
		// - disable verification if we are testing
		if($this->doVerifyTwilio && !$this->verify_twilio($rwilio['User']['Profile']['twilio_secret'])){
			echo "Twilio verification failed";
			exit;
			$this->renderError(5);
		}

		// Project exists?
		if(empty($twilio['Project']) || empty($twilio['Project']['id'])){
			// Log this for the User
			echo "Project not available";
			exit;
			// Also respond with an error message
			// - the Request came from Twilio, so it was a legitimate SMS
		}

		// Get the full Project
		$project_id = $twilio['Project']['id'];

		$this->Project =& ClassRegistry::init('Project');
		$this->Project->contain(array('User.Profile','State.Step' => array('Condition','Action')));
		$conditions = array('Project.id' => $project_id,
							'Project.live' => 1);
		$project = $this->Project->find('first',compact('conditions'));

		if(empty($project)){
			echo "Unable to find Project";
			exit;
		}

		// Set Twilio variables
		Configure::write('twilio_id',$project['User']['Profile']['twilio_id']);
		Configure::write('twilio_secret',$project['User']['Profile']['twilio_secret']);
		pr($project['User']['Profile']);

		// Get the Phone (and PhonesProject) if they exist
		// - if they exist
		$this->Phone =& ClassRegistry::init('Phone');
		$this->Phone->contain(array('Project' => array('conditions' => array('Project.id' => $project['Project']['id']))));
		$conditions = array('Phone.ptn' => $From);
		$phone = $this->Phone->find('first',compact('conditions'));

		// Do we know this person?
		if(empty($phone)){
			// No, create them
			$data = array('ptn' => $From);
			$this->Phone->create();
			if(!$this->Phone->save($data)){
				echo "Failed saving new Phone";
				exit;
			}
			// Get the Phone, again
			$this->Phone->contain(array('Project' => array('conditions' => array('Project.id' => $project['Project']['id']))));
			$phone = $this->Phone->find('first',compact('conditions'));
			if(empty($phone)){
				echo "Failed finding new Phone, after saving";
				exit;
			}
		}

		// Have $phone (guaranteed)

		// In Project?
		$this->PhonesProject =& ClassRegistry::init('PhonesProject');
		if(empty($phone['Project'])){
			$data = array('phone_id' => $phone['Phone']['id'],
						  'project_id' => $project['Project']['id'],
						  'state' => 'default');
			$this->PhonesProject->create();
			if(!$this->PhonesProject->save($data)){
				echo "Failed saving PhonesProject";
				exit;
			}
			$pp_id = $this->PhonesProject->id;
		} else {
			$pp_id = $phone['Project'][0]['id'];
		}

		// Get Full Phone Project
		$conditions = array('PhonesProject.id' => $pp_id);
		$pp = $this->PhonesProject->find('first',compact('conditions'));

		if(empty($pp)){
			echo "Failed finding PhonesProject";
			exit;
		}

		// Have $pp (PhonesProject), guaranteed

		// Process Structure based on my current State
		$states = Set::extract($project['State'],'{n}.key');

		if(!in_array($pp['PhonesProject']['state'],$states)){
			// Use default State
			// - does not change the Database value though (by design)
			$pp['PhonesProject']['state'] = 'default';
		}

		// Get State array to use
		$state_key = array_search($pp['PhonesProject']['state'],$states);

		if($state_key === false){
			echo "Failed finding state_key"; // Probably because the "default" state was removed
			exit;
		}

		$state = $project['State'][$state_key];


		// Test against a PhonesProject.attributes (or .meta)
		$meta = $pp['PhonesProject']['meta'];
		$original_meta = $meta;
		try {
			$meta = json_decode($meta);
		} catch (Exception $e){
			$meta = 'fail';
		}
		if($meta == 'fail'){
			$meta = new Object();
		}

		// Move through Steps
		foreach($state['Step'] as $step){

			if(empty($step['Condition'])){
				// No conditions set
				// - go to next Step
				continue;
			}

			// See if Condition(s) match
			// - all conditions must match for the Action to be fired
			$all_matched = true;
			foreach($step['Condition'] as $condition){

				// Check the $type
				$matched = false;
				switch($condition['type']){

					case 'starts_with':
						// Case-insensitive
						// - not using RegEx
						$tmp = stripos($Body,$condition['input1']);
						if($tmp === 0){
							$matched = true;
						}
						break;

					case 'word_count':
						// Case-insensitive
						// - not using RegEx
						$values = explode('|',$condition['input1']);
						$tmp2 = explode(' ',trim($Body));
						$tmp2_count = count($tmp2);
						foreach($values as $value){
							$value = intval($value);
							if($value == $tmp2_count){
								$matched = true;
							}
						}
						break;

					case 'attribute':
						
						// Get what we're testing against
						$tmp_conditions = explode(',',$condition['input1']);
						$all = true;
						foreach($tmp_conditions as $key => $tmp_cond){
							// Parse attribute as best as possible
							$tmp = explode('=',trim($tmp_cond));
							if(count($tmp) != 2){
								// Failed badly, should have caught this beforehand
								break;
							}

							// Left side is attribute, Right side is value (or | "pipe" separated values)
							$left = Sanitize::paranoid($tmp[0],array('.'));
							$right = Sanitize::paranoid($tmp[1],array('.','|',',',' '));
							if(empty($left)){
								break;
							}

							// Get the Attribute

							// Check for a match
							$tmp_left = explode('.',$left);
							switch($tmp_left[0]){

								case 'u':
									array_shift($tmp_left);
									$the_rest = implode('.',$tmp_left);
									$replace_with = '';
									$replace_with = $this->json_to_string($the_rest,$meta);
									// Iterate through each "or" statement on the $right
									$right_conditions = explode('|',$right);
									if(empty($right_conditions)){
										$right_conditions = array('');
									}
									$tmp_any = false;
									foreach($right_conditions as $right_condition){
										if($right_condition == $replace_with){
											$tmp_any = true;
										}
									}
									if(!$tmp_any){
										$all = false;
										break 2;
									}
								
									break;
								
								default:
									
									pr('missing thing');
									exit;
							}

						}

						if(!$all){
							break;
						}

						$matched = true;


						break;

					case 'default':
						$matched = true;
						break;
					
					default:
						// Didn't meet any of the known types
						break;

				}

				if(!$matched){
					// Missed matching on one of the $conditions
					// - break out of foreach
					// - goes to next Step
					$all_matched = false;
				}
			}


			// All conditions matched?
			if($all_matched){
				// Great!
				// - prevent other steps from running

				// Go through Actions
				$action_json = array();
				foreach($step['Action'] as $action){

					switch($action['type']){

						case 'response':
							// Respond with whatever was set

							// Parse the response Template
							$response = $action['input1'];

							// Parse out the {r.value} stuff

							// Do Word replacements
							$tmp_words = explode(' ',$Body);
							$response = $this->replace_url_brackets($tmp_words,$response);

							// Iterate through each necessary replacement
							// - this is a todo
							preg_match_all('/{([^}]+)}/i',$response,$repl_temp);
							$replacements = array();
							if(!empty($repl_temp) && isset($repl_temp[1])){
								$replacements = $repl_temp[1];
							}
							$replacements = array_unique($replacements);

							foreach($replacements as $repl){
								// See if the value exists

								$tmp = explode('.',$repl);
								if(count($tmp) <= 1){
									$response = str_ireplace('{'.$repl.'}','',$response);
									continue;
								}

								// Get the replacement value
								$first_val = array_shift($tmp);
								switch($first_val){
									
									case 'r':
										// Response JSON object

										// Does the value exist?
										// - need to do this recursively
										$the_rest = implode('.',$tmp);
										$replace_with = '';
										foreach($action_json as $action_json_obj){
											$replace_with = $this->json_to_string($the_rest,$action_json_obj);
										}

										$response = str_ireplace('{'.$repl.'}',$replace_with,$response);									

										break;
									
									case 'u':
										// User attribute
										$the_rest = implode('.',$tmp);
										$replace_with = '';
										$replace_with = $this->json_to_string($the_rest,$meta);
										// Iterate through each "or" statement on the $right
										$right_conditions = explode('|',$right);
										if(empty($right_conditions)){
											$right_conditions = array('');
										}
										$tmp_any = false;
										foreach($right_conditions as $right_condition){
											if($right_condition == $replace_with){
												$tmp_any = true;
											}
										}
										if(!$tmp_any){
											$all = false;
											break 2;
										}
										break;

									default:
										$response = str_ireplace('{'.$repl.'}','',$response);
										break;

								}

							}

							// Send SMS
							// - To and From are switched for outgoing messages (look at the function to remember)
							$this->Twilio->send_msg($To,$From,$response,$project['Project']['id']);

							// Made a request, great!
							
							break;

						case 'webhook':
							// Parse url
							$url = $action['input1'];
							$words = explode(' ',$Body);
							$url = $this->replace_url_brackets($words,$url);
							// Make Request
							// - always a GET?
							$this->Curl =& ClassRegistry::init('Curl');
							$this->Curl->url = $url;
							//$this->Curl->post = true;
							//$this->Curl->postFieldsArray = compact('From','To','Body');
							$result = $this->Curl->execute();

							// Try parsing JSON
							try {
								$json = json_decode($result);
							} catch (Exception $e){
								// Failed, just continue to the next Action
								// - log the failure for the User
								continue;
							}

							// Save the JSON data for use later
							// - in response templates :)
							//$action_json[] = $json;
							$action_json[] = $json;

							break;

						case 'attribute':
							
							// Parse the Attribute we are setting
							// - only supports the user for now

							// Respond with whatever was set

							// Parse the response Template
							$response = $action['input1'];

							// Get each of the values to set
							// - later, you should be able to insert {} values on EITHER SIDE of the =
							$tmp_conditions = explode(',',$action['input1']);
							$all = true;
							foreach($tmp_conditions as $key => $tmp_cond){
								// Parse attribute as best as possible
								$tmp = explode('=',trim($tmp_cond));
								if(count($tmp) != 2){
									// Failed badly, should have caught this beforehand
									break;
								}

								// Left side is attribute, Right side is value (or | "pipe" separated values)
								$left = Sanitize::paranoid($tmp[0],array('.','{','}'));
								$right = Sanitize::paranoid($tmp[1],array('.','|',',',' ','{','}'));
								if(empty($left)){
									break;
								}

								// Do Word replacements
								$tmp_words = explode(' ',$Body);
								$left = $this->replace_url_brackets($tmp_words,$left);
								$right = $this->replace_url_brackets($tmp_words,$right);

								// Get the Attribute to set (it is currently a string, we want an Object)
								// - parse {r.value} if necessary
								preg_match_all('/{([^}]+)}/i',$left,$repl_temp);// $repl_temp= "matches" array
								$replacements = array();
								if(!empty($repl_temp) && isset($repl_temp[1])){
									$replacements = $repl_temp[1];
								}
								$replacements = array_unique($replacements);

								// Right now we have an array like: [0] = 'u.id'

								// Iterate over each thing to replace
								foreach($replacements as $repl){

									$tmp = explode('.',$repl);
									if(count($tmp) <= 1){
										// Missing the 'u' in 'u.'
										$left = str_ireplace('{'.$repl.'}','',$left);
										continue;
									}

									// Get the replacement value
									$first_val = array_shift($tmp);
									switch($first_val){
										
										case 'u':
											// User attribute
											$the_rest = implode('.',$tmp);
											$replace_with = '';
											$replace_with = $this->json_to_string($the_rest,$meta);

											$left = str_ireplace('{'.$repl.'}',$replace_with,$left);
											break;

										case 'r':
											// Response JSON object

											// Does the value exist?
											// - need to do this recursively
											$the_rest = implode('.',$tmp);
											$replace_with = '';
											foreach($action_json as $action_json_obj){
												$replace_with = $this->json_to_string($the_rest,$action_json_obj);
											}

											$left = str_ireplace('{'.$repl.'}',$replace_with,$left);
											
											break;	
											

										default:
											// Trying to set a non-existant value
											// - is not for the User or Global
											pr('error=212');
											$left = str_ireplace('{'.$repl.'}','',$left);
											continue;
											break;

									}

								}


								// Get the Attribute to set (it is currently a string, we want an Object)
								// - parse {r.value} if necessary
								preg_match_all('/{([^}]+)}/i',$right,$repl_temp);// $repl_temp= "matches" array
								$replacements = array();
								if(!empty($repl_temp) && isset($repl_temp[1])){
									$replacements = $repl_temp[1];
								}
								$replacements = array_unique($replacements);

								// Right now we have an array like: [0] = 'u.id'

								// Iterate over each thing to replace
								foreach($replacements as $repl){

									$tmp = explode('.',$repl);
									if(count($tmp) <= 1){
										// Missing the 'u' in 'u.'
										$right = str_ireplace('{'.$repl.'}','',$right);
										continue;
									}

									// Get the replacement value
									$first_val = array_shift($tmp);
									switch($first_val){
										
										case 'u':
											// User attribute
											$the_rest = implode('.',$tmp);
											$replace_with = '';
											$replace_with = $this->json_to_string($the_rest,$meta);

											$right = str_ireplace('{'.$repl.'}',$replace_with,$right);
											break;

										case 'r':
											// Response JSON object

											// Does the value exist?
											// - need to do this recursively
											$the_rest = implode('.',$tmp);
											$replace_with = '';
											foreach($action_json as $action_json_obj){
												$replace_with = $this->json_to_string($the_rest,$action_json_obj);
											}

											$right = str_ireplace('{'.$repl.'}',$replace_with,$right);	
											
											break;	

										default:
											// Trying to set a non-existant value
											// - is not for the User or Global
											pr('error=212');
											$right = str_ireplace('{'.$repl.'}','',$right);
											continue;
											break;

									}

								}


								// Now the $left side has no {} brackets
								// - same for the Right side

								// Go through and set the actual values
								
								// Check for a match
								$tmp_left = explode('.',$left);
								switch($tmp_left[0]){

									case 'u':
										array_shift($tmp_left);
										$the_rest = implode('.',$tmp_left);
										$replace_with = '';
										$meta = $this->set_json($the_rest,$right,$meta);
									
										break;
									
									default:
										
										pr('missing thing');
										exit;
								}

							}


							// Save the new meta object!
							// - later, save multiple meta objects, including "g"="global"
							pr('Updated User Attributes');
							pr($meta);
							$new_meta = json_encode($meta);
							if($new_meta != $original_meta){
								// Save it
								$this->PhonesProject->create();
								$pp_data = array('id' => $pp['PhonesProject']['id'],
												 'meta' => $new_meta);
								if(!$this->PhonesProject->save($pp_data)){
									pr('failed saving PhonesProject meta');
								}
							}


							break;

						case 'state':

							$goto_state = trim($action['input1']);
							
							// Do Word replacements
							$tmp_words = explode(' ',$Body);
							$goto_state = $this->replace_url_brackets($tmp_words,$goto_state);

							// Get the Attribute to set (it is currently a string, we want an Object)
							// - parse {r.value} if necessary
							preg_match_all('/{([^}]+)}/i',$goto_state,$repl_temp);// $repl_temp= "matches" array
							$replacements = array();
							if(!empty($repl_temp) && isset($repl_temp[1])){
								$replacements = $repl_temp[1];
							}
							$replacements = array_unique($replacements);

							// Right now we have an array like: [0] = 'u.id'

							// Iterate over each thing to replace
							foreach($replacements as $repl){

								$tmp = explode('.',$repl);
								if(count($tmp) <= 1){
									// Missing the 'u' or 'r' in 'u.'
									$goto_state = str_ireplace('{'.$repl.'}','',$goto_state);
									continue;
								}

								// Get the replacement value
								$first_val = array_shift($tmp);
								switch($first_val){
									
									case 'u':
										// User attribute
										$the_rest = implode('.',$tmp);
										$replace_with = '';
										$replace_with = $this->json_to_string($the_rest,$meta);

										$goto_state = str_ireplace('{'.$repl.'}',$replace_with,$goto_state);
										break;

									case 'r':
										// Response JSON object

										// Does the value exist?
										// - need to do this recursively
										$the_rest = implode('.',$tmp);
										$replace_with = '';
										foreach($action_json as $action_json_obj){
											$replace_with = $this->json_to_string($the_rest,$action_json_obj);
										}

										$goto_state = str_ireplace('{'.$repl.'}',$replace_with,$goto_state);	
										
										break;	

									default:
										// Trying to set a non-existant value
										// - is not for the User or Global
										pr('error=212');
										$goto_state = str_ireplace('{'.$repl.'}','',$goto_state);
										continue;
										break;

								}

							}

							// Save the new State of the User
							pr('Updated User State');
							$new_state = trim($goto_state);
							if($new_state != $pp['PhonesProject']['state']){
								// Save it
								$this->PhonesProject->create();
								$pp_data = array('id' => $pp['PhonesProject']['id'],
												 'state' => $new_state);
								if(!$this->PhonesProject->save($pp_data)){
									pr('failed saving PhonesProject state');
								}
							}


							break;
						
						default:
							// Didn't meet any of the known types
							echo "Failed meeting any of the known Action types";
							exit;
					}

				}

				// Hard-prevent of anything else running afterwards
				exit;

			}


		}


		// No Steps matched
		// - log for the User
		// - this is shitty, because there *should* be a "default" step
		echo "No default step ready to take the leftovers";
		exit;



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


	function json_to_string($str,$obj){
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
					return $this->json_to_string($the_rest,$obj[$first]);
				} else {
					return '';
				}

			} else {
				if(isset($obj->{$first})){	
					return $this->json_to_string($the_rest,$obj->{$first});
				} else {
					return '';
				}
			}
			
		} else {
			return 'error';
		}

	}


	function set_json($path,$value,$obj){
		// Given a JSON string and a JSON object
		// - return the correct fucking thing



		// Is this the root level of the string?
		$tmp = explode('.',$path);
		if(count($tmp) == 0){
			// Fucked up, probably a trailing "." in the attribute
			return $obj;
		}
		if(count($tmp) == 1){
			// Root level now, return it
			$obj->{$tmp[0]} = $value;
			return $obj;
		}
		if(count($tmp) > 1){
			$first = array_shift($tmp);
			$the_rest = implode('.',$tmp);

			// Go Deeper

			// Nothing numeric for now
			if(is_numeric($first)){
				if(is_array($obj)){
					if(isset($obj[$first])){	
						return $this->set_json($the_rest,$value,$obj[$first]);
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
				}
			} else {
				if(isset($obj->{$first})){	
					return $this->set_json($the_rest,$value,$obj->{$first});
				} else {
					$obj->{$first} = new Object();
					return $this->set_json($the_rest,$value,$obj->{$first});
				}
			}
			
		} else {
			pr('error');
			return 'error';
		}

	}

}

?>