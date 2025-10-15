// SPA Router - Single Page Application Router
class SPARouter {
    constructor() {
        this.currentPage = null;
        this.init();
    }

    init() {
        // Intercept all link clicks
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            
            // Skip if not a link or is logout button
            if (!link || !link.href) return;
            if (link.id === 'logoutBtn') return;
            if (link.target) return;
            
            const href = link.getAttribute('href');
            if (href === '#' || href === '') {
                e.preventDefault();
                return;
            }
            
            // Check if it's an internal navigation link
            if (href && href.endsWith('.html')) {
                e.preventDefault();
                const page = href.replace('.html', '');
                this.navigate(page);
            }
        });

        // Set initial page
        const initialPage = this.getPageFromPath(window.location.pathname);
        this.currentPage = initialPage;
        this.updateActiveNavLinks(initialPage);
    }

    getPageFromPath(path) {
        const filename = path.split('/').pop() || 'index.html';
        const page = filename.replace('.html', '') || 'index';
        return page;
    }

    navigate(page) {
        if (page === this.currentPage) return;
        
        // Check if page already includes 'views/' path
        if (page.includes('views/')) {
            // Already has views/ prefix, use as is
            window.location.href = `${page}.html`;
        } else {
            // Add views/ prefix
            window.location.href = `views/${page}.html`;
        }
    }

    updateActiveNavLinks(page) {
        // Remove all active classes
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        // Map page names to their href values
        const pageHrefs = {
            'index': ['index.html', '/', ''],
            'about': ['about.html'],
            'service': ['service.html'],
            'feature': ['feature.html'],
            'contact': ['contact.html'],
            'login': ['login.html'],
            'register': ['register.html'],
            'dashboard': ['dashboard.html']
        };
        
        const hrefs = pageHrefs[page] || [page + '.html'];
        
        // Add active class to matching links
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            if (hrefs.includes(href)) {
                link.classList.add('active');
            }
        });
    }
}

// Initialize router when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.spaRouter = new SPARouter();
    });
} else {
    window.spaRouter = new SPARouter();
}
