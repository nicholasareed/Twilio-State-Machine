<?php

class TextsController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array());

	var $components = array();

	var $doVerifyTwilio = true; // default: true


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function project($project_id = null){

		$project_id = intval($project_id);

		if(!$this->DarkAuth->li){
			$this->redirect('/');
		}

		if($this->RequestHandler->isGet()){
			$this->redirect('/');
		}

		// Get Project
		$project_id = intval($project_id);

		$this->Project =& ClassRegistry::init('Project');
		$this->Project->contain(array('State.Step' => array('Condition','Action')));
		$conditions = array('Project.id' => $project_id,
							'Project.user_id' => $this->DarkAuth->id,
							'Project.live' => 1);
		$project = $this->Project->find('first',compact('conditions'));

		if(empty($project)){
			$this->_Flash('Unable to find Project','mean','/');
		}

		// Run it
		$this->doVerifyTwilio = false;
		Configure::write('demo_mode',true);

		$_REQUEST['To'] = trim($_POST['To']);
		$_REQUEST['From'] = trim($_POST['From']);
		$_REQUEST['Body'] = trim($_POST['Body']);

		$this->incoming();

		exit;

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
 		
		$expected_signature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'];
		$string_to_sign = TWILIO_ENDPOINT_URL; //$_SERVER['SCRIPT_URI'];
		
		if(strlen($_SERVER['QUERY_STRING'])){
			// This does not run when in POST mode?
			$string_to_sign .= "?{$_SERVER['QUERY_STRING']}";
		}
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
		$this->Twilio->contain(array('User','Project'));
		$conditions = array('Twilio.ptn' => $To,
							'Twilio.live' => 1);
		$twilio = $this->Twilio->find('first',compact('conditions'));

		// Twilio Number exists?
		if(empty($twilio)){
			$this->renderError(3);
		}

		// User and Profile exist?
		//if(!isset($twilio['User']) || !isset($twilio['User']['Profile']) || !isset($twilio['User']['Profile']['id']) || empty($twilio['User']['Profile']['id'])){
		if(!isset($twilio['User'])){
			$this->renderError(4);
		}

		// Verify that this is from Twilio
		// - disable verification if we are testing
		if($this->doVerifyTwilio && !$this->verify_twilio(TWILIO_TOKEN)){
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
		$this->Project->contain(array('User','State.Step' => array('Condition','Action')));
		$conditions = array('Project.id' => $project_id,
							'Project.live' => 1);
		$project = $this->Project->find('first',compact('conditions'));

		if(empty($project)){
			echo "Unable to find Project";
			exit;
		}

		// Set Incoming information variables
		Configure::write('To', $To);
		Configure::write('From', $From);
		Configure::write('Body', $Body);



		// Set Project.id global
		Configure::write('Project.id', $project['Project']['id']);

		// Set Twilio variables
		//Configure::write('twilio_id',$project['User']['Profile']['twilio_id']);
		//Configure::write('twilio_token',$project['User']['Profile']['twilio_token']);
		Configure::write('twilio_id',TWILIO_ID);
		Configure::write('twilio_token',TWILIO_TOKEN);

		// Request Hash
		// - keep all requests together
		Configure::write('request_hash',uniqid());

		// Log
		$this->ProjectLog =& ClassRegistry::init('ProjectLog');
		$logData = array('project_id' => $project['Project']['id'],
						 'type' => 'received_sms',
						 'request_hash' => Configure::read('request_hash'),
						 'data' => json_encode(array('To' => $To,
						 							 'From' => $From,
						 							 'Body' => $Body))
						 );
		$this->ProjectLog->create();
		$this->ProjectLog->save($logData);

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
			$pp_id = $phone['Project'][0]['PhonesProject']['id'];
		}

		// Get Full Phone Project
		$conditions = array('PhonesProject.id' => $pp_id);
		$pp = $this->PhonesProject->find('first',compact('conditions'));
		//pr($conditions);
		//exit;
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
			echo "Failed finding state_key"; // Probably because the "default" state was removed!
			exit;
		}

		$state = $project['State'][$state_key];
		$tmp_state = $state;
		unset($tmp_state['Step']);

		// Log entered State
		$logData = array('project_id' => $project['Project']['id'],
						 'related_id' => $state['id'],
						 'request_hash' => Configure::read('request_hash'),
						 'type' => 'entered_state',
						 'data' => json_encode($tmp_state)
						 );
		$this->ProjectLog->create();
		$this->ProjectLog->save($logData);

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

		Configure::write('user_meta',$meta);
		Configure::write('original_user_meta',$meta);

		// Get Application Meta
		$app_meta = $project['Project']['meta'];
		$original_app_meta = $app_meta;
		try {
			$app_meta = json_decode($app_meta);
		} catch (Exception $e){
			$app_meta = 'fail';
		}
		if($meta == 'fail'){
			$app_meta = new Object();
		}

		Configure::write('app_meta',$app_meta);
		Configure::write('original_app_meta',$app_meta);
		
		//pr('meta');
		//pr($meta);
		//exit;

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

					case 'contains':
						// Case-insensitive
						// - not using RegEx
						$tmp = stripos($Body,$condition['input1']);
						if($tmp !== false){
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
									$replace_with = $this->Project->get_json_value($the_rest,$meta);
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

								case 'a':
									array_shift($tmp_left);
									$the_rest = implode('.',$tmp_left);
									$replace_with = '';
									$replace_with = $this->Project->get_json_value($the_rest,$app_meta);
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
				
				// Remove unnecessary $step values for storing
				$tmp_step = $step;
				unset($tmp_step['Condition']);
				unset($tmp_step['Action']);

				// Log Step matched
				$logData = array('project_id' => $project['Project']['id'],
								 'related_id' => $step['id'],
						 		 'request_hash' => Configure::read('request_hash'),
								 'type' => 'triggered_step',
								 'data' => json_encode($tmp_step)
								 );
				$this->ProjectLog->create();
				$this->ProjectLog->save($logData);

				// Go through Actions
				$action_json = array();
				foreach($step['Action'] as $action){

					switch($action['type']){

						case 'send_sms':
							// Respond with whatever was set

							// Parse the response Template
							$message = $action['input1'];

							// Parse out the {r.value} stuff
							$words = explode(' ',$Body);
							$recipients = $action['send_sms_recipients'];
							$recipients = $this->Project->replace_url_brackets($words,$recipients);

							// Determine the Recipient it is going
							if(empty($action['send_sms_recipients'])){
								$new_To = array($From);
							} else {
								$new_To = explode(',',$recipients);
							}

							// Validate phone numbers
							// - NOT done

							// Send the SMS
							$options = array('action_id' => $action['id'],
											 'message' => $message,
											 'To' => $new_To,
											 'From' => $To);
							$status = $this->Project->send_sms($options);

							break;

						case 'webhook':
							// Parse url
							$url = $action['input1'];
							$words = explode(' ',$Body);
							$url = $this->Project->replace_url_brackets($words,$url);

							// Make Request
							// - always a GET? (should be able to POST with data also. Put EXACTLY what they want to POST. How to handle variables?)
							// - wrap these variables with {{var_here}} like Mustache
							$this->Curl =& ClassRegistry::init('Curl');
							$this->Curl->url = $url;
							//$this->Curl->post = true;
							//$this->Curl->postFieldsArray = compact('From','To','Body');
							$result = $this->Curl->execute();

							// Try parsing JSON
							$is_json = 0;
							try {
								$json = json_decode($result);
								$is_json = 1;
							} catch (Exception $e){
								// Failed, just continue to the next Action
								// - log the failure for the User
								
							}

							if($is_json){
								// Process the returned values
								// - see if any attributes were set, messages to send, etc.

								// Set Attributes
								if(isset($json->set_attributes)){

									// Set the attributes
									// - only set attributes as in the current environment
									$set_attributes = $json->set_attributes;

									$options = array('obj' => $set_attributes,
													 'use_obj' => true,
													 'user_meta' => Configure::read('user_meta'),
													 'app_meta' => Configure::read('app_meta'),
													 'action_id' => $action['id'],
													 'pp_id' => $pp['PhonesProject']['id']);
									
									$status = $this->Project->set_attributes($options);

									// Update Meta attributes
									Configure::write('user_meta',$status['user_meta']);
									Configure::write('app_meta',$status['app_meta']);

									//pr('done in texts');
									//exit;


										// This is basically an object we could be setting (not in json_format either)
										// - merge arrays should do it?
										//		- cannot merge objects though
										
										// Traverse the entire thing and append values as necessary
										// - using 'u' => 'hello' would erase it?
										// - then 'u' => array() would erase 'hello'?
										// - using 'u' => array('here') would simply append
										
										//$key = 'path to value we are changing';
										
								}

								// Set State
								
								if(isset($json->set_state)){
									// Can set the state for multiple people
									$set_state = $json->set_state;
									if(is_string($set_state)){
										// Set the current user's state

									  	$options = array('new_state' => $set_state,
									  					 'old_state' => $pp['PhonesProject']['state'],
									  					 'action_id' => $action['id'],
									  					 'pp_id' => $pp['PhonesProject']['id']);
										
										$this->Project->set_state($options);
									} else {
										// Set State for multiple users
										// - not currently working
									}
								}
								


								// Send SMS messages
								if(isset($json->send_sms)){
									// Expecting an array of Objects to send to
									// - "To" can be an array also
									$send_sms = $json->send_sms;
									if(is_string($send_sms)){
										// Sending a single SMS to the current user
										// - turn it into the format for below
										$send_sms = array(array('Body' => $send_sms));
									}

									$send_sms = (array)$send_sms;

									if(is_array($send_sms)){
										// Iterate through each
										// - expecting a consistent format of To, Body
										foreach($send_sms as $new_sms){
											if(is_object($new_sms)){
												$new_sms = (array)$new_sms;
											}
											if(!is_array($new_sms)){
												// Missing the To, Body
												pr('missing array of arrays');
												continue;
											}
											if(!isset($new_sms['To'])){
												$new_sms['To'] = $From;
											}
											if(!isset($new_sms['Body'])){
												pr('missing the Body field');
												continue;
											}

											// Send the SMS to whoever
											$options = array('action_id' => $action['id'],
															 'message' => $new_sms['Body'],
															 'To' => $new_sms['To'],
															 'From' => $To);
											$status = $this->Project->send_sms($options);

											
										}

									}
									
								}


							}

							// Log request and send_sms
							$logData = array('project_id' => $project['Project']['id'],
											 'related_id' => $action['id'],
											 'request_hash' => Configure::read('request_hash'),
											 'type' => 'action_webhook',
											 'data' => json_encode(array('url' => $url,
											 							 'response' => $result,
											 							 'is_json' => $is_json))
											 );
							$this->ProjectLog->create();
							$this->ProjectLog->save($logData);

							if(!$is_json){
								// Do not add to global values because it is not JSON
								continue;
							}

							// Save the JSON data for use later
							// - useful in response templates :)
							// - adds to any existing webhook calls
							$action_json[] = $json;
							Configure::write('action_json',$action_json);

							break;

						case 'attribute':
							
							// Parse the Attribute we are setting
							// - only supports the user for now

							// Respond with whatever was set


							$options = array('field' => $action['input1'],
											  'user_meta' => Configure::read('user_meta'),
											  'app_meta' => Configure::read('app_meta'),
											  'action_id' => $action['id'],
											  'pp_id' => $pp['PhonesProject']['id']);

							$status = $this->Project->set_attributes($options);

							// Update Meta attributes
							Configure::write('user_meta',$status['user_meta']);
							Configure::write('app_meta',$status['app_meta']);

							break;


							// OLD



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
								$left = Sanitize::paranoid($tmp[0],array('.','{','}','_'));
								$right = Sanitize::paranoid($tmp[1],array('.','|',',',' ','{','}','_','-'));
								if(empty($left)){
									break;
								}

								// Do Word replacements
								// - {2}
								$tmp_words = explode(' ',$Body);
								$left = $this->Project->replace_url_brackets($tmp_words,$left);
								$right = $this->Project->replace_url_brackets($tmp_words,$right);


								$left = $this->Project->replace_brackets($left);
								$right = $this->Project->replace_brackets($right);


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

										$meta = $this->Project->set_json($the_rest,$right,$meta);
									
										break;
									
									case 'a':
										// Application variables
										if(count($tmp_left) <= 1){
											// Missing the 'meta' value
											// - log error
											pr('expecting a.something');
											break;
										}

										array_shift($tmp_left);	// remove "a"
										$the_rest = implode('.',$tmp_left);
										$replace_with = '';

										$app_meta = $this->Project->set_json($the_rest,$right,$app_meta);
										pr('just set it');
										pr($app_meta);

										break;
									
									default:
										
										pr('missing thing');
										exit;
								}

								// Log Attribute setting
								$logData = array('project_id' => $project['Project']['id'],
												 'related_id' => $action['id'],
												 'request_hash' => Configure::read('request_hash'),
												 'type' => 'action_attribute',
												 'data' => json_encode(array('tmp' => 'in progress. attr(s) was set though'))
												 );
								$this->ProjectLog->create();
								$this->ProjectLog->save($logData);


								// Save the updated User meta object!
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


								// Save the updated Application meta object!
								pr('Updated Application Attributes');
								pr($app_meta);
								$new_app_meta = json_encode($app_meta);
								if($new_app_meta != $original_app_meta){
									// Save it
									$this->Project->create();
									$project_data = array('id' => $project['Project']['id'],
													 	  'meta' => $new_app_meta);
									if(!$this->Project->save($project_data)){
										pr('Failed saving Project meta');
									}
								}


							}

							break;

						case 'state':

						  	$options = array('new_state' => $action['input1'],
						  					 'old_state' => $pp['PhonesProject']['state'],
						  					 'action_id' => $action['id'],
						  					 'pp_id' => $pp['PhonesProject']['id']);

						  	$status = $this->Project->set_state($options);

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
		header('Nostep: noneset');
		echo "No default step ready to take the leftovers";
		exit;



	}

}

?>