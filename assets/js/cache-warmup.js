(function ($) {
    $(document).ready(function () {

        var DEBUGMODE = true; // set debug mode

        // debug log helper
        var debug = (function () {
            return {
                log: function() {
                    var args = Array.prototype.slice.call(arguments);
                    (DEBUGMODE) ? console.log.apply(console, args) : false;
                },
                warn: function() {
                    var args = Array.prototype.slice.call(arguments);
                    (DEBUGMODE) ? console.warn.apply(console, args) : false;
                },
                error: function() {
                    var args = Array.prototype.slice.call(arguments);
                    (DEBUGMODE) ? console.error.apply(console, args) : false;
                }
            }
        })();

        // popup
        var popup = null;
        $('.cache-warmup__button-start').on('click', function (e) {
            e.preventDefault();

            var url = $(this).attr('href');
            var title = 'Cache Warmup';
            var parameters = 'left=' + (screen.width - 650) + ', top=50, height=400, width=600, menubar=no, location=no, resizable=no, status=no, scrollbars=yes';

            if (popup == null || popup.closed) {
                popup = window.open(url, title, parameters);
                debug.log('open new popup: ', [title, url, parameters]);
            }
            else {
                popup.focus();
                debug.log('focus popup: ', title);
            }
        });

    });
})(jQuery);
