<?php

class ActionsController extends AppController {

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
		// Add an Action

		$step_id = intval($step_id);

		// Get Step
		$this->Step =& ClassRegistry::init('Step');
		$this->Step->contain(array('Action','State.Project'));
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
		$types = array('send_sms' => 'Send SMS',
						'webhook' => 'Webhook (http request to a server)',
						'attribute' => 'Set Attribute',
						'state' => 'Set State',
						);

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
		$data['type'] = $this->data['Action']['type'];

		if(!array_key_exists($data['type'],$types)){
			$this->_Flash('Please choose an Action Type','mean',null);
			return;
		}

		$type_chosen = $data['type'];
		$this->set(compact('type_chosen'));

		if($submitted_step != 'submitted_all'){
			return;
		}


		App::import('Sanitize');


		// Evaluate the rest of the stuff
		switch($data['type']){

			case 'send_sms':
				$data['input1'] = $this->data['Action']['input1'];
				$data['send_sms_recipients'] = $this->data['Action']['send_sms_recipients'];
				$data['send_sms_later_time_text'] = $this->data['Action']['send_sms_later_time_text'];
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),array('{','}')));
				$data['send_sms_recipients'] = Sanitize::paranoid($data['send_sms_recipients'],array_merge(Configure::read('regex_chars'),array('{','}')));
				$data['send_sms_later_time_text'] = Sanitize::paranoid($data['send_sms_later_time_text'],array_merge(Configure::read('regex_chars'),array('{','}')));
				break;

			case 'webhook':
				$data['input1'] = $this->data['Action']['input1'];
				$data['webhook_can_modify_vars'] = intval($this->data['Action']['webhook_can_modify_vars']) ? 1 : 0;
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),Configure::read('http_chars')));
				break;

			case 'attribute':
				$data['input1'] = $this->data['Action']['input1'];
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),Configure::read('http_chars'),array('{','}',',')));
				break;

			case 'state':
				$data['input1'] = $this->data['Action']['input1'];
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),Configure::read('http_chars'),array('{','}')));
				break;

			case 'default':
				
				break;

			default:
				$this->_Flash('Unable to find type','mean',null);
				return;

		}

		// Get next $order
		$data['order'] = count($step['Action']) + 1;

		// Validation
		$this->Action =& ClassRegistry::init('Action');
		$this->Action->set($data);

		if(!$this->Action->validates()){
			$this->_Flash('Please fix errors','mean',null);
			return;
		}

		// Save
		$this->Action->create();
		if(!$this->Action->save($data)){
			$this->_Flash('There were errors saving your Action, please try again','mean',null);
			return;
		}

		$data['id'] = $this->Action->id;

		// Echo out JSON
		// - newer method of returning
		echo json_encode($data);
		exit;

		// Redirect
		$this->_Flash('Action Added','nice','/projects/view/'.$step['State']['Project']['id']);

	}


	function edit($action_id = null){
		// Edit an Action

		$action_id = intval($action_id);

		// Get Action
		$this->Action =& ClassRegistry::init('Action');
		$this->Action->contain(array('Step.State.Project'));
		$conditions = array('Action.id' => $action_id,
							'Action.live' => 1);
		$action = $this->Action->find('first',compact('conditions'));

		if(empty($action)){
			$this->_Flash('Unable to find Action','mean',$this->referer('/'));
		}

		// Must be my Action
		if($action['Step']['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Action','mean',$this->referer('/'));
		}

		// Types
		$types = array('send_sms' => 'Send SMS',
						'webhook' => 'Webhook (http request to a server)',
						'attribute' => 'Set Attribute',
						'state' => 'Set State',
						);

		$type_chosen = $action['Action']['type'];

		$this->set(compact('action','types','type_chosen'));


		if($this->RequestHandler->isGet()){
			$this->data = $action;
			return;
		}

		// Parse input
		// - type cannot be changed

		App::import('Sanitize');

		$data = array();
		$data['id'] = $action['Action']['id'];

		switch($action['Action']['type']){

			case 'send_sms':
				$data['input1'] = $this->data['Action']['input1'];
				$data['send_sms_recipients'] = $this->data['Action']['send_sms_recipients'];
				$data['send_sms_later_time_text'] = $this->data['Action']['send_sms_later_time_text'];
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),array('{','}')));
				$data['send_sms_recipients'] = Sanitize::paranoid($data['send_sms_recipients'],array_merge(Configure::read('regex_chars'),array('{','}')));
				$data['send_sms_later_time_text'] = Sanitize::paranoid($data['send_sms_later_time_text'],array_merge(Configure::read('regex_chars'),array('{','}')));
				break;

			case 'webhook':
				$data['input1'] = $this->data['Action']['input1'];
				$data['webhook_can_modify_vars'] = intval($this->data['Action']['webhook_can_modify_vars']) ? 1 : 0;
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),Configure::read('http_chars')));
				break;

			case 'attribute':
				$data['input1'] = $this->data['Action']['input1'];
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),Configure::read('http_chars'),array('{','}',',')));
				break;

			case 'state':
				$data['input1'] = $this->data['Action']['input1'];
				$data['input1'] = Sanitize::paranoid($data['input1'],array_merge(Configure::read('regex_chars'),Configure::read('http_chars'),array('{','}')));
				break;

			case 'default':
				
				break;

			default:
				$this->_Flash('Unable to find type','mean',null);
				return;

		}

		// Save
		$this->Action->create();
		if(!$this->Action->save($data)){
			$this->_Flash('There were errors saving your Action, please try again','mean',null);
			return;
		}

		// Echo out JSON
		// - newer method of returning
		echo jsonSuccess(array_merge($action['Action'],$data));
		exit;

		// Redirect
		$this->_Flash('Changes Saved','nice','/projects/view/'.$action['Step']['State']['Project']['id']);

	}


	function remove($action_id = null, $code = null){
		// Remove an Action

		if($this->RequestHandler->isGet()){
			echo jsonError(101,'Expecting POST');
			exit;
		}

		$action_id = intval($action_id);

		App::import('Sanitize');
		$code = Sanitize::paranoid($code);

		// Get Action
		$this->Action =& ClassRegistry::init('Action');
		$this->Action->contain(array('Step.State.Project'));
		$conditions = array('Action.id' => $action_id,
							'Action.live' => 1);
		$action = $this->Action->find('first',compact('conditions'));

		if(empty($action)){
			$this->_Flash('Unable to find Action','mean',$this->referer('/'));
		}

		// Must be my Action
		if($action['Step']['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Action','mean',$this->referer('/'));
		}

		// Verify Code - NOT WORKING
		$expected_code = md5('test'.$action['Action']['id'].'test'); 
		if($code != $expected_code){
			//$this->_Flash('Codes did not match','mean',$this->referer('/'));
		}

		// Move to live=0
		$action['Action']['live'] = 0;

		// Re-order
		// - necessary? Just keep deleting shit (lol)
		if(!$this->Action->save($action['Action'],false,array('id','live'))){
			$this->_Flash('Failed removing Action','mean',null);
			return;
		}

		// Changes saved
		echo jsonSuccess();
		exit;

		$this->_Flash('Changes saved','nice',$this->referer('/'));

	}


	function move($action_id = null, $order = null, $step_id = null){
		// Move a Action somewhere

		$action_id = intval($action_id);
		$order = intval($order);
		$step_id = intval($step_id); // Only used when moving to a new Step
		
		// Re-order every element (right?)

		if($this->RequestHandler->isGet()){
			echo jsonError(101,'Expecting POST');
			exit;
		}

		// Get Action
		$this->Action =& ClassRegistry::init('Action');
		$this->Action->contain(array('Step.State.Project'));
		$conditions = array('Action.id' => $action_id,
							'Action.live' => 1);
		$action = $this->Action->find('first',compact('conditions'));
		
		if(empty($action)){
			$this->_Flash('Unable to find Action','mean',$this->referer('/'));
		}

		// Must be my Action
		if($action['Step']['State']['Project']['user_id'] != $this->DarkAuth->id){
			$this->_Flash('Not your Action','mean',$this->referer('/'));
		}

		// Moving Steps?
		$this->Step =& ClassRegistry::init('Step');
		if($step_id != $action['Action']['step_id']){
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

			$action['Action']['step_id'] = $step['Step']['id'];

		}

		$action['Action']['order'] = $order;

		$this->Action->save($action['Action']);

		echo jsonSuccess();
		exit;

	}

}

?>