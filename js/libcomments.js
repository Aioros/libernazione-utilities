wp.api.loadPromise.done( function() {
	
	var commentsCollection = new wp.api.collections.Comments();
	var comments = commentsCollection.fetch();
	console.log(comments);

} )