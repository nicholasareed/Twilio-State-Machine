<?php
/**
 * Extend Associations Behavior
 * Extends some basic add/delete function to the HABTM relationship
 * in CakePHP.  Also includes an unbindAll($exceptions=array()) for 
 * unbinding ALL associations on the fly.
 * 
 * This code is loosely based on the concepts from:
 * http://rossoft.wordpress.com/2006/08/23/working-with-habtm-associations/
 * 
 * @author Brandon Parise <brandon@parisemedia.com>
 * @package CakePHP Behaviors
 *
 */
class ExtendAssociationsBehavior extends ModelBehavior {
	/**
	 * Model-specific settings
	 * @var array
	 */
	var $settings = array();
	
	
	/**
	 * Setup
	 * Noething sp
	 *
	 * @param unknown_type $model
	 * @param unknown_type $settings
	 */
	function setup(&$model, $settings = array()) {
		// no special setup required
		$this->settings[$model->name] = $settings;
	}
	
	// Expect $this->Job->searchAssoc('Benefit', $jobArray('active' => 1), $benefitArray('id' => array(1,2,3)), $recursive);
	// Expect $assocArray to only be id_values for now
	
	function searchAssoc(&$model, $assoc = null, $modelArray = array(), $assocArray = array(), $recursive = 1){
		
		/*
		SELECT DISTINCT tags1.post_id
		FROM posts, post_tags AS tags1
		INNER JOIN post_tags AS tags2 ON tags1.post_id = tags2.post_id
		INNER JOIN post_tags AS tags3 ON tags1.post_id = tags3.post_id
		WHERE tags1.tag_id =2
		AND tags2.tag_id =3
		AND tags3.tag_id =4
		AND posts.name LIKE '%active%'
		AND tags1.post_id = posts.id
		
		Important:
		  INNER JOIN for each id I want to search
		
		
		TODO:
			searching by $modelArray
				For example: Job.active = 0
			
			don't only use id's for the assocArray
				For example: JOIN post_tags.tag_id = tags.id, tags.name LIKE %magic%
		
			only 1 array
				For example: array('Job.active' => 1, 'Job.complete'=> 1, 'Benefit.id' => array(1,2,4), 'Benefit.title' => 'LIKE %magic%');
		
		*/
		
		$db =& ConnectionManager::getDataSource($model->useDbConfig);
		$joinTable = $model->hasAndBelongsToMany[$assoc]['joinTable'];	
		$joinTableFullName = $db->fullTableName($joinTable);															// post_tags
		$modelTableFullName = $db->fullTableName($model);																	// posts
		$foreignKey = $model->hasAndBelongsToMany[$assoc]['foreignKey'];									// post_id
		$assocForeignKey = $model->hasAndBelongsToMany[$assoc]['associationForeignKey'];	// tag_id
		
    $query="SELECT DISTINCT tags1.";
    $query.=$foreignKey;
    $query.=" FROM ".$modelTableFullName;								// posts
    $query.=", ".$joinTableFullName." AS tags1";				// post_tags AS tags1
    $inner = '';
    $where = array();
    $i=1;    
    foreach($assocArray as $key => $assoc_id){
    	//  if(there_is_1){we_dont_need_INNER_JOIN} 
    	if($i != 1){
    		$inner .= " INNER JOIN $joinTableFullName AS tags$i ON tags1.$foreignKey = tags$i.$foreignKey";
    	}
    	$where[] = "tags$i.$assocForeignKey =$assoc_id";
    	$i++;
    }
    $where[] = "tags1.$foreignKey = $modelTableFullName.id";
    $query.=$inner;
    $query.= " WHERE ";
    
    $query.=implode(" AND ",$where);
    
    $temp_ids = $model->execute($query);
    $ids= array();
    foreach($temp_ids as $id){
    	$ids[] = $id['tags1'][$foreignKey];
    }
    
    // FOR NOW ONLY RETURN ids
    return $ids;
    
    // Later we can maybe do a find all and not use two queries
    
    $conditions = array($model->alias.'.id' => $ids);
    $model->recursive = $recursive;
    $result = $model->findAll($conditions);
    return $result;
    
    
	}
	
	
	function addAssoc(&$model, $assoc, $assoc_ids, $id = null, $extra = array())
    {
        if ($id != null) {
            $model->id = $id;
        }

        $id = $model->id;

        if (is_array($model->id)) {
            $id = $model->id[0];
        }
        
        if ($model->id !== null && $model->id !== false) {
            $db =& ConnectionManager::getDataSource($model->useDbConfig);
            
            $joinTable = $model->hasAndBelongsToMany[$assoc]['joinTable'];
            $table = $db->name($db->fullTableName($joinTable));
            
            $keys[] = $model->hasAndBelongsToMany[$assoc]['foreignKey'];
            $keys[] = $model->hasAndBelongsToMany[$assoc]['associationForeignKey'];
            foreach($extra as $key => $value){
            	$keys[] = $key;
          	}
            $fields = join(',', $keys);
            
            if(!is_array($assoc_ids)) {
                $assoc_ids = array($assoc_ids);
            }
        
            // to prevent duplicates
            $model->deleteAssoc($assoc,$assoc_ids,$id);
            
            foreach ($assoc_ids as $assoc_id) {
                $values[]  = $db->value($id, $model->getColumnType($model->primaryKey));
                $values[]  = $db->value($assoc_id);
                foreach($extra as $key => $value){                	
		            	$values[] = "'{$value}'";		            	
		          	}
                $values    = join(',', $values);
                
                $db->execute("INSERT INTO {$table} ({$fields}) VALUES ({$values})");                
                unset ($values);
            }
            
            return true;
        } else {
            return false;
        }
    }
    
