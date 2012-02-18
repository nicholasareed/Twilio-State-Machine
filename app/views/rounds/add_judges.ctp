

<!-- Form -->
<?php echo $this->Form->create('Round', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Add Judges</legend>

		<?
			foreach($missing as $id => $name){
				echo $this->General->input('Round.Judge.'.$id,array('type' => 'checkbox', 'label' => $name, 'value' => $id));
			}
		?>
		
		<? //echo $this->General->input('Round.test',array('label' => false, 'type' => 'select', 'multiple' => 'checkbox', 'options' => $missing)); ?>

		<?php echo $this->Form->submit('Add Judges', array('class' => 'btn primary', 'div' => array('class' => 'actions'))); ?>
	
	</fieldset>

<?php echo $this->Form->end(); ?>


<h2>Existing Judges</h2>

<? foreach($existing as $judge){ ?>

	<p>
		<? echo $judge['User']['Profile']['fullname']; ?>
	</p>

<? } ?>

