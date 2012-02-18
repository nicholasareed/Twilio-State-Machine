<?php

class User extends AppModel {

	// RELATIONSHIPS


	// RELATIONSHIPS
	var $belongsTo = array('Role');
	var $hasOne = array('Profile','Attendee');
	var $hasMany = array('Service');


	// FUNCTIONS

	


}