  function deleteAssoc(&$model, $assoc, $assoc_ids, $id = null)
    {
        if ($id != null) {
            $model->id = $id;
        }

        $id = $model->id;

        if (is_array($model->id)) {
            $id = $model->id[0];
        }
        
        if ($model->id !== null && $model->id !== false) {
            $db =& ConnectionManager::getDataSource($model->useDbConfig);
            
            $joinTable = $model->hasAndBelongsToMany[$assoc]['joinTable'];    
            $table = $db->name($db->fullTableName($joinTable));
            
            $mainKey = $model->hasAndBelongsToMany[$assoc]['foreignKey'];
            $assocKey = $model->hasAndBelongsToMany[$assoc]['associationForeignKey'];
            
            if(!is_array($assoc_ids)) {
                $assoc_ids = array($assoc_ids);
            }
            
            foreach ($assoc_ids as $assoc_id) {
                $db->execute("DELETE FROM {$table} WHERE {$assocKey} = '{$id}' AND {$mainKey} = '{$assoc_id}'");
            }
            return true;
        } else {
            return false;
        }
    }
    
	
	/**
	 * Add an HABTM association
	 *
	 * @param Model $model
	 * @param string $assoc
	 * @param int $id
	 * @param mixed $assoc_ids
	 * @return boolean
	 */
	function habtmAdd_old(&$model, $assoc, $id, $assoc_ids) {
		if(!is_array($assoc_ids)) {
			$assoc_ids = array($assoc_ids);
		}
		
		// make sure the association exists
		if(isset($model->hasAndBelongsToMany[$assoc])) {
			$data = $this->__habtmFind($model, $assoc, $id);
			
			// no data to update
			if(empty($data)) {
				return false;
			}
			
			// important to use array_unique() since merging will add 
			// non-unique values to the array.
			$data[$assoc][$assoc] = array_unique(am($data[$assoc][$assoc], $assoc_ids));
			return $model->save($data);
		}
		
		// association doesn't exist, return false
		return false;
	}
	
