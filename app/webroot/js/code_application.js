
var captureKeys = true;
var scrollOptions = {
						offset: -50,
						duration:0,
						axis: 'y'
					};

var project_id = 0;

var elemWasChosen = false;
var popoverDisplayed = false;

var last_event = null;
var last_ui = null;

$(document).ready(function(){

	// Get Project ID
	project_id = $('#Project').attr('project_id');

	// Remove yellowIn
	$('body').on('isYellow','.yellowIn',function(){
		// Clear it in a minute
		//$(this).delay(2000).removeClass('yellowIn');
		setTimeout($.proxy(function(){
			$(this).removeClass('yellowIn');
			},$(this)), 5000);
	});

	// Add State
	$('body').on('click','.addState',function(){

		var url = $(this).attr('href');

		$(this).text('Loading...');

		$.ajax({
			url: url,
			type: 'GET',
			context: $(this),
			success: function(response){

				$(this).text('+state');

				// Create Modal
				$('#addEditModal').html(response).modal('show');
				$('#addEditModal').find('select:first').focus();

				return;

			}
		});


		return false;


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


				$(this).text('+else if');

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

				$(this).text($(this).attr('original'));

				// Create Modal
				$('#addEditModal').html(response).modal('show');
				$('#addEditModal').find('select:first').focus();

				return;

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
				$(this).text($(this).attr('original'));

				// Create Modal
				$('#addEditModal').html(response).modal('show');
				$('#addEditModal').find('select:first').focus();

			}
		});

		return false;

	});

	// Edit Action
	$('body').on('click','a.editAction',function(){
		// Open the Addition window
		// - above Logs

		console.log('not used');
		return;

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

		// Close Modal
		$('.modal').modal('hide');
		return false;

		var $add_div = $(this).closest('.addAble');
		$('input:focus').blur();
		$('select:focus').blur();
		$add_div.find('form').remove();
		$add_div.find('h5').removeClass('nodisplay');
		$add_div.removeClass('ignoreTransparent');

		return false;
	});


	// Multi-select conditionOrAction
	$('body').on('click','.conditionOrAction',multiSelectItems);
	$('body').on('click','.elseif',multiSelectItems);

	function multiSelectItems(){
		// See if any other elements are currently selected

		// Remove popovers
		//$('.chosen').popover('hide');

		if(multiSelect){
			// Do not unselect other ones
			// - should de-select if different types of selection objects?

		} else {
			// De-select other ones
			$('.chosen').removeClass('chosen');

			// Trigger popover
			// - find popover html
			//$(this).attr('title','');
			/*
			$(this).popover({trigger: 'manual',
							 title: false,
							 content: $(this).find('.popover').html(),
							 use_alt_template: true,
							 template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"></div></div></div>'});
			$(this).popover('show');
			*/
			popoverDisplayed = true;

		}

		$(this).addClass('chosen');
		elemWasChosen = true;

		$(this).focus();

	}

	// Canceling clicking of elements
	$('body').on('click',function(){
		// See if the 'chosen exists'

		// Popover
		if(!popoverDisplayed){
			// Remove popovers
			//$('.chosen').popover('hide');
		}

		if(elemWasChosen == false){
			//console.log('no chosen');
			//$('.chosen').removeClass('chosen');
			// Remove popovers

		} else {
			//console.log('was chosen');

		}

		popoverDisplayed = false;
		elemWasChosen = false;
	});


	// Bind form submit
	$('#addEditModal').on('submit','form.adding',function(){

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
					var $form = $(this).closest('.modal')
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
				if($(this).hasClass('addingAction')){
					call_type = 'addAction';
				}
				if($(this).hasClass('addingState')){
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
						$("#t_conditionRow").tmpl(json).insertBefore('.stepRow[db_id="'+json.step_id+'"] > .actualStep > .conditionsRow > .addConditionRow').addClass('yellowIn').trigger('isYellow');
						break;

					case 'addAction':
						var tmp = $("#t_actionRow").tmpl(json);
						$("#t_actionRow").tmpl(json).insertBefore('.stepRow[db_id="'+json.step_id+'"] .actionsRow > .addActionRow').addClass('yellowIn').trigger('isYellow');
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
				$('#addEditModal').modal('hide');

				return false


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


	// Bind editing form submit
	$('#addEditModal').on('submit','form.editing',function(){

		// If resonse is JSON, then whoopee!
		var url = $(this).attr('action');

		var serializedData = $(this).serialize();

		$.ajax({
			url: url,
			type: 'POST',
			data: serializedData,
			context: $(this),
			success: function(response){
				try{
					var json = $.parseJSON(response);
				} catch(e){
					var $form = $(this).closest('.modal')
					$form.html(response);
					// Give input the focus
					$form.find('input[type="text"]').focus();
					return;
				}

				// Got JSON back
				// - means it was a success?
				
				// What did we just save?
				// - only adding a Condition or Action
				var call_type = 'editCondition';
				if($(this).hasClass('editingAction')){
					call_type = 'editAction';
				}
				// Step has no settings, so nothing to edit
				if($(this).hasClass('editingState')){
					call_type = 'editingState';
				}
				// If success, then we insert into the page, according to the $type
				// - jquery templates
				// - insertAfter the element we editing, and delete that element
				console.log('hi');
				switch(call_type){

					case 'editCondition':
						var $orig = $('.conditionRow[db_id="'+json.msg.id+'"]');
						var tmp = $("#t_conditionRow").tmpl(json.msg);
						tmp.insertAfter($orig).addClass('yellowIn').trigger('isYellow');
						$orig.remove();
						break;

					case 'editAction':
						var $orig = $('.actionRow[db_id="'+json.msg.id+'"]');
						var tmp = $("#t_actionRow").tmpl(json.msg);
						tmp.insertAfter($orig).addClass('yellowIn').trigger('isYellow');
						$orig.remove();
						break;

					case 'editState':
						var $orig = $('.stateRow[db_id="'+json.msg.id+'"]');
						var tmp = $("#t_stateRow").tmpl(json.msg);
						tmp.insertAfter($orig).addClass('yellowIn').trigger('isYellow');
						$orig.remove();
						break;
					
					default:
						// whoops
						console.log('Missed');
						console.log(call_type);
						break;

				}

				// Clear the form
				$('#addEditModal').modal('hide');

				return false
				
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
		$(this).addClass('connect');

		var options = {offset: -50,
						duration:500,
						axis: 'y'}

		// Find element to highlight
		switch($(this).attr('pl-type')){

			case 'received_sms':
				// Display all related information	
				break;
			
			case 'entered_state':
				$('.elem_state[db_id="'+$(this).attr('related_id')+'"]').addClass('connect');
				$('#project_view').scrollTo('.elem_state[db_id="'+$(this).attr('related_id')+'"]',options);
				break;
			
			case 'triggered_step':
				$('.stepRow[db_id="'+$(this).attr('related_id')+'"]').addClass('connect');
				$('#project_view').scrollTo('.stepRow[db_id="'+$(this).attr('related_id')+'"]',options);
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

				$('#project_view').scrollTo('.actionRow[db_id="'+$(this).attr('related_id')+'"]',options);
				//console.log('.actionRow[db_id="'+$(this).attr('related_id')+'"]');
				break;
			
			default:
				// An old $type
				console.log('missed');
				console.log($(this).attr('pl-type'));
				break;
		}
		


	});


	// Project Settings
	$('body').on('submit','#ProjectSetting',function(){

		// Handle submitting
		// - probably should just do this in a Modal...

		var url = $(this).attr('action');

		var serializedData = $(this).serialize();

		$.ajax({
			url: url,
			type: 'POST',
			data: serializedData,
			context: $(this),
			success: function(response){
				try{
					var json = $.parseJSON(response);
				} catch(e){
					$(this).find('.actions span').html('<i class="icon-exclamation-sign"></i> Failed saving!');

					setTimeout($.proxy(function(){
						$(this).find('.actions span').html(' ');
						},$(this)), 3000);
					return;
				}

				if(json.code == 200){
					// Saved nicely
					$(this).find('.actions span').html('<i class="icon-ok"></i> Saved Settings');

					// Update State display
					if($(this).find('#ProjectEnableState:checked').length){
						// Show State
						$('[state-status]').attr('state-status',1);
					} else {
						// Hide State
						$('[state-status]').attr('state-status',0);
					}

				} else {
					// Darn
					$(this).find('.actions span').html('<i class="icon-exclamation-sign"></i> Failed saving!');
				}

				// Remove text in a few seconds
				setTimeout($.proxy(function(){
					$(this).find('.actions span').html(' ');
					},$(this)), 3000);

			}
		});


		return false;
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

	// Send SMS Form
	$('a.testTab').on('click',function(){
		if($('#test_view').hasClass('collapsed')){
			// Expand
			$('#test_view').removeClass('collapsed');
			$('#advancedOptions').addClass('collapsed');
			//$(this).text('Hide Testing Window');
			//$(this).addClass('btn-danger');
			//$(this).removeClass('btn-success');
		} else {
			// Collapse
			$('#test_view').addClass('collapsed');
			//$(this).text('Test Application');
			//$(this).addClass('btn-success');
			//$(this).removeClass('btn-danger');
		}
		return false;
	});

	// Advanced Options visibility
	$('a.advTab').on('click',function(){
		if($('div#advancedOptions').hasClass('collapsed')){
			// Expand
			$('#advancedOptions').removeClass('collapsed');
			$('#test_view').addClass('collapsed');
			//$(this).text('Hide Adv. Options');
			//$(this).addClass('btn-danger');
		} else {
			// Collapse
			$('div#advancedOptions').addClass('collapsed');
			//$(this).text('Advanced Options');
			//$(this).removeClass('btn-danger');
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
	$('body').on('dblclick','span[data-edit-url]',function(){
		//$(this).addClass('nodisplay');
		//$(this).parent().find('.edit_inline').removeClass('nodisplay').find('input').focus();

		// Open up modal

		var url = $(this).attr('data-edit-url');

		$.ajax({
			url: url,
			type: 'GET',
			context: $(this),
			success: function(response){
				//$('.form_holder').html(response);
				//$('.form_holder').attr('form-type','addCondition');

				// Create Modal
				$('#addEditModal').html(response).modal('show');
				$('#addEditModal').find('select:first').focus();

			}
		});


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

		console.log('loading');

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
				
				// Sortable starting
				$('.conditionsRow').sortable({
					connectWith: ".conditionsRow",
					handle: '.conditionOrAction',
					items: ".conditionRow:not(.addAble)",
					start: function(event, ui){
						// Add a class!
						//ui.helper.css('background','blue');
						ui.item.addClass('is-being-dragged');
						//ui.placeholder.css('background','green');
					},
					update: function(event, ui) {
						//console.log(event);

						if (this !== ui.item.parent()[0]) {
							// only get the Last event
							// - doesn't fire on a placeholder element!
							return;
						}


						// Two elements have changed position
						// - update both of them?
						// - I think only one needs to be updated

						// If Sender exists, then I need to change the Condition.step_id
						var new_step_id = $(this).closest('.stepRow').attr('db_id');
						
						var $this = $(ui.item); // .conditionRow

						//if($this.hasClass('moved')){
							// Two events fired on Update
							// - returning here means we don't know where it is going!
							//return;
						//}
						//$this.addClass('moved');

						//console.log('moved');

						// Ignore .addAble row
						if($this.hasClass('addAble')){
							console.log('is AddAble');
							return;
						}

						// Figure out new position
						var index = $this.index();
						
						// Below the +condition ?
						if((index+1) == $this.closest('.conditionsRow').find('.conditionRow').length){
							$this.insertBefore($this.prev());
							index = $this.index();
						}
						var new_order = index+1;

						// Get ID
						var id = $this.attr('db_id');

						// Ajax update call
						// - sending current position
						$.ajax({
							url: '/conditions/move/'+id+'/'+new_order+'/'+new_step_id,
							type: 'POST',
							success: function(response){
								try {
									var json = $.parseJSON(response);
								} catch(e) {
									console.log('Failed loading as json');
									console.log(response);
									return;
								}

							}
						})

					}
				});
				$('.actionsRow').sortable({
					connectWith: ".actionsRow",
					handle: '.conditionOrAction',
					items: ".actionRow:not(.addAble)",
					update: function(event, ui) {

						if (this !== ui.item.parent()[0]) {
							// only get the Last event
							// - doesn't fire on a placeholder element!
							return;
						}


						// Two elements have changed position
						// - update both of them?
						// - I think only one needs to be updated

						// If Sender exists, then I need to change the Condition.step_id
						var new_step_id = $(this).closest('.stepRow').attr('db_id');
						
						var $this = $(ui.item); // .conditionRow

						// Ignore .addAble row
						if($this.hasClass('addAble')){
							console.log('is AddAble');
							return;
						}

						// Figure out new position
						var index = $this.index();
						
						// Below the +condition ?
						if((index+1) == $this.closest('.actionsRow').find('.actionRow').length){
							$this.insertBefore($this.prev());
							index = $this.index();
						}
						var new_order = index+1;

						// Get ID
						var id = $this.attr('db_id');

						// Ajax update call
						// - sending current position
						$.ajax({
							url: '/actions/move/'+id+'/'+new_order+'/'+new_step_id,
							type: 'POST',
							success: function(response){
								try {
									var json = $.parseJSON(response);
								} catch(e) {
									console.log('Failed loading as json');
									console.log(response);
									return;
								}

							}
						})

					}
				});
				$('.stepRows').sortable({
					connectWith: ".stepRows",
					handle: '.elseif',
					items: ".stepRow:not(.addAble)",
					update: function(event, ui) {

						if (this !== ui.item.parent()[0]) {
							// only get the Last event
							// - doesn't fire on a placeholder element!
							return;
						}


						// Two elements have changed position
						// - update both of them?
						// - I think only one needs to be updated

						// If Sender exists, then I need to change the Step.state_id
						var new_state_id = $(this).closest('.stateRow').attr('db_id');
						
						var $this = $(ui.item); // .conditionRow

						// Ignore .addAble row
						if($this.hasClass('addAble')){
							console.log('is AddAble Row');
							return;
						}

						// Figure out new position
						var index = $this.index();
						
						// Below the +elseif ?
						if((index+1) == $this.closest('.stepRows').find('.stepRow').length){
							$this.insertBefore($this.prev());
							index = $this.index();
						}
						var new_order = index+1;

						// Get ID
						var id = $this.attr('db_id');

						// Ajax update call
						// - sending current position
						$.ajax({
							url: '/steps/move/'+id+'/'+new_order+'/'+new_state_id,
							type: 'POST',
							success: function(response){
								try {
									var json = $.parseJSON(response);
								} catch(e) {
									console.log('Failed loading as json');
									console.log(response);
									return;
								}

							}
						})

					}
				});

			}
		});
	},1000);

	// Test SMS form
	$('#TextTest input[type="submit"]').click(function(e){
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


	// Help icons
	$('body').on('click','i.help-icon',function(){
		// Display the help message
		trigger_help($(this).attr('data-help-trigger'));
	});

	// - not this next part
	$('body').on('hover','.conditionOrAction',function(event){
		if(event.type == 'mouseenter'){
			// Display popover
			if($(this).find('.popover').length > 0){
				$(this).popover({trigger: 'manual',
								 content: $(this).find('.popover').html(),
								 use_alt_template: true,
								 template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"></div></div></div>'});
				$(this).popover('show');
			}
		} else {
			// Hide popover
			$(this).popover('hide');

		}
	});

	$(this).popover({trigger: 'manual',
					 content: $(this).find('.popover').html(),
					 use_alt_template: true,
					 template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><div class="popover-content"></div></div></div>'});


	window.setInterval(function(){
		// Fuck it, do an interval
		onreload();

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


function onreload(){
	// Function to call when page should be "refreshed" with js events

	/*
	// Tooltips for "double-click edit"
	$('.do-tooltip').tooltip({
								trigger: 'manual',
								delay: {show:100}
							})
	$('body').on('click','.do-tooltip', function(){
		if(!isBound($(this))){return;}
		$('.do-tooltip').tooltip('hide');
		$(this).tooltip('show');
	});
	$('body').on('click','.tooltip',function(){
		if(!isBound($(this))){return;}
		$('.do-tooltip').tooltip('hide');
	});
*/
}


function isBound($this){
	if($this.hasClass('bound')){
		return false;
	}
	$this.addClass('bound');
	return true;
}


function trigger_help(template){
	// Triggers a help message to be auto-displayed
	// - opens Help window, displays correct template

	// Clear existing
	$('#help_message').html('');

	// Add new help message
	$('#t_help_'+template).tmpl().appendTo('#help_message');

	// Display it
	// - hide test_view (if shown)
	// - show Help window
	$('.help_panel_tab').trigger('click');
	$('#test_view').addClass('collapsed');
	$('#advancedOptions').removeClass('collapsed');

	$.scrollTo('#help_message');

}
