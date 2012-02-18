<?php

class TeamsController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array('member','admin'));

	// Event Access Control
	var $_eAccess = array('*' => array());

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function index(){
		// Team list
		// - teams can post various messages
		// - looking for a person, etc.

		// Expected actions:
		// - Attendee: look at joining a Team. If in a Team...what should they see?
		// - Everyone else: just look at names

		// List all Teams
		$this->Team =& ClassRegistry::init('Team');
		$conditions = array('Team.live' => 1,
							'Team.event_id' => $this->EAuth->event_id);
		$this->Team->contain(array('Attendee.User.Profile'));
		$teams = $this->Team->find('all',compact('conditions'));

		$this->set(compact('teams'));

	}


	function mine(){
		// Renders the below view
		$this->action = 'view';

		// Do I have a team?
		if(empty($this->EAuth->EA['Team'])){
			// No Team for me
			$this->redirect('/teams');
		}

		// I do, render the View for it
		$this->view($this->EAuth->EA['Team'][0]['id']);

		return;
	}


	function view($team_id = null){
		// View a Team
		// - Members/Attendees
		// - Hack(s)
		
		$team_id = intval($team_id);

		$this->Team =& ClassRegistry::init('Team');
		$this->Team->contain(array('Hack','Attendee.User.Profile'));
		$conditions = array('Team.id' => $team_id,
							'Team.event_id' => $this->EAuth->event_id,
							'Team.live' => 1);
		$team = $this->Team->find('first',compact('conditions'));

		if(empty($team)){
			$this->_Flash('Unable to find Team','mean','/');
		}
		
		// My Team?
		$mine = false;
		$attendee_ids = Set::extract($team['Attendee'],'{n}.user_id');
		if(in_array($this->DarkAuth->id,$attendee_ids)){
			$mine = true;
		}

		$this->Hack =& ClassRegistry::init('Hack');
		foreach($team['Hack'] as $key => $hack){
			$team['Hack'][$key]['canView'] = $this->Hack->canView($hack,null,$this->EAuth->EA);
		}

		// Have an Invite?
		$invite = array();
		if(!$mine && $this->DarkAuth->li){
			$this->TeamInvite =& ClassRegistry::init('TeamInvite');
			$this->TeamInvite->contain();
			$conditions = array('TeamInvite.event_id' => $this->EAuth->event_id,
								'TeamInvite.team_id' => $team['Team']['id'],
								'TeamInvite.user_id' => $this->DarkAuth->id,
								'TeamInvite.live' => 1);
			$invite = $this->TeamInvite->find('first',compact('conditions'));
		}

		// Set View variables
		$this->set(compact('team','mine','invite'));

	}


	function create(){
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


	function leave($team_id = null){
		// Leave a Team
		// - if the last person leaves, the Team is dissolved

		$team_id = intval($team_id);
	
		// Am I on the Team?
		$team_ids = Set::extract($this->EAuth->EA['Team'],'{n}.id');

		if(!in_array($team_id,$team_ids)){
			// You are not on that Team!
			$this->_Flash('You are not on that Team!','mean','/teams');
		}

		$this->Team =& ClassRegistry::init('Team');
		
		// Get the Team
		$this->Team->contain(array('Attendee'));
		$conditions = array('Team.id' => $team_id,
							'Team.event_id' => $this->EAuth->event_id);
		$team = $this->Team->find('first',compact('conditions'));
		
		if(empty($team)){
			$this->_Flash('Unable to find Team','mean','/teams');
		}


		if($this->RequestHandler->isGet()){
			return;
		}


		// Delete my association with the Team
		$this->Team->deleteAssoc('Attendee',$team['Team']['id'],$this->DarkAuth->id);

		// Get the number of Attendees
		// - if I left, there must still be a person
		// - otherwise, we delete the Team
		$num = count($team['Attendee']);

		if($num == 1){
			// Remove the Team
			// - live=0
			$team['Team']['live'] = 0;
			$this->Team->create();
			if(!$this->Team->save($team['Team'],false,array('id','live'))){
				$this->_Flash('Failed updating Team','mean','/teams');
			}
		}

		// Redirect
		$this->_Flash('You left your Team','nice','/teams');

	}

}

?>