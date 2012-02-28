
<!-- Form -->
<?php echo $this->Form->create('Action', array('url' => $this->here, 'class' => 'form-stacked')); ?>

	<h5>Add an Action</h5>

	<?
		echo $this->General->input('Action.type',array('label' => false, 'type' => 'select', 'empty'=> true, 'options' => $types, 'disabled' => isset($type_chosen) ? 'disabled':False ));
		
		if(!isset($type_chosen)){
			
			echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_type'));

			echo '<div class="loadingGif nodisplay">'.$this->Html->image('ajax-loader.gif').'</div>';

			echo $this->Form->submit('Next Step', array('class' => 'btn primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
		
		} else {

				echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_all'));
				echo $this->General->input('Action.type',array('type' => 'hidden'));
				
				switch($type_chosen){

					case 'response':
						echo $this->General->input('Action.input1',array('label' => false, 'type' => 'text'));
						break;

					case 'webhook':
						echo $this->General->input('Action.input1',array('label' => false, 'type' => 'text', 'help' => 'Requires http:// or https:// at the beginning'));
						break;

					case 'attribute':
						echo $this->General->input('Action.input1',array('label' => false, 'type' => 'text', 'help' => 'Example: u.registered=1,u.name=nick reed'));
						break;

					case 'state':
						echo $this->General->input('Action.input1',array('label' => false, 'type' => 'text', 'help' => 'Example: default'));
						break;

					case 'default':
						// 'default' automatically adds the Step
						break;
						
					default:
						break;

				}


			echo $this->Form->submit('Add Action', array('class' => 'btn primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
		}

	?>

<?php echo $this->Form->end(); ?>
