
<script type="text/javascript">
	
	/*
	$(document).ready(function(){

		$('#ActionType').on('change',function(){

			

		});

	});
	*/

</script>


<!-- Form -->
<?php echo $this->Form->create('Condition', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Edit Action</legend>

		<?
			echo $this->General->input('Condition.type',array('label' => 'Type', 'type' => 'select', 'empty'=> true, 'options' => $types, 'disabled' => 'disabled'));
			
			echo $this->General->input('Condition.type',array('type' => 'hidden'));
					
			switch($type_chosen){

				case 'starts_with':
					echo $this->General->input('Condition.input1',array('label' => 'Starts with', 'type' => 'text'));
					echo $this->General->input('Condition.case_sensitive',array('label' => 'Case-sensitive ', 'type' => 'checkbox'));
					break;

				case 'regex':
					echo $this->General->input('Condition.input1',array('label' => 'Regular Expression', 'type' => 'text', 'help' => 'check out this '.$this->Html->link('great resource','http://gskinner.com/RegExr/')));
					break;

				case 'word_count':
					echo $this->General->input('Condition.input1',array('label' => 'Number of Words', 'type' => 'text'));
					break;

				case 'attribute':
					echo $this->General->input('Condition.input1',array('label' => 'User Attribute', 'type' => 'text', 'help' => 'Example: u.registered=1,u.name=nick reed'));
					break;

				case 'default':
					// 'default' automatically adds the Step
					break;

				default:
					break;

			}


			echo $this->Form->submit('Save Condition', array('class' => 'btn primary', 'div' => array('class' => 'actions'), 'after' => $this->Html->link('Remove Condition','/conditions/remove/'.$condition['Condition']['id'],array('class' => 'remove_on_edit'))));
			

		?>

	</fieldset>

<?php echo $this->Form->end(); ?>
