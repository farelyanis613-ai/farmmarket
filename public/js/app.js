document.addEventListener('DOMContentLoaded', function () {
    const deleteLinks = document.querySelectorAll('a[href*="action=cart/remove"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            if (!confirm('Voulez-vous supprimer cet article du panier ?')) {
                event.preventDefault();
            }
        });
    });
});

// Search by category function
function searchByCategory() {
    const categorySelect = document.getElementById('categorySearch');
    if (categorySelect) {
        const category = categorySelect.value;
        if (category) {
            window.location.href = `index.php?action=products&category=${encodeURIComponent(category)}`;
        } else {
            window.location.href = 'index.php?action=products';
        }
    }
}

// Allow Enter key in category search
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('categorySearch');
    if (categorySelect) {
        categorySelect.addEventListener('keypress', function (event) {
            if (event.key === 'Enter') {
                searchByCategory();
            }
        });
    }
});

// Hero image rotation and marquee + profile edit toggle
document.addEventListener('DOMContentLoaded', function () {
    // Load hero images passed from PHP
    try {
        var heroImages = window.heroImages || [];
    } catch (e) {
        var heroImages = [];
    }

    var heroBg = document.getElementById('hero-bg');
    var heroNextBtn = document.getElementById('heroNextBtn');
    if (heroImages && heroImages.length && heroBg) {
        heroBg.style.opacity = 0;
        heroBg.style.transform = 'translateX(6%) scale(1.02)';
        var lastHeroIndex = -1;
        function setHero(src) {
            heroBg.style.opacity = 0;
            heroBg.style.transform = 'translateX(8%) scale(1.02)';
            setTimeout(function () {
                heroBg.style.backgroundImage = "url('" + src + "')";
                heroBg.style.opacity = 1;
                heroBg.style.transform = 'translateX(0) scale(1)';
            }, 200);
        }
        function nextHero(forceIndex) {
            if (!heroImages.length) return;
            var nextIndex;
            if (typeof forceIndex === 'number') {
                nextIndex = forceIndex % heroImages.length;
            } else {
                nextIndex = Math.floor(Math.random() * heroImages.length);
                while (heroImages.length > 1 && nextIndex === lastHeroIndex) {
                    nextIndex = Math.floor(Math.random() * heroImages.length);
                }
            }
            lastHeroIndex = nextIndex;
            setHero(heroImages[nextIndex]);
        }
        nextHero();
        var heroInterval = setInterval(nextHero, 10000);

        if (heroNextBtn) {
            heroNextBtn.addEventListener('click', function () {
                clearInterval(heroInterval);
                nextHero();
                heroInterval = setInterval(nextHero, 10000);
            });
        }

        // Trigger the same hero reveal effect when Ctrl+R or Cmd+R is pressed without reloading
        document.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'r') {
                event.preventDefault();
                nextHero();
            }
        });
    }

    // Marquee animation every 5s
    var marqueeInner = document.getElementById('hero-marquee-inner');
    var marqueeText = "Découvrez une expérience de marché fermier en ligne : qualité, transparence et rapidité pour vos commandes de viande, œufs et produits laitiers.";
    if (marqueeInner) {
        marqueeInner.textContent = marqueeText;
        function animateMarquee() {
            var durationMs = 18000; // slower animation
            marqueeInner.style.transition = 'none';
            // start off-screen at the right so the beginning of the phrase appears first
            marqueeInner.style.transform = 'translateX(' + window.innerWidth + 'px)';
            requestAnimationFrame(function () {
                marqueeInner.style.transition = 'transform ' + (durationMs / 1000) + 's linear';
                marqueeInner.style.transform = 'translateX(-' + marqueeInner.offsetWidth + 'px)';
            });
        }
        animateMarquee();
        setInterval(animateMarquee, 20000); // schedule next run after animation completes
    }

    // Profile edit toggle
    var toggleBtn = document.getElementById('toggle-edit-profile');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var form = document.querySelector('form');
            if (!form) return;
            var editable = form.querySelectorAll('[data-editable]');
            editable.forEach(function (el) {
                if (el.hasAttribute('disabled')) {
                    el.removeAttribute('disabled');
                    el.classList.remove('bg-slate-50');
                } else {
                    el.setAttribute('disabled', 'disabled');
                    el.classList.add('bg-slate-50');
                }
            });
            toggleBtn.textContent = toggleBtn.textContent.trim() === 'Modifier mes informations' ? 'Annuler modification' : 'Modifier mes informations';
        });
    }

    // Theme toggle for farmer/delivery account pages
    var themeToggleBtn = document.getElementById('themeToggleBtn');
    if (themeToggleBtn) {
        var body = document.body;
        var savedTheme = localStorage.getItem('accountThemeMode');
        if (savedTheme === 'alt') {
            body.classList.add('alt-theme');
        }

        themeToggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            body.classList.toggle('alt-theme');
            localStorage.setItem('accountThemeMode', body.classList.contains('alt-theme') ? 'alt' : 'default');
        });
    }

    // CTA subtle scroll animation on hover already handled by CSS; also add slight continuous motion
    var cta = document.getElementById('home-cta');
    if (cta) {
        // gentle oscillation
        cta.animate([
            { transform: 'translateX(0)' },
            { transform: 'translateX(6px)' },
            { transform: 'translateX(0)' }
        ], { duration: 4000, iterations: Infinity, direction: 'alternate' });
    }
});
