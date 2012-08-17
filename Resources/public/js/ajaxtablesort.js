(function($) {
    $(function($) {
        $('.a-filter').live('click', function() {
            var element = $(this);
            var target  = $('#results');

            $.ajax({
                url:      element.attr('href'),
                type:     "POST",
                dataType: "html",
                success:  function(html) {
                    target.replaceWith($('#results', $(html)));
                }
            });

            return false;
        });
    });
})(jQuery);
