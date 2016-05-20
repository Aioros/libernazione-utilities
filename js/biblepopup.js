jQuery(document).ready(function($) {
	$(document).on("click", ".bible-link", function() {
		var popup = $($(this).attr("href"));
		popup.parent().show();

		return false;
	})
});