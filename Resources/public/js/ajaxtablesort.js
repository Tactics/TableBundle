jQuery(function($) {
    $('body').on('click', '.a-filter', function() {
        var element = $(this);
        var target  = '#results';

        $(target).closest('.widget').addClass('ajax-loading');

        $.ajax({
            url:      element.attr('href'),
            type:     "POST",
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
    });
});
