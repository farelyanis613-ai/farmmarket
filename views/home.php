<?php require __DIR__ . '/partials/header.php'; ?>

<?php if (!empty($_SESSION['success'])) : ?>
<div class="mx-auto max-w-6xl px-4 py-4 md:px-6">
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php
    $hero_images = [];
    $hero_dir = __DIR__ . '/../public/images/background';
    if (is_dir($hero_dir)) {
        foreach (scandir($hero_dir) as $file) {
            if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp'])) {
                $hero_images[] = 'public/images/background/' . $file;
            }
        }
    }
    $hero_images_json = json_encode($hero_images);
    $hero_default = $hero_images[0] ?? 'public/images/background/accueil.PNG';
?>

<div class="pb-16">

    <!-- ═══════════════════════════════════════════
         HERO
    ════════════════════════════════════════════ -->
    <section class="fm-hero relative overflow-hidden -mx-6">
        <div id="hero-bg" class="absolute inset-0" style="background-image: url('<?= $hero_default ?>'); background-size: cover; background-position: center;"></div>
        <div class="fm-hero-overlay absolute inset-0"></div>

        <!-- Slide indicators -->
        <div id="hero-dots" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2 z-10"></div>

        <!-- Navigation arrows -->
        <button id="heroPrev" aria-label="Image précédente" class="fm-hero-arrow left-4">&#8592;</button>
        <button id="heroNext" aria-label="Image suivante"    class="fm-hero-arrow right-4">&#8594;</button>

        <div class="relative z-10 h-full flex items-end pb-14 md:items-center md:pb-0 px-6 md:px-16 mx-auto max-w-6xl">
            <div class="max-w-2xl">
                <span class="fm-eyebrow">Marché fermier en ligne · Bénin</span>
                <h1 class="fm-hero-title">
                    Des produits d'élevage frais,<br>
                    directement de l'éleveur<br>
                    à votre table.
                </h1>
                <p class="fm-hero-sub">
                    Qualité garantie, prix équitables, livraison rapide à domicile.
                </p>
                <div class="flex flex-wrap gap-3 mt-8">
                    <a href="index.php?action=products" class="btn-primary">
                        Voir les produits
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd"/></svg>
                    </a>
                    <a href="#how-it-works" class="btn-ghost">Comment ça marche</a>
                </div>
            </div>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 md:px-6 space-y-16 mt-12">

        <!-- ═══════════════════════════════════════════
             CHIFFRES CLÉS
        ════════════════════════════════════════════ -->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-4" aria-label="Chiffres clés">
            <?php
            $stats = [
                ['num' => '100%', 'label' => 'Produits frais',     'icon' => '🌿'],
                ['num' => '24h',  'label' => 'Délai de livraison', 'icon' => '🚚'],
                ['num' => '4+',   'label' => 'Catégories',         'icon' => '🛒'],
                ['num' => 'GPS',  'label' => 'Suivi en temps réel','icon' => '📍'],
            ];
            foreach ($stats as $s): ?>
            <div class="fm-stat-card">
                <span class="fm-stat-icon"><?= $s['icon'] ?></span>
                <span class="fm-stat-num"><?= $s['num'] ?></span>
                <span class="fm-stat-label"><?= $s['label'] ?></span>
            </div>
            <?php endforeach; ?>
        </section>

        <!-- ═══════════════════════════════════════════
             RECHERCHE RAPIDE
        ════════════════════════════════════════════ -->
        <section class="section-card fm-search-card" aria-label="Rechercher un produit">
            <h2 class="section-title fm-section-title">Trouver vos produits</h2>
            <form action="index.php" method="get" class="flex flex-col sm:flex-row gap-3 mt-4">
                <input type="hidden" name="action" value="products">
                <?php
                        require_once __DIR__ . '/../models/CategoryModel.php';
                        $catModel = new CategoryModel();
                        $homeCategories = $catModel->all();
                ?>
                <select id="homeCategoryFilter" name="category" class="fm-select flex-grow">
                    <option value="">Toutes les catégories</option>
                    <?php if (!empty($homeCategories) && is_array($homeCategories)): ?>
                        <?php foreach ($homeCategories as $c): ?>
                            <option value="<?= htmlspecialchars($c['name'] ?? '') ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button type="submit" class="btn-primary whitespace-nowrap">
                    Rechercher
                </button>
            </form>
        </section>

        <!-- ═══════════════════════════════════════════
             COMMENT ÇA MARCHE
        ════════════════════════════════════════════ -->
        <section id="how-it-works" aria-labelledby="hiw-title">
            <p class="fm-overline">Simple &amp; transparent</p>
            <h2 id="hiw-title" class="fm-section-title mt-1 mb-10">Comment ça marche ?</h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <?php
                $steps = [
                    ['n'=>'01','icon'=>'🛒','title'=>'Choisissez','desc'=>'Parcourez notre catalogue et sélectionnez vos produits frais.'],
                    ['n'=>'02','icon'=>'💳','title'=>'Payez','desc'=>'Réglez en toute sécurité via Mobile Money (MTN, Moov…).'],
                    ['n'=>'03','icon'=>'📍','title'=>'Localisez','desc'=>'Donnez votre adresse. Notre GPS repère votre position exacte.'],
                    ['n'=>'04','icon'=>'🚚','title'=>'Recevez','desc'=>'Votre livreur part avec l\'itinéraire tracé jusqu\'à votre porte.'],
                ];
                foreach ($steps as $step): ?>
                <div class="section-card fm-step-card fm-reveal">
                    <span class="fm-step-num"><?= $step['n'] ?></span>
                    <span class="fm-step-icon"><?= $step['icon'] ?></span>
                    <h3 class="fm-step-title"><?= $step['title'] ?></h3>
                    <p class="fm-step-desc"><?= $step['desc'] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════
             OFFRES (deux cartes côte à côte)
        ════════════════════════════════════════════ -->
        <section aria-label="Nos offres">
            <p class="fm-overline">Ce que nous proposons</p>
            <h2 class="fm-section-title mt-1 mb-8">Conçu pour vous</h2>
            <div class="grid gap-6 md:grid-cols-2">

                <div class="section-card fm-offer-card fm-offer-green fm-reveal">
                    <div class="fm-offer-icon">🛒</div>
                    <h3 class="fm-offer-title">Pour les clients</h3>
                    <p class="fm-offer-desc">Parcourez, commandez et suivez vos achats de produits frais d'élevage en toute simplicité, où que vous soyez.</p>
                    <ul class="fm-checklist">
                        <li>Produits frais garantis à chaque commande</li>
                        <li>Prix directs sans intermédiaire</li>
                        <li>Suivi GPS de votre livraison en temps réel</li>
                    </ul>
                    <a href="index.php?action=products" class="btn-primary mt-auto">Commander maintenant →</a>
                </div>

                <div class="section-card fm-offer-card fm-offer-orange fm-reveal">
                    <div class="fm-offer-icon">🚚</div>
                    <h3 class="fm-offer-title">Livraison à domicile</h3>
                    <p class="fm-offer-desc">Recevez vos produits frais directement chez vous. Notre livreur dispose des coordonnées GPS exactes dès la commande.</p>
                    <ul class="fm-checklist">
                        <li>Livraison rapide sous 24h</li>
                        <li>Produits conservés au frais pendant le transport</li>
                        <li>Itinéraire GPS calculé automatiquement</li>
                    </ul>
                    <a href="index.php?action=delivery/how-it-works" class="btn-secondary mt-auto">En savoir plus →</a>
                </div>

            </div>
        </section>

        <!-- ═══════════════════════════════════════════
             CTA FINAL
        ════════════════════════════════════════════ -->
        <section class="fm-cta-banner text-center" aria-label="Appel à l'action">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">
                Prêt à commander vos produits frais ?
            </h2>
            <p class="text-emerald-100 mb-6 max-w-lg mx-auto">
                Rejoignez Farmmarket et profitez d'une expérience d'achat simple, locale et transparente.
            </p>
            <a id="homeCatalogBtn" href="index.php?action=products" class="btn-secondary home-catalog-btn">
                Voir le catalogue complet
            </a>
        </section>

    </div><!-- /max-w-6xl -->
