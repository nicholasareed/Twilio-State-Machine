
<script type="text/javascript">
	
	$(document).ready(function(){
		$('form').on('submit',function(e){
			$submit = $(this).find('input[type="submit"]');
			$submit.val('Submitting');
			$submit.attr('disabled','disabled');
			$submit.click(function(){
				return false;
			});
		});
	}):

</script>

<h2>Create Application</h2>

<!-- Form -->
<?php echo $this->Form->create('Project', array('url' => $this->here)); ?>
	<fieldset>
		

		<?
			
			echo $this->General->input('Project.name',array('label' => 'App Name'));

			if($extra_numbers){
				echo $this->General->input('Project.ptn',array('class' => 'input-mini', 'maxlength' => 3, 'label' => 'Area Code',
																'help' => 'Leave blank to not choose a phone number'));
			} else {
				echo '<span class="label label-info">Upgrade your plan for more phone numbers</span>';
			}

			echo "<hr>";
			
			echo $this->Form->submit('Create App', array('class' => 'btn btn-success', 'div' => array('class' => 'actions'), 'after' => ' or '.$this->Html->link('go back','/projects')));
			
		?>

	</fieldset>

<?php echo $this->Form->end(); ?>
