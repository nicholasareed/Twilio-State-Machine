
<div class="modal-header">
	<a class="close" data-dismiss="modal">×</a>
	<h3>Add State</h3>
</div>

<div class="modal-body">
	<!-- Form -->
	<?php echo $this->Form->create('State', array('url' => $this->here, 'class' => 'form-stacked adding addingState')); ?>

		<?
			
			echo $this->General->input('State.key',array('label' => false, 'placeholder' => 'state key', 'help' => 'Only letters, numbers, and underscore characters are allowed'));	

			echo $this->Form->submit('Add State', array('class' => 'btn primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
		

		?>

	<?php echo $this->Form->end(); ?>
</div>