<?php

class InvitesController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array());

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function add(){
		// Somebody input an email because they want to be notified

		if($this->RequestHandler->isGet()){
			return;
		}

		// Parse input

		$data = array();
		$data['email'] = $this->data['Invite']['email'];

		// Sanitize
		$methods = array('email' => 'email',
						 );

		$data = arraySanitize($data,$methods);

		// Validation

		$this->Invite =& ClassRegistry::init('Invite');
		$this->Invite->set($data);

		if(!$this->Invite->validates()){
			$this->set('vErrors', $this->Invite->invalidFields());
			return;
		}

		// Save
		$this->Invite->create();
		if(!$this->Invite->save($data)){
			$this->_Flash('There were errors saving your Invitation Request, please try again','mean',null);
			$this->set('vErrors', $this->Invite->invalidFields());
			return;
		}


		// Redirect to thank you page
		$this->redirect('/pages/thank_you');

	}

}

?>