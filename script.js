
jQuery(document).ready(function() {

    // Start with disabled submit button
    jQuery(".addnewpage :submit").attr("disabled", "disabled");
    // Then enable it when a title is entered
    jQuery(".addnewpage input[name='title']").keyup(function(){
        var $submit = jQuery(this).parent("form").find(":submit");
        if (jQuery(this).val().length > 0) {
            $submit.removeAttr("disabled");
        } else {
            // For when the user deletes the text
            $submit.attr("disabled", "disabled");
        }
    });

    // Change the form's action on submit
    $editform = jQuery(".addnewpage form").submit(function(e) {
        var ns = jQuery(this).find("[name='np_cat']").val();
        var title = jQuery(this).find("input[name='title']").val();
        var action = DOKU_BASE+"?do=edit&id="+ns+":"+title;
        jQuery(this).attr("action", action);
        return true;
    });

});
