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


	function create(){

		// NOT DONE



		// Person creating a Team
		// - cannot already be on a Team
		
		// Cannot already be on a Team
		if(!empty($this->EAuth->EA['Team'])){
			$this->_Flash('You are already on a Team!','mean','/teams');
		}

		
		if($this->RequestHandler->isGet()){
			return;
		}

		// Parse input

		$data = array();
		$data['event_id'] = $this->EAuth->event_id;
		$data['creator_id'] = $this->DarkAuth->id;
		$data['name'] = $this->data['Team']['name'];
		$data['bio'] = $this->data['Team']['bio'];
		$data['public_message'] = $this->data['Team']['public_message'];

		// Sanitize
		$methods = array('name' => 'escape',
						 'bio' => 'escape',
						 'public_message' => 'escape');

		$data = arraySanitize($data,$methods);

		// Validation

		$this->Team =& ClassRegistry::init('Team');
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

		// Get Team.id
		$team_id = $this->Team->id;

		// Add habtm relationship
		$this->Team->habtmNick('Attendee',$team_id,$this->EAuth->EA['Attendee']['id']);

		// Redirect
		$this->_Flash('Your Team is ready, start inviting people','nice','/teams/view/'.$team_id);

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

}

?>