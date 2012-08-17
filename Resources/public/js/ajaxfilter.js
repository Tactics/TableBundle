(function($) {
    $(function($) {
        $('.form-filter').live('submit', function() {
            var form = $(this);

            $.ajax({
                url:      form.attr('action'),
                type:     "POST",
                data:     form.serialize(), 
                dataType: "html",
                success:  function(html) {
                    var target = '#'+form.attr('data-target');
                    $(target).replaceWith($(target, $(html)));
                }
            });

            return false;
        });
    });
})(jQuery);
