(function ($) {
    "use strict";

    // Spinner
    var spinner = function () {
        setTimeout(function () {
            if ($('#spinner').length > 0) {
                $('#spinner').removeClass('show');
            }
        }, 1);
    };
    spinner();
    
    
    // Initiate the wowjs
    new WOW().init();


    // Sticky Navbar
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.sticky-top').css('top', '0px');
        } else {
            $('.sticky-top').css('top', '-100px');
        }
    });
    
    
    // Dropdown on mouse hover
    const $dropdown = $(".dropdown");
    const $dropdownToggle = $(".dropdown-toggle");
    const $dropdownMenu = $(".dropdown-menu");
    const showClass = "show";
    
    $(window).on("load resize", function() {
        if (this.matchMedia("(min-width: 992px)").matches) {
            $dropdown.hover(
            function() {
                const $this = $(this);
                $this.addClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "true");
                $this.find($dropdownMenu).addClass(showClass);
            },
            function() {
                const $this = $(this);
                $this.removeClass(showClass);
                $this.find($dropdownToggle).attr("aria-expanded", "false");
                $this.find($dropdownMenu).removeClass(showClass);
            }
            );
        } else {
            $dropdown.off("mouseenter mouseleave");
        }
    });
    
    
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 300) {
            $('.back-to-top').fadeIn('slow');
        } else {
            $('.back-to-top').fadeOut('slow');
        }
    });
    $('.back-to-top').click(function () {
        $('html, body').animate({scrollTop: 0}, 1500, 'easeInOutExpo');
        return false;
    });


    // Facts counter
    $('[data-toggle="counter-up"]').counterUp({
        delay: 10,
        time: 2000
    });


    // Header carousel
    $(".header-carousel").owlCarousel({
        autoplay: false,
        smartSpeed: 1500,
        items: 1,
        dots: false,
        loop: true,
        nav : true,
        navText : [
            '<i class="bi bi-chevron-left"></i>',
            '<i class="bi bi-chevron-right"></i>'
        ]
    });


    // Testimonials carousel
    $(".testimonial-carousel").owlCarousel({
        autoplay: false,
        smartSpeed: 1000,
        center: true,
        dots: true,
        loop: true,
        responsive: {
            0:{
                items:1
            },
            768:{
                items:2
            },
            992:{
                items:3
            }
        }
    });


    // Login Form Handler
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const email = $('#email').val();
        const password = $('#password').val();
        
        if (!email || !password) {
            alert('Please enter email and password!');
            return false;
        }
        
        // Email validacija
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email!');
            return false;
        }
        
        // Simulacija uspješnog logina - sačuvaj korisnika u localStorage
        const userData = {
            email: email,
            firstName: 'User',
            loginTime: new Date().toISOString()
        };
        localStorage.setItem('currentUser', JSON.stringify(userData));
        
        alert('Successfully logged in!\nEmail: ' + email);
        
        // Preusmjeri na dashboard
        window.location.href = 'dashboard.html';
        
        return false;
    });


    // Register Form Handler
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const firstName = $('#firstName').val();
        const lastName = $('#lastName').val();
        const email = $('#email').val();
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        const terms = $('#terms').is(':checked');
        
        // Validacija obaveznih polja
        if (!firstName || !lastName || !email || !password || !confirmPassword) {
            alert('Please fill in all required fields!');
            return false;
        }
        
        // Email validacija
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email!');
            return false;
        }
        
        // Provjera lozinke
        if (password.length < 6) {
            alert('Password must be at least 6 characters long!');
            return false;
        }
        
        // Provjera da se lozinke poklapaju
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return false;
        }
        
        // Provjera terms and conditions
        if (!terms) {
            alert('You must accept the Terms & Conditions!');
            return false;
        }
        
        // Simulacija uspješne registracije - sačuvaj korisnika
        const userData = {
            email: email,
            firstName: firstName,
            lastName: lastName,
            loginTime: new Date().toISOString()
        };
        localStorage.setItem('currentUser', JSON.stringify(userData));
        
        alert('Successfully registered!\nWelcome, ' + firstName + ' ' + lastName + '!');
        
        // Preusmjeri na dashboard
        window.location.href = 'dashboard.html';
        
        return false;
    });
    
})(jQuery);

