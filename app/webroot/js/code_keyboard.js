
var multiSelect = false;

$(document).ready(function(){
		

		// Track the Ctrl Key (Windows and Mac)
		KeyboardJS.bind.key('command', function(){
			multiSelect = true;
		},function(){
			multiSelect = false;
		});

		KeyboardJS.bind.key('backspace', chosenDelete);
		KeyboardJS.bind.key('delete', chosenDelete);
		KeyboardJS.bind.key('d', chosenDelete);


		function chosenDelete(){
			// See what we're deleting
			if($('input:focus').length > 0){
				console.log($('input:focus'));
				console.log('input has focus');
				return;
			}
			$('.chosen').each(function(i,elem){
				// Remove the thing
				// - awesome
				var url = $(elem).attr('data-remove-url');
				// Remove
				$('input:focus').blur(); // remove Focus from every input
				$(this).closest('[data-level]').remove();
				$('.popover').hide(); // temporary fix, doesn't actually remove
				
				$.ajax({
					url: url,
					cache: false,
					type: 'POST',
					success: function(response){
						try {
							var json = $.parseJSON(response);
						} catch(e){
							// Failed
							window.location = window.location.href;
						}
						console.log(json);
						if(json.code != 200){
							window.location = window.location.href;
						}

						return;

					}
				});
			});

			return false;
		}


		// Copying

		KeyboardJS.bind.key('shift + equal', copyElement);
		function copyElement(){
			// See what we're deleting
			if($('input:focus').length > 0){
				console.log($('input:focus'));
				console.log('input has focus');
				return;
			}

			// Can only be choosing one at a time?
			// - no real reason, just limiting potential user errors
			if($('.chosen').length > 1){
				console.log('Too many chosen to copy. limit=1');
				return false;
			}

			$('.chosen').each(function(i,elem){
				// Remove the thing
				// - awesome
				var url = $(elem).attr('data-copy-url');
				
				$.ajax({
					url: url,
					cache: false,
					type: 'POST',
					context: $(elem),
					success: function(response){
						try {
							var json = $.parseJSON(response);
						} catch(e){
							// Failed
							console.log('failed');
							//window.location = window.location.href;
							return;
						}

						// Insert
						//$(this).insertAfter();
						$("#t_conditionRow").tmpl(json).insertAfter($(this).closest('[data-level]'));

						return;

					}
				});
			});

			return false;
		}



		// Saving the result
		// - when ENTER is pressed (needs to be Keydown to work with Keyboard.js)
		$('body').on('keydown','.edit_inline input',function(e){
			var code = (e.keyCode ? e.keyCode : e.which);
			if(code == 13) { //Enter keycode
				// Save result
				var result = $(this).val();
				var data = {'data[Data][input1]': result};

				// If empty, remove
				if(result == ''){
					// Remove

					// Make remove request
					var url = $(this).parent().attr('data-remove-url');

					// Get next thing to select
					$(this).closest('[data-level]').next().addClass('key-selected');

					// Remove Action
					$('input:focus').blur(); // remove Focus from every input
					$(this).closest('[data-level]').remove();

					$.ajax({
						url: url,
						cache: false,
						type: 'POST',
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


				}


				// Make ajax call
				// - assume success, don't handle failure yet
				//var db_id = $(this).parents('.conditionRow').attr('db_id');
				var url = $(this).parent().attr('data-url');
				$.ajax({
					url: url,
					type: 'POST',
					data: data,
					success: function(response){
						
						try{
							var json = $.parseJSON(response);
						} catch(e){
							alert('Failed Validation');
							return false;
						}

						console.log(json);

					}
				});

				$(this).parent().parent().find('span.editable').text(result).removeClass('nodisplay');
				$('input:focus').blur(); // remove Focus from every input
				$(this).parent('.edit_inline').addClass('nodisplay');

				return false;
			}
		});


		//  Navigating using keyboard
		/*
		KeyboardJS.bind.key('i', arrow_up);
		KeyboardJS.bind.key('k', arrow_down);
		KeyboardJS.bind.key('j', arrow_left);
		KeyboardJS.bind.key('l', arrow_right);
		KeyboardJS.bind.key('c', collapse_row);
		*/

		// - UP
		function arrow_up(){
			// Have selected a Group-Level?
			if(!captureKeys){
				return;
			}

			// Prevent event bubbling
			//e.preventDefault();

			// Get the next Row in this Level
			var $this = $('.key-selected');
			console.log($this);
			var dl = $this.attr('data-level');
			var $next = $this.prev();
			

			// Must be same data-level (prevent going into actionRow)
			if($next.attr('data-level') == dl){
				// Great, same level
				// - go to it
				$this.removeClass('key-selected');
				$next.addClass('key-selected');
				//$.scrollTo('.key-selected',scrollOptions);
				scrollToInView($next);

				return;
			}

			// Add end of this, so try going up one level, then to the next one
			var $next_up = $this.parents('[data-level="'+(dl-1)+'"]');
			$next_up = $next_up.prev();

			// Go to the first data-level that matches original
			$next_same_dl = $next_up.find('[data-level="'+dl+'"]:last');
			if($next_same_dl.attr('data-level') == dl){
				$this.removeClass('key-selected');
				$next_same_dl.addClass('key-selected');
				//$.scrollTo('.key-selected',scrollOptions);
				scrollToInView($next_same_dl);
				return;
			}

		}
		// - DOWN
		function arrow_down(){
			// Have selected a Group-Level?
			console.log('down');
			if(!captureKeys){
				console.log('not capturing');
				return;
			}

			// Anything key-selected?
			if($('.key-selected').length <= 0){
				$('.conditionRow:first').addClass('key-selected');
				return;
			}

			// Prevent event bubbling
			//e.stopPropagation();

			var i = 0;
			$('.key-selected').each(function(index){
				i++;
			});

			if(i > 1){
				console.log('more than 1 selected');
				return;
			}

			// Get the next Row in this Level
			var $this = $('.key-selected');
			var dl = parseInt($this.attr('data-level'));
			var $next = $this.next();
			console.log('dls');
			console.log($next.attr('data-level'));
			console.log(dl);
			
			// Must be same data-level (prevent going into actionRow)
			if($next.attr('data-level') == dl){
				console.log('same DL');
				// Great, same level
				// - go to it
				$this.removeClass('key-selected');
				$next.addClass('key-selected');
				//$.scrollTo('.key-selected',scrollOptions);
				scrollToInView($next);
				return false;
			}

			// Add end of this, so try going up one level, then to the next one
			var $next_up = $this.parents('[data-level="'+(parseInt(dl)-1)+'"]');
			$next_up = $next_up.next();
			console.log('next');
			console.log($next_up);

			// Go to the first data-level that matches original
			$next_same_dl = $next_up.find('[data-level="'+dl+'"]:first');
			console.log($next_same_dl);
			if($next_same_dl.attr('data-level') == dl){
				console.log(2);
				$this.removeClass('key-selected');
				$next_same_dl.addClass('key-selected');
				//console.log($next_same_dl);
				//$.scrollTo('.key-selected',scrollOptions);
				scrollToInView($next_same_dl);
				return;
			}

			return false;

		}
		// - Right
		function arrow_right(){
			// Have selected a Group-Level?
			console.log('right');
			if(!captureKeys){
				return;
			}

			// Prevent event bubbling
			//e.stopPropagation();

			// Get the next "depth"

			// Get the next Row in this Level
			var $this = $('.key-selected');
			var dl = $this.attr('data-level');
			var next_level = (parseInt(dl)+1);

			if($this.attr('depth-search')){
				// Get first child for that next depth
				var $inside = $this.parent().parent().find($this.attr('depth-search'));
				var $next_up = $inside.find('[data-level="'+next_level+'"]:first-child');

				if($next_up.length > 0){
					// Move to that one
					// - make it visible

					if($inside.hasClass('collapsed')){
						$inside.parents('.stepRow').find('span.cancollapse').trigger('expand');
					}

					$this.removeClass('key-selected');
					$next_up.addClass('key-selected');
					
					$.scrollTo('.key-selected',scrollOptions);
					return;
				}
				
			} else {
				// Elements are direct children of this <div>
				var $next_up = $this.find('[data-level="'+next_level+'"]:first');
				console.log($next_up);
				if($next_up.length > 0){
					// Move to that one
					// - make it visible

					if($next_up.hasClass('collapsed')){
						$this.find('span.cancollapse[collapse-level="'+next_level+'"]').trigger('expand');
					}

					$this.removeClass('key-selected');
					$next_up.addClass('key-selected');
					
					$.scrollTo('.key-selected',scrollOptions);
					return;
				}
			}
			
			return;

		}
		// - Left
		function arrow_left(){
			// Have selected a Group-Level?
			if(!captureKeys){
				return;
			}

			// Prevent event bubbling
			//e.stopPropagation();

			// Get the next "depth"

			// Get the next Row in this Level
			var $this = $('.key-selected');
			var dl = $this.attr('data-level');

			if($this.parent().attr('data-level')){
				$this.removeClass('key-selected');
				$this.parent().addClass('key-selected');
				$.scrollTo('.key-selected',scrollOptions);
				return;
			}

			// Action Row?
			if($this.hasClass('actionRow')){
				// Move to Condition Row
				$this.removeClass('key-selected');
				var $cr = $this.closest('.actualStep').find('.conditionRow:first-child');
				$cr.addClass('key-selected');
				$.scrollTo('.key-selected',scrollOptions);
				return false;
			}

			var $up = $this.parent().closest('[data-level]');
			$this.removeClass('key-selected');
			$up.addClass('key-selected');
			$.scrollTo('.key-selected',scrollOptions);
			return;
			


			if($this.attr('depth-search').length > 0){
				// Get first child for that next depth
				var $inside = $this.parent().find($this.attr('depth-search'));
				var $next_up = $inside.find('[data-level="'+(parseInt(dl)+1)+'"]');
				console.log($next_up);
				if($next_up.length > 0){
					// Move to that one
					$this.removeClass('key-selected');
					$next_up.addClass('key-selected');
					$.scrollTo('.key-selected',scrollOptions);
					return;
				}
				
			}

			return;

		}
		// - ENTER (on key-selected)
		KeyboardJS.bind.key('enter', function(e){
			if(!captureKeys){
				console.log('no capture');
				return;
			}
			if($('input:focus').length > 0){
				console.log($('input:focus'));
				console.log('input has focus');
				return;
			}

			// Get key-selected
			// - click on the 'edit' button
			var $span = $('.key-selected').find('span.editable');
			if($span.length > 0){
				e.stopPropagation(); // Prevent ENTER from firing the edit field
				$span.click();
			}
			var $add = $('.key-selected.addAble')
			if($add.length > 0){
				$add.find('a').click();
			}
		});
		// Double-dlick
		$('body').on('dblclick','.key-selected',function(){
			var $span = $('.key-selected').find('span.editable');
			if($span.length > 0){
				$span.click();
			}
		});


		// Add key-selected events for rows
		$('body').on('click','[data-level]',function(e){
			$('.key-selected').removeClass('key-selected');
			$(this).addClass('key-selected');
			//e.stopPropagation();
		});


		// - "c" collapsing!
		// - BROKEN
		function collapse_row(){
			// Have selected a Group-Level?
			if(!captureKeys){
				return;
			}

			// Prevent event bubbling
			//e.stopPropagation();

			// Get the current depth

			// Get the next Row in this Level
			var $this = $('.key-selected');
			var dl = parseInt($this.attr('data-level'));

			// Get cancollapse element

			// Move the thing up
			// - like you pressed left
			// - will break for Steps...
			$('span.cancollapse[collapse-level="'+dl+'"]').trigger('expand');


			// Get the next Row in this Level
			var $this = $('.key-selected');
			var dl = $this.attr('data-level');

			if($this.parent().attr('data-level')){
				$this.removeClass('key-selected');
				$this.parent().addClass('key-selected');
				$.scrollTo('.key-selected',scrollOptions);
				return;
			}

			// Action Row?
			if($this.hasClass('actionRow')){
				// Move to Condition Row
				$this.removeClass('key-selected');
				var $cr = $this.closest('.actualStep').find('.conditionRow:first-child');
				$cr.addClass('key-selected');
				$.scrollTo('.key-selected',scrollOptions);
				return false;
			}

			var $up = $this.parent().closest('[data-level]');
			$this.removeClass('key-selected');
			$up.addClass('key-selected');
			$.scrollTo('.key-selected',scrollOptions);
			return;
			

			return;

			if($this.parent().attr('data-level')){
				$this.removeClass('key-selected');
				$this.parent().addClass('key-selected');
				$.scrollTo('.key-selected',scrollOptions);
				return;
			}

			// Action Row?
			if($this.hasClass('actionRow')){
				// Move to Condition Row
				$this.removeClass('key-selected');
				var $cr = $this.closest('.actualStep').find('.conditionRow:first-child');
				$cr.addClass('key-selected');
				$.scrollTo('.key-selected',scrollOptions);
				return false;
			}

			var $up = $this.parent().closest('[data-level]');
			$this.removeClass('key-selected');
			$up.addClass('key-selected');
			$.scrollTo('.key-selected',scrollOptions);
			return;
			


			if($this.attr('depth-search').length > 0){
				// Get first child for that next depth
				var $inside = $this.parent().find($this.attr('depth-search'));
				var $next_up = $inside.find('[data-level="'+(parseInt(dl)+1)+'"]');
				console.log($next_up);
				if($next_up.length > 0){
					// Move to that one
					$this.removeClass('key-selected');
					$next_up.addClass('key-selected');
					$.scrollTo('.key-selected',scrollOptions);
					return;
				}
				
			}

			return;

		}
});