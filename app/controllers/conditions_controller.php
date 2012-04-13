<?php

class ConditionsController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array('member'));

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		App::import('Vendor', 'Markdown', array('file' => 'Markdown/markdown.php'));
		
		$this->Help =& ClassRegistry::init('Help');
		$conditions = array('Help.live' => 1);
		$helps = $this->Help->find('all',compact('conditions'));
		$helps = array_combine(Set::extract($helps,'{n}.Help.key'),$helps);
		$this->set(compact('helps'));

		parent::beforeFilter();
	}


	function add($step_id = null){
		// Add a Condition

		$step_id = intval($step_id);

		// Get Step
		$this->Step =& ClassRegistry::init('Step');
		$this->Step->contain(array('Condition','State.Project'));
		$conditions = array('Step.id' => $step_id,
							'Step.live' => 1);
		$step = $this->Step->find('first',compact('conditions'));

		if(empty($step)){
			$this->_Flash('Did not find Step','mean',$this->referer('/'));
		}

		// Must be my Step
		if($step['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Step','mean',$this->referer('/'));
		}

		// Types
		$types = array('starts_with' => 'Starts with',
						'contains' => 'Contains',
						'regex' => 'Regular Expression Match',
						'word_count' => 'Word Count',
						'attribute' => 'User Attribute',
						'default' => 'Default');

		$this->set(compact('types'));

		
		if($this->RequestHandler->isGet()){
			return;
		}


		// Parse input
		// - first, comes back as a $type

		$submitted_step = $this->data['Hidden']['step'];

		// Get the submitted $type first
		$data = array();
		$data['step_id'] = $step['Step']['id'];
		$data['type'] = $this->data['Condition']['type'];

		if(!array_key_exists($data['type'],$types)){
			$this->_Flash('Please choose a Condition Type','mean',null);
			return;
		}

		$type_chosen = $data['type'];
		$this->set(compact('type_chosen'));

		if($data['type'] == 'default'){
			// Default step automatically adds
			$submitted_step = 'submitted_all';
		}

		if($submitted_step != 'submitted_all'){
			return;
		}


		App::import('Sanitize');


		// Evaluate the rest of the stuff
		switch($data['type']){

			case 'starts_with':
				$data['input1'] = $this->data['Condition']['input1'];
				$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']) ? 1 : 0;
				$data['input1'] = Sanitize::paranoid($data['input1'],Configure::read('regex_chars'));
				//$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']);
				break;

			case 'contains':
				$data['input1'] = $this->data['Condition']['input1'];
				$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']) ? 1 : 0;
				$data['input1'] = Sanitize::paranoid($data['input1'],Configure::read('regex_chars'));
				//$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']);
				break;

			case 'regex':
				$this->_Flash('Regular Expression Matching not available yet','mean',false);
				return;

			case 'word_count':
				$tmp = trim($this->data['Condition']['input1']);
				$tmp1 = explode('|',$tmp);
				$tmp2 = array();
				foreach($tmp1 as $value){
					$tmp2[] = intval($value);
				}
				$tmp2 = array_unique($tmp2);
				
				$data['input1'] =implode('|',$tmp2);
				if($data['input1'] != $tmp){
					$this->_Flash('Invalid characters included. Result does not match input. Result="'.$data['input1'].'"','mean',null);
					return;
				}
				break;

			case 'attribute':
				$initial = trim($this->data['Condition']['input1']);
				$tmp_conditions = explode(',',$initial);
				$result = array();
				foreach($tmp_conditions as $key => $tmp_cond){
					// Parse attribute as best as possible
					$tmp = explode('=',trim($tmp_cond));
					if(count($tmp) != 2){
						$this->_Flash('Missing an = sign','mean',null);
						return;
					}
					// Left side is attribute, Right side is value (or | "pipe" separated values)
					$left = Sanitize::paranoid($tmp[0],array('.'));
					$right = Sanitize::paranoid($tmp[1],array('.','|',',',' '));
					if(empty($left)){
						$this->_Flash('Invalid formatting submitted','mean',null);
						return;
					}
					$result[] = $left.'='.$right;
				}
				
				$data['input1'] = implode(',',$result);
				if($data['input1'] != $initial){
					$this->_Flash('Invalid characters included. Result does not match input. Result="'.$data['input1'].'"','mean',null);
					return;
				}
				$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']) ? 1 : 0;
				break;

			case 'default':
				
				break;

			default:
				$this->_Flash('Unable to find type','mean',null);
				return;

		}

		// Get next $order
		$data['order'] = count($step['Condition']) + 1;

		// Validation
		$this->Condition =& ClassRegistry::init('Condition');
		$this->Condition->set($data);

		if(!$this->Condition->validates()){
			$this->_Flash('Please fix errors','mean',null);
			return;
		}

		// Save
		$this->Condition->create();
		if(!$this->Condition->save($data)){
			$this->_Flash('There were errors saving your Condition, please try again','mean',null);
			return;
		}

		$data['id'] = $this->Condition->id;


		// Echo out JSON
		// - newer method of returning
		echo json_encode($data);
		exit;

		// Redirect
		$this->_Flash('Condition Added','nice','/projects/view/'.$step['State']['Project']['id']);

	}


	function edit($condition_id = null){
		// Edit a Condition

		$condition_id = intval($condition_id);

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

		// Types
		$types = array('starts_with' => 'Starts with...',
						'contains' => 'Contains',
						'regex' => 'Regular Expression Match',
						'word_count' => 'Word Count',
						'attribute' => 'User Attribute',
						'default' => 'Default');

		$type_chosen = $condition['Condition']['type'];

		$this->set(compact('condition','types','type_chosen'));


		if($this->RequestHandler->isGet()){
			$this->data = $condition;
			return;
		}

		// Parse input
		// - type cannot be changed

		App::import('Sanitize');

		$data = array();
		$data['id'] = $condition['Condition']['id'];

		switch($condition['Condition']['type']){

			case 'starts_with':
				$data['input1'] = $this->data['Condition']['input1'];
				$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']) ? 1 : 0;
				$data['input1'] = Sanitize::paranoid($data['input1'],Configure::read('regex_chars'));
				//$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']);
				break;

			case 'contains':
				$data['input1'] = $this->data['Condition']['input1'];
				$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']) ? 1 : 0;
				$data['input1'] = Sanitize::paranoid($data['input1'],Configure::read('regex_chars'));
				//$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']);
				break;

			case 'regex':
				$this->_Flash('Regular Expression Matching not available yet','mean',false);
				return;

			case 'word_count':
				$tmp = trim($this->data['Condition']['input1']);
				$tmp1 = explode('|',$tmp);
				$tmp2 = array();
				foreach($tmp1 as $value){
					$tmp2[] = intval($value);
				}
				$tmp2 = array_unique($tmp2);
				
				$data['input1'] =implode('|',$tmp2);
				if($data['input1'] != $tmp){
					$this->_Flash('Invalid characters included. Result does not match input. Result="'.$data['input1'].'"','mean',null);
					return;
				}
				break;

			case 'attribute':
				$initial = trim($this->data['Condition']['input1']);
				$tmp_conditions = explode(',',$initial);
				$result = array();
				foreach($tmp_conditions as $key => $tmp_cond){
					// Parse attribute as best as possible
					$tmp = explode('=',trim($tmp_cond));
					if(count($tmp) != 2){
						$this->_Flash('Missing an = sign','mean',null);
						return;
					}
					// Left side is attribute, Right side is value (or | "pipe" separated values)
					$left = Sanitize::paranoid($tmp[0],array('.'));
					$right = Sanitize::paranoid($tmp[1],array('.','|',',',' '));
					if(empty($left)){
						$this->_Flash('Invalid formatting submitted','mean',null);
						return;
					}
					$result[] = $left.'='.$right;
				}
				
				$data['input1'] = implode(',',$result);
				if($data['input1'] != $initial){
					$this->_Flash('Invalid characters included. Result does not match input. Result="'.$data['input1'].'"','mean',null);
					return;
				}
				$data['case_sensitive'] = intval($this->data['Condition']['case_sensitive']) ? 1 : 0;
				break;

			case 'default':
				
				break;

			default:
				$this->_Flash('Unable to find type','mean',null);
				return;

		}

		// Save
		$this->Condition->create();
		if(!$this->Condition->save($data)){
			echo jsonError(101,'There were errors saving your condition');
			exit;
			$this->_Flash('There were errors saving your Condition, please try again','mean',null);
			return;
		}

		// Echo out JSON
		// - newer method of returning
		echo jsonSuccess(array_merge($condition['Condition'],$data));
		exit;

		// Redirect
		$this->_Flash('Changes Saved','nice','/projects/view/'.$condition['Step']['State']['Project']['id']);

	}


	function remove($condition_id = null, $code = null){
		// Remove a Condition

		if($this->RequestHandler->isGet()){
			echo jsonError(101,'Expecting POST');
			exit;
		}

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

		// Verify Code (NOT DOING)
		$expected_code = md5('test'.$condition['Condition']['id'].'test'); 
		if($code != $expected_code){
			//$this->_Flash('Codes did not match','mean',$this->referer('/'));
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


	function move($condition_id = null, $order = null, $step_id = null){
		// Move a Condition somewhere

		$condition_id = intval($condition_id);
		$order = intval($order);
		$step_id = intval($step_id); // Only used when moving to a new Step
		
		// Re-order every element (right?)

		if($this->RequestHandler->isGet()){
			echo jsonError(101,'Expecting POST');
			exit;
		}

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

		// Moving Steps?
		$this->Step =& ClassRegistry::init('Step');
		if($step_id != $condition['Condition']['step_id']){
			// Validate the new step
			$this->Step->contain(array('State.Project'));
			$conditions = array('Step.id' => $step_id,
								'Step.live' => 1);
			$step = $this->Step->find('first',compact('conditions'));

			// Step Exists?
			if(empty($step)){
				echo jsonError(101,'Not in a step');
				exit;
			}

			// My Step?
			if($step['State']['Project']['user_id'] != $this->DarkAuth->id){
				echo jsonError(101,'Not your Step');
				exit;
			}

			$condition['Condition']['step_id'] = $step['Step']['id'];

		}

		$condition['Condition']['order'] = $order;

		$this->Condition->save($condition['Condition']);

		echo jsonSuccess();
		exit;

		// Get actual order
		// - re-order motherfuckers
		$conditions = array('Step.id' => $condition['Condition']['step_id']);
		$steps = $this->Step->find('all',compact('conditions'));


	}


	function copy($condition_id = null){
		// Copy a Condition
		// - gets inserted below the copied one

		if($this->RequestHandler->isGet()){
			echo "expecting POST";
			exit;
		}
		// Fucking awesome

		// Add a Condition

		$condition_id = intval($condition_id);

		// Get Condition to Copy
		$this->Condition =& ClassRegistry::init('Condition');
		$this->Condition->contain(array('Step.State.Project'));
		$conditions = array('Condition.id' => $condition_id,
							'Condition.live' => 1);
		$condition = $this->Condition->find('first',compact('conditions'));

		if(empty($condition)){
			$this->_Flash('Did not find Condition','mean',$this->referer('/'));
		}

		// Must be my Condition
		if($condition['Step']['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Condition','mean',$this->referer('/'));
		}

		// Remove id
		unset($condition['Condition']['id']);

		// Save
		$this->Condition->create();
		if(!$this->Condition->save($condition['Condition'])){
			$this->_Flash('There were errors copying your Condition, please try again','mean',null);
			return;
		}

		$condition['Condition']['id'] = $this->Condition->id;


		// Echo out JSON
		// - newer method of returning
		echo json_encode($condition['Condition']);
		exit;

	}

}

?>