<?php

	class AppController extends Controller {
		
		// Default Helpers
		var $helpers = array('Html','Form','General');

		// Default DarkAuth Access Control
		var $_dAccess = array('*' => array('admin'));

		// Default Event Access
		var $_eRequired = true; // Required to be at an Event => always True?
		var $_eAccess = array('*' => array('admin'));

		// Default components
		var $components = array('Session','RequestHandler','DarkAuth','EAuth');


		function beforeFilter(){
			// Handle theming	

			// Settings
			// - if used, get this from IDC?
			// - settings would be on a per-Event basis

		}


		// Login
		function _login(){
			if(is_array($this->data) && array_key_exists('DarkAuth',$this->data) ){
				$result = $this->DarkAuth->authenticate_from_post($this->data['DarkAuth']); 
				if($result == false){
					$this->_Flash('Invalid Email/Password, Please try again','mean',null);
				} else {
					// Success!
					$this->_Flash('Logged in','nice','/');
				}
				$this->data['DarkAuth']['password'] = '';
			}
		}


		// Simple setFlash replacement
		function _Flash($text = null, $layout = null, $redirect = '/'){
			$this->Session->setFlash($text,$layout);
			if($redirect != null){
				$this->redirect($redirect);
			}
		}

	}

?>