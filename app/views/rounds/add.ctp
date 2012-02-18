

<!-- Form -->
<?php echo $this->Form->create('Round', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Add Round</legend>

		<? echo $this->General->input('Round.name',array('label' => 'Name')); ?>

		<? echo $this->General->input('Round.venue_id',array('label' => 'Venue', 'type' => 'select', 'help' => 'Optional', 'empty'=> true, 'options' => $venues)); ?>

		<? echo $this->General->input('Round.level',array('label' => 'Level', 'help' => '(Numeric)')); ?>

		<? echo $this->General->input('Round.can_submit_to',array('label' => 'Teams submit directly to', 'type' => 'checkbox')); ?>

		<?php echo $this->Form->submit('Add Round', array('class' => 'btn primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>
