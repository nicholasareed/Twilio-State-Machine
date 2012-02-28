<?php

class StatesController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array('member'));

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function add($project_id = null){
		// Add a State


		$project_id = intval($project_id);

		// Get Project
		$this->Project =& ClassRegistry::init('Project');
		$this->Project->contain(array('State'));
		$conditions = array('Project.id' => $project_id,
							'Project.live' => 1);
		$project = $this->Project->find('first',compact('conditions'));

		if(empty($project)){
			$this->_Flash('Did not find Project','mean',$this->referer('/'));
		}

		// Must be my Project
		if($project['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Project','mean',$this->referer('/'));
		}


		if($this->RequestHandler->isGet()){
			return;
		}


		// Parse input
		$data = array();
		$data['project_id'] = $project['Project']['id'];
		$data['live'] = 1;
		$data['key'] = Inflector::slug($this->data['State']['key']);

		// Sanitize
		$methods = array('key' => array('paranoid',array('_')));
		$data = arraySanitize($data,$methods);


		// Validate
		$this->State =& ClassRegistry::init('State');
		$this->State->set($data);

		if(!$this->State->validates()){
			return false;
		}

		// Must be only key
		$conditions = array('State.key' => $data['key'],
							'State.project_id' => $project['Project']['id'],
							'State.live' => 1);
		$state = $this->State->find('first',compact('conditions'));

		if(!empty($state)){
			$this->State->invalidate('key','Key already used');
			return false;
		}

		// Save
		$this->State->create();
		if(!$this->State->save($data)){
			$this->_Flash('Failed saving new State','mean',$this->referer('/'));
		}
		$data['id'] = $this->State->id;

		$pData = array();
		$pData = $data;
		$pData['Step'] = array('Condition' => array(),
							   'Action' => array());

		// Nicely saved
		echo json_encode($pData);
		exit;
		//$this->_Flash('Added new Step','nice',$this->referer('/'));

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