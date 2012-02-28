
<script type="text/javascript">
	
	/*
	$(document).ready(function(){

		$('#ActionType').on('change',function(){

			

		});

	});
	*/

</script>


<!-- Form -->
<?php echo $this->Form->create('Action', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Edit Action</legend>

		<?
			echo $this->General->input('Action.type',array('label' => 'Type', 'type' => 'select', 'empty'=> true, 'options' => $types, 'disabled' => 'disabled'));
			
			echo $this->General->input('Action.type',array('type' => 'hidden'));
					
			switch($type_chosen){

				case 'response':
					echo $this->General->input('Action.input1',array('label' => 'Response', 'type' => 'text'));
					break;

				case 'webhook':
					echo $this->General->input('Action.input1',array('label' => 'Webhook', 'type' => 'text', 'help' => 'Requires http:// or https:// at the beginning'));
					break;

				case 'attribute':
					echo $this->General->input('Action.input1',array('label' => 'User Attribute', 'type' => 'text', 'help' => 'Example: u.registered=1,u.name=nick reed'));
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


			echo $this->Form->submit('Save Action', array('class' => 'btn primary', 'div' => array('class' => 'actions')));
			

		?>

	</fieldset>

<?php echo $this->Form->end(); ?>
