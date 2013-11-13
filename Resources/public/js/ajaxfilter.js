jQuery(function($) {
    $('body')
    // Submit filter using ajax
    .on('submit', '.form-filter', function() {
        var form = $(this);
        var target = '#'+form.attr('data-target');

        $(target).closest('.widget').addClass('ajax-loading');

        $.ajax({
            url:      form.attr('action'),
            type:     "POST",
            data:     form.serialize(),
            dataType: "html",
            success:  function(html) {
                $(target).replaceWith($(target, $(html)));
            },
            complete: function()
            {
                $(target).closest('.widget').removeClass('ajax-loading');
            }
        });

        return false;
    })
    // Form submit button clears all filter values and submits form
    .on('reset', '.form-filter', function() {
        var form = $(this);
        form.find(':text, select, :radio').val('');
        form.submit();
        return false;
    })
});
