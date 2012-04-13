<?php

class Step extends AppModel {

	var $actsAs = array('Sequence' => array('group_fields' => 'state_id', 'start_at' => 1));

	// RELATIONSHIPS
	var $belongsTo = array('State');
	var $hasMany = array('Condition' => array('conditions' => array('Condition.live' => 1),
												'order' => 'Condition.order ASC'),
						 'Action' => array('conditions' => array('Action.live' => 1),
						 					'order' => 'Action.order ASC'));
	

	// FUNCTIONS

	


}
