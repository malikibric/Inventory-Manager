var homeContent = null;
var app = null;

$(document).ready(function() {
    homeContent = $('#main-content').html();
    
    app = $.spapp({
        defaultView: "#home",
        templateDir: "./views/",
        pageNotFound: "error_404"
    });

    app.route({
        view: "home",
        onReady: function() {
            if (homeContent) {
                $('#main-content').html(homeContent);
            }
            if (typeof WOW !== 'undefined') {
                new WOW().init();
            }
        }
    });

    app.route({
        view: "about",
        load: "about"
    });

    app.route({
        view: "services",
        load: "service"
    });

    app.route({
        view: "features",
        load: "feature"
    });

    app.route({
        view: "contact",
        load: "contact"
    });

    app.route({
        view: "login",
        load: "login",
        onReady: function() {
            if (typeof window.initLoginPage === 'function') {
                window.initLoginPage();
            }
        }
    });

    app.route({
        view: "register",
        load: "register",
        onReady: function() {
            if (typeof window.initRegisterPage === 'function') {
                window.initRegisterPage();
            }
        }
    });

    app.route({
        view: "dashboard",
        load: "dashboard",
        onReady: function() {
            if (typeof window.initDashboard === 'function') {
                window.initDashboard();
            }
        }
    });

    app.run();
});
