<?php

class AppData extends AppModel {
	// Hosted elsewhere (MongoLab?)

	var $useDbConfig = 'mongo';
	var $useTable = false; // Will be changed later


	// RELATIONSHIPS
	

	// FUNCTIONS

	function getAppDatabase($project_id = null){
		// Gets the application database (collection) from MongoLab

		// Set collection to use
		//$this->useTable = 'application_'.$project_id;

		$collection_name = 'application_'.$project_id;

		App::import('Core','HttpSocket');
		$HttpSocket =& new HttpSocket();

		$url = 'https://api.mongolab.com/api/1/databases/'.MONGO_DB.'/collections?apiKey='.MONGO_API_KEY;
		$collections = $HttpSocket->get($url);
		$collections = json_decode($collections);
		
		// Collection exists?
		if(!in_array($collection_name,$collections)){
			// Create it!
			// - even need to do this?
			// - the query returns it as an empty Object, so no real need to first create it
		}

		// Get the collection
		$url = 'https://api.mongolab.com/api/1/databases/'.MONGO_DB.'/collections/'.$collection_name.'?apiKey='.MONGO_API_KEY;
		$collection = $HttpSocket->get($url);
		pr($collection);
		$collection = json_decode($collection);

		
		return $collection;

	}
	


}
