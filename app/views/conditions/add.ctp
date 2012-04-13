


<div class="modal-header">
	<a class="close" data-dismiss="modal">×</a>
	<h3>Add Condition</h3>
</div>

<div class="modal-body">

	<div class="formLeft">
		
		<!-- Form -->
		<?php echo $this->Form->create('Condition', array('url' => $this->here, 'class' => 'form-stacked adding addingCondition')); ?>
				
				<?
					echo $this->General->input('Condition.type',array('label' => false, 'type' => 'select', 'empty'=> true, 'options' => $types, 'disabled' => isset($type_chosen) ? 'disabled':False ));
					
					if(!isset($type_chosen)){
						
						echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_type'));

						echo '<div class="loadingGif nodisplay">'.$this->Html->image('ajax-loader.gif').'</div>';

						echo $this->Form->submit('Next', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
					
					} else {

							echo $this->General->input('Hidden.step',array('type' => 'hidden', 'value' => 'submitted_all'));
							echo $this->General->input('Condition.type',array('type' => 'hidden'));
							
							switch($type_chosen){

								case 'starts_with':
									echo $this->General->input('Condition.input1',array('label' => false, 'type' => 'text'));
									echo $this->General->input('Condition.case_sensitive',array('label' => 'Case-sensitive ', 'type' => 'checkbox'));
									break;

								case 'contains':
									echo $this->General->input('Condition.input1',array('label' => false, 'type' => 'text'));
									echo $this->General->input('Condition.case_sensitive',array('label' => 'Case-sensitive ', 'type' => 'checkbox'));
									break;

								case 'regex':
									echo $this->General->input('Condition.input1',array('label' => false, 'type' => 'text', 'help' => 'check out this '.$this->Html->link('great resource','http://gskinner.com/RegExr/')));
									break;

								case 'word_count':
									echo $this->General->input('Condition.input1',array('label' => false, 'type' => 'text'));
									break;

								case 'attribute':
									echo $this->General->input('Condition.input1',array('label' => false, 'type' => 'text', 'help' => 'Example: u.meta.registered=1,u.meta.name=nick reed'));
									echo $this->General->input('Condition.case_sensitive',array('label' => 'Case-sensitive ', 'type' => 'checkbox'));
									break;

								case 'default':
									// 'default' automatically adds the Step
									break;
									
								default:
									break;

							}


						echo $this->Form->submit('Add Condition', array('class' => 'btn btn-primary', 'div' => array('class' => 'actions2'), 'after' => ' or '.$this->Html->link('cancel',$this->here,array('class' => 'add_cancel'))));
					}

				?>


		<?php echo $this->Form->end(); ?>
	</div>

	<div class="formRight">
		<? echo Markdown($helps['action']['Help']['markdown']); ?>
	</div>
</div>

<!--
<div class="modal-footer">
	<a href="#" class="btn btn-primary">Save changes</a>
	<a href="#" class="btn">Close</a>
</div>
-->
