<?php

class UsersController extends AppController {

	// CLASS VARIABLES

	var $uses = array();

	// DarkAuth
	var $_dAccess = array('*' => array(),
							'login_as' => array('admin'));

	var $components = array();


	// FUNCTIONS

	function beforeFilter(){

		parent::beforeFilter();
	}


	function test($value = 'no_value'){
		// Test values used by a test project
		// - testing different webhook results and UX
		//exit;
		$return = array('send_sms' => 'stringhere',
						'send_sms2' => array(array('To' => array('+16502068481','+16027059885'), 'Body' => 'input value: '.$value),
											array('Body' => 'test')), // Can have multiple "To" fields
						'set_state2' => 'state_2',
						'set_attributes2' => array('a.name' => 'TxtSpring',
												  'u.meta.inputval' => $value,
												  'u.meta.awesome' => array('ok','cool'),
												  'u.meta.more' => array('test1' => 'value1',
												  					'test2' => 'value2')));
		//pr($return);
		echo json_encode($return);
		exit;

	}


	function pricing(){
		$this->Plan =& ClassRegistry::init('Plan');
		$plans = $this->Plan->getPlans();

		$buy_button = 'buy';
		if($this->DarkAuth->li){
			$buy_button = 'upgrade';
		}

		$this->set(compact('plans','buy_button'));

	}


	function purchase($key = null){
		// Doesn't handle upgrades yet
		// - Stripe should make that easy though

		$this->_Flash('Purchasing currently closed, please add yourself to the Invite list!','mean','/');

		if($this->DarkAuth->li){
			$this->_Flash('Please contact support to upgrade your plan, thanks!','mean','/pricing');
		}

		$this->Plan =& ClassRegistry::init('Plan');
		$plans = $this->Plan->getPlans();

		if(!array_key_exists($key,$plans)){
			$This->_Flash('Sorry, that plan does not exist','mean','/pricing');
		}
		$plan = $plans[$key];

		$this->set('plan',$plan);


		if($this->RequestHandler->isGet()){
			return;
		}

		// Parse new user

		// Create the account first (email, etc.)
		// Then, charge the card (handle either one failing?)
		// - if creating User fails, then just return, do not charge the card
		// - if charging card fails, have them re-enter everything? 
		//	 - use transactions?

		// Create new User
		$data = array();
		$data['email'] = $this->data['User']['email'];
		$data['password'] = $this->data['User']['pswd'];
		$data['plan'] = $plan['key'];

		// Validate
		$this->User =& ClassRegistry::init('User');
		$this->User->set($data);

		if(!$this->User->validates()){
			$this->_Flash('Please fix the errors below','mean',null);
			return false;
		}

		// Change password
		$data['password'] = $this->DarkAuth->hasher($data['password']);

		// Try to save the User
		// - with a transaction
		$this->User->begin();
		$this->User->create();
		if(!$this->User->save($data)){
			$this->_Flash('Errors occurred when creating account, please try again','mean',null);
			return false;
		}

		// Saving User succeeded
		$data['id'] = $this->User->id;

		// Try charging their card


		// Import Stripe libraries
		App::import('Vendor','Stripe',array('file' => 'Stripe/Stripe.php'));

		// Set API Key
		Stripe::setApiKey(STRIPE_SECRET_KEY);

		// Get Token from $this->data
		$token = $this->data['Stripe']['token'];

		// create the charge on Stripe's servers - this will charge the user's card
		$customer = Stripe_Customer::create(array(
		  //"amount" => CHARGE_IN_CENTS, // amount in cents, again
		  //"currency" => "usd",
		  "card" => $token,
		  "plan" => $plan['key'],
		  'email' => $data['email'],
		  "description" => 'email: '.$data['email'].' | id: '.$data['id'])
		);

		try{
			$paid = $customer->paid;
			$customer_id = $customer->id;
		} catch(Exception $e){
			// Error, probably Charge failed

			// Rollback the User creation
			$this->User->rollback();

			$this->_Flash('An error occurred charging your card. Please try again','mean',null);
			return false;
		}

		//$data['payment_details'] = $charge_id;
		//$data['payment_complete'] = 1;

		// Successfully charge the User
		$this->User->commit();

		// Move them to the next stage! 
		// - drop them at the Applications page

		// Get rid of session info
		$this->DarkAuth->destroyData();

		// Log in with new credentials
		$this->Session->write($this->DarkAuth->secure_key(),array('User' => $data));

		// Redirect
		$this->_Flash('Thanks for signing up!','nice','/');

	}


	function direct(){
		// Redirect based on input

		if(!$this->DarkAuth->li){
			$this->redirect('/pages/home');
		}

		if($this->DarkAuth->DA['Access']['admin']){
			$this->redirect('/admins');
		}

		$this->redirect('/projects');

	}


	function login($success_message = null){
		// Log in for everybody
		// - including Marc
		
		if($this->DarkAuth->li){
			$this->redirect('/');
		}
		if($success_message){
			$this->_Flash('Successfully registered! Please log in','nice','/users/login');
		}
		$this->_login();
	}


	function logout(){ 
		$this->DarkAuth->logout(); 
	}


	function register_not_allowed_yet(){
		// Basic signup

		exit;


		$this->User =& ClassRegistry::init('User');

		if($this->RequestHandler->isGet()){
			return;
		}


		// Parse input
		$data = array();

		$data['email'] = trim($this->data['User']['email']);
		$data['pswd'] = $this->data['User']['pswd'];

		// Sanitize
		$methods = array('email' => 'email');
		$data = arraySanitize($data,$methods);

		// Already registered with that Email?
		// - support other services as well
		$conditions = array('User.email' => $data['email']);
		$user = $this->User->find('first',compact('conditions'));

		if(!empty($user)){
			$this->_Flash('Email already in use','mean',null);
			return;
		}

		// Validation
		$this->User->set($data);

		if(!$this->User->validates()){
			return;
		}


		// Save
		if(!$this->User->save($data)){
			return false;
		}

		// Add as an Attendee
		$user_id = $this->User->id;

		// Already an Attendee
		// - default is as a participant/member
		$this->Attendee =& ClassRegistry::init('Attendee');
		$conditions = array('Attendee.user_id' => $user_id,
							'Attendee.event_id' => EVENT_ID);
		$attendee = $this->Attendee->find('first',compact('conditions'));

		if(!empty($attendee)){

		}


	}

}

?>