<?php

class Invite extends AppModel {

	// RELATIONSHIPS


	// RELATIONSHIPS


	// FUNCTIONS

	
	// VALIDATION

	var $validate = array('email' => array('rule1' => array('rule' => 'email',
															'message' => 'Please enter a valid Email Address for us to contact you'),
										   'rule2' => array('rule' => 'isUnique',
										   					'message' => 'We already have your Email Address!')));


}
