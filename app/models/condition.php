<?php

class Condition extends AppModel {

	var $actsAs = array('Sequence' => array('group_fields' => 'step_id', 'start_at' => 1));

	// RELATIONSHIPS
	var $belongsTo = array('Step');


	// FUNCTIONS

	


}
