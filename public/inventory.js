'use strict';

$(function() {
    $('select.tags').select2({
        tags: true,
        tokenSeparators: [',']
    });
});
