<?php

class State extends AppModel {

	// RELATIONSHIPS
	var $belongsTo = array('Project');
	var $hasMany = array('Step' => array('conditions' => array('Step.live' => 1),
										 'order' => 'Step.order ASC'));


	// FUNCTIONS

	


}
