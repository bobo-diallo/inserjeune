const $ = require('jquery');

setTimeout(function () {
    $('.kz-flashbag').fadeOut("slow");
}, 50000);

$("#lnkapropos").on( "click", function() {
    if ($('#apropos').hasClass("d-none")) {
        $('#apropos').removeClass("d-none");
    } else {
        $('#apropos').addClass("d-none");
    }
});
