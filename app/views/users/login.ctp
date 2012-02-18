<?php echo $this->Form->create('DarkAuth', array('id' => 'LoginForm', 'url' => $this->here)); ?>
	<fieldset>
		<legend>Log In</legend>
		
		<? echo $this->General->input('DarkAuth.username',array('label' => 'Email Address')); ?>

		<? echo $this->General->input('DarkAuth.password',array('label' => 'Password')); ?>
			
		<?php echo $this->Form->submit('Log In', array('class' => 'btn primary', 
														'div' => array('class' => 'actions'),
														'after' => ' or '.$this->Html->link('Sign Up','/users/apply'))); ?>
	
	</fieldset>
<?php echo $this->Form->end(); ?>