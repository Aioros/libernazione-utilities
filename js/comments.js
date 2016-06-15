jQuery('document').ready(function($) {

    var commentform = $('#commentform'); // find the comment form
    commentform.prepend('<div id="comment-status"></div>'); // add info panel before the form to provide feedback or errors
    var statusdiv = $('#comment-status'); // define the infopanel

    commentform.submit(function() {
        //serialize and store form data in a variable
        var formdata = commentform.serialize();
        //Add a status message
        statusdiv.html('<p>Processing...</p>');
        //Extract action URL from commentform
        var formurl = commentform.attr('action');
        //Post Form with data
        $.ajax({
            type: 'post',
            url: formurl,
            data: formdata,
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

                        if (parentId) {
                            target = $("#comment-" + parentId).children(".comment").last();
                        } else {
                            target = $(".comment.depth-1").last();
                        }
                        // Qualcosa non mi quadra. Controllare se devo spostare io la commentform prima.

                    }

                    commentform.find('textarea[name=comment]').val('');
                    
                }
            }
        });
        return false;
    });

});