<?php

class Phone extends AppModel {
	// A "Phone" is the incoming PTN and id

	// RELATIONSHIPS
	var $belongsTo = array('Step');
	var $hasAndBelongsToMany = array('Project');


	// FUNCTIONS

	


}
