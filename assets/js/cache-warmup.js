(function ($) {
    $(document).ready(function () {

        /* set debug mode */

        var DEBUGMODE = false;
        if ($('.rex-is-debugmode').length) {
            DEBUGMODE = true;
        }


        /* debug log helper */

        var debug = (function () {
            return {
                log: function () {
                    var args = Array.prototype.slice.call(arguments);
                    (DEBUGMODE) ? console.log.apply(console, args) : false;
                },
                info: function () {
                    var args = Array.prototype.slice.call(arguments);
                    (DEBUGMODE) ? console.info.apply(console, args) : false;
                },
                error: function () {
                    var args = Array.prototype.slice.call(arguments);
                    (DEBUGMODE) ? console.error.apply(console, args) : false;
                }
            }
        })();


        /* warmup popup */

        var popup = null;
        var popupButton = $('.cache-warmup__button-start');
        var warmupButton = $('#rex-page-cache-warmup-warmup');

        popupButton.on('click', function (e) {
            e.preventDefault();

            var url = $(this).attr('href');
            var title = 'Cache Warmup';
            var parameters = 'left=' + (screen.width - 650) + ', top=50, height=471, width=600, menubar=no, location=no, resizable=no, status=no, scrollbars=yes';

            if (popup === null || popup.closed) {
                popup = window.open(url, title, parameters);
                popup.resizeTo(600, 470);
                debug.log('open new popup: ', [title, url, parameters]);
            }
            else {
                popup.focus();
                debug.log('focus popup: ', title);
            }
        });

        warmupButton.on('click', '.cache-warmup__button--success, .cache-warmup__button--cancel', function (e) {
            e.preventDefault();
            debug.log('close popup.');
            window.close();
        });

        warmupButton.on('click', '.cache-warmup__button--again', function (e) {
            e.preventDefault();
            document.location.reload(true);
        });


        /**
         * Content
         * precompiles handlebar templates and injects content to page
         *
         * @param config
         * @constructor
         */
        var Content = function (config) {
            debug.info('new Content');

            // register templates
            if (config.templates && config.templates.length) {
                this._templates = {};
                this._registerTemplates(config.templates);
            }
        };

        Content.prototype = {

            _registerTemplates: function (templateSlugs) {
                if (templateSlugs.length) {
                    templateSlugs.forEach(function (slug) {
                        this._templates[slug] = Handlebars.compile($('#cache_warmup_tpl_' + slug).html());
                    }, this);
                    debug.log('content: registered ' + templateSlugs.length + ' templates: ' + templateSlugs);
                    return this;
                }
            },

            _selectTarget: function (target) {
                var _target = $('.cache-warmup__target__' + target);
                return (_target.length) ? _target : undefined;
            },

            injectTemplate: function (target, template) {
                if (this._templates[template]) {
                    this._selectTarget(target).html(this._templates[template]());
                    debug.log('content: inject ' + template + ' to ' + target);
                }
            },

            injectContent: function (target, content) {
                var _target = this._selectTarget(target);
                if (_target) {
                    _target.html(content);
                    debug.log('content: inject content to ' + target);
                }
            },

            removeElement: function (target) {
                this._selectTarget(target).remove();
            },

            setFromValue: function (value) {
                $('.cache-warmup__target__progress-from').html(value);
            },

            setToValue: function (value) {
                $('.cache-warmup__target__progress-to').html(value);
            }
        };


        /**
         * Stopwatch
         * binds timer (external package) to given element selector
         *
         * @param selector
         * @constructor
         */
        var Stopwatch = function (selector) {
            debug.info('new Stopwatch at "' + selector + '"');

            this._selector = selector;
            this._el = null;
        };

        Stopwatch.prototype = {

            start: function (value) {
                debug.log('stopwatch: started at ' + value);
                this._el = $(this._selector);
                this._el.timer({
                    'seconds': value,
                    'format': '%H:%M:%S'
                });
            },

            pause: function () {
                this._el.timer('pause');
                debug.log('stopwatch: stopped at ' + this._el.data('seconds'));
            },

            getTime: function () {
                return this._el.data('seconds');
            },

            reset: function () {
                this.start();
                debug.info('stopwatch: reset.');
            }
        };


        /**
         * Progressbar
         * controls the progress bar (bootstrap), sets to given value
         *
         * @param selector
         * @param value
         * @constructor
         */
        var Progressbar = function (selector, value) {
            debug.info('new Progressbar at "' + selector + '" starting at ' + value);

            this._selector = selector;
            this._el = $(selector);
            this._value = value;
            this._min = 0;
            this._max = 100;
        };

        Progressbar.prototype = {

            setProgress: function (value) {
                if (value > this._max) {
                    this._value = this._max;
                }
                else if (value < this._min) {
                    this._value = this._min;
                }
                else {
                    this._value = value;
                }
                this._update();
                debug.log('progressbar: set to ' + this._value);
                return this;
            },

            getProgress: function () {
                return this._value;
            },

            reset: function () {
                this._value = this._min;
                this._update();
                debug.info('progressbar: reset.');
                return this;
            },

            _update: function () {
                this._el = $(this._selector);
                this._el.find('.progress-bar').css('width', this._value + '%');
                return this;
            }
        };


        /**
         * Calculator
         * stores number of chunks and calculates progress
         * does not care about items but chunks only
         *
         * @param config
         * @constructor
         */
        var Calculator = function (config) {
            this._initialConfig = config;

            this._init();
            debug.info('new Calculator :', JSON.stringify(this.config));
        };

        Calculator.prototype = {

            _init: function () {
                this.config = {};
                for (var item in this._initialConfig) {
                    if (this._initialConfig.hasOwnProperty(item)) {
                        this.config[item] = this._initialConfig[item];
                        this.config[item].current = 0;
                    }
                }
            },

            reset: function () {
                this._init();
                debug.info('calculator: reset.');
            },

            registerNextChunk: function (type) {
                this.config[type].current += 1;
                debug.log('calculator: set ' + type + ' to ' + this.config[type].current + '/' + this.config[type].total + ', overall progress at ' + this.getProgress() + '% now');
                return this;
            },

            getCurrent: function (type) {
                return this.config[type].current;
            },

            getTotal: function (type) {
                return this.config[type].total;
            },

            getProgress: function () {
                return this._calculateProgress();
            },

            _calculateProgress: function () {
                var finished = 0;
                var total = 0;
                for (var type in this.config) {
                    if (this.config.hasOwnProperty(type)) {
                        finished += this.getCurrent(type);
                        total += this.getTotal(type);
                    }
                }
                return Math.round(finished / total * 100);
            }
        };


        /**
         * Config
         * prepares config JSON, returns URIs for generator requests
         *
         * @param itemsJSON
         * @param generatorUrl
         * @constructor
         */
        var Config = function (itemsJSON, generatorUrl, token) {
            this._items = itemsJSON;
            this._generatorUrl = generatorUrl;
            this._token = token;

            if (!$.isEmptyObject(this._items) && this._generatorUrl.length) {
                debug.info("new Config for " + this._getDebugInfo() + "generator at " + generatorUrl + " with token " + token);
            }
            else {
                debug.error('new Config: no content.');
            }
        };

        Config.prototype = {

            _getDebugInfo: function () {
                var info = '';
                var types = this.getItemTypes();
                if (types.length) {
                    types.forEach(function (entry) {
                        info += this.getNumOfItems(entry) + ' ' + entry + ' (' + this.getNumOfChunks(entry) + ' chunks), ';
                    }, this);
                    return info;
                }
            },

            hasItems: function () {
                return Object.keys(this._items).some(function(type) {
                    return this._items[type].count > 0;
                }, this);
            },

            getNumOfItems: function (type) {
                return this._items[type] ? this._items[type].count : 0;
            },

            getNumOfChunks: function (type) {
                return this._items[type].items ? this._items[type].items.length : 0;
            },

            getItemTypes: function () {
                return (Object.keys(this._items));
            },

            generateTokenParameter: function () {
                return this._token ? "&_csrf_token=" + this._token : null;
            },

            getUrlsForType: function (type) {
                var urls = [];
                var chunk = [];
                if (this._items[type] && this._items[type].items.length) {
                    for (var i = 0, imax = this._items[type].items.length; i < imax; i++) {
                        chunk = [];
                        for (var j = 0, jmax = this._items[type].items[i].length; j < jmax; j++) {
                            chunk.push(this._items[type].items[i][j].join('.'));
                        }
                        urls.push({
                            'absolute': this._generatorUrl + '&' + type + '=' + chunk.join() + this.generateTokenParameter(),
                            'slug': type + '=' + chunk.join(),
                            'itemsNum': jmax
                        });
                    }
                }
                return urls;
            }
        };


        /**
         * Cache
         * sends ajax request to generator file
         *
         * @param cacheWarmup
         * @constructor
         */
        var Cache = function (cacheWarmup) {
            this.cacheWarmup = cacheWarmup;
        };

        Cache.prototype = {

            generate: function (type, callback) {
                var timerStart;
                var timerEnd;
                var executionTimes = [];
                var that = this;
                var urls = this.cacheWarmup.config.getUrlsForType(type);
                var cachedItemsCount = 0;

                if (urls.length) {

                    // loop through urls and send serial requests (not parallel!)
                    urls.reduce(function (p, url, index) {
                        return p.then(function () {
                            timerStart = new Date().getTime();
                            // send request
                            return $.ajax({
                                    url: url.absolute,
                                    cache: false,
                                    beforeSend: function () {
                                        // update components
                                        // why not after request? because from UX view it feels better beforehand.
                                        debug.log('---');
                                        cachedItemsCount += url.itemsNum;
                                        that.cacheWarmup.content.setFromValue(cachedItemsCount);
                                        that.cacheWarmup.calculator.registerNextChunk(type);
                                        that.cacheWarmup.progressbar.setProgress(that.cacheWarmup.calculator.getProgress());
                                    }
                                })
                                .done(function (data) {
                                    // special: error on success (http status 200)
                                    // media manager returns 200 even if an image cannot be generated (too big, RAM exceeded)
                                    // we assume an error if response starts with rex-page-header
                                    // otherwise page will return blank if stuff works out as expected
                                    if (data.substr(0, 30) === '<header class="rex-page-header') {
                                        debug.error('cache: request error for ' + url.slug);
                                        that.cacheWarmup.isError('RAM exceeded', 'internal', url.absolute);
                                        throw new Error('RAM exceeded');
                                    }
                                    else {
                                        // get debug infos
                                        timerEnd = new Date().getTime();
                                        debug.log('cache: request ' + (index + 1) + '/' + urls.length + ' (' + (timerEnd - timerStart) + ' ms) success for ' + url.slug);
                                        executionTimes.push(timerEnd - timerStart);
                                    }
                                })
                                .fail(function (jqXHR, textStatus, errorThrown) {
                                    // throw up error message, statuscode and URL to page where error occured
                                    that.cacheWarmup.isError(errorThrown, jqXHR.status, url.absolute);
                                    throw new Error(errorThrown);
                                });
                        });
                    }, Promise.resolve()).then(function () {
                        // finished all requests
                        debug.info('cache: finished all ' + urls.length + ' requests (' + that._calculateAverageExecutionTime(executionTimes) + ' ms average).');
                        callback();
                    });

                }
                else {
                    debug.error('cache: no items for type ' + type);
                    callback();
                }
            },

            _calculateAverageExecutionTime: function (items) {
                if (items.length) {
                    return Math.round(items.reduce(function (a, b) {
                            return a + b;
                        }) / items.length);
                }
            }
        };


        /**
         * CacheWarmup
         *
         * @param config
         * @constructor
         */
        var CacheWarmup = function (config) {
            debug.info('new CacheWarmup');

            // prepare config
            this.config = new Config(config.itemsJSON, config.generatorUrl, config.token);
            if (this.config.hasItems()) {

                // set up components
                this.content = new Content(config);
                this.stopwatch = new Stopwatch(config.components.stopwatch);
                this.progressbar = new Progressbar(config.components.progressbar);
                this.calculator = new Calculator(this._prepareCalculatorConfig());
                this.cache = new Cache(this);

                // run
                this.run();
            }
            else {
                // has no config
                debug.error('No config available.');
                this.content = new Content(config);
                this.isNothing();
            }
        };

        CacheWarmup.prototype = {

            _prepareCalculatorConfig: function () {
                var config = {};
                var types = this.config.getItemTypes();
                if (types.length) {
                    types.forEach(function (entry) {
                        config[entry] = {'total': this.config.getNumOfChunks(entry)}
                    }, this);
                    return config;
                }
            },

            run: function () {
                var that = this;

                // inject content and footer
                this.content.injectTemplate('content', 'content_task');
                this.content.injectTemplate('footer', 'button_cancel');

                // inject progress bar
                this.content.injectTemplate('progressbar', 'progressbar');

                // inject stopwatch and start
                this.content.injectTemplate('elapsed', 'stopwatch');
                this.stopwatch.start();

                // prepare and progress pages
                this.content.injectTemplate('title', 'title_pages');
                this.content.injectTemplate('task', 'progress_pages');
                this.content.setToValue(this.config.getNumOfItems('pages'));
                // callback hell starts here:
                that.cache.generate('pages', function () {

                    // prepare and progress images
                    that.content.injectTemplate('title', 'title_images');
                    that.content.injectTemplate('task', 'progress_images');
                    that.content.setToValue(that.config.getNumOfItems('images'));
                    that.cache.generate('images', function () {

                        that.stopwatch.pause();
                        debug.info('cache: finished with all items.');
                        that.isFinished();
                    });
                });
            },

            isFinished: function () {

                // snap finished time
                var stopwatchSnapshot = $(this.stopwatch._selector).html();

                // inject content and footer
                this.content.injectTemplate('content', 'content_info');
                this.content.injectTemplate('footer', 'button_success');

                // inject values
                this.content.injectTemplate('title', 'title_finished');
                this.content.injectTemplate('icon', 'icon_finished');
                this.content.injectTemplate('text', 'text_finished');

                // remove progressbar
                this.content.removeElement('progressbar');

                // inject generator values (processed items and timing)
                if (this.config.getNumOfItems('pages')) {
                    $('.cache-warmup__target__finished-pages-num').prepend(this.config.getNumOfItems('pages') + ' ');
                }
                else {
                    this.content.removeElement('finished-pages-num');
                }
                if (this.config.getNumOfItems('images')) {
                    $('.cache-warmup__target__finished-images-num').prepend(this.config.getNumOfItems('images') + ' ');
                }
                else {
                    this.content.removeElement('finished-images-num');
                }
                if (!(this.config.getNumOfItems('pages') && this.config.getNumOfItems('images'))) {
                    this.content.removeElement('finished-join');
                }
                this.content.injectContent('finished-time-num', stopwatchSnapshot);
            },

            isError: function (message, code, url) {

                // inject content and footer
                this.content.injectTemplate('content', 'content_info');
                this.content.injectTemplate('footer', 'button_again');

                // prepare error message details
                var errorDetails = this.content._templates['text_error']() + '<p><code>' + message + ' (' + code + ')</code> &rarr; <a href="' + url + '">' + this.content._templates['error_link']() + '</a></p>';

                // inject values
                this.content.injectTemplate('title', 'title_error');
                this.content.injectTemplate('icon', 'icon_error');
                this.content.injectContent('text', errorDetails);

                // remove progressbar
                this.content.removeElement('progressbar');
            },

            isNothing: function () {

                // inject content and footer
                this.content.injectTemplate('content', 'content_info');
                this.content.injectTemplate('footer', 'button_again');

                // inject values
                this.content.injectTemplate('title', 'title_nothing');
                this.content.injectTemplate('icon', 'icon_nothing');
                this.content.injectTemplate('text', 'text_nothing');

                // remove progressbar
                this.content.removeElement('progressbar');
            }
        };


        /* Cache warmup */

        if (typeof cacheWarmupItems !== 'undefined') {

            new CacheWarmup({
                'itemsJSON': cacheWarmupItems,
                'token': cacheWarmupToken || false,
                'generatorUrl': window.location.origin + window.location.pathname + '?page=cache_warmup/generator',
                'templates': [
                    'content_task', 'content_info',
                    'stopwatch', 'progressbar',
                    'title_pages', 'title_images', 'title_finished', 'title_error', 'title_nothing',
                    'progress_pages', 'progress_images',
                    'icon_finished', 'icon_error', 'icon_nothing',
                    'text_finished', 'text_error', 'text_nothing',
                    'error_link',
                    'button_success', 'button_again', 'button_cancel'
                ],
                'targets': [
                    'title', 'content', 'progressbar', 'footer', 'task', 'elapsed', 'icon', 'text'
                ],
                'components': {
                    'stopwatch': '#cache_warmup_time',
                    'progressbar': '.cache-warmup__progressbar'
                }
            });
        }

    });
})(jQuery);