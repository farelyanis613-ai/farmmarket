<?php require __DIR__ . '/../partials/header.php'; ?>

<div class="fm-orders-wrap pb-16">

    <!-- ── En-tête page ───────────────────────────────── -->
    <div class="fm-page-header">
        <div>
            <h1 class="fm-page-title">Mes commandes</h1>
            <p class="fm-page-sub">Retrouvez l'historique de toutes vos commandes Farmmarket</p>
        </div>
        <a href="index.php?action=products" class="fm-btn-primary">
            + Nouvelle commande
        </a>
    </div>

    <!-- ── Message succès ─────────────────────────────── -->
    <?php if (!empty($_SESSION['success'])) : ?>
        <div class="fm-alert-success">
            <span>✓</span> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (empty($orders)) : ?>

        <!-- ── État vide ───────────────────────────────── -->
        <div class="fm-empty-state">
            <div class="fm-empty-icon">🛒</div>
            <h2 class="fm-empty-title">Aucune commande pour le moment</h2>
            <p class="fm-empty-sub">Vous n'avez pas encore passé de commande. Découvrez nos produits frais d'élevage.</p>
            <a href="index.php?action=products" class="fm-btn-primary">Découvrir nos produits</a>
        </div>
</div>

<!-- Inline styles moved to public/css/style.css -->
<!-- views/cart/orders.php: original <style> block consolidated into public/css/style.css -->

