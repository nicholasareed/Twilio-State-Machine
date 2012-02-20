
<br />

<div class="row">
	<div class="span12">
		<?php echo $this->Form->create('Text', array('id' => 'SignupForm', 'url' => $this->here)); ?>
			<fieldset>
				<legend>Test SMS</legend>
				
				<div class="alert-message block-message">
					In DEMO_MODE: No actual SMS messages will be sent, just watch the sidebar instead
				</div>
				
				<? echo $this->General->input('Text.debug_mode',array('label' => 'Debug Mode', 'type' => 'select', 'options' => array(1 => 'On',0 => 'Off'))); ?>

				<? echo $this->General->input('Text.to',array('label' => 'To','default' => '+12069228264')); ?>

				<? echo $this->General->input('Text.from',array('label' => 'From', 'default' => '+16027059885')); ?>

				<? echo $this->General->input('Text.body',array('label' => 'Body')); ?>


					
				<?php echo $this->Form->submit('Test', array('class' => 'btn primary', 'div' => array('class' => 'actions'))); ?>
			
			</fieldset>

		<?php echo $this->Form->end(); ?>


		<div>
			<h3>Text</h3>
			<br />
			<div id="results"></div>
			<br /><br />
			<h3>HTML</h3>
			<br />
			<div id="results2"></div>
			<!--<textarea id="results_old" style="width:80%;"></textarea>-->
		</div>
	</div>
	<div class="span4">
		<h3>Recent</h3>
		<div id="recent_outgoing">
			
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
				}
			});


		});

		//outgoing();

	});

	function outgoing(){
		// Returns outgoing messages
		// - in order

		// get last ID
		// - if one exists
		var last_id = 0;
		if($('#recent_outgoing > div:first-child').length){
			// One exists
			// - get first
			last_id = $('#recent_outgoing > div:first-child').attr('sent_id');
		}

		var url = '/texts/outgoing/'+last_id;

		$.ajax({
			url: url,
			success: function(responseHtml){
				var json = $.parseJSON(responseHtml);

				$.each(json, function(i,v){
					// Add to <div>
					$('#recent_outgoing').prepend('<div style="display:none;" sent_id="'+v.id+'">'+v.to_ptn+': '+v.text+'</div>');
					// Fade it in
					$('div[sent_id="'+v.id+'"]').fadeIn('slow');
				});

				// Run again in 5 seconds
				window.setTimeout(outgoing,2000);
			}
		});

	}

</script>