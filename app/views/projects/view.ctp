
<script type="text/javascript">
	
	$(document).ready(function(){

		// Add Condition
		$('.conditionRow').on('click','.addCondition',function(){
			// Redirect to correct URL?
			// - the fuck?
		});

		// Remove Condition
		$('.conditionRow').on('click','.removeCondition',function(){
			// Ajax request
			
			// Remove Action
			$(this).parents('.conditionRow').remove();

			// Make request
			var url = $(this).attr('href');

			$.ajax({
				url: url,
				cache: false,
				success: function(response){
					try {
						var json = $.parseJSON(response);
					} catch(e){
						// Failed
						window.location = window.location.href;
					}

					if(json.code != 200){
						window.location = window.location.href;
					}

					return;

				}
			});

			return false;

		});

		// Remove Action
		$('.actionRow').on('click','.removeAction',function(){
			// Ajax request
			
			// Remove Action
			$(this).parents('.actionRow').remove();

			// Make request
			var url = $(this).attr('href');

			$.ajax({
				url: url,
				cache: false,
				success: function(response){
					try {
						var json = $.parseJSON(response);
					} catch(e){
						// Failed
						window.location = window.location.href;
					}

					if(json.code != 200){
						window.location = window.location.href;
					}

					return;

				}
			});

			return false;

		});

	});


	// Get outgoing messages
	// - use websockets for this
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


<div class="row">
	<div class="span16">

		<h1><small>Project</small><br /><? echo $project['Project']['name']; ?></h2>


		<div id="project_view">

			<!-- State -->
			<? foreach($project['State'] as $state){ ?>
				
				<br />
				<h2><small>State</small><br /><?= $state['key']; ?></h2>

				<!-- Step -->
				<? 
					$i = 1;
					foreach($state['Step'] as $step){ ?>
					
					<div class="row stepRow">
						<div class="span3 text-right">
							<h3>
								<!--<small>Step</small>
								<br />-->
								<?= $i;$i++; ?>

							</h3>

							<!--
							<? echo $this->Html->link('Add a Condition','/conditions/add/'.$step['id'],array('class' => 'addCondition','step' => $step['id'])); ?>
							<br />
							<? echo $this->Html->link('Add an Action','/actions/add/'.$step['id'],array('class' => 'addAction','step' => $step['id'])); ?>
							-->

						</div>
						<div class="span13">

							<!-- Condition -->
							<? 
								foreach($step['Condition'] as $condition){
									switch($condition['type']){
										
										case 'starts_with':
											$tmp = 'Starts with: '.$condition['input1'];
											break;
										
										case 'regex':
											$tmp = 'RegEx: '.$condition['input1'];
											break;
										
										case 'word_count':
											$tmp = 'Word Count: '.$condition['input1'];
											break;
										
										case 'attribute':
											$tmp = 'Attribute: '.$condition['input1'];
											break;
										
										case 'default':
											$tmp = 'Default';
											break;

										default:
											$tmp = "Unknown type";
											break;
									}
							?>
									<div class="row conditionRow">
										<div class="span13">
											<h4>
												<small>Condition
													<span class="options"><? echo $this->Html->link('Edit','/conditions/edit/'.$condition['id']); ?>&nbsp;&nbsp;&nbsp;<? echo $this->Html->link('Add','/conditions/add/'.$step['id'].'/'.$condition['id']); ?>&nbsp;&nbsp;&nbsp;<? echo $this->Html->link('Remove','/conditions/remove/'.$condition['id'].'/'.md5('test'.$condition['id'].'test'),array('class' => 'removeCondition')); ?></span>
												</small>
												<br />
												<? echo $tmp; ?>
											</h4>
										</div>
									</div>
							<?
								}
							?>

							<!-- Add a Condition -->
							<!--
							<div class="row conditionRow <? echo !empty($step['Condition']) ? 'transparentNoHover': ''; ?>">
								<div class="span13">
									<h3>
										<? echo $this->Html->link('Add a Condition','/conditions/add/'.$step['id'],array('class' => 'addCondition','step' => $step['id'])); ?>
									</h3>
								</div>
							</div>
							-->




								<!-- Action (indented) -->
								<? 
									//pr($step['Action']);
									foreach($step['Action'] as $action){
										switch($action['type']){
											case 'response':
												$tmp = 'Response: '.$action['input1'];
												break;

											case 'webhook':
												$tmp = 'Webhook: '.$action['input1'];
												break;
										
											case 'attribute':
												$tmp = 'Set Attribute: '.$action['input1'];
												break;
										
											case 'state':
												$tmp = 'Set State: '.$action['input1'];
												break;

											default:
												$tmp = "Unknown type: ".$action['type'];
												break;
										}
							?>
										<div class="row actionRow">
											<div class="span12 offset1">
												<h4>
													<small>Action 
														<span class="options"><? echo $this->Html->link('Edit','/actions/edit/'.$action['id']); ?>&nbsp;&nbsp;&nbsp;<? echo $this->Html->link('Remove','/actions/remove/'.$action['id'].'/'.md5('test'.$action['id'].'test'),array('class' => 'removeAction')); ?></span>
													</small>
													<br />
													<? echo $tmp; ?>
												</h4>
											</div>
										</div>
							<?
								}
							?>

							<!-- Add an Action -->
							<!--
							<div class="row actionRow <? echo !empty($step['Action']) ? 'transparentNoHover': ''; ?>">
								<div class="span12 offset1">
									<h3>
										<? echo $this->Html->link('Add an Action','/actions/add/'.$step['id'],array('step' => $step['id'])); ?>

									</h3>
								</div>
							</div>
							-->

							
						</div>
					</div>

				<? } ?>

				<!-- Add Step -->
				<div class="row stepRow">
					<div class="span3 text-right">
						<br />
						<? echo $this->Html->link('Add Step','/steps/add/'.$state['id'].'/'.md5('test'.$state['id'].'test')); ?>
					</div>
				</div>

			<? } ?>

		</div>

	</div>
	<div class="span8">

		<h3>Recent</h3>
		<div id="recent_outgoing">
			
		</div>

	</div>
</div>
