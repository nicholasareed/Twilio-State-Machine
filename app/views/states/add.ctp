
<!-- Form -->
<?php echo $this->Form->create('State', array('url' => $this->here, 'class' => 'form-stacked')); ?>

	<h5>Add a State</h5>

	<?
		
		echo $this->General->input('State.key',array('label' => false, 'placeholder' => 'state key', 'help' => 'Only letters, numbers, and underscore characters are allowed'));	

		echo $this->Form->submit('Add State', array('class' => 'btn primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
	

	?>

<?php echo $this->Form->end(); ?>
