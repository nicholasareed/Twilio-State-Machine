

<!-- Form -->
<?php echo $this->Form->create('Project', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Create Application</legend>

		<?
			
			echo $this->General->input('Project.name',array('label' => 'Name'));
			
			echo $this->Form->submit('Create Application', array('class' => 'btn primary', 'div' => array('class' => 'actions'), 'after' => ' or '.$this->Html->link('go back','/projects')));
			
		?>

	</fieldset>

<?php echo $this->Form->end(); ?>
