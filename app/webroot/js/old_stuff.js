


		// Bind form submit
		$('.form_holder').on('submit','form',function(){
			
			// If resonse is JSON, then whoopee!
			var url = $(this).attr('action');

			$.ajax({
				url: url,
				type: 'POST',
				data: $(this).serialize(),
				success: function(response){
					try{
						var json = $.parseJSON(response);
					} catch(e){
						$('.form_holder').html(response);
						return;
					}


					// Get correct Text to use
					switch($('.form_holder').attr('form-type')){

						case 'addCondition':
						case 'editCondition':
							json.text = '';
							json.editdata = {};
							json.editdata.value = json.input1;
							switch(json.type){

								case 'starts_with':
									json.editdata.type = 'Starts with: ';
									break;
								
								case 'regex':
									json.editdata.type = 'RegEx: ';
									break;
								
								case 'word_count':
									json.editdata.type = 'Word Count: ';
									break;
								
								case 'attribute':
									json.editdata.type = 'Attribute: ';
									break;
								
								case 'default':
									json.editdata.type = 'Default';
									break;
								
								default:
									break;
							}

							break;

						case 'addAction':
						case 'editAction':
							json.text = {};
							json.editdata = {};
							json.editdata.value = json.input1;

							switch(json.type){

								case 'response':
									json.editdata.type = 'Send SMS: ';
									break;

								case 'webhook':
									json.editdata.type = 'Webhook: ';
									break;
							
								case 'attribute':
									json.editdata.type = 'Set Attribute: ';
									break;
							
								case 'state':
									json.editdata.type = 'Set State: ';
									break;

								default:
									json.editdata.type = "Unknown type: ";
									break;

							}

							//$('#t_span_editable').tmpl({type:type,value:json.input1});
							console.log(json);
							break;

						
						default:
							break;
					}

					// If success, then we add to the page, according to the $type
					// - jquery templates
					switch($('.form_holder').attr('form-type')){

						case 'addCondition':
							var tmp = $("#t_conditionRow").tmpl(json);
							$("#t_conditionRow").tmpl(json).insertBefore('.stepRow[db_id="'+json.step_id+'"] > .actualStep > .addConditionRow');
							break;

						case 'editCondition':
							var tmp = $("#t_conditionRow").tmpl(json);
							// Find Condition.id
							var nextRow = $('.conditionRow[condition_id="'+json.id+'"]').next('.conditionRow');
							$('.conditionRow[condition_id="'+json.id+'"]').remove();
							$("#t_conditionRow").tmpl(json).insertBefore(nextRow);
							break;

						case 'addAction':
							var tmp = $("#t_actionRow").tmpl(json);
							$("#t_actionRow").tmpl(json).insertBefore('.stepRow[db_id="'+json.step_id+'"] .actionsRow > .addActionRow');
							break;

						case 'editAction':
							var nextRow = $('.actionRow[action_id="'+json.id+'"]').next('.actionRow');
							$('.actionRow[db_id="'+json.id+'"]').remove();
							$("#t_actionRow").tmpl(json).insertBefore(nextRow);
							break;
						
						default:
							// whoops
							console.log('Missed');
							console.log($('.form_holder').attr('form-type'));
							break;

					}

					// Clear the form
					$('.form_holder').html('');
					
				}
			});

			return false;
		});