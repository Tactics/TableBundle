jQuery(function($) {
    $('.form-filter').live('submit', function() {
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
    });
});
