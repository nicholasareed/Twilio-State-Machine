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
		$data['id'] = $this->Step->id;
		$data['Condition'] = array();
		$data['Action'] = array();

		// Nicely saved
		echo json_encode($data);
		exit;
		//$this->_Flash('Added new Step','nice',$this->referer('/'));

	}


	function remove($step_id = null, $code = null){
		// Remove a step

		if($this->RequestHandler->isGet()){
			return;
		}

		$step_id = intval($step_id);

		App::import('Sanitize');
		$code = Sanitize::paranoid($code);

		// Get Step
		$this->Step =& ClassRegistry::init('Step');
		$this->Step->contain(array('State.Project'));
		$conditions = array('Step.id' => $step_id,
							'Step.live' => 1);
		$step = $this->Step->find('first',compact('conditions'));
		
		if(empty($step)){
			$this->_Flash('Unable to find Step','mean',$this->referer('/'));
		}

		// Must be my Step
		if($step['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Step','mean',$this->referer('/'));
		}

		// Verify Code
		$expected_code = md5('test'.$step['Step']['id'].'test'); 
		if($code != $expected_code){
			//$this->_Flash('Codes did not match','mean',$this->referer('/'));
		}

		// Move to live=0
		$step['Step']['live'] = 0;

		// Re-order
		// - necessary? Just keep deleting shit (lol)
		if(!$this->Step->save($step['Step'],false,array('id','live'))){
			$this->_Flash('Failed removing Step','mean',null);
			return;
		}

		// Changes saved
		echo jsonSuccess();
		exit;

		$this->_Flash('Changes saved','nice',$this->referer('/'));

	}


	function move($step_id = null, $order = null, $state_id = null){
		// Move a Step somewhere

		$step_id = intval($step_id);
		$order = intval($order);
		$state_id = intval($state_id); // Only used when moving to a new Step
		
		// Re-order every element (right?)

		if($this->RequestHandler->isGet()){
			echo jsonError(101,'Expecting POST');
			exit;
		}

		// Get Step
		$this->Step =& ClassRegistry::init('Step');
		$this->Step->contain(array('State.Project'));
		$conditions = array('Step.id' => $step_id,
							'Step.live' => 1);
		$step = $this->Step->find('first',compact('conditions'));
		
		if(empty($step)){
			$this->_Flash('Unable to find Step','mean',$this->referer('/'));
		}

		// Must be my Step
		if($step['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Step','mean',$this->referer('/'));
		}

		// Moving States?
		$this->State =& ClassRegistry::init('State');
		if($state_id != $step['Step']['state_id']){
			// Validate the new step
			$this->State->contain(array('Project'));
			$conditions = array('State.id' => $state_id,
								'State.live' => 1);
			$state = $this->State->find('first',compact('conditions'));

			// Step Exists?
			if(empty($state)){
				echo jsonError(101,'Not in a State');
				exit;
			}

			// My State?
			if($state['Project']['user_id'] != $this->DarkAuth->id){
				echo jsonError(101,'Not your State');
				exit;
			}

			$step['Step']['state_id'] = $state['State']['id'];

		}

		$step['Step']['order'] = $order;

		$this->Step->save($step['Step']);

		echo jsonSuccess();
		exit;

	}

}

?>