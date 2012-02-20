<?php

class Step extends AppModel {

	// RELATIONSHIPS
	var $belongsTo = array('State');
	var $hasMany = array('Condition' => array('conditions' => array('Condition.live' => 1),
												'order' => 'Condition.order ASC'),
						 'Action' => array('conditions' => array('Action.live' => 1),
						 					'order' => 'Action.order ASC'));
	

	// FUNCTIONS

	


}
