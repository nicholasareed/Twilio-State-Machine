<?php

class UsersController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array(),
						'login_as' => array('admin'));

	// Event Access Control
	var $_eAccess = array('*' => array());

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function login_as($user_id = null){
		// Must be logged in as Marc first
		
		$user_id = intval($user_id);
		
		// Get the User
		$this->User =& ClassRegistry::init('User');
		$this->User->contain();

		$conditions = array('User.id' => $user_id);
		$user = $this->User->find('first',compact('conditions'));

		if(empty($user)){
			pr('bad request');
			exit;
		}

		// Log out Mark
		// - kill the Session data
		$this->DarkAuth->destroyData();

		// Log in as new person
		$this->Session->write($this->DarkAuth->secure_key(),$user);

		// Redirect
		$this->redirect('/');

	}


	function direct(){
		// Redirect based on input

		if(!$this->DarkAuth->li){
			$this->redirect('/pages/home');
		}

		if($this->EAuth->EA['Access']['attendee']){
			$this->redirect('/attendees/home');
		}

		if($this->EAuth->EA['Access']['admin']){
			$this->redirect('/admins/home');
		}

		if($this->EAuth->EA['Access']['judge']){
			$this->redirect('/judges/home');
		}

		if($this->EAuth->EA['Access']['sponsors']){
			$this->redirect('/sponsors/home');
		}

		// Dunno how we got here...
		$this->redirect('/pages/display/home');

	}


	function login($success_message = null){
		// Log in for everybody
		// - including Marc
		
		if($this->DarkAuth->li){
			$this->_Flash('Currently logged in','nice','/');
		}
		if($success_message){
			$this->_Flash('Successfully registered! Please log in','nice','/users/login');
		}
		$this->_login();
	}


	function logout(){ 
		$this->DarkAuth->logout(); 
	}


	function register(){
		// Basic signup
		// - automatically add to the current Event

		$this->User =& ClassRegistry::init('User');

		if($this->RequestHandler->isGet()){
			return;
		}


		// Parse input
		$data = array();

		$data['email'] = trim($this->data['User']['email']);
		$data['pswd'] = $this->data['User']['pswd'];

		// Sanitize
		$methods = array('email' => 'email');
		$data = arraySanitize($data,$methods);

		// Already registered with that Email?
		// - support other services as well
		$conditions = array('User.email' => $data['email']);
		$user = $this->User->find('first',compact('conditions'));

		if(!empty($user)){
			$this->_Flash('Email already in use','mean',null);
			return;
		}

		// Validation
		$this->User->set($data);

		if(!$this->User->validates()){
			return;
		}


		// Save
		if(!$this->User->save($data)){
			return false;
		}

		// Add as an Attendee
		$user_id = $this->User->id;

		// Already an Attendee
		// - default is as a participant/member
		$this->Attendee =& ClassRegistry::init('Attendee');
		$conditions = array('Attendee.user_id' => $user_id,
							'Attendee.event_id' => EVENT_ID);
		$attendee = $this->Attendee->find('first',compact('conditions'));

		if(!empty($attendee)){

		}


	}


	function settings(){
		// View my Settings

		// Logged in?
		if(!$this->DarkAuth->li){
			$this->redirect('/users/login');
		}
		
		$this->User =& ClassRegistry::init('User');
		$this->User->contain(array('Profile'));
		$conditions = array('User.id' => $this->DarkAuth->id);

		$user = $this->User->find('first',compact('conditions'));

		if(empty($user)){
			// uh oh
			$this->redirect('/');
		}

		if($this->RequestHandler->isGet()){
			$this->data = $user;
			return;
		}


		// Parse input

		$this->Profile = & ClassRegistry::init('Profile');
		
		// Collect $data
		$userData = array();
		$userData['id'] = $user['User']['id'];
		// add: $password changing here

		$profileData = array();
		$profileData['id'] = $user['Profile']['id'];
		$profileData['fullname'] = $this->data['Profile']['fullname'];
		$profileData['bio'] = $this->data['Profile']['bio'];
		$profileData['cell_phone'] = $this->data['Profile']['cell_phone'];
		
		// Sanitize
		$methods = array('fullname' => array('paranoid',array(' ','.','-')),
						 'bio' => 'escape',
						 'cell_phone' => 'phone');
		$profileData = arraySanitize($profileData,$methods);

		// Reset some data
		$this->data['Profile']['cell_phone'] = $profileData['cell_phone'];


		// Get rid of Cell Phone if not required
		if(empty($profileData['cell_phone'])){
			unset($profileData['cell_number']);
		}

		// Validate
		
		$this->Profile->set($profileData);

		if(!$this->Profile->validates()){
			$this->_Flash('Please fix errors','mean',null);
			return;
		}

		// Save
		$this->Profile->create();
		if(!$this->Profile->save($profileData)){
			$this->_Flash('Failed to save Profile','mean',null);
			return;
		}

		// Redirect
		$this->_Flash('Changes Saved!','nice','/users/settings');

	}

}

?>