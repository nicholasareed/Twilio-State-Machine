<?php

class User extends AppModel {

	// RELATIONSHIPS


	// RELATIONSHIPS
	var $belongsTo = array('Role');
	var $hasMany = array('Project','Twilio');
	var $hasOne = array('Profile');


	// FUNCTIONS

	


}
