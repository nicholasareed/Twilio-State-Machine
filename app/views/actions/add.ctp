
<div class="modal-header">
	<a class="close" data-dismiss="modal">×</a>
	<h3>Add Action</h3>
</div>

<div class="modal-body clearfix">

	<div class="formLeft">

		<!-- Form -->
		<?php echo $this->Form->create('Action', array('url' => $this->here, 'class' => 'form-stacked adding addingAction')); ?>

			<?
				echo $this->General->input('Action.type',array('label' => false, 'type' => 'select', 'empty'=> true, 'options' => $types, 'disabled' => isset($type_chosen) ? 'disabled':False ));
				
				if(!isset($type_chosen)){
					
					echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_type'));

					echo '<div class="loadingGif nodisplay">'.$this->Html->image('ajax-loader.gif').'</div>';

					echo $this->Form->submit('Next Step', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
				
				} else {

						echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_all'));
						echo $this->General->input('Action.type',array('type' => 'hidden'));
						
						switch($type_chosen){

							case 'send_sms':
								echo $this->General->input('Action.input1',array('label' => 'Text', 'type' => 'text'));
								echo $this->General->input('Action.send_sms_recipients',array('label' => 'Recipient', 'type' => 'text', 'help' => 'Comma-separated phone numbers, or blank to use as a response'));
								echo $this->General->input('Action.send_sms_later_time_text',array('label' => 'Send Delay', 'type' => 'text', 'help' => 'Try "+2 minutes" or "tomorrow 12pm EST"'));
								break;

							case 'webhook':
								echo $this->General->input('Action.input1',array('label' => false, 'type' => 'text', 'help' => 'Requires http:// or https:// at the beginning'));
								echo $this->General->input('Action.webhook_can_modify_vars',array('type' => 'checkbox', 'label' => 'Can modify variables'));
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


					echo $this->Form->submit('Add Action', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
				}

			?>

		<?php echo $this->Form->end(); ?>

	</div>
	<div class="formRight">
		<? echo Markdown($helps['action']['Help']['markdown']); ?>
	</div>
</div>
