document.addEventListener('DOMContentLoaded', function () {

    const burgerBtn     = document.querySelector('.ufc-burger-btn');
    const mobilePanel   = document.querySelector('.ufc-mobile-panel');
    const mobileOverlay = document.querySelector('.ufc-mobile-overlay');
    const mobileClose   = document.querySelector('.ufc-mobile-close');
    const siteHeader    = document.querySelector('.ufc-header') || document.querySelector('.site-header');

    function openMobileMenu() {
        if (!mobilePanel || !mobileOverlay || !burgerBtn) return;

        mobilePanel.classList.add('active');
        mobileOverlay.classList.add('active');
        burgerBtn.classList.add('active');
        burgerBtn.setAttribute('aria-expanded', 'true');

        document.body.style.overflow = 'hidden';
    }


    function closeMobileMenu() {
        if (!mobilePanel || !mobileOverlay || !burgerBtn) return;

        mobilePanel.classList.remove('active');
        mobileOverlay.classList.remove('active');
        burgerBtn.classList.remove('active');
        burgerBtn.setAttribute('aria-expanded', 'false');

        document.body.style.overflow = '';
    }

    if (burgerBtn) {
        burgerBtn.addEventListener('click', function () {
            const isOpen = mobilePanel && mobilePanel.classList.contains('active');
            if (isOpen) {
                closeMobileMenu();
            } else {
                openMobileMenu();
            }
        });
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', closeMobileMenu);
    }

    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }


    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobilePanel && mobilePanel.classList.contains('active')) {
            closeMobileMenu();
        }
    });


    var mobileLinks = document.querySelectorAll('.ufc-mobile-menu-list a, .ufc-mobile-auth-link');
    mobileLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            setTimeout(closeMobileMenu, 150);
        });
    });


    var lastScrollY = 0;
    var ticking = false;

    function handleHeaderScroll() {
        if (!siteHeader) return;

        if (window.scrollY > 80) {
            siteHeader.style.boxShadow = '0 2px 30px rgba(210, 10, 10, 0.3)';
        } else {
            siteHeader.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.8)';
        }
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        lastScrollY = window.scrollY;
        if (!ticking) {
            window.requestAnimationFrame(handleHeaderScroll);
            ticking = true;
        }
    }, { passive: true }); 

    var animatedElements = document.querySelectorAll('.ufc-animate');

    if (animatedElements.length > 0 && 'IntersectionObserver' in window) {
        animatedElements.forEach(function (el) {
            el.classList.add('ufc-animate-ready');
        });

        void document.body.offsetHeight;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('ufc-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.05,
            rootMargin: '0px 0px 0px 0px'
        });

        animatedElements.forEach(function (el) {
            observer.observe(el);
        });
    }

    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var targetId = this.getAttribute('href');
            if (targetId === '#' || targetId === '') return;

            var targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();

                // Calculer l'offset du header sticky
                var headerHeight = siteHeader ? siteHeader.offsetHeight : 0;
                var targetPosition = targetEl.getBoundingClientRect().top + window.scrollY - headerHeight - 20;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    var mainEvent = document.querySelector('.ufc-main-event');
    if (mainEvent) {
        mainEvent.setAttribute('data-ufc-role', 'main-event');
    }

});
