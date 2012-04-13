<?php

class AdminsController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array('admin'));

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function index(){
		// Links, mostly



	}


	function users(){
		// Show info about all the Users

		$this->User =& ClassRegistry::init('User');
		$this->User->contain(array('Project','Twilio'));
		$users = $this->User->find('all',compact('conditions'));

		$this->set(compact('users'));

	}

}

?>