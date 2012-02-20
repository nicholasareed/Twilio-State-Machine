<?php  
class DarkAuthComponent extends Object { 

	var $user_model_name = 'User'; 
	var $user_name_field = 'email'; //e.g. email or firstname or username... 
	var $user_name_case_folding = 'lower'; //do you want to case fold the username before verifying? either 'lower','upper','none', to change case to lower/upper/leave it alone before matching.
	var $user_pass_field = 'password'; 
	var $user_live_field = 'live'; // surely you have a field in you users table to show whether the user is active or not? set to null if not.
	var $user_live_value = 1; 
	var $group_model_name = 'Role'; //Group for access control if used, if not used please set to an empty string. NB: DON'T CALL requiresAuth with Groups if no group model. it will error.
	var $group_name_field = 'name'; // the name of the field used for the groups name. This will be used to check against passed groups.
	var $HABTM = false; //set to false if you don't use a HABTM group relationship. Ignore if no association. 
	var $superuser_group = 'Root'; //if you want a single group to have automatically granted access to any restriction. 
	var $login_view = '/login';  //this is the login view, usually {user_controller}/login but you may have changed the routes.
	var $deny_view = '/deny';  //this is the default denied access view. 
	var $logout_page = '/'; // NB this is were to redirect AFTER logout by default 
	var $login_failed_message = '<p class="error">Login Failed, Please check your details and try again.</p>'; //This message is setFlash()'d on failed login. 
	var $logout_message = '<p class="success">You have been succesfully logged out.</p>'; //Message to setFlash after logout. 
	var $allow_cookie = false; //Allow use of cookies to remember authenticated sessions. 
	var $cookie_expiry = '+6 Months'; //how long until cookies expire. format is "strtotime()" based (http://php.net/strtotime).
	var $session_secure_key = 'sRm298sdf9dAdlxBy'; //some random stuff that someone is unlikey to guess.  

	/* 
	* You can edit this function to explain how you want to hash your passwords. 
	* Also you can use it as a static function in your controller to hash passwords beforeSave 
	*/ 
	function hasher($plain_text){

		// No passwords during testing
		return $plain_text;


		$cost = 10;

		// brcypt implementation
		$salt = substr(Configure::read('Security.salt'), 0, 22);
		return crypt($plain_text, '$2a$' . $cost . '$' . $salt);

		// Old md5 hasher
		$hashed = md5('hallo'.$plain_text.'deez'); 
		return $hashed; 
	} 

	########################################################################## 
	/* 
	* DON'T EDIT THESE OR ANYTHING BELOW HERE UNLESS YOU KNOW WHAT YOU'RE DOING 
	*/ 
	var $controller; 
	var $here; 
	var $components=array('Session'); 
	var $current_user; 
	var $from_session; 
	var $from_post; 
	var $from_cookie; 

	var $DA; // all DarkAuth variables that are passed to the View
	var $li; // is the User logged in or not?
	var $id; // user_id of logged in User

	
  	function initialize(&$controller){

		$this->controller = $controller; 
		
		// Initilize the User Model: ClassRegistry::init('User')
		$this->controller->{$this->user_model_name} =& ClassRegistry::init($this->user_model_name);

		$this->here = substr($this->controller->here,strlen($this->controller->base)); 
		 
		// Fuck this little guy
		//$this->controller->_login(); 
		 
		// Now check session/cookie info. 
		$this->getUserInfoFromSessionOrCookie(); 
		
		//now see if the calling controller wants auth
		// - different than normal DarkAuth 1.3 usage
		// 	 - per-action control (I like this way better)
		$vars = get_class_vars($this->controller->name.'Controller');
					
		if(empty($vars['_dAccess'])) {
			$vars = get_class_vars('AppController');
			$vars['_dAccess'] = array();
		}
		
		$action = $this->controller->action;   
			
		if(isset($this->controller->params['prefix'])){
			if(isset($vars['_dAccess'][$action])){
				$vars['_dAccess'][$action][] = $this->controller->params['prefix'];
			} else {
				$vars['_dAccess'][$action] = array($this->controller->params['prefix']);
			}
		}
			
		// We want Auth for any action here
		$access = $vars['_dAccess'];
		$deny = false; 
		if(is_array($access)){
			if(array_key_exists($action,$access)){
				if(!empty($access[$action])){
					if(!$this->isAllowed($access[$action])){
						$deny = true;
					}
				}
			} elseif(array_key_exists('*',$access)) {
				if(!empty($access['*'])){
					if(!$this->isAllowed($access['*'])){ 
						$deny = true;
					}
				}
			}
		}

		// Old version of doing auth (controller level, not action level)
		//now see if the calling controller wants auth 
		/*
		if( array_key_exists('_DarkAuth', $this->controller) ){ 
		  // We want Auth for any action here 
		  if(!empty($this->controller->_DarkAuth['onDeny'])){
				  $deny = $this->controller->_DarkAuth['onDeny']; 
				}else{ 
				  $deny = null; 
				} 
				if(!empty($this->controller->_DarkAuth['required'])){ 
				  $this->requiresAuth($this->controller->_DarkAuth['required'],$deny); 
				}else{ 
			$this->requiresAuth(null,$deny); 
		  } 
		} 
		*/

		$this->deny = $deny;

		// Not logged in and denied?
		if(!$this->li && $this->deny){ 
			$this->controller->redirect('/users/login');
		}

	}


