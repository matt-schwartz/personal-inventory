'use strict';

$(function() {
    $('select.tags').select2({
        tags: true,
        tokenSeparators: [',']
    });

    $('[data-confirm]').on('click', function(e) {
        if (window.confirm($(this).data('confirm'))) {
            return true;
        } else {
            e.preventDefault();
            return false;
        }
    });
});
