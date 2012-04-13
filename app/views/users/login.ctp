

<div class="row">
	<div class="span6 offset3">

		<?php echo $this->Form->create('DarkAuth', array('id' => 'LoginForm', 'class' => 'well', 'url' => $this->here)); ?>

			<h2>Log In</h2>
			<br />

			<fieldset>
				
				<? echo $this->General->input('DarkAuth.username',array('label' => 'Email Address')); ?>

				<? echo $this->General->input('DarkAuth.password',array('label' => 'Password')); ?>
					
				<?php echo $this->Form->submit('Log In', array('class' => 'btn btn-primary', 
																'div' => array('class' => 'actions'),
																'after' => ' or '.$this->Html->link('Request Invite','/invites/add'))); ?>
			
			</fieldset>
		<?php echo $this->Form->end(); ?>
	</div>
</div>