	function startup(&$controller){

		//finally give the view access to the data 
		$this->DA = array(
			'li' => $this->li,
			'id' => $this->id,
			'User'=>$this->getUserInfo(), 
			'Access'=>$this->getAccessList()
		); 

		// Set User if it exists
		if($this->li){
			$this->DA['User'] = $this->current_user[$this->user_model_name];
		}
		$this->controller->set('_DarkAuth',$this->DA); 


		// Denied access?
		// - display 'denied' view
		if($this->deny){
			echo $this->controller->render('/users/deny');
			exit;
		}
		
	}


	function secure_key(){ 
		static $key; 
		if(!$key){ 
			$key = md5(Configure::read('Security.salt').'!DarkAuth!'.$this->session_secure_key); 
		} 
		return $key; 
	}


	function requiresAuth($groups=array(),$deny_redirect=null){ 
		if( empty($this->current_user) ){ 
			// Still no info! render login page! 
			if($this->from_post){ 
				$this->controller->_Flash($this->login_failed_message,'mean','/users/login');  
			} 
		  $this->controller->render($this->login_view); 
		  exit(); 
		}else{ 
		  if($this->from_post){ 
					// user just authed, so redirect to avoid post data refresh. 
					$this->controller->redirect($this->here,null,null,true); 
					exit(); 
		  } 
		  // User is authenticated, so we just need to check against the groups. 
		  if( empty($groups) ){ 
			// No Groups specified so we are good to go! 
			$deny = false; 
		  }else{ 
			$deny = !$this->isAllowed($groups); 
		  } 
		  if($deny){ 
			// Current User Doesn't Have Access! DENY 
			if($deny_redirect){ 
						$this->controller->redirect($deny_redirect); 
						exit(); 
					}else{ 
						$this->controller->render($this->deny_view); 
						exit(); 
					} 
		  } 
		} 
		return true; 
	} 
  
  function isAllowed($groups=array()){ 
	if( empty($this->current_user) ){ 
	  // No information about the user! FALSE 
	  return false; 
	}else{ 
	  // User is authenticated, so we just need to check against the groups. 
	  if( empty($groups) ){ 
		// No Groups specified so we are good to go! TRUE 
		return true; 
	  } 
	   
	  if(!is_array($groups)){ 
		//if a string passed, turn to an array with one element 
		$groups = array(0 => $groups);  
	  } 
	   
	  $access = $this->getAccessList(); 
			 
	  foreach($groups as $g){ 
		if(array_key_exists($g,$access) && $access[$g]){ 
		  return true; 
		} 
	  } 
	} 
  } 
   
  function getCookieInfo(){ 
		if(!array_key_exists('DarkAuth',$_COOKIE)){ 
			//No cookie 
			return false; 
		} 
		list($hash,$data) = explode("|||",$_COOKIE['DarkAuth']); 
		if($hash != md5($data.$this->secure_key())){ 
			//Cookie has been tampered with 
			return false; 
		} 
		$crumbs = unserialize(base64_decode($data)); 
		if(!array_key_exists('username',$crumbs) || 
			 !array_key_exists('password',$crumbs) || 
			 !array_key_exists('expiry'  ,$crumbs)){ 
			//Cookie doesn't contain the correct info. 
			return false; 
		} 
		if(!isset($crumbs['expiry']) || $crumbs['expiry'] <= time()){ 
			//Cookie is out of date! 
			return false; 
		} 
		//All checks passed, cookie is genuine. remove expiry time and return 
		unset($crumbs['expiry']); 
		return $crumbs;         
  } 
   
  function setCookieInfo($data,$expiry=0){ 
	  if($data === false){ 
			//remove cookie! 
			$cookie = false; 
			$expiry = 100; //should be in the past enough! 
	  }else{ 
			$serial = base64_encode(serialize($data)); 
			$hash = md5($serial.$this->secure_key()); 
			$cookie = $hash."|||".$serial; 
		} 
		if($_SERVER['SERVER_NAME']=='localhost'){ 
		  $domain = null; 
		}else{ 
		  $domain = '.'.$_SERVER['SERVER_NAME']; 
		} 
		return setcookie('DarkAuth', $cookie, $expiry, $this->controller->base, $domain); 
  } 

  function authenticate_from_post($data){ 
		$this->from_post = true; 
		return $this->authenticate($data); 
  } 
  function authenticate_from_session($data){ 
		$this->from_session = true; 
		return $this->authenticate($data); 
	} 
	function authenticate_from_cookie(){ 
		$this->from_cookie = true; 
		return $this->authenticate($this->getCookieInfo()); 
	} 
	 
