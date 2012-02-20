<?php

class Project extends AppModel {

	// RELATIONSHIPS
	var $belongsTo = array('User');
	var $hasMany = array('State','Twilio');
	var $hasAndBelongsToMany = array('Phone');


	// FUNCTIONS

	


}
