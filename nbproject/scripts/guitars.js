"use strict";

$(document).ready(function() {
    $('#slides ul').bxSlider({
        slideWidth: 300,
        minSlides: 1,
        maxSlides: 1,
        captions: true, 
        randomStart: true,
        auto: true,
        pause: 3000,
        pager: true,
        pagerSelector: '#page-numbers',
        pagerType: 'short',
        
    });
});