	function habtmAdd(&$model, $assoc, $id, $assoc_ids , $extra = array() ) {
		if(!is_array($assoc_ids)) {
		$assoc_ids = array($assoc_ids);
		}
		
		// make sure the association exists
		if(isset($model->hasAndBelongsToMany[$assoc])) {
		
		$data = $this->__habtmFind($model, $assoc, $id);
		
		// no data to update
		if(empty($data)) {
		return false;
		}
		
		// important to use array_unique() since merging will add
		// non-unique values to the array.
		$data[$assoc][$assoc] = array_unique(am($data[$assoc][$assoc], $assoc_ids));
		
		$success = $model->save($data);
		
		// save extra fields
		$joinTable = $model->hasAndBelongsToMany[$assoc]['joinTable'];
		$tablePrefix = $model->tablePrefix;
		$associationForeignKey = $model->hasAndBelongsToMany[$assoc]['associationForeignKey'];
		if( $success && !empty( $joinTable ) && count( $extra) ){
			$sql = array();
			foreach( $extra as $key => $value ){
					if(is_int($value)){
						$sql[] = $key . " = ". addslashes($value);
					} else {
						$sql[] = $key . " = '". addslashes($value) . "'";
					}
				}
			$model->query( "UPDATE $tablePrefix"."$joinTable SET ".implode( "," , $sql )." WHERE $associationForeignKey IN ( ". implode( " , " , $assoc_ids ) ." )");
		}
		
		return $success;
		}
		
		// association doesn't exist, return false
		return false;
	}
	
	function habtmUpdate(&$model, $assoc, $id, $assoc_ids, $extra = array() ) {
		if(!is_array($assoc_ids)) {
		$assoc_ids = array($assoc_ids);
		}
		
		// make sure the association exists
		if(isset($model->hasAndBelongsToMany[$assoc])) {
			$data = $this->__habtmFind($model, $assoc, $id);
			//debug($data);
			
			// no data to update
			if(empty($data)) {
				return false;
			}
			
			// save extra fields
			$joinTable = $model->hasAndBelongsToMany[$assoc]['joinTable'];
			$tablePrefix = $model->tablePrefix;
			$associationForeignKey = $model->hasAndBelongsToMany[$assoc]['associationForeignKey'];
			$foreignKey = $model->hasAndBelongsToMany[$assoc]['foreignKey'];
			$thisid = $data[$model->name]['id'];
			
			if( !empty( $joinTable ) && count( $extra) ){
				$sql = array();
				foreach( $extra as $key => $value ){
					if(is_int($value)){
						$sql[] = $key . " = ". addslashes($value);
					} else {
						$sql[] = $key . " = '". addslashes($value) . "'";
					}
				}				
				$success = $model->query( "UPDATE $tablePrefix"."$joinTable SET ".implode( "," , $sql )." WHERE $foreignKey = $thisid AND $associationForeignKey IN ( ". implode( " , " , $assoc_ids ) ." )");
			}
			return $success;
		}
		// association doesn't exist, return false
		return false;
	}
	
	/**
	 * Delete an HABTM Association
	 *
	 * @param Model $model
	 * @param string $assoc
	 * @param int $id
	 * @param mixed $assoc_ids
	 * @return boolean
	 */
	 
	 // Cant use bindModel and expect this to work
	function habtmDelete(&$model, $assoc, $id, $assoc_ids) {
		if(!is_array($assoc_ids)) {
			$assoc_ids = array($assoc_ids);
		}
		
		// make sure the association exists
		if(isset($model->hasAndBelongsToMany[$assoc])) {
			$data = $this->__habtmFind($model, $assoc, $id);
			
			// no data to update
			if(empty($data)) {
				return false;
			}
						
			// if the * (all) is set then we want to delete all
			if($assoc_ids[0] == '*') {
				$data[$assoc][$assoc] = array();
			} else {
				// use array_diff to see what values we DONT want to delete
				// which is the ones we want to re-save.
				$data[$assoc][$assoc] = array_diff($data[$assoc][$assoc], $assoc_ids);				
			}
			return $model->save($data);
		}
		
		// association doesn't exist, return false		
		return false;
	}
		
	/**
	 * Delete All HABTM Associations
	 * Just a nicer way to do easily delete all.
	 *
	 * @param Model $model
	 * @param string $assoc
	 * @param int $id
	 * @return boolean
	 */
	function habtmDeleteAll(&$model, $assoc, $id) {
		return $this->habtmDelete($model, $assoc, $id, '*');
	}
	
