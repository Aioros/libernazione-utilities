(function() {

	var $ = jQuery;

	$.fn.isVisible = function(){
	    var element = this.get(0);
	    var bounds = element.getBoundingClientRect();
	    return bounds.top < window.innerHeight && bounds.bottom > 0;
	}

	// Ritorna una funzione che carica uno script esterno e lo inserisce prima del primo script in pagina (fisso)
	var loadScript = (function(firstScript) {
	  return function(src, onload) {
	    var script = document.createElement('script');
	    script.src = src;
	    firstScript.parentNode.insertBefore(script, firstScript);
	    script.onload = onload;
	  }
	}(document.getElementsByTagName('script')[0]));

	window.libStartSocial = function() {
		loadScript('https://platform.twitter.com/widgets.js');
		loadScript('//connect.facebook.net/en_US/all.js#xfbml=1&appId=370346486491395');
		loadScript('https://apis.google.com/js/platform.js');
	};

	var scrollTimer, lastScrollFireTime = 0;
	var direction;

	$(window).scroll(function() {
		var minScrollTime = 100;
	    var now = new Date().getTime();

	    function processScroll() {
	    	if ($(".social-buttons-placeholder").parent().outerHeight() > 400) {
		    	$(".social-buttons-placeholder").each(function() {
		    		var visible = $(this).isVisible();
		    		var position = $(this).attr("data-position");
		    		if (visible && position == "top" && !$(".social-buttons").hasClass("top")) {
		    			$(".social-buttons").css({top: "20px", bottom: "auto"}).removeClass("bottom").addClass("top");
		    		} else if (visible && position == "bottom" && !$(".social-buttons").hasClass("bottom")) {
		    			$(".social-buttons").css({top: "auto", bottom: "20px"}).removeClass("top").addClass("bottom");
		    		}
		    	});
		    }
	    }

	    if (!scrollTimer) {
	        if (now - lastScrollFireTime > (3 * minScrollTime)) {
	            processScroll();   // fire immediately on first scroll
	            lastScrollFireTime = now;
	        }
	        scrollTimer = setTimeout(function() {
	            scrollTimer = null;
	            lastScrollFireTime = new Date().getTime();
	            processScroll();
	        }, minScrollTime);
	    }

	});

})();