  function authenticate($data){
	if($data === false){ 
		$this->destroyData(); 
		return false; 
	}
	if($this->from_session || $this->from_cookie){ 
	  $hashed_password = $data['password']; // Does not re-hash the password (saving time because the hashed password is in the Session)
	}else{ 
	  $hashed_password = $this->hasher($data['password']); 
	}
	switch($this->user_name_case_folding){ 
			case 'lower': 
				$data['username'] = strtolower($data['username']); 
				break;             
			case 'upper'; 
				$data['username'] = strtoupper($data['username']); 
				break; 
			default: 
				break; 
	} 
	$conditions = array( 
	  $this->user_model_name.".".$this->user_name_field => $data['username'], 
	  $this->user_model_name.".".$this->user_pass_field => $hashed_password 
	); 
	if($this->user_live_field){ 
	  $field = $this->user_model_name.".".$this->user_live_field; 
	  $conditions[$field] = $this->user_live_value; 
	}; 
	$this->controller->{$this->user_model_name}->contain(array($this->group_model_name));
	$check = $this->controller->{$this->user_model_name}->find($conditions);
	if($check){
	   $this->Session->write($this->secure_key(),$check);
		if( 
			  $this->allow_cookie && //check we're allowing cookies 
			  $this->from_post && //check this was a posted login attempt. 
			  array_key_exists('remember_me',$data) && //check they where given the option! 
			  $data['remember_me'] == true //check they WANT a cookie set 
		 ){ 
			 // set our cookie! 
			 if(array_key_exists('cookie_expiry',$data)){ 
			   $this->cookie_expiry = $data['cookie_expiry']; 
			 }else{ 
			   $this->cookie_expiry; 
			 } 
			 if(strtotime($this->cookie_expiry) <= time()){ 
				// Session cookie? might as well not set at all... 
			 }else{ 
			   $expiry = strtotime($this->cookie_expiry); 
			   $this->setCookieInfo(array('username'=>$data['username'], 'password'=>$hashed_password, 'expiry'=>$expiry), $expiry); 
			 }  
		}
		$this->current_user = $check;
		$this->li = true;
		$this->id = $check[$this->user_model_name]['id'];


		return true; 
	} else {
		if($this->from_post){
		 	//$this->Session->setFlash($this->login_failed_message);
		 	return false;
		} 
		$this->destroyData(); 
		return false; 
	} 
  } 

  function getUserInfo(){ 
	return $this->current_user[$this->user_model_name]; 
  } 
  function getAllUserInfo(){ 
	return $this->current_user; 
  } 
  function getAccessList(){ 
	static $access_list = false; 
	if(!$access_list){ 
	  $access_list = $this->_generateAccessList(); 
	} 
	return $access_list; 
  } 
  function _generateAccessList(){ 
	if(!$this->group_model_name){ 
	  return array(); 
	} 
	$all_groups = $this->controller->{$this->user_model_name}->{$this->group_model_name}->find('list'); 
	if(!count($all_groups)){  return array(); } 
	$access = array_combine($all_groups,array_fill(0,count($all_groups),0)); //create empty array. 
	 
	if(empty($this->current_user)){ 
	  // NO AUTHENTICATION, SO EMTPY ARRAY! 
	  return $access; 
	}  
	if($this->HABTM){ 
	  // could be many groups  
	  $ugroups = Set::combine($this->current_user[$this->group_model_name],'{n}.id','{n}.'.$this->group_name_field); 
	  foreach($all_groups as $id => $role){ 
		if(in_array($role,$ugroups)){ 
		  $access[$role] = 1; 
		}else{ 
		  $access[$role] = 0; 
		} 
	  } 
	}else{ 
	  // single group assoc, id = user.group_id 
	  $foreign_key = $this->controller->{$this->user_model_name}->belongsTo[$this->group_model_name]['foreignKey']; 
	  foreach($all_groups as $id => $role){ 
		if($this->current_user[$this->user_model_name][$foreign_key] == $id){ 
		  $access[$role] = 1; 
		}else{ 
		  $access[$role] = 0; 
		} 
	  } 
	} 
	if($this->superuser_group && $access[$this->superuser_group]){ 
	  return array_combine($all_groups,array_fill(0,count($all_groups),1)); 
	}else{ 
	  return $access; 
	} 
  } 

  function destroyData(){ 
	$this->Session->delete($this->secure_key()); 
	if($this->allow_cookie){ 
	  $this->setcookieInfo(false);  
	} 
	$this->current_user = null; 
  } 

  function logout($redirect=false){ 
	$this->destroyData(); 
	if(!$redirect){ 
	  $redirect = $this->logout_page; 
	} 
		$this->controller->_Flash($this->logout_message,'nice',null);  
	$this->controller->redirect($redirect,null,true); 
	exit(); 
  } 

  function getUserInfoFromSessionOrCookie(){ 
	if( !empty($this->current_user) ){  
	  return false;  
	} 
	if($this->Session->valid() && $this->Session->check($this->secure_key()) ){ 
	  $this->current_user = $this->Session->read($this->secure_key()); 
	  return $this->authenticate_from_session(array( 
		'username' => $this->current_user[$this->user_model_name][$this->user_name_field], 
		'password' => $this->current_user[$this->user_model_name][$this->user_pass_field], 
	  )); 
	}elseif($this->allow_cookie){ 
			return $this->authenticate_from_cookie(); 
	} 
  } 
} 
?> 