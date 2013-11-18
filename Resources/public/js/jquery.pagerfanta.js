jQuery(function($) {
    $(document).on('click', 'ul.pagination > li > a', function() {
        sendRequestAndReloadData($(this).attr('href'), $(this).closest('ul.pagination'));

        return false;

    });

    $(document).on('change', 'ul.pagination select.pager', function() {
        var routeParameters = $.extend($(this).data('route-parameters'), { page: $(this).val() });
        var routeName = $(this).data('route-name');

        sendRequestAndReloadData(Routing.generate(routeName, routeParameters), $(this).closest('ul.pagination'));

        return false;
    });

    function sendRequestAndReloadData(url, pagination) {
        var cnt = 0;
        $('.pagination').each(function(){
            if ($(this).get(0) == pagination.get(0))
            {
                return false;
            }
            cnt++;
        });

        $.ajax({
            'url': url,
            type: 'POST',
            dataType: 'html',
            success: function(html) {
                var newElement = $(html).find('.pagination:eq(' + cnt + ')');
                pagination.closest('.panel').replaceWith(newElement.closest('.panel'));

                $('.chosen').chosen();
            }
        });
    }
});
