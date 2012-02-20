<?php

class User extends AppModel {

	// RELATIONSHIPS


	// RELATIONSHIPS
	var $belongsTo = array('Role');
	var $hasMany = array('Project');
	var $hasOne = array('Profile');


	// FUNCTIONS

	


}
