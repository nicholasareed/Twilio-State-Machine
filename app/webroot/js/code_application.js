
var captureKeys = true;
var scrollOptions = {
						offset: -50,
						duration:0
					};

var project_id = 0;

$(document).ready(function(){

	// Get Project ID
	project_id = $('#Project').attr('project_id');

	// Add State
	$('body').on('click','.addState',function(){

		var url = $(this).attr('href');

		$(this).text('Loading...');

		$.ajax({
			url: url,
			type: 'GET',
			context: $(this),
			success: function(response){
				//$('.form_holder').html(response);
				//$('.form_holder').attr('form-type','addCondition');

				// Change text back
				$(this).text('Add a State');
				// Make link invisible
				$(this).parent().addClass('nodisplay');

				// Add form
				var $div_span = $(this).closest('div');
				$div_span.find('div.form').remove();
				$div_span.prepend('<div class="form">'+response+'</div>');
				$div_span.closest('.addAble').addClass('ignoreTransparent');

				console.log($div_span.find('form input:first'));
				$div_span.find('form select:first').focus().trigger('click');

			}
		});

		return false;

	});


	// Add Step
	$('body').on('click','.addStep',function(){
		// Add a Step

		var url = $(this).attr('href');

		$(this).text('Loading...');

		$.ajax({
			url: url,
			type: 'POST',
			data: {},
			context: $(this),
			success: function(response){
				try{
					var json = $.parseJSON(response);
				} catch(e){
					// Fuck
					console.log('Not JSON, as expected');
					return false;
				}

				$(this).text('Add Step');

				// Add Template to Form
				//console.log($("#t_step").tmpl(json));
				$("#t_step").tmpl(json).insertBefore('.stateRow[db_id="'+json.state_id+'"] .addStepRow');
				//console.log(json);
				//console.log('added');
			}
		});

		return false;

	});

	// Add Condition
	$('body').on('click','.addCondition',function(){
		// Open the Addition window
		// - above Logs

		var url = $(this).attr('href');

		$(this).text('Loading...');

		$.ajax({
			url: url,
			type: 'GET',
			context: $(this),
			success: function(response){
				//$('.form_holder').html(response);
				//$('.form_holder').attr('form-type','addCondition');

				// Change text back
				$(this).text('Add a Condition');
				// Make link invisible
				$(this).parent().addClass('nodisplay');

				// Add form
				var $div_span = $(this).closest('div');
				$div_span.find('div.form').remove();
				$div_span.prepend('<div class="form">'+response+'</div>');
				$div_span.closest('.addAble').addClass('ignoreTransparent');

				console.log($div_span.find('form input:first'));
				$div_span.find('form select:first').focus().trigger('click');

			}
		});

		return false;

	});

	// Edit Condition
	$('body').on('click','a.editCondition',function(){
		// Open the Addition window
		// - above Logs

		var url = $(this).attr('href');

		$.ajax({
			url: url,
			type: 'GET',
			success: function(response){
				$('.form_holder').html(response);
				$('.form_holder').attr('form-type','editCondition');
			}
		});

		return false;

	});
	
	// Add Action
	$('body').on('click','.addAction',function(){
		// Open the Addition window
		// - above Logs

		var url = $(this).attr('href');

		$(this).text('Loading...');

		$.ajax({
			url: url,
			type: 'GET',
			context: $(this),
			success: function(response){
				//$('.form_holder').html(response);
				//$('.form_holder').attr('form-type','addCondition');

				// Change text back
				$(this).text('Add an Action');
				// Make link invisible
				$(this).parent().addClass('nodisplay');

				// Add form
				var $div_span = $(this).closest('div');
				$div_span.find('div.form').remove();
				$div_span.prepend('<div class="form">'+response+'</div>');
				$div_span.closest('.addAble').addClass('ignoreTransparent');

				console.log($div_span.find('form input:first'));
				$div_span.find('form select:first').focus().trigger('click');

			}
		});

		return false;

	});

	// Edit Action
	$('body').on('click','a.editAction',function(){
		// Open the Addition window
		// - above Logs

		var url = $(this).attr('href');

		$.ajax({
			url: url,
			type: 'GET',
			success: function(response){
				$('.form_holder').html(response);
				$('.form_holder').attr('form-type','editAction');
			}
		});

		return false;

	});

	// Canceling Addition
	$('body').on('click','.add_cancel',function(){
		var $add_div = $(this).closest('.addAble');
		$('input:focus').blur();
		$('select:focus').blur();
		$add_div.find('form').remove();
		$add_div.find('h5').removeClass('nodisplay');
		$add_div.removeClass('ignoreTransparent');

		return false;
	});


	// Bind form submit
	$('body').on('submit','.addAble form',function(){

		// If resonse is JSON, then whoopee!
		var url = $(this).attr('action');

		var serializedData = $(this).serialize();

		$(this).find('.loadingGif').removeClass('nodisplay');
		
		$.ajax({
			url: url,
			type: 'POST',
			data: serializedData,
			context: $(this),
			success: function(response){
				try{
					var json = $.parseJSON(response);
				} catch(e){
					var $form = $(this).closest('div.form')
					$form.html(response);
					// Give input the focus
					$form.find('input[type="text"]').focus();
					return;
				}

				// Got JSON back
				// - means it was a success?
				
				// What did we just save?
				// - only adding a Condition or Action
				var call_type = 'addCondition';
				if($(this).closest('.addAble').hasClass('addActionRow')){
					call_type = 'addAction';
				}
				if($(this).closest('.addAble').hasClass('addStateRow')){
					call_type = 'addState';
				}

				// Get correct Text to use
				/*
				switch(call_type){

					case 'addCondition':
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
				*/

				// If success, then we add to the page, according to the $type
				// - jquery templates
				switch(call_type){

					case 'addCondition':
						var tmp = $("#t_conditionRow").tmpl(json);
						$("#t_conditionRow").tmpl(json).insertBefore('.stepRow[db_id="'+json.step_id+'"] > .actualStep > .conditionsRow > .addConditionRow');
						break;

					case 'addAction':
						var tmp = $("#t_actionRow").tmpl(json);
						$("#t_actionRow").tmpl(json).insertBefore('.stepRow[db_id="'+json.step_id+'"] .actionsRow > .addActionRow');
						break;

					case 'addState':
						var tmp = $("#t_stateRow").tmpl(json);
						$("#t_stateRow").tmpl(json).insertBefore('.addStateRow');
						break;
					
					default:
						// whoops
						console.log('Missed');
						console.log(call_type);
						break;

				}

				// Clear the form
				var $add_div = $(this).closest('.addAble');
				$('input:focus').blur();
				$('select:focus').blur();
				$add_div.find('div.form').remove();
				$add_div.find('h5').removeClass('nodisplay');
				$add_div.removeClass('ignoreTransparent');
				
			}
		});

		return false;
	});

	// Remove Condition
	$('body').on('click','.removeCondition',function(){
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
	$('body').on('click','.removeAction',function(){
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


	// Highlight related ProjectLog actions
	// - not used
	$('#logs').on('click','.pl_trigger',function(){
		
		//console.log($(this).attr('pl-type'));
		$('.connect').removeClass('connect');

		var options = {offset: -50,
						duration:500}

		// Find element to highlight
		switch($(this).attr('pl-type')){

			case 'received_sms':
				// Display all related information	
				break;
			
			case 'entered_state':
				$('.elem_state[db_id="'+$(this).attr('related_id')+'"]').addClass('connect');
				$.scrollTo('.elem_state[db_id="'+$(this).attr('related_id')+'"]',options);
				break;
			
			case 'triggered_step':
				$('.stepRow[db_id="'+$(this).attr('related_id')+'"]').addClass('connect');
				$.scrollTo('.stepRow[db_id="'+$(this).attr('related_id')+'"]',options);
				break;
			
			case 'action_webhook':
			case 'action_attribute':
			case 'action_state_change':
			case 'action_sent_sms':
				var $actionsRow = $('.actionRow[db_id="'+$(this).attr('related_id')+'"]');
				$actionsRow.addClass('connect');
				if($actionsRow.parents('.actionsRow').hasClass('collapsed')){
					$actionsRow.parents('.stepRow').find('span.cancollapse').trigger('click');
				}
				// Must be visible

				$.scrollTo('.actionRow[db_id="'+$(this).attr('related_id')+'"]',options);
				//console.log('.actionRow[db_id="'+$(this).attr('related_id')+'"]');
				break;
			
			default:
				// An old $type
				console.log('missed');
				console.log($(this).attr('pl-type'));
				break;
		}
		


	});

	// More Logs
	$('.moreLogs').on('click',function(){
		outgoing();
		return false;
	});

	// Refresh the Aoo/User Database
	$('.refreshDatabase').on('click',function(){
		refreshDatabase();
		return false;
	});


	$('body').on('click','span.cancollapse',function(){
		$(this).trigger('expand');
	});
	$('body').on('expand','span.cancollapse',function(){
		if($(this).hasClass('collapsed')){
			// Expand
			// - State or Step?
			if($(this).hasClass('state_collapse')){
				$(this).parents('.stateRow').find('.stepRow').removeClass('collapsed');
				$(this).removeClass('collapsed');
				$(this).html('&nbsp;-&nbsp;')
			} else {
				$(this).parents('.stepRow').find('.actionsRow').removeClass('collapsed');
				$(this).removeClass('collapsed');
				$(this).html('&nbsp;-&nbsp;')
			}
		} else {
			// Collapse
			if($(this).hasClass('state_collapse')){
				$(this).parents('.stateRow').find('.stepRow').addClass('collapsed');
				$(this).addClass('collapsed');
				$(this).html('&nbsp;+&nbsp;')
			} else {
				$(this).parents('.stepRow').find('.actionsRow').addClass('collapsed');
				$(this).addClass('collapsed');
				$(this).html('&nbsp;+&nbsp;')
			}

		}
	});


	$('.sendSms').on('click',function(){
		if($(this).parent('#test_view').hasClass('collapsed')){
			// Expand
			$(this).parent('#test_view').removeClass('collapsed');
			$(this).text('Collapse Testing Window');
		} else {
			// Collapse
			$(this).parent('#test_view').addClass('collapsed');
			$(this).text('Send Test SMS');
		}
		return false;
	});


	$('#ConditionType').on('change',function(){

		if($(this).val() == 'default'){
			$('form input[type="submit"]').val('Add Condition');
		} else {
			$('form input[type="submit"]').val('Next');
		}

	});


	// Edit a Condition/Action

	// Clicking Edit link for a Condition
	$('body').on('click','span.editable',function(){
		$(this).addClass('nodisplay');
		$(this).parent().find('.edit_inline').removeClass('nodisplay').find('input').focus();
	});

	$('body').on('focus','input',function(){
		captureKeys = false;
	});
	$('body').on('focus','select',function(){
		captureKeys = false;
	});
	$('body').on('blur','input',function(){
		captureKeys = true;
	});
	$('body').on('blur','select',function(){
		captureKeys = true;
	});


	// Load all JSON
	window.setTimeout(function(){
		//console.log('loading JSON data');

		$.ajax({
			url: '/projects/view_json/'+project_id,
			type: 'GET',
			success: function(response){
				//console.log(response);
				var json = $.parseJSON(response);
				console.log(json);
				$('#project_view').html('');
				$("#t_project").tmpl(json).appendTo('#project_view');

				/*$.each(json.State,function(i,v){
					
				});*/

			}
		});
	},1000);

	window.setTimeout('outgoing',5000);

});


// Get outgoing messages
// - use websockets for this
function outgoing(){
	// Returns outgoing messages
	// - in order

	// get last ID
	// - if one exists
	var last_id = 0;
	if($('#logs > div:first-child').length){
		// One exists
		// - get first
		last_id = $('#logs > div:first-child').attr('pl_id');
	}

	var url = '/projects/logs/'+project_id+'/'+last_id;

	$.ajax({
		url: url,
		success: function(responseHtml){
			var json = $.parseJSON(responseHtml);

			// Sort
			json.sort(function(a,b) { return parseFloat(a.ProjectLog.id) - parseFloat(b.ProjectLog.id) } );

			$.each(json, function(i,v){
				// Add to <div>

				var pl = v.ProjectLog;

				// Use correct ProjectLog.type
				switch(pl.type){

					case 'received_sms':
						var sms_data = $.parseJSON(pl.data);
						$('#logs').prepend('<div style="display:none;" pl_id="'+pl.id+'" class="pl_trigger fadeIn" pl-type="'+pl.type+'" related_id="'+pl.related_id+'">'+'Received SMS: "'+sms_data.Body+'"</div>');
						break;
					
					case 'entered_state':
						var state_data = $.parseJSON(pl.data);
						$('#logs').prepend('<div style="display:none;" pl_id="'+pl.id+'" class="pl_trigger fadeIn" pl-type="'+pl.type+'" related_id="'+pl.related_id+'">'+'Entered State: '+state_data.key+'</div>');
						// Bind hover
						/*$('.pl_trigger[pl-type="'+pl.type+'"][related_id="'+pl.related_id+'"]').hover(function(){
							$('.elem_state[db_id="'+pl.related_id+'"]').addClass('connect');
						},function(){
							$('.elem_state[db_id="'+pl.related_id+'"]').removeClass('connect');
						});*/
						break;
					
					case 'triggered_step':
						var step_data = $.parseJSON(pl.data);
						$('#logs').prepend('<div style="display:none;" pl_id="'+pl.id+'" class="pl_trigger fadeIn" pl-type="'+pl.type+'" related_id="'+pl.related_id+'">'+'Triggered Step'+'</div>');
						// Bind hover
						/*$('.pl_trigger[pl-type="'+pl.type+'"][related_id="'+pl.related_id+'"]').hover(function(){
							$('.stepRow[db_id="'+pl.related_id+'"]').addClass('connect');
						},function(){
							$('.stepRow[db_id="'+pl.related_id+'"]').removeClass('connect');
						});*/
						break;
					
					case 'action_webhook':
						var webhook_data = $.parseJSON(pl.data); // Holds the URL, etc.
						//console.log(webhook_data);
						$('#logs').prepend('<div style="display:none;" pl_id="'+pl.id+'" class="pl_trigger fadeIn" pl-type="'+pl.type+'" related_id="'+pl.related_id+'">'+'Webhook: '+webhook_data.url+'</div>');
						break;
					
					case 'action_attribute':
						var attribute_data = $.parseJSON(pl.data);
						$('#logs').prepend('<div style="display:none;" pl_id="'+pl.id+'" class="pl_trigger fadeIn" pl-type="'+pl.type+'" related_id="'+pl.related_id+'">'+'Set Attibute: "'+attribute_data.tmp+'"'+'</div>');
						break;
					
					case 'action_state_change':
						var state_change_data = $.parseJSON(pl.data);
						$('#logs').prepend('<div style="display:none;" pl_id="'+pl.id+'" class="pl_trigger fadeIn" pl-type="'+pl.type+'" related_id="'+pl.related_id+'">'+'Changed state: "'+state_change_data.old_state+'" to "'+state_change_data.new_state+'"'+'</div>');
						break;
					
					case 'action_sent_sms':
						var sms_data = $.parseJSON(pl.data);
						$('#logs').prepend('<div style="display:none;" pl_id="'+pl.id+'" class="pl_trigger fadeIn" pl-type="'+pl.type+'" related_id="'+pl.related_id+'">'+'Sent SMS: "'+sms_data.From+'" to "'+sms_data.To+'" body "'+sms_data.Body+'"</div>');
						break;
					
					default:
						console.log('Missed all types');
						console.log(pl.type);
						break;

				}

				//$('#recent_outgoing').prepend('<div style="display:none;" sent_id="'+v.id+'">'+v.to_ptn+': '+v.text+'</div>');

				// Fade it in
				$('.fadeIn').fadeIn('slow').removeClass('fadeIn');

			});

			// Run again in 5 seconds
			var time_to_run = 6000;
			window.setTimeout(outgoing,time_to_run*1000);
		}
	});

}

// Refresh Database
function refreshDatabase(){
	// Returns outgoing messages
	// - in order

	var url = '/projects/db/'+project_id;

	$.ajax({
		url: url,
		success: function(responseHtml){
			
			// Update Database display
			$('.database_holder').html(responseHtml);

		}
	});
}
