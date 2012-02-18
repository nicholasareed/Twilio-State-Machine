

<!-- Form -->
<?php echo $this->Form->create('User', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Register</legend>

		<? echo $this->General->input('Profile.fullname',array('label' => 'Full Name')); ?>

		<? echo $this->General->input('User.email',array('label' => 'Email Address')); ?>

		<? echo $this->General->input('User.password',array('label' => 'Password')); ?>
			
		<?php echo $this->Form->submit('Register', array('class' => 'btn primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>
