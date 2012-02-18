

<!-- Form -->
<?php echo $this->Form->create('User', array('url' => $this->here)); ?>
	<fieldset>
		<legend>My Settings</legend>

		<? echo $this->General->input('Profile.fullname',array('label' => 'Full Name')); ?>

		<? echo $this->General->input('Profile.bio',array('label' => 'About Me')); ?>

		<? echo $this->General->input('Profile.cell_phone',array('label' => 'Cell Number', 'help' => "We'll send you Texts during the Hackathon")); ?>

		<?php echo $this->Form->submit('Save Settings', array('class' => 'btn primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>
