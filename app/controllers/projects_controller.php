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

		$this->Project =& ClassRegistry::init('Project');
		$conditions = array('Project.live' => 1,
							'Project.user_id' => $this->DarkAuth->id);
		$this->Project->contain();
		$projects = $this->Project->find('all',compact('conditions'));

		// Set View variables
		$this->set(compact('projects'));

	}


	function view_json($project_id = null){

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
		//pr($project);
		// Set View variables
		$this->set(compact('project'));

	}


	function add(){
		// Person creating an Application
		// - cannot already be on a Team
		
		// Get possible Twilio numbers?

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

		// Save
		$this->Project->create();
		if(!$this->Project->save($data)){
			$this->_Flash('There were errors saving your Application, please try again','mean',null);
			return;
		}

		// Get Project.id
		$project_id = $this->Project->id;

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