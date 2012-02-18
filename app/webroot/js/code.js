
$(document).ready(function(){
	//$('[rel=twipsy]').twipsy();

	// Dropdown example for topbar nav
	// ===============================

	$('li.dropdown').hover(function(){
		$(this).addClass('open');
	},function(){
		$(this).removeClass('open');
	});

	// Sortable Tables
	$("table.sortable").tablesorter(); 

	// Tabs
	$('.tabs').tabs()

	// Search box
	$('.search-box input').keypress(function(e){
		if(e.which == 13){
	 		$('form.search-box').submit();
	 		e.preventDefault();
		}
	});

	// Select Carrier - Requirements dropdown
	$('.select-carriers input[type="checkbox"]').bind('change',function(){
		if($(this).attr('checked') == 'checked'){
			$('.carrier-warnings[carrier_id="'+$(this).val()+'"]').show();
		} else {
			$('.carrier-warnings[carrier_id="'+$(this).val()+'"]').hide();
		}
	});

	// Software checkboxes
	$('.select-software input[type="checkbox"]').bind('change',function(){
		// Any selected?
		if($('.select-software input[type="checkbox"]:checked').length){
			$('#SoftwareNone').removeAttr('checked');
		} else {
			$('#SoftwareNone').attr('checked','checked');
		}
	});

	// CD same as PT
	$('#UserCdSamePt').bind('change',function(){
		if($(this).attr('checked').length){
			// Match PT info
			$('#UserCdBank').val($('#UserPtBank').val());
			$('#UserCdBranch').val($('#UserPtBranch').val());
			$('#UserCdAccountNumber').val($('#UserPtAccountNumber').val());
			$('#UserCdRoutingNumber').val($('#UserPtRoutingNumber').val());
		}
	});

	// Empty Tables
	// - a blank row is added that spans everything
	$('table').each(function(){
		// Count the number of rows
		var rows = $(this).find('tbody tr').size();
		var headers = $(this).find('th').size();

		// Must be a header row, and not a header-less table
		if(rows == 0){
			// Add a row
			var html = '<tr><td class="empty-row" colspan="'+headers+'">Empty Table</td></tr>';
			$(this).find('tbody').html(html);
		}

	});




});