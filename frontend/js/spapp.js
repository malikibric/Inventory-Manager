(function($) {
    $.spapp = function(options) {
        var settings = $.extend({
            defaultView: "#home",
            templateDir: "./views/",
            pageNotFound: "error_404"
        }, options);

        var routes = {};
        var currentView = null;
        var cache = {};
        var noCachePages = ['login', 'register'];
        var pageMap = {
            'services': 'service',
            'features': 'feature'
        };

        function init() {
            $(window).on('hashchange', function() {
                loadView();
            });

            loadView();
        }

        function loadView() {
            var hash = window.location.hash || settings.defaultView;
            var viewName = hash.replace('#', '');
            
            if (!viewName) viewName = settings.defaultView.replace('#', '');

            if (routes[viewName]) {
                showView(viewName);
            } else {
                showView(settings.pageNotFound);
            }
        }

        function showView(viewName) {
            var route = routes[viewName];
            if (!route) return;
            
            if (currentView === viewName && !route.load) return;

            var shouldCache = !noCachePages.includes(viewName);

            if (shouldCache && cache[viewName]) {
                $('#main-content').html(cache[viewName]);
                currentView = viewName;
                updateNav(viewName);
                window.scrollTo({ top: 0, behavior: 'smooth' });
                if (route.onReady) route.onReady();
                return;
            }

            if (route.load) {
                var fileName = route.load;
                if (!fileName.endsWith('.html')) {
                    fileName = (pageMap[viewName] || viewName) + '.html';
                }
                var filePath = settings.templateDir + fileName;

                $.ajax({
                    url: filePath,
                    type: 'GET',
                    dataType: 'html',
                    success: function(data) {
                        var content = extractContent(data);
                        content = fixPaths(content);

                        if (shouldCache) {
                            cache[viewName] = content;
                        }

                        $('#main-content').html(content);
                        currentView = viewName;
                        updateNav(viewName);
                        window.scrollTo({ top: 0, behavior: 'smooth' });

                        if (route.onCreate && !cache[viewName + '_created']) {
                            route.onCreate();
                            cache[viewName + '_created'] = true;
                        }
                        if (route.onReady) route.onReady();
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to load:', filePath, error);
                        $('#main-content').html('<div class="container py-5"><div class="alert alert-danger"><h4>Error</h4><p>Could not load page: ' + filePath + '</p><a href="#home" class="btn btn-primary mt-3">Return Home</a></div></div>');
                    }
                });
            } else {
                currentView = viewName;
                updateNav(viewName);
                if (route.onReady) route.onReady();
            }
        }

        function extractContent(html) {
            var doc = $($.parseHTML(html, document, true));
            var content = '';

            var pageHeader = doc.filter('.page-header').add(doc.find('.page-header'));
            if (pageHeader.length) {
                content += pageHeader[0].outerHTML;
            }

            var containers = doc.filter('.container-fluid, .container-xxl').add(doc.find('.container-fluid, .container-xxl'));
            containers.each(function() {
                if (!$(this).closest('nav').length && 
                    !$(this).closest('.footer').length && 
                    !$(this).attr('id')?.includes('spinner') &&
                    !$(this).hasClass('page-header')) {
                    content += this.outerHTML;
                }
            });

            return content || html;
        }

        function fixPaths(content) {
            content = content.replace(/src="\.\.\/img\//g, 'src="img/');
            content = content.replace(/src='\.\.\/img\//g, "src='img/");
            content = content.replace(/href="\.\.\/css\//g, 'href="css/');
            content = content.replace(/href="\.\.\/js\//g, 'href="js/');
            return content;
        }

        function updateNav(viewName) {
            $('.nav-link').removeClass('active');
            $('.nav-link[href="#' + viewName + '"]').addClass('active');
        }

        return {
            route: function(config) {
                if (config.view) {
                    routes[config.view] = config;
                }
                return this;
            },
            run: function() {
                init();
                return this;
            }
        };
    };
})(jQuery);
