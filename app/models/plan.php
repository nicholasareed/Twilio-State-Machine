<?php

class Plan extends AppModel {

	var $useTable = false;


	// RELATIONSHIPS
	//var $belongsTo = array('User');


	// FUNCTIONS

	function getPlans(){

		$plans = array(array('name' => 'Startup',
							 'key' => 'pro1',
							 'cost' => '$10/mo',
							 'cost_in_cents' => 1000,
							 'numbers' => '1 Phone Number',
							 'numbers_count' => 1,
							 'messages' => '200 SMS Messages',
							 'messages_count' => 200,
							 'desc' => 'Perfect for starting out'
							 ),
					   array('name' => 'Silver',
							 'key' => 'pro5',
							 'cost' => '$50/mo',
							 'cost_in_cents' => 5000,
							 'numbers' => '5 Phone Numbers',
							 'numbers_count' => 5,
							 'messages' => '1,500 SMS Messages',
							 'messages_count' => 1500,
							 'desc' => 'Best for businesses'
							 ),
					   array('name' => 'Gold',
							 'key' => 'pro50',
							 'cost' => '$300/mo',
							 'cost_in_cents' => 30000,
							 'numbers' => '50 Phone Numbers',
							 'numbers_count' => 50,
							 'messages' => '10,000 SMS Messages',
							 'messages_count' => 10000,
							 'desc' => 'Developer-centric'
							 )
					  	);
		$plans = array_combine(Set::extract($plans,'{n}.key'),$plans);
		return $plans;
	}


}
