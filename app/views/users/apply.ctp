
<!-- Steps -->
<? echo $this->element('signup_steps',array('steps' => array('details' => array('Location Info',null,false),
															 'confidentiality' => array('Sign Agreement',null,false),
															 'carriers' => array('Carriers',null,false),
															 'software' => array('Software',null,false),
															 'review' => array('Account Review',null,false)))); ?>

<!-- Form -->
<?php echo $this->Form->create('User', array('id' => 'SignupForm', 'url' => $this->here)); ?>
	<fieldset>
		<legend>Contact Information</legend>
		
		
		<? echo $this->General->input('User.first',array('label' => 'First Name')); ?>

		<? echo $this->General->input('User.middle',array('label' => 'Middle Initial')); ?>

		<? echo $this->General->input('User.last',array('label' => 'Last Name')); ?>

		<? echo $this->General->input('User.phone',array('label' => 'Phone Number', 'help' => 'We may contact you before access is granted')); ?>

		<? echo $this->General->input('User.email',array('label' => 'Email Address', 'help' => 'We will never spam your email or give your information to a 3rd party')); ?>

			
		<?php echo $this->Form->submit('Continue', array('class' => 'btn primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>
