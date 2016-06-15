jQuery('document').ready(function($) {

    var commentform = $('#commentform'); // find the comment form
    commentform.prepend('<div id="comment-status"></div>'); // add info panel before the form to provide feedback or errors
    var statusdiv = $('#comment-status'); // define the infopanel

    commentform.submit(function() {
        //serialize and store form data in a variable
        var formdata = commentform.serialize();
        //Add a status message
        statusdiv.html('<p>Invio commento in corso...</p>');
        //Extract action URL from commentform
        var formurl = commentform.attr('action');
        //Post Form with data
        $.ajax({
            type: 'post',
            url: formurl,
            data: formdata,
            dataType: 'json',
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                statusdiv.html('<p class="ajax-error" >C\'è stato un errore nell\'invio del commento.</p>');
            },
            success: function(data, textStatus) {
                if (textStatus != "success" || data.status == "error") {
                    statusdiv.html('<p class="ajax-error">C\'è stato un errore nell\'invio del commento.</p>');
                } else {
                    var status = data.status;

                    if (data.status == "moderation") {
                        statusdiv.html('<p class="ajax-success">Il commento è in moderazione.</p>');
                    } else if (data.status == "publish") {
                        statusdiv.html('<p class="ajax-success">Commento inviato.</p>');

                        var comment = $(data.html);
                        var parentId = data.comment_parent;

                        if (parentId > 0) {
                            var parent = $("#comment-" + parentId);
                            var depthClass = parent.attr("class").match(/depth-(\d+)\b/);
                            var newDepth = parseInt(depthClass[1]) + 1;
                            depthClass = comment.attr("class").match(/depth-\d+\b/)[0];
                            comment.removeClass(depthClass).addClass("depth-" + newDepth);
                            var siblings = parent.children(".comment");
                        } else {
                            var parent = $(".commentlist");
                            var siblings = $(".comment.depth-1");
                        }
                        if (siblings.length > 0) {
                            var last = siblings.last();
                            if (last.hasClass("odd")) {
                                comment.removeClass("odd").addClass("even");
                            } else {
                                comment.removeClass("even").addClass("odd");
                            }
                            last.after(comment);
                        } else {
                            parent.append(comment);
                        }
                        loadGravatars();
                        // Inizia a funzionare. Mi manca il tasto reply. Controllare quando è unico commento.
                        
                    }

                    commentform.find('textarea[name=comment]').val('');
                    
                }
            }
        });
        return false;
    });

});