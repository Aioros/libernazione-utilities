wp.api.loadPromise.done( function() {
	
	var commentsCollection = new wp.api.collections.Comments();
	var comments = commentsCollection.fetch({data: {"post": libComments.post_id}}).done(function(comments) {
		console.log(comments);
	});

} )