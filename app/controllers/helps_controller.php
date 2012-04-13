<?php

class HelpsController extends AppController {

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
		// List all
		$this->Help =& ClassRegistry::init('Help');
		$helps = $this->Help->find('all',compact('conditions'));
		$this->set(compact('helps'));
	}


	function add(){
		// Add Help field


		if($this->RequestHandler->isGet()){
			// Insert as defaults
			return;
		}
		

		// Parse input

		$data = array();
		$data['key'] = Inflector::slug($this->data['Help']['key']);
		$data['title'] = Inflector::humanize($this->data['Help']['key']);

		// Validation
		$this->Help =& ClassRegistry::init('Help');

		$this->Help->set($data);

		if(!$this->Help->validates()){
			$this->_Flash('Please fix errors','mean',null);
			return;
		}

		// Save
		$this->Help->create();
		if(!$this->Help->save($data)){
			$this->_Flash('There were errors saving your Help, please try again','mean',null);
			return;
		}

		$help_id = $this->Help->id;

		// Redirect
		$this->_Flash('Changes have been saved','nice','/helps/edit/'.$help_id);

		
	}


	function edit($help_id = null){
		// Edit Help field

		$help_id = intval($help_id);

		// Get Help
		$this->Help =& ClassRegistry::init('Help');

		$this->Help->contain();
		$conditions = array('Help.id' => $help_id);

		$help = $this->Help->find('first',compact('conditions'));

		// No Help?
		if(empty($help)){
			$this->_Flash('Unable to find Help','mean','/helps');
		}

		// Set Values
		$this->set(compact('help'));

		if($this->RequestHandler->isGet()){
			// Insert as defaults
			$this->data = $help;
			return;
		}
		

		// Parse input

		$data = array();
		$data['id'] = $help['Help']['id'];
		$data['markdown'] = $this->data['Help']['markdown'];


		// Validation

		$this->Help->set($data);

		if(!$this->Help->validates()){
			$this->_Flash('Please fix errors','mean',null);
			return;
		}

		// Save
		$this->Help->create();
		if(!$this->Help->save($data)){
			$this->_Flash('There were errors saving your Help, please try again','mean',null);
			return;
		}

		// Redirect
		$this->_Flash('Changes have been saved','nice','/helps/edit/'.$help['Help']['id']);

		
	}

}

?>