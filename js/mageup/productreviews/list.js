document.observe('dom:loaded', function(){
    $$('.item .ratings a').each(function(link) {
        if (typeof link != 'undefined') {
            link.onclick = function(e) {
                var productLink = e.target.parentElement.parentElement.parentElement.firstElementChild.href;
                var t = opener ? opener.window : window;
                t.location.href=productLink + '#productreviews-list';
                return false;
            };
        }
    });

    $$('.item .no-rating a').each(function(noRatingLink) {
        if (typeof noRatingLink != 'undefined') {
            noRatingLink.onclick = function() {
                var productLink = e.target.parentElement.parentElement.firstElementChild.href;
                var t = opener ? opener.window : window;
                t.location.href=productLink + '#productreviews-container';
                return false;
            };
        }
    });
});