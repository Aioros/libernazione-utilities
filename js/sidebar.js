jQuery(document).ready(function($) {

	if ($(".sidebar").length > 0) {
		
		var lastPageTop = 0;
		var scrollingSidebar = $('.sidebar');
    	var initialOffset = scrollingSidebar.offset();
    	var initialMarginTop = parseInt(scrollingSidebar.css("marginTop"));
    	initialOffset.top -= initialMarginTop;
    	initialOffset.left -= parseInt(scrollingSidebar.css("marginLeft"));

		$(window).scroll(function() {
			var headerHeight = ($("#site-header").hasClass("hidden") ? 0 : $("#site-header").offset().top - $(window).scrollTop() + $("#site-header").outerHeight());
	        var currentPageTop = $(window).scrollTop() + headerHeight;
	        var delta = currentPageTop - lastPageTop;
	        var currentPageBottom = currentPageTop + $(window).height();
	        var currentSidebarTop = scrollingSidebar.offset().top - initialMarginTop;
	        var currentSidebarBottom = currentSidebarTop + scrollingSidebar.height();
	        
	        if (delta > 0 && currentPageBottom > currentSidebarBottom && currentSidebarBottom + delta < $(document).height()) {
	        	scrollingSidebar.css("marginTop", "+=" + delta);
	        }
	        if (delta < 0 && currentPageTop < currentSidebarTop) {
				scrollingSidebar.css("marginTop", "+=" + Math.max(delta, initialMarginTop - parseInt(scrollingSidebar.css("marginTop"))));
	        }

	        lastPageTop = currentPageTop;
	    });

	}

});
