<?php

class State extends AppModel {

	// RELATIONSHIPS
	var $belongsTo = array('Project');
	var $hasMany = array('Step' => array('conditions' => array('Step.live' => 1),
										 'order' => 'Step.order ASC'));


	// FUNCTIONS

	var $validate = array('key' => array('rule' => 'notEmpty',
										 'required' => true,
										 'allowEmpty' => false,
										 'message' => 'Please include a state key value'));
	


}
