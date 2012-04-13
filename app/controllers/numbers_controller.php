<?php

class NumbersController extends AppController {

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
		// My Numbers

		$this->Twilio =& ClassRegistry::init('Twilio');
		$conditions = array('Twilio.live' => 1,
							'Twilio.user_id' => $this->DarkAuth->id);
		$this->Twilio->contain(array('Project'));
		$numbers = $this->Twilio->find('all',compact('conditions'));

		// Plans and buying numbers
		$this->Plan =& ClassRegistry::init('Plan');
		$plans = $this->Plan->getPlans();
		$plan = $plans[$this->DarkAuth->DA['User']['plan']];

		$this->Twilio =& ClassRegistry::init('Twilio');
		$this->Twilio->contain();
		$conditions = array('Twilio.live' => 1,
							'Twilio.user_id' => $this->DarkAuth->id);
		$numbers_count = $this->Twilio->find('count',compact('conditions'));
		$extra_numbers = $plan['numbers_count'] - $numbers_count;

		// Set View variables
		$this->set(compact('numbers','extra_numbers'));

	}

}

?>