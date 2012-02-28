<?php

class UsersController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array(),
							'login_as' => array('admin'));

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function test($value = 'no_value'){
		// Test values used by a test project
		// - testing different webhook results and UX
		//exit;
		$return = array('send_sms' => 'stringhere',
						'send_sms2' => array(array('To' => array('+16502068481','+16027059885'), 'Body' => 'input value: '.$value),
											array('Body' => 'test')), // Can have multiple "To" fields
						'set_state2' => 'state_2',
						'set_attributes2' => array('a.name' => 'TxtSpring',
												  'u.meta.inputval' => $value,
												  'u.meta.awesome' => array('ok','cool'),
												  'u.meta.more' => array('test1' => 'value1',
												  					'test2' => 'value2')));
		//pr($return);
		echo json_encode($return);
		exit;

	}


	function direct(){
		// Redirect based on input

		if(!$this->DarkAuth->li){
			$this->redirect('/pages/home');
		}

		$this->redirect('/projects');

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


	function register_not_allowed_yet(){
		// Basic signup

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

}

?>