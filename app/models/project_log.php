<?php

class ProjectLog extends AppModel {

	// RELATIONSHIPS
	var $belongsTo = array('Project');

	var $order = 'ProjectLog.id DESC';

	//var $useDbConfig = 'mongo';


	// FUNCTIONS

	


}