</div>

<!-- Inline styles moved to public/css/style.css -->
<!-- ═══════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════ -->
<script>
(function () {
    // ── Hero slideshow ──────────────────────────
    var images = <?= $hero_images_json ?? '[]' ?>;
    var bg     = document.getElementById('hero-bg');
    var dotsEl = document.getElementById('hero-dots');
    var idx    = 0;

    if (images.length > 1) {
        // Build dots
        images.forEach(function (_, i) {
            var d = document.createElement('button');
            d.className = 'fm-dot' + (i === 0 ? ' active' : '');
            d.setAttribute('aria-label', 'Image ' + (i + 1));
            d.addEventListener('click', function () { goTo(i); });
            dotsEl.appendChild(d);
        });

        function goTo(n) {
            idx = (n + images.length) % images.length;
            bg.style.opacity = '0';
            setTimeout(function () {
                bg.style.backgroundImage = "url('" + images[idx] + "')";
                bg.style.opacity = '1';
            }, 350);
            document.querySelectorAll('.fm-dot').forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
            });
        }

        document.getElementById('heroNext').addEventListener('click', function () { goTo(idx + 1); });
        document.getElementById('heroPrev').addEventListener('click', function () { goTo(idx - 1); });
        setInterval(function () { goTo(idx + 1); }, 5000);
    } else {
        // Hide arrows if only one image
        document.getElementById('heroNext').style.display = 'none';
        document.getElementById('heroPrev').style.display = 'none';
    }

    // ── Scroll reveal ───────────────────────────
    var revealEls = document.querySelectorAll('.fm-reveal');
    var observer  = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    revealEls.forEach(function (el) { observer.observe(el); });

    // ── Catalog button carries category ─────────
    var catalogBtn = document.getElementById('homeCatalogBtn');
    var catSelect  = document.getElementById('homeCategoryFilter');
    if (catalogBtn && catSelect) {
        catalogBtn.addEventListener('click', function (e) {
            var cat = catSelect.value;
            var url = 'index.php?action=products' + (cat ? '&category=' + encodeURIComponent(cat) : '');
            window.location.href = url;
            e.preventDefault();
        });
    }
})();
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>