/* ── En-tête ──────────────────────────────────── */
.fm-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.fm-page-title {
    font-size: clamp(1.4rem, 3vw, 1.9rem);
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.fm-page-sub {
    font-size: 0.88rem;
    color: #64748b;
    margin: 4px 0 0;
}

/* ── Boutons ──────────────────────────────────── */
.fm-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.65rem 1.4rem;
    border-radius: 9999px;
    background: #16a34a;
    color: #fff;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background 180ms, transform 180ms;
}
.fm-btn-primary:hover { background: #15803d; transform: translateY(-1px); }

.fm-btn-sm {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 150ms;
    white-space: nowrap;
}
.fm-btn-green  { background: #16a34a; color: #fff; }
.fm-btn-green:hover  { background: #15803d; }
.fm-btn-indigo { background: #4f46e5; color: #fff; }
.fm-btn-indigo:hover { background: #4338ca; }

/* ── Alerte succès ────────────────────────────── */
.fm-alert-success {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
    border-radius: 12px;
    padding: 0.85rem 1.25rem;
    font-size: 0.9rem;
    font-weight: 500;
}

/* ── État vide ────────────────────────────────── */
.fm-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 12px;
    padding: 4rem 2rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
}
.fm-empty-icon  { font-size: 3.5rem; }
.fm-empty-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; }
.fm-empty-sub   { font-size: 0.9rem; color: #64748b; max-width: 34ch; margin: 0; }

/* ── Filtres ──────────────────────────────────── */
.fm-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.fm-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.4rem 1rem;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 150ms;
}
.fm-filter-pill:hover { border-color: #16a34a; color: #15803d; }
.fm-filter-active { background: #16a34a; border-color: #16a34a; color: #fff; font-weight: 600; }
.fm-filter-active:hover { color: #fff; }
.fm-filter-count {
    background: rgba(0,0,0,0.12);
    border-radius: 9999px;
    padding: 0 6px;
    font-size: 0.73rem;
    font-weight: 700;
}
.fm-filter-active .fm-filter-count { background: rgba(255,255,255,0.25); }

/* ── Résumé ───────────────────────────────────── */
.fm-summary-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
@media (max-width: 600px) { .fm-summary-row { grid-template-columns: repeat(2,1fr); } }
.fm-summary-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-align: center;
}
.fm-summary-icon { font-size: 1.4rem; }
.fm-summary-val  { font-size: 1.4rem; font-weight: 800; color: #15803d; line-height: 1; }
.fm-summary-lbl  { font-size: 0.73rem; color: #64748b; font-weight: 500; }

/* ── Grille commandes ─────────────────────────── */
.fm-orders-grid {
    display: grid;
    gap: 16px;
    grid-template-columns: 1fr;
}
@media (min-width: 768px)  { .fm-orders-grid { grid-template-columns: repeat(2,1fr); } }
@media (min-width: 1100px) { .fm-orders-grid { grid-template-columns: repeat(3,1fr); } }

/* ── Carte commande ───────────────────────────── */
.fm-order-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 14px;
    transition: box-shadow 200ms, transform 200ms;
}
.fm-order-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,0.09); transform: translateY(-2px); }

/* En-tête carte */
.fm-order-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
}
.fm-order-id   { font-size: 1rem; font-weight: 700; color: #0f172a; }
.fm-order-date { font-size: 0.78rem; color: #94a3b8; margin-top: 2px; }

/* Badges statut */
.fm-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    white-space: nowrap;
}
.fm-badge-pending    { background: #fef9c3; color: #854d0e; }
.fm-badge-confirmed  { background: #dbeafe; color: #1e40af; }
.fm-badge-delivering { background: #ffedd5; color: #9a3412; }
.fm-badge-delivered  { background: #dcfce7; color: #166534; }
.fm-badge-cancelled  { background: #fee2e2; color: #991b1b; }

/* Barre de progression */
.fm-progress-bar {
    display: flex;
    align-items: center;
    gap: 0;
}
.fm-prog-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    flex-shrink: 0;
}
.fm-prog-dot {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
    color: #94a3b8;
}
.fm-prog-label {
    font-size: 0.6rem;
    color: #94a3b8;
    font-weight: 500;
    white-space: nowrap;
}
.fm-prog-line {
    flex: 1;
    height: 2px;
    background: #e2e8f0;
    margin-bottom: 16px;
}
.fm-prog-line-done { background: #16a34a; }
.fm-prog-done .fm-prog-dot  { background: #16a34a; border-color: #16a34a; color: #fff; }
.fm-prog-done .fm-prog-label{ color: #16a34a; }
.fm-prog-active .fm-prog-dot { background: #fff; border-color: #16a34a; color: #16a34a; font-size: 0.85rem; box-shadow: 0 0 0 3px #dcfce7; }
.fm-prog-active .fm-prog-label { color: #15803d; font-weight: 700; }

/* Bannière annulée */
.fm-cancelled-banner {
    background: #fee2e2;
    color: #991b1b;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    font-size: 0.82rem;
    font-weight: 600;
    text-align: center;
}

/* Infos livraison */
.fm-order-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 10px 12px;
    background: #f8fafc;
    border-radius: 10px;
    font-size: 0.83rem;
    color: #475569;
}
.fm-order-info-row {
    display: flex;
    align-items: flex-start;
    gap: 7px;
}
.fm-info-icon { font-size: 0.9rem; flex-shrink: 0; margin-top: 1px; }

/* Pied carte */
.fm-order-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
    flex-wrap: wrap;
}
.fm-order-total {
    font-size: 1.3rem;
    font-weight: 800;
    color: #15803d;
    line-height: 1;
}
.fm-fcfa { font-size: 0.78rem; font-weight: 500; color: #64748b; }
.fm-order-actions { display: flex; gap: 8px; flex-wrap: wrap; }

/* ── Reveal scroll ────────────────────────────── */
.fm-reveal {
    opacity: 0;
    transform: translateY(14px);
    transition: opacity 0.45s ease, transform 0.45s ease;
}
.fm-reveal.visible { opacity: 1; transform: translateY(0); }
@media (prefers-reduced-motion: reduce) {
    .fm-reveal { opacity: 1; transform: none; transition: none; }
}

<script>
(function(){
    // Scroll reveal
    var els = document.querySelectorAll('.fm-reveal');
    var obs = new IntersectionObserver(function(entries){
        entries.forEach(function(e){
            if(e.isIntersecting){ e.target.classList.add('visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.1 });
    els.forEach(function(el){ obs.observe(el); });
})();
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>