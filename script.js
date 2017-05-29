jQuery(document).ready(function () {
    var $form = jQuery(".addnewpage form");
    if (!$form.length) return;

    var $ns = $form.find("[name='np_cat']");
    var $title = $form.find("input[name='title']");
    var $id = $form.find("input[name='id']");
    var $submit = $form.find(':submit');

    // disable submit unless something is in input or input is disabled
    if ($title.attr('type') === 'text') {
        $submit.attr('disabled', 'disabled');
        $title.keyup(function () {
            if ($title.val().length > 0) {
                $submit.removeAttr('disabled');
            } else {
                $submit.attr('disabled', 'disabled');
            }
        });
    }

    // Change the form's page-ID field on submit
    $form.submit(function () {
        // Build the new page ID and save in hidden form field
        var id = $ns.val().replace('@INPUT@', $title.val());
        $id.val(id);

        // Clean up the form vars, just to make the resultant URL a bit nicer
        $ns.prop("disabled", true);
        $title.prop("disabled", true);

        return true;
    });

});