	/**
	 * Find 
	 * This method allows cake to do the dirty work to 
	 * fetch the current HABTM association.
	 *
	 * @param Model $model
	 * @param string $assoc
	 * @param int $id
	 * @return array
	 */	
	function __habtmFind(&$model, $assoc, $id) {
		// temp holder for model-sensitive params
		$tmp_recursive = $model->recursive;
		$tmp_cacheQueries = $model->cacheQueries;
		
		$model->recursive = 1;
		$model->cacheQueries = false;
		
		// unbind all models except the habtm association
		$this->unbindAll($model, array('hasAndBelongsToMany' => array($assoc)));
		$data = $model->find(array($model->name.'.'.$model->primaryKey => $id));
			
		$model->recursive = $tmp_recursive;
		$model->cacheQueries = $tmp_cacheQueries;
		
		if(!empty($data)) {
			// use Set::extract to extract the id's ONLY of the $assoc
			$data[$assoc] = array($assoc => Set::extract($data, $assoc.'.{n}.'.$model->primaryKey));
		}
		
		return $data;
	}
	
	/**
	 * UnbindAll with Exceptions
	 * Allows you to quickly unbindAll of a model's 
	 * associations with the exception of param 2.
	 *
	 * Usage:
	 *   $this->Model->unbindAll(); // unbinds ALL
	 *   $this->Model->unbindAll(array('hasMany' => array('Model2')) // unbind All except hasMany-Model2
	 * 
	 * @param Model $model
	 * @param array $exceptions
	 */
	function unbindAll(&$model, $exceptions = array()) {
		$unbind = array();
		foreach($model->__associations as $type) {
			foreach($model->{$type} as $assoc=>$assocData) {
				// if the assoc is NOT in the exceptions list then
				// add it to the list of models to be unbound.
				if(@!in_array($assoc, $exceptions[$type])) {
					$unbind[$type][] = $assoc;
				}
			}
		}
		// if we actually have models to unbind
		if(count($unbind) > 0) {
			$model->unbindModel($unbind);
		}
	}
	
	function considerModel(&$model, $params = array())
	{
		if( !is_array($params) )
			$params = array($params);
		
		$classname = get_class($this); // for debug output
		
		foreach($model->__associations as $ass)
		{
			if(!empty($this->{$ass}))
			{
				// This model has an association ‘$ass’ defined (like ‘hasMany’, …)
				
				$model->__backAssociation[$ass] = $this->{$ass};
				
				foreach($this->{$ass} as $model => $detail)
				{
					if(!in_array($model,$params))
					{
						//debug("Ignoring association $classname $ass $model ");
						$model->__backAssociation = array_merge($model->__backAssociation, $this->{$ass});
						unset($this->{$ass}[$model]);
					}				
				}			
			}
		}
		
		return true;
	}
	
	
	function habtmNick(&$model,$assoc,$id,$assoc_ids,$extra = array()) {
		$db =& ConnectionManager::getDataSource($model->useDbConfig);
	
		if(!is_array($assoc_ids)){
			$assoc_ids = array($assoc_ids);
		}
		if (isset($model->hasAndBelongsToMany[$assoc])) {
			list($join) = $model->joinModel($model->hasAndBelongsToMany[$assoc]['with']);
			$conditions = array($join . '.' . $model->hasAndBelongsToMany[$assoc]['foreignKey'] => $id);
			$links = array();

			if ($model->hasAndBelongsToMany[$assoc]['unique']) {
				//$model->{$join}->deleteAll($conditions);				
			} else {
				list($recursive, $fields) = array(-1, $model->hasAndBelongsToMany[$assoc]['associationForeignKey']);
				$links = Set::extract(
					$model->{$join}->find('all', compact('conditions', 'recursive', 'fields')),
					"{n}.{$join}." . $model->hasAndBelongsToMany[$assoc]['associationForeignKey']
				);
			}
			foreach($assoc_ids as $ids){
				
				$newValues = array();				
				$values  = array(
					$db->value($id, $model->getColumnType($model->primaryKey)),
					$db->value($ids)
				);

				$fields = array(
						$db->name($model->hasAndBelongsToMany[$assoc]['foreignKey']),
						$db->name($model->hasAndBelongsToMany[$assoc]['associationForeignKey'])
					);
				foreach($extra as $k => $v){
					$values[] = $db->value($v, $model->getColumnType($k));
					$fields[] = $db->name($k);
				}
				$values  = join(',', $values);
				$newValues[] = "({$values})";
				unset($values);				
				if (!empty($newValues)) {
					$fields = join(',', $fields);	
					$db->insertMulti($model->{$join}, $fields, $newValues);
				}
			}
		}
	}
	
	
}
?>