
<? echo $this->Html->link('Help Index','/helps'); ?>

<br />
<br />

<!-- Form -->
<?php echo $this->Form->create('Help', array('url' => $this->here)); ?>
	<fieldset>

		<? echo $this->General->input('Help.key',array('label' => 'key')); ?>

		<?php echo $this->Form->submit('Save', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>
