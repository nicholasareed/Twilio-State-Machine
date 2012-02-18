
<script type="text/javascript">
	
	var prev = '';

	$(document).ready(function(){

		window.setInterval(test,1000);

	});

	function test(){
		var data = $('#PageSource').val();
		if(data != prev){
			prev = data;
			$.post('/pages/test', { data: data}, function(responseHtml){
				$('.page-display').html(responseHtml);

			// Every 5 seconds, update the preview page
				//window.setTimeout(test,5000);
			});
		}
	}

</script>


<?php echo $this->Form->create('Page', array('url' => $this->here)); ?>
	<fieldset>

		<? echo $this->General->input('Page.live',array('label' => 'Active: Visible on site?',
															 'type' => 'checkbox')); ?>

		<? echo $this->General->input('Page.key',array('label' => 'Key', 'help' => '/pages/key_is_this')); ?>

		<? echo $this->General->input('Page.type',array('label' => 'Type', 'type' => 'select', 'options' => $types,'help' => 'Will be shown only for the selected Type')); ?>

		<? echo $this->General->input('Page.title',array('label' => 'Title', 'help' => 'IDC/PDC - "title_here"')); ?>

		<? echo $this->General->input('Page.source',array('label' => 'Source', 'style' => 'width:90%;height:500px;')); ?>

		<? echo $this->General->input('Page.note',array('label' => 'Internal Note')); ?>

		<?php echo $this->Form->submit('Save Changes to Page', array('class' => 'btn primary', 
																		'div' => array('class' => 'actions')
																		,'after' => ' or '.$this->Html->link('go back','/pages/index/'))); ?>
		
	</fieldset>

<?php echo $this->Form->end(); ?>
