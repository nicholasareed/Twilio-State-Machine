<?php

class StepsController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array('member'));

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function add($state_id = null){
		// Add a Step

		$state_id = intval($state_id);

		// Get Step
		$this->State =& ClassRegistry::init('State');
		$this->State->contain(array('Step','Project'));
		$conditions = array('State.id' => $state_id,
							'State.live' => 1);
		$state = $this->State->find('first',compact('conditions'));

		if(empty($state)){
			$this->_Flash('Did not find State','mean',$this->referer('/'));
		}

		// Must be my State
		if($state['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your State','mean',$this->referer('/'));
		}

		// Add, without needing confirmation
		$this->Step  =& ClassRegistry::init('Step');

		$data = array('state_id' => $state['State']['id'],
					  'order' => count($state['Step']) + 1);
		$this->Step->create();
		if(!$this->Step->save($data)){
			$this->_Flash('Failed saving new Step','mean',$this->referer('/'));
		}

		// Nicely saved
		$this->_Flash('Added new Step','nice',$this->referer('/'));

	}


	function remove($condition_id = null, $code = null){
		// Remove a step


		// NOT WORKING!!
		exit;


		$condition_id = intval($condition_id);

		App::import('Sanitize');
		$code = Sanitize::paranoid($code);

		// Get Condition
		$this->Condition =& ClassRegistry::init('Condition');
		$this->Condition->contain(array('Step.State.Project'));
		$conditions = array('Condition.id' => $condition_id,
							'Condition.live' => 1);
		$condition = $this->Condition->find('first',compact('conditions'));
		
		if(empty($condition)){
			$this->_Flash('Unable to find Condition','mean',$this->referer('/'));
		}

		// Must be my Condition
		if($condition['Step']['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Condition','mean',$this->referer('/'));
		}

		// Verify Code
		$expected_code = md5('test'.$condition['Condition']['id'].'test'); 
		if($code != $expected_code){
			$this->_Flash('Codes did not match','mean',$this->referer('/'));
		}

		// Move to live=0
		$condition['Condition']['live'] = 0;

		// Re-order
		// - necessary? Just keep deleting shit (lol)
		if(!$this->Condition->save($condition['Condition'],false,array('id','live'))){
			$this->_Flash('Failed removing Condition','mean',null);
			return;
		}

		// Changes saved
		echo jsonSuccess();
		exit;

		$this->_Flash('Changes saved','nice',$this->referer('/'));

	}


	function move(){
		// Move a Condition somewhere



	}

}

?>