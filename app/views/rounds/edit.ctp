

<!-- Form -->
<?php echo $this->Form->create('Help', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Edit Help</legend>

		<? echo $this->General->input('Help.markdown',array('label' => 'Markdown')); ?>

		<?php echo $this->Form->submit('Save', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>
