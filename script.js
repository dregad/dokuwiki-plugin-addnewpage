jQuery(function () {
    jQuery(".addnewpage form").each(function () {
        let $form = jQuery(this);
        let $ns = $form.find("[name='np_cat']");
        let $title = $form.find("input[name='title']");
        let $id = $form.find("input[name='id']");
        let $submit = $form.find(':submit');

        // disable submit unless something is in input or input is disabled
        if ($title.attr('type') === 'text') {
            $submit.attr('disabled', 'disabled');
            $title.on('input', function () {
                if ($title.val().length > 0) {
                    $submit.removeAttr('disabled');
                } else {
                    $submit.attr('disabled', 'disabled');
                }
            });
        }

        // Change the form's page-ID field on submit
        $form.on("submit", function () {
            const PLACEHOLDER = "@INPUT@";

            // Build the new page ID
            let page_id = $ns.val();
            if (page_id.indexOf(PLACEHOLDER) !== -1) {
                // Process the placeholder
                page_id = page_id.replaceAll(PLACEHOLDER, $title.val());
            } else if ($title.val()) {
                // There is no placeholder, just append the user's input (if any)
                page_id += ":" + $title.val();
            }

            // Save the new page ID in the hidden form field
            $id.val(page_id);

            // Clean up the form vars, just to make the resultant URL a bit nicer
            $ns.prop("disabled", true);
            $title.prop("disabled", true);

            return true;
        });

    });
});
