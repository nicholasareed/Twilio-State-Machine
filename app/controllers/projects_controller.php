<?php

class ProjectsController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array('member'));

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function index(){
		// My Projects

		//$this->_Flash('message','nice',null); // testing

		$this->Project =& ClassRegistry::init('Project');
		$conditions = array('Project.live' => 1,
							'Project.user_id' => $this->DarkAuth->id);
		$this->Project->contain(array('Twilio'));
		$projects = $this->Project->find('all',compact('conditions'));
		
		// Fuck it, do a HABTM count of users
		$this->PhonesProject =& ClassRegistry::init('PhonesProject');
		foreach($projects as $key => $project){
			$conditions = array('PhonesProject.project_id' => $project['Project']['id']);
			$pp = $this->PhonesProject->find('count',compact('conditions'));
			$projects[$key]['Project']['pp_count'] = $pp;
		}


		// Stats
		// - numbers and sms messages


		// Numbers
		// - get phone numbers based on plan
		$this->Plan =& ClassRegistry::init('Plan');
		$plans = $this->Plan->getPlans();
		// Plan exists? (not currently checking)
		$plan = $plans[$this->DarkAuth->DA['User']['plan']];

		$numbers = array();

		$this->Twilio =& ClassRegistry::init('Twilio');
		$this->Twilio->contain();
		$conditions = array('Twilio.live' => 1,
							'Twilio.user_id' => $this->DarkAuth->id);
		$numbers = $this->Twilio->find('count',compact('conditions'));
		$extra_numbers = $plan['numbers_count'] - $numbers;

		// Set View variables
		$this->set(compact('projects','plans','extra_numbers'));

	}


	function view_json($project_id = null){

		$project_id = intval($project_id);

		$this->Project =& ClassRegistry::init('Project');
		$this->Project->contain(array('Twilio','State.Step' => array('Condition','Action')));
		$conditions = array('Project.id' => $project_id,
							'Project.user_id' => $this->DarkAuth->id,
							'Project.live' => 1);
		$project = $this->Project->find('first',compact('conditions'));

		if(empty($project)){
			$this->_Flash('Unable to find Project','mean','/');
		}

		// Must be my project
		if($project['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Invalid project chosen','mean',$this->referer('/'));
		}
		
		$project['Numbers'] = Set::extract($project['Twilio'],'{n}.friendly');
		$project['Numbers'] = implode(', ',$project['Numbers']);
		unset($project['Twilio']);

		echo json_encode($project);
		exit;

	}


	function view($project_id = null){
		// View a Project
		// - show each State, Step(s), etc.
		
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

		// Must be my project
		if($project['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Invalid project chosen','mean',$this->referer('/'));
		}
		
		// Get Twilio Numbers
		$this->Twilio =& ClassRegistry::init('Twilio');
		$this->Twilio->contain();
		$conditions = array('Twilio.project_id' => $project['Project']['id'],
							'Twilio.live' => 1);
		$fields = array('ptn','ptn');
		$twilios = $this->Twilio->find('list',compact('conditions','fields'));

		$this->data = $project;

		$this->set(compact('project','twilios'));

	}


	function add(){
		// Person creating an Application
		// - cannot already be on a Team
		
		App::import('Vendor','TwilioRest',array('file' => 'Twilio/Twilio.php'));

		// Get possible new Twilio numbers
		// - get phone numbers based on plan
		$this->Plan =& ClassRegistry::init('Plan');
		$plans = $this->Plan->getPlans();
		// Plan exists? (not currently checking)
		$plan = $plans[$this->DarkAuth->DA['User']['plan']];

		$numbers = array();

		$this->Twilio =& ClassRegistry::init('Twilio');
		$this->Twilio->contain();
		$conditions = array('Twilio.live' => 1,
							'Twilio.user_id' => $this->DarkAuth->id);
		$numbers = $this->Twilio->find('count',compact('conditions'));
		$extra_numbers = $plan['numbers_count'] - $numbers;

		$this->set(compact('extra_numbers'));

		if($this->RequestHandler->isGet()){
			return;
		}

		// Parse input

		$data = array();
		$data['user_id'] = $this->DarkAuth->id;
		$data['name'] = $this->data['Project']['name'];
		$data['meta'] = '{}';

		// Sanitize
		$methods = array('name' => array('paranoid',array(' ',',','.','-')),
						 );

		$data = arraySanitize($data,$methods);

		// Validation

		$this->Project =& ClassRegistry::init('Project');
		$this->Project->set($data);

		if(!$this->Project->validates()){
			$this->_Flash('Please fix errors','mean',null);
			return;
		}

		// Validate Phone Number for buying
		// - only letting them choose the area code, we auto-buy a number based on that
		if($extra_numbers > 0){
			if(!empty($this->data['Project']['ptn'])){
				if(strlen($this->data['Project']['ptn']) != 3){
					$this->Project->invalidate('ptn','Enter an area code');
					return false;
				}

				$searchParams['AreaCode'] = $this->data['Project']['ptn'];

				$twilio_client = new Services_Twilio(TWILIO_ID, TWILIO_TOKEN);
				$available_numbers = $twilio_client->account->available_phone_numbers->getList('US', 'Local', $searchParams);
				if(empty($available_numbers)) {
					$this->_Flash('Unable to find any numbers in that Area Code, please try again','mean',null);
					return false;
				}

				// Number to use
				$buy_number = $available_numbers->available_phone_numbers[0]->phone_number;
				
				// Choose one of the numbers
				// - probably should verify everything is working as expected before buying this number
				try {
					// Buy the phone number
					$bought_number = $twilio_client->account->incoming_phone_numbers->create(array('PhoneNumber' => $buy_number,
																									'SmsUrl' => 'http://incoming.appsprey.com/',
																									'SmsMethod' => 'POST',
																									'VoiceUrl' =>'http://incoming.appsprey.com/',
																									'VoiceMethod' => 'POST',
																									'StatusCallback' => 'http://incoming.appsprey.com/',
																									'StatusCallbackMethod' => 'POST'));
					//$bought_number = 1;
				} catch (Exception $e) {
					$this->_Flash('Failed buying number, please try again','mean',null);
					return false;
				}
			}

		}

		// Save
		$this->Project->create();
		if(!$this->Project->save($data)){
			$this->_Flash('There were errors saving your Application, please try again','mean',null);
			return;
		}

		// Get Project.id
		$project_id = $this->Project->id;

		// Link with Twilio
		// - should have the option to use an existing bought number
		if(isset($bought_number)){
			$twilioData = array();
			$twilioData['project_id'] = $project_id;
			$twilioData['user_id'] = $this->DarkAuth->id;
			$twilioData['ptn'] = $buy_number;
			$twilioData['friendly'] = prettyPhone($buy_number);
			// Save the Twilio number
			// - if this fucks up, we're in trouble
			if(!$this->Twilio->save($twilioData)){
				// Fuck
				// - just continue on, this is a big problem though
			}


		} else {
			// No number stuff done, just create the App

		}


		// Build default State, Step, etc.

		// State
		$stateData = array('project_id' => $project_id,
							'key' => 'default',
							'desc' => 'Initial State for a person');
		$this->State =& ClassRegistry::init('State');
		$this->State->create();
		if(!$this->State->save($stateData)){
			// Shit, log error
			$this->_Flash('There were errors saving your Application','mean','/projects/view/'.$project_id);
		}
		$state_id = $this->State->id;

		// Step
		$stepData = array('state_id' => $state_id,
							'order' => 1);
		$this->Step =& ClassRegistry::init('Step');
		$this->Step->create();
		if(!$this->Step->save($stepData)){
			// Shit, log error
			$this->_Flash('There were errors saving your Application','mean','/projects/view/'.$project_id);
		}
		$step_id = $this->Step->id;

		// Condition
		$conditionData = array('step_id' => $step_id,
								'type' => 'starts_with',
								'input1' => 'help',
								'order' => 1);
		$this->Condition =& ClassRegistry::init('Condition');
		$this->Condition->create();
		if(!$this->Condition->save($conditionData)){
			// Shit, log error
			$this->_Flash('There were errors saving your Application','mean','/projects/view/'.$project_id);
		}


		// Action
		$actionData = array('step_id' => $step_id,
								'type' => 'response',
								'input1' => 'here is a response help message',
								'order' => 1);
		$this->Action =& ClassRegistry::init('Action');
		$this->Action->create();
		if(!$this->Action->save($actionData)){
			// Shit, log error
			$this->_Flash('There were errors saving your Application','mean','/projects/view/'.$project_id);
		}

		// Redirect
		$this->redirect('/projects/view/'.$project_id);

	}


	function settings($project_id = null){
		
		// Edit Project Settings
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

		// Must be my project
		if($project['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Invalid project chosen','mean',$this->referer('/'));
		}


		if($this->RequestHandler->isGet()){
			$this->data = $project;
			return;
		}

		// Parse input
		// - type cannot be changed

		App::import('Sanitize');

		$data = array();
		$data['id'] = $project['Project']['id'];
		$data['enable_state'] = intval($this->data['Project']['enable_state']);

		// Save
		if(!$this->Project->save($data,false,array_keys($data))){
			echo jsonError(101,'Failed saving Project Settings');
			exit;
		}

		echo jsonSuccess('Settings Saved');
		exit;

	}


	function edit($team_id = null){

		// NOT DONE



		// Edit the info for your Team(s)

		$team_id = intval($team_id);

		// Get Team
		$this->Team =& ClassRegistry::init('Team');

		$this->Team->contain(array('Attendee'));
		$conditions = array('Team.id' => $team_id,
							'Team.event_id' => $this->EAuth->event_id,
							'Team.live' => 1);

		$team = $this->Team->find('first',compact('conditions'));

		// No Team?
		if(empty($team)){
			$this->_Flash('Unable to find Team','mean','/teams');
		}

		// I am on Team?
		// - could also check my EA['Team']
		// - I bet this causes an error later
		$user_ids = Set::extract($team['Attendee'],'{n}.user_id');

		if(!in_array($this->DarkAuth->id,$user_ids)){
			$this->_Flash('You are not on that Team','mean','/teams/view/'.$team_id);
		}


		// Set Values
		$this->set(compact('team'));

		if($this->RequestHandler->isGet()){
			// Insert as defaults
			$this->data = $team;
			return;
		}
		

		// Parse input
		// - same as create()

		$data = array();
		$data['id'] = $team['Team']['id'];
		$data['name'] = $this->data['Team']['name'];
		$data['bio'] = $this->data['Team']['bio'];
		$data['public_message'] = $this->data['Team']['public_message'];

		// Sanitize
		$methods = array('name' => 'escape',
						 'bio' => 'escape',
						 'public_message' => 'escape');

		$data = arraySanitize($data,$methods);

		// Validation

		$this->Team->set($data);

		if(!$this->Team->validates()){
			$this->_Flash('Please fix errors','mean',null);
			return;
		}

		// Save
		$this->Team->create();
		if(!$this->Team->save($data)){
			$this->_Flash('There were errors saving your Team, please try again','mean',null);
			return;
		}

		// Redirect
		$this->_Flash('Changes have been saved','nice','/teams/view/'.$team['Team']['id']);

		
	}


	function db($project_id = null){
		// View the databases for this application

		$project_id = intval($project_id);

		$this->Project =& ClassRegistry::init('Project');
		$this->Project->contain();
		$conditions = array('Project.id' => $project_id,
							'Project.user_id' => $this->DarkAuth->id,
							'Project.live' => 1);
		$project = $this->Project->find('first',compact('conditions'));

		if(empty($project)){
			$this->_Flash('Unable to find Project','mean','/');
		}

		// Make database request
		//$this->AppData =& ClassRegistry::init('AppData');
		//$app_data = $this->AppData->getAppDatabase($project['Project']['id']);
		
		// User Meta
		$this->PhonesProject =& ClassRegistry::init('PhonesProject');
		$conditions = array('PhonesProject.project_id' => $project['Project']['id']);
		$pp = $this->PhonesProject->find('all',compact('conditions'));
		
		foreach($pp as $key => $p){
			// Turn into complete JSON Object
			$json = $p['PhonesProject']['meta'];
			$json = json_decode($json);
		}

		// Set View variables
		$this->set(compact('project','pp','user_data'));

	}


	function set_db_value(){
		// Set a DB value

		if($this->RequestHandler->isGet()){
			return;
		}

		// Get value
		
		
	}


	function logs($project_id = null, $last_id = 0){
		// Display logs for a Project

		$project_id = intval($project_id);
		$last_id = intval($last_id);

		$this->Project =& ClassRegistry::init('Project');
		$this->Project->contain();
		$conditions = array('Project.id' => $project_id,
							'Project.user_id' => $this->DarkAuth->id,
							'Project.live' => 1);
		$project = $this->Project->find('first',compact('conditions'));

		if(empty($project)){
			$this->_Flash('Unable to find Project','mean','/');
		}

		$this->ProjectLog =& ClassRegistry::init('ProjectLog');
		$this->ProjectLog->contain();
		$conditions = array('ProjectLog.project_id' => $project['Project']['id'],
							'ProjectLog.id >' => $last_id);
		$limit = 50;
		$pl = $this->ProjectLog->find('all',compact('conditions','limit'));
		
		// Reverse sort by key (for js to process easier)

		echo json_encode($pl);
		exit;

	}

}

?>