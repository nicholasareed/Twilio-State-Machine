
<script type="text/javascript">
	
	/*
	$(document).ready(function(){

		$('#ActionType').on('change',function(){

			if($(this).val() == 'default'){
				$('form input[type="submit"]').val('Add Condition');
			} else {
				$('form input[type="submit"]').val('Next Step');
			}

		});

	});
	*/

</script>


<!-- Form -->
<?php echo $this->Form->create('Action', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Add Action</legend>

		<?
			echo $this->General->input('Action.type',array('label' => 'Type', 'type' => 'select', 'empty'=> true, 'options' => $types, 'disabled' => isset($type_chosen) ? 'disabled':False ));
			
			if(!isset($type_chosen)){
				
				echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_type'));

				echo $this->Form->submit('Next Step', array('class' => 'btn primary', 'div' => array('class' => 'actions')));
			
			} else {

					echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_all'));
					echo $this->General->input('Action.type',array('type' => 'hidden'));
					
					switch($type_chosen){

						case 'response':
							echo $this->General->input('Action.input1',array('label' => 'Response', 'type' => 'text'));
							break;

						case 'webhook':
							echo $this->General->input('Action.input1',array('label' => 'Webhook', 'type' => 'text', 'help' => 'Requires http:// or https:// at the beginning'));
							break;

						case 'attribute':
							echo $this->General->input('Action.input1',array('label' => 'Set Attribute', 'type' => 'text', 'help' => 'Example: u.registered=1,u.name=nick reed'));
							break;

						case 'state':
							echo $this->General->input('Action.input1',array('label' => 'Set State', 'type' => 'text', 'help' => 'Example: default'));
							break;

						case 'default':
							// 'default' automatically adds the Step
							break;
							
						default:
							break;

					}


				echo $this->Form->submit('Add Action', array('class' => 'btn primary', 'div' => array('class' => 'actions'), 'after' => ' or '.$this->Html->link('start over',$this->here)));
			}

		?>

	</fieldset>

<?php echo $this->Form->end(); ?>
