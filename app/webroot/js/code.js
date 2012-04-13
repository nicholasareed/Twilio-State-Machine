
$(document).ready(function(){
	//$('[rel=twipsy]').twipsy();

	// Hide Modal
	$('#addEditModal').modal({
		show: false
	})

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
	$('.tabbable').tab()

	// Search box
	$('.search-box input').keypress(function(e){
		if(e.which == 13){
	 		$('form.search-box').submit();
	 		e.preventDefault();
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


function scrollToInView(el){
	// var el = $(jquery object)

	var ps = getPageScroll();
	var ph = getPageHeight();

	var top = ps[1];
	var bottom = ps[1] + ph;

	var el_offset = el.offset();
	var el_top = el_offset.top;
	var el_bottom = el_offset.top + el.height();

	// Too high?
	if(el_top < top){
		$.scrollTo(el,{offset:-50});
		return;
	}

	// Too low?
	if(el_bottom > bottom){
		var top_offset = -ph + 50;
		$.scrollTo(el,{offset: top_offset});
		return;
	}

	//console.log('in view');

	return;

}


// getPageScroll() by quirksmode.com
function getPageScroll() {
    var xScroll, yScroll;
    if (self.pageYOffset) {
      yScroll = self.pageYOffset;
      xScroll = self.pageXOffset;
    } else if (document.documentElement && document.documentElement.scrollTop) {
      yScroll = document.documentElement.scrollTop;
      xScroll = document.documentElement.scrollLeft;
    } else if (document.body) {// all other Explorers
      yScroll = document.body.scrollTop;
      xScroll = document.body.scrollLeft;
    }
    return new Array(xScroll,yScroll)
}

// Adapted from getPageSize() by quirksmode.com
function getPageHeight() {
    var windowHeight
    if (self.innerHeight) { // all except Explorer
      windowHeight = self.innerHeight;
    } else if (document.documentElement && document.documentElement.clientHeight) {
      windowHeight = document.documentElement.clientHeight;
    } else if (document.body) { // other Explorers
      windowHeight = document.body.clientHeight;
    }
    return windowHeight
}