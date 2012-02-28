
<? echo $this->Html->link('Send Test SMS','/',array('class' => 'btn default sendSms')); ?>

<div class="row testSmsRow">
	<div class="span12">

		<?php echo $this->Form->create('Text', array('url' => '/texts/project/'.$project['Project']['id'])); ?>
			<fieldset>
				
				<div class="alert-message block-message">
					In DEMO_MODE: No actual SMS messages will be sent, just watch the Log to see what WOULD have happened
				</div>

				<? echo $this->General->input('Text.to',array('label' => 'To','default' => '+12069228264')); ?>

				<? echo $this->General->input('Text.from',array('label' => 'From', 'default' => '+16027059885')); ?>

				<? echo $this->General->input('Text.body',array('label' => 'Body')); ?>
					
				<?php echo $this->Form->submit('Fake Send SMS', array('class' => 'btn primary', 'div' => array('class' => 'actions'))); ?>
			
			</fieldset>

		<?php echo $this->Form->end(); ?>


		<div id="results2">

		</div>

	</div>

</div>

<script type="text/javascript">
	
	$(document).ready(function(){

		// Capture form submit and re-route
		$('input[type="submit"]').click(function(e){
			e.preventDefault();

			// Submit over ajax
			var data = {To: $('#TextTo').val(),
						From: $('#TextFrom').val(),
						Body: $('#TextBody').val(),
						debug_mode: $('#TextDebugMode').val()};

			// Ajax query
			$.ajax({
				url: '/texts/test',
				type: 'POST',
				cache: false,
				data: data,
				success: function(responseHtml){
					//console.log(responseHtml);
					$('#results').text(responseHtml);
					$('#results2').html(responseHtml);
					outgoing();
				}
			});


		});

		//outgoing();

	});

</script>