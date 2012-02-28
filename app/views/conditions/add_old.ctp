
<!-- Form -->
<?php echo $this->Form->create('Condition', array('url' => $this->here)); ?>
	<fieldset>
		<legend>Add Condition</legend>

		<?
			echo $this->General->input('Condition.type',array('label' => 'Type', 'type' => 'select', 'empty'=> true, 'options' => $types, 'disabled' => isset($type_chosen) ? 'disabled':False ));
			
			if(!isset($type_chosen)){
				
				echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_type'));

				echo $this->Form->submit('Next', array('class' => 'btn primary', 'div' => array('class' => 'actions')));
			
			} else {

					echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_all'));
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
							echo $this->General->input('Condition.input1',array('label' => 'User Attribute', 'type' => 'text', 'help' => 'Example: u.meta.registered=1,u.meta.name=nick reed'));
							break;

						case 'default':
							// 'default' automatically adds the Step
							break;
							
						default:
							break;

					}


				echo $this->Form->submit('Add Condition', array('class' => 'btn primary', 'div' => array('class' => 'actions'), 'after' => ' or '.$this->Html->link('start over',$this->here)));
			}

		?>

	</fieldset>

<?php echo $this->Form->end(); ?>
